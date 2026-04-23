<?php include 'includes/_login.php'; ?>
<?php include 'includes/_pullinfo.php'; ?>
<?php
$title       = "Band Dashboard";
$description = "Manage your band stats and tracks.";

require_once __DIR__ . '/assets/libs/data.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$memberCount = count($gpBandMembers);

$gpStats        = ['views' => null, 'followers' => null, 'streams' => null, 'likes' => null, 'shares' => null, 'plays' => null];
$upcomingEvents = [];

if ($gpBand) {
    $allBands  = read_bands();
    $bandApiId = 1;
    foreach (array_values($allBands) as $i => $b) {
        if ($b['id'] === $gpBand['id']) {
            $bandApiId = $i + 1;
            break;
        }
    }

    $rawStats = @file_get_contents('./api/band_stats.json');
    $allStats = json_decode($rawStats, true);
    if (is_array($allStats)) {
        if (isset($allStats['band_id'])) {
            $allStats = [$allStats];
        }
        foreach ($allStats as $s) {
            if (($s['band_id'] ?? null) == $bandApiId) {
                $gpStats = [
                    'views'     => $s['views']     ?? 0,
                    'followers' => $s['followers'] ?? 0,
                    'streams'   => $s['streams']   ?? 0,
                    'likes'     => $s['likes']     ?? 0,
                    'shares'    => $s['shares']    ?? 0,
                    'plays'     => $s['plays']     ?? 0,
                ];
                break;
            }
        }
    }

    $eventsFilePath = __DIR__ . '/secure/events.json';
    if (file_exists($eventsFilePath)) {
        $allEvents = json_decode(@file_get_contents($eventsFilePath), true) ?: [];
        $today = date('Y-m-d');
        foreach ($allEvents as $e) {
            if (($e['band_id'] ?? '') === $gpBand['id'] && ($e['date'] ?? '') >= $today) {
                $upcomingEvents[] = $e;
            }
        }
        usort($upcomingEvents, function($a, $b) {
            return strcmp($a['date'] ?? '', $b['date'] ?? '');
        });
        $upcomingEvents = array_slice($upcomingEvents, 0, 3);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/band-page.css">
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="dashboard">

<?php if (!$gpBand): ?>

    <div class="card no-band-notice">
        <h2>No Band Yet</h2>
        <p>Create or join a band from settings to manage your dashboard.</p>
        <a href="settings.php" class="primary-btn">Go to Settings</a>
    </div>

<?php else: ?>

    <div class="card band-hero">
        <div class="band-hero-inner">
            <div class="band-hero-content">
                <div class="band-hero-meta-top">
                    <?php if (!empty($gpBand['genre'])): ?>
                    <span class="meta-item"><?php echo htmlspecialchars($gpBand['genre']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($gpBand['formedYear'])): ?>
                    <span class="meta-item">Est. <?php echo htmlspecialchars($gpBand['formedYear']); ?></span>
                    <?php endif; ?>
                    <span class="meta-item"><?php echo $memberCount; ?> member<?php echo $memberCount !== 1 ? 's' : ''; ?></span>
                </div>
                <h1 class="band-hero-name"><?php echo htmlspecialchars($gpBand['name']); ?></h1>
            </div>
            <div class="avatar-stack">
                <?php foreach (array_slice($gpBandMembers, 0, 5) as $i => $member): ?>
                <?php
                    $m  = $member['musician'] ?? [];
                    $dn = trim($m['stageName'] ?? '') ?: trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? '')) ?: 'Unknown';
                    $in = strtoupper(substr($dn, 0, 1));
                    $pi = $m['profileImage'] ?? '';
                    $hp = $pi !== '' && file_exists(__DIR__ . '/' . $pi);
                ?>
                <div class="avatar-stack-item" title="<?php echo htmlspecialchars($dn); ?>">
                    <?php if ($hp): ?>
                        <img src="<?php echo htmlspecialchars($pi); ?>" alt="<?php echo htmlspecialchars($dn); ?>">
                    <?php else: ?>
                        <?php echo $in; ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if ($memberCount > 5): ?>
                <div class="avatar-stack-item avatar-stack-more">+<?php echo $memberCount - 5; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="band-hero-footer">
            <div class="hero-invite">
                <span class="join-code-label">Invite Code</span>
                <span class="join-code"><?php echo htmlspecialchars($gpBand['joinCode']); ?></span>
            </div>
            <a href="public-band.php?name=<?php echo urlencode($gpBand['name']); ?>" target="_blank" class="hero-public-btn">
                View Public Page →
            </a>
        </div>
        
    </div>

    <div class="card">
        <h2>Band Stats</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['views'] ?? 0); ?>"><?php echo $gpStats['views'] !== null ? number_format($gpStats['views']) : '—'; ?></div>
                <div class="stat-label">Profile Views</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['followers'] ?? 0); ?>"><?php echo $gpStats['followers'] !== null ? number_format($gpStats['followers']) : '—'; ?></div>
                <div class="stat-label">Followers</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['streams'] ?? 0); ?>"><?php echo $gpStats['streams'] !== null ? number_format($gpStats['streams']) : '—'; ?></div>
                <div class="stat-label">Streams</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['likes'] ?? 0); ?>"><?php echo $gpStats['likes'] !== null ? number_format($gpStats['likes']) : '—'; ?></div>
                <div class="stat-label">Likes</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['shares'] ?? 0); ?>"><?php echo $gpStats['shares'] !== null ? number_format($gpStats['shares']) : '—'; ?></div>
                <div class="stat-label">Shares</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?php echo (int)($gpStats['plays'] ?? 0); ?>"><?php echo $gpStats['plays'] !== null ? number_format($gpStats['plays']) : '—'; ?></div>
                <div class="stat-label">Total Plays</div>
            </div>
        </div>
    </div>

    <div>

        <div class="card">
            <h2>
                Announcements
                <?php if ($gpIsOwner): ?>
                <button id="toggleAnnForm" style="float:right;font-size:0.75rem;padding:4px 14px;border-radius:20px;background:rgba(170,131,246,0.2);border:1px solid rgba(170,131,246,0.4);color:#fff;cursor:pointer;letter-spacing:0.05em;">+ New</button>
                <?php endif; ?>
            </h2>

            <?php if ($gpIsOwner): ?>
            <div id="annForm" style="display:none;margin-bottom:20px;background:rgba(255,255,255,0.04);border:0.5px solid rgba(255,255,255,0.1);border-radius:10px;padding:16px 18px;">
                <input type="text" id="annTitle" placeholder="Announcement title" style="width:100%;box-sizing:border-box;padding:0.6rem 0.8rem;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:lavender;font-size:0.95rem;margin-bottom:10px;">
                <textarea id="annNotes" placeholder="Notes / details (optional)" rows="3" style="width:100%;box-sizing:border-box;padding:0.6rem 0.8rem;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:lavender;font-size:0.9rem;resize:vertical;"></textarea>
                <div style="margin-top:10px;display:flex;gap:8px;">
                    <button onclick="postAnnouncement()" style="padding:6px 18px;border-radius:20px;background:rgba(170,131,246,0.25);border:1px solid rgba(170,131,246,0.5);color:lavender;cursor:pointer;font-size:0.85rem;">Post</button>
                    <button onclick="document.getElementById('annForm').style.display='none'" style="padding:6px 14px;border-radius:20px;background:transparent;border:1px solid rgba(255,255,255,0.15);color:rgba(200,184,216,0.6);cursor:pointer;font-size:0.85rem;">Cancel</button>
                </div>
                <div id="annToast" style="margin-top:8px;font-size:0.82rem;color:#fff;display:none;"></div>
            </div>
            <?php endif; ?>

            <div id="annList"><p class="muted-text">No announcements yet.</p></div>
        </div>

        <div class="card">
            <h2>Upcoming Events</h2>
            <?php if (empty($upcomingEvents)): ?>
            <p class="muted-text">No upcoming events scheduled.</p>
            <?php else: ?>
            <div class="events-list">
                <?php foreach ($upcomingEvents as $evt): ?>
                <?php
                    $evtDate = new DateTime($evt['date']);
                    $evtDay  = $evtDate->format('j');
                    $evtMon  = strtoupper($evtDate->format('M'));
                    $evtTime = '';
                    if (!empty($evt['time'])) {
                        $t = DateTime::createFromFormat('H:i', $evt['time']);
                        $evtTime = $t ? $t->format('g:i A') : $evt['time'];
                    }
                ?>
                <div class="event-item">
                    <div class="event-date-badge">
                        <span class="event-day"><?php echo $evtDay; ?></span>
                        <span class="event-mon"><?php echo $evtMon; ?></span>
                    </div>
                    <div class="event-info">
                        <div class="event-title"><?php echo htmlspecialchars($evt['title']); ?></div>
                        <div class="event-meta"><?php echo $evtTime; ?><?php if (!empty($evt['description'])): ?><?php echo $evtTime ? ' · ' : ''; ?><?php $d = $evt['description']; echo htmlspecialchars(strlen($d) > 55 ? substr($d, 0, 55) . '…' : $d); ?><?php endif; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <a href="schedule.php" class="events-link">View full schedule →</a>
        </div>

    </div>

    <div class="card">
        <h2>Members</h2>
        <div class="members-grid">
            <?php foreach ($gpBandMembers as $member): ?>
            <?php
                $m = $member['musician'] ?? [];
                $displayName = trim($m['stageName'] ?? '');
                if ($displayName === '') {
                    $displayName = trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? ''));
                }
                if ($displayName === '') $displayName = 'Unknown Member';
                $initials     = strtoupper(substr($displayName, 0, 1));
                $instrument   = $m['instrument'] ?? '';
                $profileImage = $m['profileImage'] ?? '';
                $hasPhoto     = $profileImage !== '' && file_exists(__DIR__ . '/' . $profileImage);
                $bio   = $m['bio']  ?? '';
                $city  = $m['city'] ?? '';
                $mRole = $member['role'] ?? 'member';
            ?>
            <div class="member-card">
                <div class="member-card-avatar">
                    <?php if ($hasPhoto): ?>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="<?php echo htmlspecialchars($displayName); ?>">
                    <?php else: ?>
                        <?php echo $initials; ?>
                    <?php endif; ?>
                </div>
                <div class="member-card-name"><?php echo htmlspecialchars($displayName); ?></div>
                <?php if ($instrument): ?>
                <div class="member-card-instrument"><?php echo htmlspecialchars($instrument); ?></div>
                <?php endif; ?>
                <span class="member-role"><?php echo htmlspecialchars($mRole); ?></span>
                <div class="member-hover-info" style="">
                    <div class="mhi-name"><?php echo htmlspecialchars($displayName); ?></div>
                    <span class="mhi-role"><?php echo htmlspecialchars(ucfirst($mRole)); ?></span>
                    <?php if ($city): ?>
                        <div class="mhi-city"><?php echo htmlspecialchars($city); ?></div>
                    <?php endif; ?>
                    <?php if ($bio): ?>
                        <div class="mhi-bio"><?php echo htmlspecialchars($bio); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h2>Music</h2>

        <div class="upload-zone" id="sheetUploadZone" style="margin-bottom:16px;">
            <input type="file" id="sheetFileInput" accept=".pdf">
            <div class="upload-icon-wrap">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div class="upload-text"><strong>drop a PDF here</strong> or click to browse</div>
            <div class="upload-formats"><span class="format-pill">PDF</span></div>
        </div>

        <div id="sheetList"><p class="muted-text">No music uploaded yet.</p></div>
    </div>

