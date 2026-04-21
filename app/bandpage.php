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

// Fetch stats server-side (avoids CORS when running on localhost)
$gpStats       = ['views' => null, 'followers' => null, 'streams' => null, 'likes' => null, 'shares' => null, 'plays' => null];
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

    // $ch = curl_init('https://nonliminal.plscd.com/api/band_stats.json');
    // curl_setopt_array($ch, [
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_TIMEOUT        => 5,
    //     CURLOPT_SSL_VERIFYPEER => false,
    //     CURLOPT_USERAGENT      => 'GitPushPlay/1.0',
    // ]);
    // $rawStats = curl_exec($ch);
    // curl_close($ch);

    // if ($rawStats !== false) {
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

    // Upcoming events for this band (next 3, future dates only)
    $eventsFilePath = __DIR__ . '/secure/events.json';
    if (file_exists($eventsFilePath)) {
        $allEvents = json_decode(@file_get_contents($eventsFilePath), true) ?: [];
        $today = date('Y-m-d');
        $upcomingEvents = array_values(array_filter(
            $allEvents,
            fn($e) => ($e['band_id'] ?? '') === $gpBand['id'] && ($e['date'] ?? '') >= $today
        ));
        usort($upcomingEvents, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        $upcomingEvents = array_slice($upcomingEvents, 0, 3);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
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

    <!-- ── Band Hero ── -->
    <div class="card band-hero">
        <div class="band-hero-inner">
            <div class="band-hero-content">
                <div class="band-hero-meta-top">
                    <?php if (!empty($gpBand['genre'])): ?>
                    <span class="genre-pill"><?= htmlspecialchars($gpBand['genre']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($gpBand['formedYear'])): ?>
                    <span class="meta-item">Est. <?= htmlspecialchars($gpBand['formedYear']) ?></span>
                    <?php endif; ?>
                    <span class="meta-item"><?= $memberCount ?> member<?= $memberCount !== 1 ? 's' : '' ?></span>
                </div>
                <h1 class="band-hero-name"><?= htmlspecialchars($gpBand['name']) ?></h1>
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
                <div class="avatar-stack-item" title="<?= htmlspecialchars($dn) ?>">
                    <?php if ($hp): ?>
                        <img src="<?= htmlspecialchars($pi) ?>" alt="<?= htmlspecialchars($dn) ?>">
                    <?php else: ?>
                        <?= $in ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if ($memberCount > 5): ?>
                <div class="avatar-stack-item avatar-stack-more">+<?= $memberCount - 5 ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($gpIsOwner): ?>
        <div class="band-hero-footer">
            <div class="hero-invite">
                <span class="join-code-label">Invite Code</span>
                <span class="join-code"><?= htmlspecialchars($gpBand['joinCode']) ?></span>
            </div>
            <a href="public-band.php?code=<?= urlencode($gpBand['joinCode']) ?>" target="_blank" class="hero-public-btn">
                View Public Page →
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Stats ── -->
    <div class="card">
        <h2>Band Stats</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['views'] ?? 0) ?>"><?= $gpStats['views'] !== null ? number_format($gpStats['views']) : '—' ?></div>
                <div class="stat-label">Profile Views</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['followers'] ?? 0) ?>"><?= $gpStats['followers'] !== null ? number_format($gpStats['followers']) : '—' ?></div>
                <div class="stat-label">Followers</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['streams'] ?? 0) ?>"><?= $gpStats['streams'] !== null ? number_format($gpStats['streams']) : '—' ?></div>
                <div class="stat-label">Streams</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['likes'] ?? 0) ?>"><?= $gpStats['likes'] !== null ? number_format($gpStats['likes']) : '—' ?></div>
                <div class="stat-label">Likes</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['shares'] ?? 0) ?>"><?= $gpStats['shares'] !== null ? number_format($gpStats['shares']) : '—' ?></div>
                <div class="stat-label">Shares</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" data-target="<?= (int)($gpStats['plays'] ?? 0) ?>"><?= $gpStats['plays'] !== null ? number_format($gpStats['plays']) : '—' ?></div>
                <div class="stat-label">Total Plays</div>
            </div>
        </div>
    </div>

    <!-- ── Two-col: Announcements + Upcoming Events ── -->
    <div>

        <!-- Announcements -->
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

        <!-- Upcoming Events -->
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
                        <span class="event-day"><?= $evtDay ?></span>
                        <span class="event-mon"><?= $evtMon ?></span>
                    </div>
                    <div class="event-info">
                        <div class="event-title"><?= htmlspecialchars($evt['title']) ?></div>
                        <div class="event-meta"><?= $evtTime ?><?php if (!empty($evt['description'])): ?><?= $evtTime ? ' · ' : '' ?><?php $d = $evt['description']; echo htmlspecialchars(strlen($d) > 55 ? substr($d, 0, 55) . '…' : $d); ?><?php endif; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <a href="schedule.php" class="events-link">View full schedule →</a>
        </div>

    </div>

    <!-- ── Members ── -->
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
            ?>
            <div class="member-card">
                <div class="member-card-avatar">
                    <?php if ($hasPhoto): ?>
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="<?= htmlspecialchars($displayName) ?>">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <div class="member-card-name"><?= htmlspecialchars($displayName) ?></div>
                <?php if ($instrument): ?>
                <div class="member-card-instrument"><?= htmlspecialchars($instrument) ?></div>
                <?php endif; ?>
                <span class="member-role"><?= htmlspecialchars($member['role'] ?? 'member') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ──  Music ── -->
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

