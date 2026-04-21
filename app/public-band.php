<?php
require_once __DIR__ . '/assets/libs/data.php';

$code = strtoupper(trim($_GET['code'] ?? ''));
$band = $code !== '' ? find_band_by_join_code($code) : null;

if (!$band) {
    http_response_code(404);
    $notFound = true;
} else {
    $notFound   = false;
    $members    = get_band_members($band['id']);
    $services   = [];
    foreach ($members as $mem) {
        $mid = $mem['musicianId'] ?? '';
        if ($mid) {
            foreach (get_services_for_musician($mid) as $svc) {
                if (!empty($svc['profileUrl'])) {
                    $services[$svc['serviceName']] = $svc;
                }
            }
        }
    }

    // Upcoming public events
    $allEvents = read_pub_events($band['id']);
    $today     = date('Y-m-d');
    $upcoming  = array_values(array_filter($allEvents, fn($e) => ($e['date'] ?? '') >= $today && !empty($e['is_public'])));
    usort($upcoming, fn($a, $b) => strcmp($a['date'] . $a['time'], $b['date'] . $b['time']));
    $upcoming = array_slice($upcoming, 0, 8);

    // Tracks
    $allTracks = read_pub_tracks($band['id']);

    // Announcements
    $announcements = read_pub_announcements($band['id']);

    // Years active label
    $yearsActive = '';
    if (!empty($band['formedYear'])) {
        $yrs = (int)date('Y') - (int)$band['formedYear'];
        $yearsActive = $yrs > 0 ? $yrs . ' yr' . ($yrs !== 1 ? 's' : '') : 'New';
    }
}

function read_pub_events(string $bandId): array {
    $file = __DIR__ . '/secure/events.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?: [];
    return array_values(array_filter($data, fn($e) => ($e['band_id'] ?? '') === $bandId));
}

function read_pub_tracks(string $bandId): array {
    $file = __DIR__ . '/secure/tracks.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?: [];
    return array_values(array_filter($data, fn($t) => ($t['band_id'] ?? '') === $bandId));
}