<?php endif; ?>
</main>

<?php if ($gpBand): ?>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, t => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[t]));
}

const isOwner = <?php echo $gpIsOwner ? 'true' : 'false'; ?>;

document.getElementById('toggleAnnForm')?.addEventListener('click', () => {
    const f = document.getElementById('annForm');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
});

async function loadAnnouncements() {
    try {
        const res  = await fetch('assets/libs/announcements.php');
        const data = await res.json();
        renderAnnouncements(data);
    } catch(e) {}
}

function renderAnnouncements(items) {
    const list = document.getElementById('annList');
    if (!items.length) { list.innerHTML = '<p class="muted-text">No announcements yet.</p>'; return; }
    list.innerHTML = items.map(a => `
        <div style="background:rgba(255,255,255,0.04);border:0.5px solid rgba(255,255,255,0.1);border-radius:10px;padding:16px 18px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div>
                    <div style="font-size:1rem;color:lavender;font-weight:600;">${escapeHTML(a.title)}</div>
                    <div style="font-size:0.75rem;color:rgba(200,184,216,0.45);margin-top:2px;">${escapeHTML(a.posted_by)} &nbsp;·&nbsp; ${new Date(a.created_at).toLocaleDateString('default',{month:'short',day:'numeric',year:'numeric'})}</div>
                </div>
                ${isOwner ? `<button onclick="deleteAnnouncement('${a.id}')" style="flex-shrink:0;background:rgba(255,68,68,0.1);color:#ffb3b3;border:1px solid rgba(255,68,68,0.3);padding:3px 9px;border-radius:6px;font-size:11px;cursor:pointer;color:white;">Delete</button>` : ''}
            </div>
            ${a.notes ? `<p style="margin:10px 0 0;font-size:0.88rem;color:rgba(200,184,216,0.75);white-space:pre-wrap;">${escapeHTML(a.notes)}</p>` : ''}
        </div>`).join('');
}