/* ── Track state & utilities (disabled) ──────────────────────────────────────
// ── State ─────────────────────────────────────────────────────────────────────
let tracks = [], currentId = null, currentAudio = null, progressRaf = null;

function fmt(s) {
    s = Math.max(0, Math.floor(s));
    return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
}

function genWave(n, seed) {
    return Array.from({ length: n }, (_, i) =>
        Math.round(Math.abs(Math.sin(seed * 0.9 + i * 0.38 + i * i * 0.008)) * 22 + 5 + Math.abs(Math.cos(i * 0.7 + seed)) * 7)
    );
}

function seedFromId(id) {
    return id.split('').reduce((acc, c) => acc + c.charCodeAt(0), 0);
}
────────────────────────────────────────────────────────────────────────────── */

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, t => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[t]));
}

/* ── Track playback, server sync, render, upload zone (disabled) ─────────────
// ── Playback ──────────────────────────────────────────────────────────────────
function stopCurrent() {
    if (currentAudio) { currentAudio.pause(); currentAudio = null; }
    if (progressRaf)  { cancelAnimationFrame(progressRaf); progressRaf = null; }
    currentId = null;
}

function tick(id) {
    const t = tracks.find(t => t.id === id);
    if (!t || !currentAudio) return;
    const cur = currentAudio.currentTime, dur = currentAudio.duration || t.duration;
    updateProgress(id, cur, dur);
    if (!currentAudio.paused && cur < dur) progressRaf = requestAnimationFrame(() => tick(id));
    else if (cur >= dur) { stopCurrent(); render(); }
}

function updateProgress(id, cur, dur) {
    const pf = document.getElementById('pf-' + id);
    const ct = document.getElementById('cur-' + id);
    if (!pf || !ct) return;
    pf.style.width = (dur > 0 ? Math.min(100, (cur / dur) * 100) : 0) + '%';
    ct.textContent = fmt(cur);
    const wf = document.getElementById('wf-' + id);
    if (wf) {
        const bars = wf.querySelectorAll('.wf-bar');
        const head = Math.floor((cur / dur) * bars.length);
        bars.forEach((b, i) => { b.className = 'wf-bar' + (i < head ? ' wf-played' : i === head ? ' wf-head' : ''); });
    }
}

function togglePlay(id) {
    const t = tracks.find(t => t.id === id);
    if (!t) return;
    if (currentId === id) {
        if (currentAudio && !currentAudio.paused) { currentAudio.pause(); cancelAnimationFrame(progressRaf); }
        else if (currentAudio) { currentAudio.play(); progressRaf = requestAnimationFrame(() => tick(id)); }
        render(); return;
    }
    stopCurrent();
    const audio = new Audio(t.url);
    audio.volume = 0.8;
    currentAudio = audio; currentId = id;
    audio.addEventListener('loadedmetadata', () => { t.duration = audio.duration; render(); });
    audio.play()
        .then(() => { progressRaf = requestAnimationFrame(() => tick(id)); render(); })
        .catch(() => { stopCurrent(); render(); });
    render();
}

function seek(e, id) {
    const t = tracks.find(t => t.id === id);
    if (!t) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    const dur  = (currentAudio && currentId === id ? currentAudio.duration : null) || t.duration;
    if (currentAudio && currentId === id) currentAudio.currentTime = pct * dur;
    updateProgress(id, pct * dur, dur);
}

function setVol(v, id) {
    if (currentId === id && currentAudio) currentAudio.volume = v / 100;
}

// ── Server sync ───────────────────────────────────────────────────────────────
async function loadTracks() {
    try {
        const res  = await fetch('assets/libs/track-upload.php');
        const data = await res.json();
        tracks = data.map(t => ({
            id:       t.id,
            title:    t.title,
            filename: t.original_name,
            duration: 0,
            url:      'uploads/' + encodeURIComponent(t.band_id) + '/' + encodeURIComponent(t.filename),
            wave:     genWave(60, seedFromId(t.id)),
            uploadedBy: t.uploaded_by ?? '',
        }));
        render();
    } catch (e) {
        document.getElementById('trackList').innerHTML = '<p class="muted-text">Could not load tracks.</p>';
    }
}

async function handleFiles(files) {
    for (const file of Array.from(files)) {
        if (!file.type.startsWith('audio/')) continue;

        const fd = new FormData();
        fd.append('track', file);
        fd.append('title', file.name.replace(/\.[^.]+$/, ''));
        fd.append('csrf_token', csrfToken);

        try {
            const res    = await fetch('assets/libs/track-upload.php', { method: 'POST', body: fd });
            const result = await res.json();
            if (!result.success) { alert(result.message); }
        } catch (e) {
            alert('Upload failed: ' + file.name);
        }
    }
    await loadTracks();
}

async function removeTrack(id) {
    if (!confirm('Delete this track? This cannot be undone.')) return;
    if (currentId === id) stopCurrent();

    const res    = await fetch('assets/libs/track-upload.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body:    JSON.stringify({ action: 'delete', id }),
    });
    const result = await res.json();
    if (result.success) {
        tracks = tracks.filter(t => t.id !== id);
        render();
    } else {
        alert(result.message);
    }
}

// ── Render ────────────────────────────────────────────────────────────────────
function render() {
    const list = document.getElementById('trackList');
    if (tracks.length === 0) {
        list.innerHTML = '<p class="muted-text">No tracks uploaded yet.</p>';
        return;
    }
    list.innerHTML = tracks.map(t => {
        const playing  = currentId === t.id && currentAudio && !currentAudio.paused;
        const active   = currentId === t.id;
        const dur      = (active && currentAudio?.duration) ? currentAudio.duration : t.duration;
        const curT     = (active && currentAudio) ? currentAudio.currentTime : 0;
        const pct      = dur > 0 ? Math.min(100, (curT / dur) * 100) : 0;
        const head     = dur > 0 ? Math.floor((curT / dur) * t.wave.length) : 0;
        const playIcon = playing
            ? `<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1" fill="currentColor"/><rect x="14" y="4" width="4" height="16" rx="1" fill="currentColor"/></svg>`
            : `<svg viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21" fill="currentColor"/></svg>`;

        return `
        <div class="track-item${active ? ' is-playing' : ''}" id="track-${t.id}">
            <div class="card-top">
                <button class="play-btn" onclick="togglePlay('${t.id}')">${playIcon}</button>
                <div class="track-info">
                    <div class="track-title">${escapeHTML(t.title)}</div>
                    <div class="track-meta">${escapeHTML(t.filename)}${dur > 0 ? ' &nbsp;·&nbsp; ' + fmt(dur) : ''}${t.uploadedBy ? ' &nbsp;·&nbsp; ' + escapeHTML(t.uploadedBy) : ''}</div>
                </div>
                <button class="track-delete" onclick="removeTrack('${t.id}')" title="Remove">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                </button>
            </div>
            <div class="waveform" id="wf-${t.id}" onclick="seek(event, '${t.id}')">
                ${t.wave.map((h, i) => `<div class="wf-bar${i < head ? ' wf-played' : i === head && active ? ' wf-head' : ''}" style="height:${h}px"></div>`).join('')}
            </div>
            <div class="progress-row">
                <span class="time" id="cur-${t.id}">${fmt(curT)}</span>
                <div class="progress-track" onclick="seek(event, '${t.id}')">
                    <div class="progress-fill" id="pf-${t.id}" style="width:${pct}%"></div>
                </div>
                <span class="time right">${dur > 0 ? fmt(dur) : '--:--'}</span>
            </div>
            <div class="vol-row">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 010 7.07"/></svg>
                <input type="range" class="vol" min="0" max="100" step="1" value="80"
                    oninput="setVol(this.value,'${t.id}');document.getElementById('vv-${t.id}').textContent=this.value+'%'" />
                <span class="vol-val" id="vv-${t.id}">80%</span>
            </div>
        </div>`;
    }).join('');
}

// ── Upload zone events ────────────────────────────────────────────────────────
const fileInput  = document.getElementById('fileInput');
const uploadZone = document.getElementById('uploadZone');

fileInput.addEventListener('change', e => handleFiles(e.target.files));
uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });

loadTracks();
────────────────────────────────────────────────────────────────────────────── */

// ── Announcements ─────────────────────────────────────────────────────────────
const isOwner = <?= $gpIsOwner ? 'true' : 'false' ?>;

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

// ── Sheet Music ───────────────────────────────────────────────────────────────
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