function read_pub_announcements(string $bandId): array {
    $file = __DIR__ . '/secure/announcements.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?: [];
    $items = array_values(array_filter($data, fn($a) => ($a['band_id'] ?? '') === $bandId));
    usort($items, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return array_slice($items, 0, 5);
}

$pageTitle = $band ? htmlspecialchars($band['name']) . ' | GitPushPlay' : 'Band Not Found | GitPushPlay';

$serviceIcons = [
    'spotify'    => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.586 14.424a.622.622 0 01-.857.207c-2.348-1.435-5.304-1.76-8.785-.964a.623.623 0 01-.277-1.215c3.809-.87 7.077-.496 9.712 1.115a.622.622 0 01.207.857zm1.223-2.722a.78.78 0 01-1.072.257C14.1 12.087 10.57 11.6 7.324 12.56a.78.78 0 01-.449-1.492c3.658-1.1 8.202-.567 11.31 1.562a.78.78 0 01.257 1.072zm.105-2.835C15.1 9.01 9.64 8.824 6.25 9.88a.937.937 0 01-.538-1.793C9.395 6.94 15.466 7.154 19.107 9.4a.937.937 0 01-.963 1.467z"/></svg>',
    'youtube'    => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21.543 6.498C22 8.28 22 12 22 12s0 3.72-.457 5.502c-.254.985-.997 1.76-1.938 2.022C17.896 20 12 20 12 20s-5.893 0-7.605-.476c-.945-.266-1.687-1.04-1.938-2.022C2 15.72 2 12 2 12s0-3.72.457-5.502c.254-.985.997-1.76 1.938-2.022C6.107 4 12 4 12 4s5.896 0 7.605.476c.945.266 1.687 1.04 1.938 2.022zM10 15.5l6-3.5-6-3.5v7z"/></svg>',
    'soundcloud' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M1.175 12.225c-.017 0-.034.002-.05.006a.25.25 0 00-.195.23l-.468 3.104.468 3.063a.25.25 0 00.245.218c.14 0 .25-.112.25-.25v-.001l.532-3.03-.532-3.104a.25.25 0 00-.25-.236zm2.137-.8c-.02 0-.04.003-.058.009a.3.3 0 00-.218.275l-.4 3.63.4 3.56a.3.3 0 00.276.262.3.3 0 00.3-.3v-.001l.456-3.521-.456-3.63a.3.3 0 00-.3-.284zm19.688.8c-.138 0-.25.112-.25.25v5.886c0 .138.112.25.25.25h.002a2.498 2.498 0 000-4.996 2.5 2.5 0 00-.002 0v-1.14a.25.25 0 00-.25-.25h.25zm-4.5-.8c-.165 0-.3.135-.3.3l-.35 4.114.35 3.436c0 .165.135.3.3.3s.3-.135.3-.3l.4-3.436-.4-4.114a.3.3 0 00-.3-.3z"/></svg>',
    'bandcamp'   => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M0 18.75l7.437-13.5H24l-7.438 13.5z"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    <style>
        /* ── Shell ── */
        .pub-shell {
            max-width: 1100px;
            margin: 60px auto 80px;
            padding: 0 20px;
            font-family: 'Belleza', sans-serif;
        }

        /* ── Hero ── */
        .pub-hero {
            position: relative;
            overflow: hidden;
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 18px;
            padding: 48px 48px 36px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 12px 60px rgba(0,0,0,0.5);
        }
        .pub-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 10% 80%, rgba(100,70,180,0.09) 0%, transparent 55%);
            pointer-events: none;
        }
        .pub-hero-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            position: relative;
        }
        .pub-hero-content { flex: 1; min-width: 0; }
        .pub-band-name {
            font-size: 3.2rem;
            color: #fff;
            margin: 0 0 14px;
            letter-spacing: -0.01em;
            line-height: 1.05;
        }
        .pub-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 28px; }
        .pub-pill {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        /* ── Avatar stack ── */
        .pub-avatar-stack {
            display: flex;
            flex-direction: row-reverse;
            flex-shrink: 0;
        }
        .pub-stack-item {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(40,30,60,0.9);
            border: 2.5px solid rgba(8,3,18,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: rgba(255,255,255,0.8);
            overflow: hidden;
            margin-left: -14px;
            transition: transform 0.2s;
            cursor: default;
        }
        .pub-stack-item:hover { transform: translateY(-5px); z-index: 10; }
        .pub-stack-item img { width: 100%; height: 100%; object-fit: cover; }
        .pub-stack-more {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.5);
            font-size: 0.72rem;
        }

        /* ── Streaming links row ── */
        .pub-hero-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid rgba(255,255,255,0.07);
            flex-wrap: wrap;
        }
        .pub-service-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.13);
            color: rgba(255,255,255,0.7);
            border-radius: 6px;
            padding: 5px 12px;
            text-decoration: none;
            font-size: 0.82rem;
            transition: border-color 0.2s, color 0.2s;
        }
        .pub-service-link:hover { border-color: rgba(255,255,255,0.38); color: #fff; }

        /* ── Stats strip ── */
        .pub-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 14px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .pub-stat {
            padding: 20px 10px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.07);
        }
        .pub-stat:last-child { border-right: none; }
        .pub-stat-num {
            font-family: 'Belleza', sans-serif;
            font-size: 2rem;
            color: #fff;
            line-height: 1;
            margin-bottom: 5px;
        }
        .pub-stat-label {
            font-size: 0.67rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
        }

        /* ── Glass card ── */
        .pub-section {
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 14px;
            padding: 26px 30px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
            color: lavender;
        }
        .pub-section h2 {
            font-family: 'Belleza', sans-serif;
            font-size: 1rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin: 0 0 18px;
        }

        /* ── Two-column ── */
        .pub-two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .pub-two-col > .pub-section { margin-bottom: 0; }

        /* ── Member cards ── */
        .pub-members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .pub-member-card {
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 18px 12px 14px;
            text-align: center;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .pub-member-card:hover {
            border-color: rgba(255,255,255,0.22);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .pub-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(40,30,60,0.9);
            border: 1.5px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: rgba(255,255,255,0.7);
            overflow: hidden;
            margin: 0 auto 10px;
        }
        .pub-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .pub-member-name {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 3px;
        }
        .pub-member-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.32);
        }

        /* ── Events ── */
        .pub-event-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 0;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .pub-event-item:last-child { border-bottom: none; }
        .pub-event-badge {
            flex-shrink: 0;
            width: 40px;
            text-align: center;
        }
        .pub-event-day {
            display: block;
            font-family: 'Belleza', sans-serif;
            font-size: 1.5rem;
            color: #fff;
            line-height: 1;
        }
        .pub-event-mon {
            display: block;
            font-size: 0.58rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.32);
            margin-top: 2px;
        }
        .pub-event-info { flex: 1; min-width: 0; }
        .pub-event-title {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pub-event-meta {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.32);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pub-no-events {
            font-size: 0.88rem;
            color: rgba(255,255,255,0.28);
        }

        /* ── Tracks ── */
        .pub-track-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .pub-track-row:last-child { border-bottom: none; }
        .pub-track-num {
            width: 22px;
            text-align: right;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.25);
            flex-shrink: 0;
        }
        .pub-track-info { flex: 1; min-width: 0; }
        .pub-track-title {
            font-size: 0.92rem;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pub-track-uploader {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.3);
            margin-top: 2px;
        }
        .pub-track-audio {
            flex-shrink: 0;
        }
        .pub-track-audio audio {
            height: 28px;
            width: 180px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .pub-track-audio audio:hover { opacity: 1; }

        /* ── Announcements ── */
        .pub-announce-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .pub-announce-item:last-child { border-bottom: none; }
        .pub-announce-title {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 4px;
        }
        .pub-announce-body {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.45);
            line-height: 1.55;
        }
        .pub-announce-meta {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.25);
            margin-top: 6px;
        }

        /* ── Not found ── */
        .pub-not-found {
            text-align: center;
            padding: 100px 20px;
            color: rgba(200,184,216,0.6);
        }
        .pub-not-found h1 { color: lavender; font-size: 2rem; margin-bottom: 12px; }
        .pub-back {
            display: inline-block;
            margin-top: 18px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.18);
            padding: 6px 18px;
            border-radius: 6px;
            transition: color 0.2s, border-color 0.2s;
        }
        .pub-back:hover { color: #fff; border-color: rgba(255,255,255,0.4); }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .pub-two-col { grid-template-columns: 1fr; }
            .pub-stats { grid-template-columns: repeat(2, 1fr); }
            .pub-stat:nth-child(2) { border-right: none; }
            .pub-stat:nth-child(1),
            .pub-stat:nth-child(2) { border-bottom: 1px solid rgba(255,255,255,0.07); }
            .pub-hero-inner { flex-direction: column; align-items: flex-start; }
            .pub-band-name { font-size: 2.2rem; }
            .pub-hero { padding: 30px 24px 24px; }
            .pub-track-audio audio { width: 130px; }
        }
        @media (max-width: 440px) {
            .pub-band-name { font-size: 1.8rem; }
            .pub-members-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<?php if ($notFound): ?>
<div class="pub-not-found">
    <h1>Band Not Found</h1>
    <p>No band matches that invite code.</p>
    <a class="pub-back" href="index.php">← Back to Home</a>
</div>

<?php else: ?>
<div class="pub-shell">

    <!-- ── Hero ── -->
    <div class="pub-hero">
        <div class="pub-hero-inner">
            <div class="pub-hero-content">
                <div class="pub-meta">
                    <?php if (!empty($band['genre'])): ?>
                        <span class="pub-pill"><?= htmlspecialchars($band['genre']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($band['formedYear'])): ?>
                        <span class="pub-pill">Est. <?= htmlspecialchars($band['formedYear']) ?></span>
                    <?php endif; ?>
                    <span class="pub-pill"><?= count($members) ?> member<?= count($members) !== 1 ? 's' : '' ?></span>
                </div>
                <h1 class="pub-band-name"><?= htmlspecialchars($band['name']) ?></h1>

                <?php if (!empty($services)): ?>
                <div class="pub-hero-footer">
                    <?php foreach ($services as $key => $svc): ?>
                        <?php if (!empty($svc['profileUrl'])): ?>
                        <a class="pub-service-link" href="<?= htmlspecialchars($svc['profileUrl']) ?>" target="_blank" rel="noopener">
                            <?= $serviceIcons[$key] ?? '' ?>
                            <?= ucfirst(htmlspecialchars($key)) ?>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Avatar stack -->
            <?php
            $stackMembers = array_slice($members, 0, 5);
            $extra = count($members) - count($stackMembers);
            ?>
            <div class="pub-avatar-stack">
                <?php if ($extra > 0): ?>
                    <div class="pub-stack-item pub-stack-more">+<?= $extra ?></div>
                <?php endif; ?>
                <?php foreach (array_reverse($stackMembers) as $sm):
                    $sm_musician = $sm['musician'] ?? [];
                    $sm_name = trim($sm_musician['stageName'] ?? '') ?: trim(($sm_musician['firstName'] ?? '') . ' ' . ($sm_musician['lastName'] ?? '')) ?: '?';
                    $sm_initial = strtoupper(substr($sm_name, 0, 1));
                    $sm_photo = !empty($sm_musician['profileImage']) ? htmlspecialchars($sm_musician['profileImage']) : null;
                ?>
                <div class="pub-stack-item" title="<?= htmlspecialchars($sm_name) ?>">
                    <?php if ($sm_photo): ?>
                        <img src="<?= $sm_photo ?>" alt="<?= htmlspecialchars($sm_name) ?>">
                    <?php else: ?>
                        <?= $sm_initial ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Stats strip ── -->
    <div class="pub-stats">
        <div class="pub-stat">
            <div class="pub-stat-num"><?= count($members) ?></div>
            <div class="pub-stat-label">Members</div>
        </div>
        <div class="pub-stat">
            <div class="pub-stat-num"><?= count($upcoming) ?></div>
            <div class="pub-stat-label">Upcoming Events</div>
        </div>
        <div class="pub-stat">
            <div class="pub-stat-num"><?= count($allTracks) ?></div>
            <div class="pub-stat-label">Tracks</div>
        </div>
        <div class="pub-stat">
            <div class="pub-stat-num"><?= $yearsActive ?: ($band['formedYear'] ?? '—') ?></div>
            <div class="pub-stat-label"><?= $yearsActive ? 'Active' : 'Est.' ?></div>
        </div>
    </div>

    <!-- ── Members + Events two-col ── -->
    <div class="pub-two-col">

        <!-- Members -->
        <div class="pub-section">
            <h2>Members</h2>
            <div class="pub-members-grid">
            <?php foreach ($members as $mem):
                $m = $mem['musician'] ?? [];
                $name = trim($m['stageName'] ?? '') ?: trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? '')) ?: 'Unknown';
                $initial = strtoupper(substr($name, 0, 1));
                $photo = !empty($m['profileImage']) ? htmlspecialchars($m['profileImage']) : null;
            ?>
            <div class="pub-member-card">
                <div class="pub-avatar">
                    <?php if ($photo): ?>
                        <img src="<?= $photo ?>" alt="<?= htmlspecialchars($name) ?>">
                    <?php else: ?>
                        <?= $initial ?>
                    <?php endif; ?>
                </div>
                <div class="pub-member-name"><?= htmlspecialchars($name) ?></div>
                <?php if (!empty($m['instrument'])): ?>
                    <div class="pub-member-sub"><?= htmlspecialchars($m['instrument']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="pub-section">
            <h2>Upcoming Events</h2>
            <?php if (empty($upcoming)): ?>
                <p class="pub-no-events">No upcoming public events scheduled.</p>
            <?php else: ?>
                <?php foreach ($upcoming as $ev):
                    $evDate = strtotime($ev['date']);
                ?>
                <div class="pub-event-item">
                    <div class="pub-event-badge">
                        <span class="pub-event-day"><?= date('j', $evDate) ?></span>
                        <span class="pub-event-mon"><?= date('M', $evDate) ?></span>
                    </div>
                    <div class="pub-event-info">
                        <div class="pub-event-title"><?= htmlspecialchars($ev['title']) ?></div>
                        <div class="pub-event-meta">
                            <?= !empty($ev['time']) ? htmlspecialchars($ev['time']) : '' ?>
                            <?php if (!empty($ev['description'])): ?>
                                <?= !empty($ev['time']) ? ' · ' : '' ?><?= htmlspecialchars(strlen($ev['description']) > 50 ? substr($ev['description'], 0, 50) . '…' : $ev['description']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <?php if (!empty($allTracks)): ?>
    <!-- ── Tracks ── -->
    <div class="pub-section">
        <h2>Tracks</h2>
        <?php foreach ($allTracks as $i => $track):
            $audioPath = 'uploads/' . htmlspecialchars($band['id']) . '/' . htmlspecialchars($track['filename']);
        ?>
        <div class="pub-track-row">
            <div class="pub-track-num"><?= $i + 1 ?></div>
            <div class="pub-track-info">
                <div class="pub-track-title"><?= htmlspecialchars($track['title'] ?? $track['original_name'] ?? 'Untitled') ?></div>
                <?php if (!empty($track['uploaded_by'])): ?>
                    <div class="pub-track-uploader"><?= htmlspecialchars($track['uploaded_by']) ?></div>
                <?php endif; ?>
            </div>
            <div class="pub-track-audio">
                <audio controls preload="none" src="<?= $audioPath ?>"></audio>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


</div>
<?php endif; ?>
</body>
</html>