async function postAnnouncement() {
    const title = document.getElementById('annTitle').value.trim();
    const notes = document.getElementById('annNotes').value.trim();
    const toast = document.getElementById('annToast');

    if (!title) { toast.style.display='block'; toast.textContent='Title is required.'; return; }

    const res    = await fetch('assets/libs/announcements.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body:    JSON.stringify({ action: 'create', title, notes }),
    });
    const result = await res.json();
    if (result.success) {
        document.getElementById('annTitle').value = '';
        document.getElementById('annNotes').value = '';
        document.getElementById('annForm').style.display = 'none';
        loadAnnouncements();
    } else {
        toast.style.display = 'block';
        toast.textContent   = result.message;
    }
}

async function deleteAnnouncement(id) {
    if (!confirm('Delete this announcement?')) return;
    const res = await fetch('assets/libs/announcements.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body:    JSON.stringify({ action: 'delete', id }),
    });
    const result = await res.json();
    if (result.success) loadAnnouncements();
}

loadAnnouncements();

async function loadSheets() {
    try {
        const res  = await fetch('assets/libs/sheetmusic-upload.php');
        const data = await res.json();
        renderSheets(data);
    } catch(e) {}
}

function renderSheets(items) {
    const list = document.getElementById('sheetList');
    if (!items.length) { list.innerHTML = '<p class="muted-text">No sheet music uploaded yet.</p>'; return; }
    list.innerHTML = items.map(s => `
        <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.04);border:0.5px solid rgba(255,255,255,0.1);border-radius:10px;padding:13px 16px;margin-bottom:8px;">
            <svg viewBox="0 0 24 24" style="fill: white;" width="22" height="22" fill="none" stroke="#aa83f6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div style="flex:1;min-width:0;">
                <div style="font-size:0.93rem;color:lavender;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHTML(s.title)}</div>
                <div style="font-size:0.73rem;color:rgba(200,184,216,0.45);margin-top:2px;">${escapeHTML(s.uploaded_by)} &nbsp;·&nbsp; ${escapeHTML(s.original_name)}</div>
            </div>
            <a href="uploads/${encodeURIComponent(s.band_id)}/sheets/${encodeURIComponent(s.filename)}" target="_blank" style="flex-shrink:0;background:rgba(170,131,246,0.15);color:#c8a8f8;border:1px solid rgba(170,131,246,0.35);padding:4px 12px;border-radius:6px;font-size:12px;text-decoration:none;color:white;">View</a>
            <button onclick="deleteSheet('${s.id}')" style="flex-shrink:0;background:rgba(255,68,68,0.1);color:#ffb3b3;border:1px solid rgba(255,68,68,0.3);padding:4px 9px;border-radius:6px;font-size:11px;cursor:pointer;color:white;">✕</button>
        </div>`).join('');
}

