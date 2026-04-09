<?php

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Generic helpers
// ---------------------------------------------------------------------------

function secure_path(string $filename): string
{
    return __DIR__ . '/../../secure/' . $filename;
}

function ensure_file_exists(string $path): void
{
    if (!file_exists($path)) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
    }
}

function read_json(string $path): array
{
    ensure_file_exists($path);
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function write_json(string $path, array $data): bool
{
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

function gen_id(): string
{
    return bin2hex(random_bytes(8));
}

// ---------------------------------------------------------------------------
// Musicians
// ---------------------------------------------------------------------------

function musicians_path(): string { return secure_path('musicians.json'); }

function read_musicians(): array  { return read_json(musicians_path()); }

function find_musician_by_user_id(string $userId): ?array
{
    foreach (read_musicians() as $m) {
        if (($m['userId'] ?? '') === $userId) return $m;
    }
    return null;
}

function find_musician_by_id(string $id): ?array
{
    foreach (read_musicians() as $m) {
        if (($m['id'] ?? '') === $id) return $m;
    }
    return null;
}

function create_musician(string $userId, array $fields): array
{
    if (find_musician_by_user_id($userId) !== null) {
        return ['success' => false, 'message' => 'Musician profile already exists for this user.'];
    }

    $musicians = read_musicians();
    $musicians[] = [
        'id'           => gen_id(),
        'userId'       => $userId,
        'stageName'    => trim($fields['stageName'] ?? ''),
        'firstName'    => trim($fields['firstName'] ?? ''),
        'lastName'     => trim($fields['lastName'] ?? ''),
        'city'         => trim($fields['city'] ?? ''),
        'instrument'   => trim($fields['instrument'] ?? ''),
        'bio'          => trim($fields['bio'] ?? ''),
        'profileImage' => trim($fields['profileImage'] ?? ''),
        'createdAt'    => date('c'),
    ];

    if (!write_json(musicians_path(), $musicians)) {
        return ['success' => false, 'message' => 'Could not save musician profile.'];
    }

    return ['success' => true, 'message' => 'Musician profile created.'];
}

function update_musician(string $id, array $fields): array
{
    $musicians = read_musicians();
    $allowed   = ['stageName', 'firstName', 'lastName', 'city', 'instrument', 'bio', 'profileImage'];
    $found     = false;

    foreach ($musicians as &$m) {
        if (($m['id'] ?? '') === $id) {
            foreach ($allowed as $key) {
                if (array_key_exists($key, $fields)) {
                    $m[$key] = trim((string) $fields[$key]);
                }
            }
            $found = true;
            break;
        }
    }
    unset($m);

    if (!$found) return ['success' => false, 'message' => 'Musician not found.'];
    if (!write_json(musicians_path(), $musicians)) return ['success' => false, 'message' => 'Could not save changes.'];

    return ['success' => true, 'message' => 'Musician profile updated.'];
}

// ---------------------------------------------------------------------------
// Bands
// ---------------------------------------------------------------------------

function bands_path(): string { return secure_path('bands.json'); }

function read_bands(): array  { return read_json(bands_path()); }

function find_band_by_id(string $id): ?array
{
    foreach (read_bands() as $b) {
        if (($b['id'] ?? '') === $id) return $b;
    }
    return null;
}

function generate_join_code(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code  = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function find_band_by_join_code(string $code): ?array
{
    $code = strtoupper(trim($code));
    foreach (read_bands() as $b) {
        if (strtoupper($b['joinCode'] ?? '') === $code) return $b;
    }
    return null;
}

function create_band(string $ownerMusicianId, array $fields): array
{
    $joinCode = generate_join_code();
    $attempts = 0;
    while (find_band_by_join_code($joinCode) !== null && $attempts < 10) {
        $joinCode = generate_join_code();
        $attempts++;
    }

    $bandId  = gen_id();
    $bands   = read_bands();
    $bands[] = [
        'id'              => $bandId,
        'name'            => trim($fields['name'] ?? ''),
        'genre'           => trim($fields['genre'] ?? ''),
        'ownerMusicianId' => $ownerMusicianId,
        'formedYear'      => trim($fields['formedYear'] ?? ''),
        'isActive'        => true,
        'joinCode'        => $joinCode,
        'createdAt'       => date('c'),
    ];

    if (!write_json(bands_path(), $bands)) {
        return ['success' => false, 'message' => 'Could not save band.'];
    }

    create_membership($bandId, $ownerMusicianId, 'owner', $ownerMusicianId);

    return ['success' => true, 'message' => 'Band created.', 'bandId' => $bandId];
}

function update_band(string $id, array $fields): array
{
    $bands   = read_bands();
    $allowed = ['name', 'genre', 'formedYear', 'isActive'];
    $found   = false;

    foreach ($bands as &$b) {
        if (($b['id'] ?? '') === $id) {
            foreach ($allowed as $key) {
                if (array_key_exists($key, $fields)) {
                    $b[$key] = $fields[$key];
                }
            }
            $found = true;
            break;
        }
    }
    unset($b);

    if (!$found) return ['success' => false, 'message' => 'Band not found.'];
    if (!write_json(bands_path(), $bands)) return ['success' => false, 'message' => 'Could not save changes.'];

    return ['success' => true, 'message' => 'Band updated.'];
}

function join_band_by_code(string $code, string $musicianId): array
{
    $band = find_band_by_join_code($code);

    if (!$band) {
        return ['success' => false, 'message' => 'Invalid band code. No band found.'];
    }

    $existing = array_filter(
        read_memberships(),
        fn($m) => ($m['bandId'] ?? '') === $band['id'] && ($m['musicianId'] ?? '') === $musicianId
    );

    if (!empty($existing)) {
        return ['success' => false, 'message' => 'You are already a member of this band.'];
    }

    $result = create_membership($band['id'], $musicianId, 'member', $musicianId);

    if (!$result['success']) return $result;

    return ['success' => true, 'message' => 'Joined band "' . $band['name'] . '" successfully!', 'bandId' => $band['id']];
}

// ---------------------------------------------------------------------------
// Band Memberships
// ---------------------------------------------------------------------------

function memberships_path(): string { return secure_path('band_memberships.json'); }

function read_memberships(): array  { return read_json(memberships_path()); }

function get_memberships_for_musician(string $musicianId): array
{
    return array_values(array_filter(read_memberships(), fn($m) => ($m['musicianId'] ?? '') === $musicianId));
}

function get_active_band_for_musician(string $musicianId): ?array
{
    $memberships = get_memberships_for_musician($musicianId);
    if (empty($memberships)) return null;
    foreach ($memberships as $mem) {
        if (($mem['status'] ?? '') === 'active') {
            return find_band_by_id($mem['bandId'] ?? '');
        }
    }
    return null;
}

function get_membership_for_musician_in_band(string $musicianId, string $bandId): ?array
{
    foreach (read_memberships() as $m) {
        if (($m['musicianId'] ?? '') === $musicianId && ($m['bandId'] ?? '') === $bandId) {
            return $m;
        }
    }
    return null;
}

function get_band_members(string $bandId): array
{
    $memberships = array_values(array_filter(
        read_memberships(),
        fn($m) => ($m['bandId'] ?? '') === $bandId && ($m['status'] ?? '') === 'active'
    ));

    foreach ($memberships as &$mem) {
        $mem['musician'] = find_musician_by_id($mem['musicianId'] ?? '') ?? [];
    }
    unset($mem);

    return $memberships;
}

function remove_band_member(string $membershipId): array
{
    $memberships = read_memberships();
    $found       = false;

    foreach ($memberships as &$m) {
        if (($m['id'] ?? '') === $membershipId) {
            if (($m['role'] ?? '') === 'owner') {
                return ['success' => false, 'message' => 'Cannot remove the band owner.'];
            }
            $m['status'] = 'removed';
            $found = true;
            break;
        }
    }
    unset($m);

    if (!$found) return ['success' => false, 'message' => 'Membership not found.'];
    if (!write_json(memberships_path(), $memberships)) return ['success' => false, 'message' => 'Could not save changes.'];

    return ['success' => true, 'message' => 'Member removed.'];
}

function create_membership(string $bandId, string $musicianId, string $role, string $invitedByMusicianId): array
{
    $memberships = read_memberships();
    $memberships[] = [
        'id'                  => gen_id(),
        'bandId'              => $bandId,
        'musicianId'          => $musicianId,
        'role'                => $role,
        'status'              => 'active',
        'invitedByMusicianId' => $invitedByMusicianId,
        'joinedAt'            => date('c'),
    ];

    if (!write_json(memberships_path(), $memberships)) {
        return ['success' => false, 'message' => 'Could not save membership.'];
    }

    return ['success' => true, 'message' => 'Membership created.'];
}

// ---------------------------------------------------------------------------
// Venues
// ---------------------------------------------------------------------------

function venues_path(): string { return secure_path('venues.json'); }

function read_venues(): array  { return read_json(venues_path()); }

function find_venue_by_id(string $id): ?array
{
    foreach (read_venues() as $v) {
        if (($v['id'] ?? '') === $id) return $v;
    }
    return null;
}

function create_venue(array $fields): array
{
    $venues = read_venues();
    $venues[] = [
        'id'        => gen_id(),
        'name'      => trim($fields['name'] ?? ''),
        'city'      => trim($fields['city'] ?? ''),
        'state'     => trim($fields['state'] ?? ''),
        'capacity'  => (int) ($fields['capacity'] ?? 0),
        'createdAt' => date('c'),
    ];

    if (!write_json(venues_path(), $venues)) {
        return ['success' => false, 'message' => 'Could not save venue.'];
    }

    return ['success' => true, 'message' => 'Venue created.'];
}

// ---------------------------------------------------------------------------
// Events
// ---------------------------------------------------------------------------

function events_path(): string { return secure_path('events.json'); }

function read_events(): array  { return read_json(events_path()); }

function get_events_for_band(string $bandId): array
{
    return array_values(array_filter(read_events(), fn($e) => ($e['bandId'] ?? '') === $bandId));
}

function create_event(array $fields): array
{
    $events = read_events();
    $events[] = [
        'id'        => gen_id(),
        'bandId'    => trim($fields['bandId'] ?? ''),
        'venueId'   => trim($fields['venueId'] ?? ''),
        'eventName' => trim($fields['eventName'] ?? ''),
        'eventDate' => trim($fields['eventDate'] ?? ''),
        'startTime' => trim($fields['startTime'] ?? ''),
        'endTime'   => trim($fields['endTime'] ?? ''),
        'createdAt' => date('c'),
    ];

    if (!write_json(events_path(), $events)) {
        return ['success' => false, 'message' => 'Could not save event.'];
    }

    return ['success' => true, 'message' => 'Event created.'];
}

// ---------------------------------------------------------------------------
// Linked Services
// ---------------------------------------------------------------------------

function linked_services_path(): string { return secure_path('linked_services.json'); }

function read_linked_services(): array  { return read_json(linked_services_path()); }

function get_services_for_musician(string $musicianId): array
{
    return array_values(array_filter(read_linked_services(), fn($s) => ($s['musicianId'] ?? '') === $musicianId));
}

function upsert_linked_service(string $musicianId, string $serviceName, array $fields): array
{
    $services = read_linked_services();
    $found    = false;

    foreach ($services as &$s) {
        if (($s['musicianId'] ?? '') === $musicianId && ($s['serviceName'] ?? '') === $serviceName) {
            $s['serviceUserId'] = trim($fields['serviceUserId'] ?? '');
            $s['profileUrl']    = trim($fields['profileUrl'] ?? '');
            $found = true;
            break;
        }
    }
    unset($s);

    if (!$found) {
        $services[] = [
            'id'            => gen_id(),
            'musicianId'    => $musicianId,
            'serviceName'   => $serviceName,
            'serviceUserId' => trim($fields['serviceUserId'] ?? ''),
            'profileUrl'    => trim($fields['profileUrl'] ?? ''),
            'createdAt'     => date('c'),
        ];
    }

    if (!write_json(linked_services_path(), $services)) {
        return ['success' => false, 'message' => 'Could not save linked service.'];
    }

    return ['success' => true, 'message' => 'Linked service saved.'];
}
