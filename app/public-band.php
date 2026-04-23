<?php
require_once __DIR__ . '/assets/libs/data.php';

$bandName = trim($_GET['name'] ?? '');
$code     = strtoupper(trim($_GET['code'] ?? ''));
$band     = $bandName !== '' ? find_band_by_name($bandName) : ($code !== '' ? find_band_by_join_code($code) : null);

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

    $allEvents = read_pub_events($band['id']);
    $today     = date('Y-m-d');
    $upcoming  = [];
    foreach ($allEvents as $e) {
        if (($e['date'] ?? '') >= $today && !empty($e['is_public'])) {
            $upcoming[] = $e;
        }
    }
    usort($upcoming, function($a, $b) {
        return strcmp($a['date'] . $a['time'], $b['date'] . $b['time']);
    });
    $upcoming = array_slice($upcoming, 0, 8);
}

function read_pub_events(string $bandId): array {
    $file = __DIR__ . '/secure/events.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?: [];
    $result = [];
    foreach ($data as $e) {
        if (($e['band_id'] ?? '') === $bandId) {
            $result[] = $e;
        }
    }
    return $result;
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
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/public-band.css">
    <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
</head>
<body>

<?php if ($notFound): ?>
<div class="pub-not-found">
    <h1>Band Not Found</h1>
    <p>No band found with that name.</p>
    <a class="pub-back" href="index.php">← Back to Home</a>
</div>

<?php else: ?>
<div class="pub-shell">

    <div class="pub-hero">
        <div class="pub-hero-inner">
            <div class="pub-hero-content">
                <div class="pub-meta">
                    <?php if (!empty($band['genre'])): ?>
                        <span class="pub-pill"><?php echo htmlspecialchars($band['genre']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($band['formedYear'])): ?>
                        <span class="pub-pill">Est. <?php echo htmlspecialchars($band['formedYear']); ?></span>
                    <?php endif; ?>
                    <span class="pub-pill"><?php echo count($members); ?> member<?php echo count($members) !== 1 ? 's' : ''; ?></span>
                </div>
                <h1 class="pub-band-name"><?php echo htmlspecialchars($band['name']); ?></h1>

                <?php if (!empty($services)): ?>
                <div class="pub-hero-footer">
                    <?php foreach ($services as $key => $svc): ?>
                        <?php if (!empty($svc['profileUrl'])): ?>
                        <a class="pub-service-link" href="<?php echo htmlspecialchars($svc['profileUrl']); ?>" target="_blank" rel="noopener">
                            <?php echo $serviceIcons[$key] ?? ''; ?>
                            <?php echo ucfirst(htmlspecialchars($key)); ?>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php
            $stackMembers = array_slice($members, 0, 5);
            $extra = count($members) - count($stackMembers);
            ?>
            <div class="pub-avatar-stack">
                <?php if ($extra > 0): ?>
                    <div class="pub-stack-item pub-stack-more">+<?php echo $extra; ?></div>
                <?php endif; ?>
                <?php foreach (array_reverse($stackMembers) as $sm):
                    $sm_musician = $sm['musician'] ?? [];
                    $sm_name = trim($sm_musician['stageName'] ?? '') ?: trim(($sm_musician['firstName'] ?? '') . ' ' . ($sm_musician['lastName'] ?? '')) ?: '?';
                    $sm_initial = strtoupper(substr($sm_name, 0, 1));
                    $sm_photo = !empty($sm_musician['profileImage']) ? htmlspecialchars($sm_musician['profileImage']) : null;
                ?>
                <div class="pub-stack-item" title="<?php echo htmlspecialchars($sm_name); ?>">
                    <?php if ($sm_photo): ?>
                        <img src="<?php echo $sm_photo; ?>" alt="<?php echo htmlspecialchars($sm_name); ?>">
                    <?php else: ?>
                        <?php echo $sm_initial; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="pub-stats">
        <div class="pub-stat">
            <div class="pub-stat-num"><?php echo count($members); ?></div>
            <div class="pub-stat-label">Members</div>
        </div>
        <div class="pub-stat">
            <div class="pub-stat-num"><?php echo count($upcoming); ?></div>
            <div class="pub-stat-label">Upcoming Events</div>
        </div>
    </div>

    <div class="pub-two-col">

        <div class="pub-section">
            <h2>Members</h2>
            <div class="pub-members-grid">
            <?php foreach ($members as $mem):
                $m     = $mem['musician'] ?? [];
                $name  = trim($m['stageName'] ?? '') ?: trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? '')) ?: 'Unknown';
                $initial = strtoupper(substr($name, 0, 1));
                $photo = !empty($m['profileImage']) ? htmlspecialchars($m['profileImage']) : null;
                $mBio  = $m['bio']  ?? '';
                $mCity = $m['city'] ?? '';
                $mInst = $m['instrument'] ?? '';
                $mRole = $mem['role'] ?? 'member';
            ?>
            <div class="pub-member-card">
                <div class="pub-avatar">
                    <?php if ($photo): ?>
                        <img src="<?php echo $photo; ?>" alt="<?php echo htmlspecialchars($name); ?>">
                    <?php else: ?>
                        <?php echo $initial; ?>
                    <?php endif; ?>
                </div>
                <div class="pub-member-name"><?php echo htmlspecialchars($name); ?></div>
                <?php if ($mInst): ?>
                    <div class="pub-member-sub"><?php echo htmlspecialchars($mInst); ?></div>
                <?php endif; ?>
                <div class="pub-member-hover">
                    <div class="pub-mh-name"><?php echo htmlspecialchars($name); ?></div>
                    <span class="pub-mh-role"><?php echo htmlspecialchars(ucfirst($mRole)); ?></span>
                    <?php if ($mCity): ?>
                        <div class="pub-mh-city"><?php echo htmlspecialchars($mCity); ?></div>
                    <?php endif; ?>
                    <?php if ($mBio): ?>
                        <div class="pub-mh-bio"><?php echo htmlspecialchars($mBio); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

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
                        <span class="pub-event-day"><?php echo date('j', $evDate); ?></span>
                        <span class="pub-event-mon"><?php echo date('M', $evDate); ?></span>
                    </div>
                    <div class="pub-event-info">
                        <div class="pub-event-title"><?php echo htmlspecialchars($ev['title']); ?></div>
                        <div class="pub-event-meta">
                            <?php echo !empty($ev['time']) ? htmlspecialchars($ev['time']) : ''; ?>
                            <?php if (!empty($ev['description'])): ?>
                                <?php echo !empty($ev['time']) ? ' · ' : ''; ?><?php echo htmlspecialchars(strlen($ev['description']) > 50 ? substr($ev['description'], 0, 50) . '…' : $ev['description']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>


</div>
<?php endif; ?>
</body>
</html>