async function handleSheetFile(file) {
    if (!file || file.type !== 'application/pdf') { alert('Only PDF files are allowed.'); return; }
    const fd = new FormData();
    fd.append('sheet', file);
    fd.append('title', file.name.replace(/\.pdf$/i, ''));
    fd.append('csrf_token', csrfToken);
    const res    = await fetch('assets/libs/sheetmusic-upload.php', { method: 'POST', body: fd });
    const result = await res.json();
    if (result.success) await loadSheets();
    else alert(result.message);
}

async function deleteSheet(id) {
    if (!confirm('Delete this sheet music?')) return;
    const res    = await fetch('assets/libs/sheetmusic-upload.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body:    JSON.stringify({ action: 'delete', id }),
    });
    const result = await res.json();
    if (result.success) { const data = (await (await fetch('assets/libs/sheetmusic-upload.php')).json()); renderSheets(data); }
}

const sheetInput    = document.getElementById('sheetFileInput');
const sheetZone     = document.getElementById('sheetUploadZone');
sheetInput.addEventListener('change', e => handleSheetFile(e.target.files[0]));
sheetZone.addEventListener('dragover',  e => { e.preventDefault(); sheetZone.classList.add('drag-over'); });
sheetZone.addEventListener('dragleave', () => sheetZone.classList.remove('drag-over'));
sheetZone.addEventListener('drop', e => { e.preventDefault(); sheetZone.classList.remove('drag-over'); handleSheetFile(e.dataTransfer.files[0]); });

loadSheets();
</script>

<?php endif; ?>
</body>
</html>
