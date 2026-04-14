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
$gpStats = ['views' => null, 'followers' => null, 'streams' => null, 'likes' => null, 'shares' => null, 'plays' => null];

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

    <div class="top-row">

        <div class="card band-col">
            <h1 class="band-name"><?= htmlspecialchars($gpBand['name']) ?></h1>
            <div class="band-meta">
                <?php if (!empty($gpBand['genre'])): ?>
                <span class="genre-pill"><?= htmlspecialchars($gpBand['genre']) ?></span>
                <?php endif; ?>
                <?php if (!empty($gpBand['formedYear'])): ?>
                <span class="meta-item">Est. <?= htmlspecialchars($gpBand['formedYear']) ?></span>
                <?php endif; ?>
                <span class="meta-item"><?= $memberCount ?> member<?= $memberCount !== 1 ? 's' : '' ?></span>
            </div>

            <hr class="band-divider">

            <?php foreach ($gpBandMembers as $member): ?>
            <?php
                $m = $member['musician'] ?? [];
                $displayName = trim($m['stageName'] ?? '');
                if ($displayName === '') {
                    $displayName = trim(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? ''));
                }
                if ($displayName === '') $displayName = 'Unknown Member';
                $initials   = strtoupper(substr($displayName, 0, 1));
                $instrument = $m['instrument'] ?? '';
                $sub        = $instrument;
            ?>
            <div class="member-row">
                <div class="member-avatar"><?= $initials ?></div>
                <div class="member-info">
                    <div class="member-name"><?= htmlspecialchars($displayName) ?></div>
                    <?php if ($sub): ?>
                    <div class="member-sub"><?= htmlspecialchars($sub) ?></div>
                    <?php endif; ?>
                </div>
                <span class="member-role"><?= htmlspecialchars($member['role'] ?? 'member') ?></span>
            </div>
            <?php endforeach; ?>

            <?php if ($gpIsOwner): ?>
            <div class="join-code-block">
                <span class="join-code-label">Invite Code</span>
                <span class="join-code"><?= htmlspecialchars($gpBand['joinCode']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="stats-col" style="height: 95%; margin-bottom: 24px;">
            <h2>Band Stats</h2>
            <div class="stats-items">
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['views']     !== null ? number_format($gpStats['views'])     : '—' ?></div>
                    <div class="stat-label">Profile Views</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['followers'] !== null ? number_format($gpStats['followers']) : '—' ?></div>
                    <div class="stat-label">Followers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['streams']   !== null ? number_format($gpStats['streams'])   : '—' ?></div>
                    <div class="stat-label">Streams</div>
                </div>
            </div>
            <div class="stats-items stats-items-lower">
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['likes']  !== null ? number_format($gpStats['likes'])  : '—' ?></div>
                    <div class="stat-label">Likes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['shares'] !== null ? number_format($gpStats['shares']) : '—' ?></div>
                    <div class="stat-label">Shares</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num"><?= $gpStats['plays']  !== null ? number_format($gpStats['plays'])  : '—' ?></div>
                    <div class="stat-label">Total Plays</div>
                </div>
            </div>
        </div>

    </div>

    <div class="card tracks-card">
        <h2>Tracks</h2>

        <div class="upload-zone" id="uploadZone">
            <input type="file" id="fileInput" accept="audio/*" multiple>
            <div class="upload-icon-wrap">
                <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
            </div>
            <div class="upload-text"><strong>drop audio files here</strong> or click to browse</div>
            <div class="upload-formats">
                <span class="format-pill">MP3</span>
                <span class="format-pill">WAV</span>
                <span class="format-pill">FLAC</span>
                <span class="format-pill">M4A</span>
                <span class="format-pill">OGG</span>
            </div>
        </div>

        <div id="trackList"><p class="muted-text">No tracks uploaded yet.</p></div>
    </div>

<?php endif; ?>
</main>

<?php if ($gpBand): ?>
<script>
// ── State (mirrors demos.php pattern) ─────────────────────────────────────────
let tracks = [], nextId = 1, currentId = null, currentAudio = null, progressRaf = null;

function fmt(s) {
    s = Math.max(0, Math.floor(s));
    return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
}

function genWave(n, seed) {
    return Array.from({ length: n }, (_, i) =>
        Math.round(Math.abs(Math.sin(seed * 0.9 + i * 0.38 + i * i * 0.008)) * 22 + 5 + Math.abs(Math.cos(i * 0.7 + seed)) * 7)
    );
}

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
    pf.style.width  = (dur > 0 ? Math.min(100, (cur / dur) * 100) : 0) + '%';
    ct.textContent  = fmt(cur);
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

function removeTrack(id) {
    if (currentId === id) stopCurrent();
    const i = tracks.findIndex(t => t.id === id);
    if (i > -1) { URL.revokeObjectURL(tracks[i].url); tracks.splice(i, 1); }
    render();
}

// ── File handling (same as demos.php) ─────────────────────────────────────────
function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (!file.type.startsWith('audio/')) return;
        const url  = URL.createObjectURL(file);
        const id   = nextId++;
        const seed = id * 7 + file.name.length;
        const track = { id, title: file.name.replace(/\.[^.]+$/, ''), filename: file.name, duration: 0, url, wave: genWave(60, seed) };
        const probe = new Audio(url);
        probe.addEventListener('loadedmetadata', () => { track.duration = probe.duration; render(); });
        tracks.unshift(track);
        render();
    });
}

// ── Render ────────────────────────────────────────────────────────────────────
function render() {
    const list = document.getElementById('trackList');
    if (tracks.length === 0) {
        list.innerHTML = '<p class="muted-text">No tracks uploaded yet.</p>';
        return;
    }
    list.innerHTML = tracks.map(t => {
        const playing = currentId === t.id && currentAudio && !currentAudio.paused;
        const active  = currentId === t.id;
        const dur     = (active && currentAudio?.duration) ? currentAudio.duration : t.duration;
        const curT    = (active && currentAudio) ? currentAudio.currentTime : 0;
        const pct     = dur > 0 ? Math.min(100, (curT / dur) * 100) : 0;
        const head    = dur > 0 ? Math.floor((curT / dur) * t.wave.length) : 0;
        const playIcon = playing
            ? `<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1" fill="currentColor"/><rect x="14" y="4" width="4" height="16" rx="1" fill="currentColor"/></svg>`
            : `<svg viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21" fill="currentColor"/></svg>`;

        return `
        <div class="track-item${active ? ' is-playing' : ''}" id="track-${t.id}">
            <div class="card-top">
                <button class="play-btn" onclick="togglePlay(${t.id})">${playIcon}</button>
                <div class="track-info">
                    <div class="track-title">${t.title}</div>
                    <div class="track-meta">${t.filename}${dur > 0 ? ' &nbsp;·&nbsp; ' + fmt(dur) : ''}</div>
                </div>
                <button class="track-delete" onclick="removeTrack(${t.id})" title="Remove">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                </button>
            </div>
            <div class="waveform" id="wf-${t.id}" onclick="seek(event, ${t.id})">
                ${t.wave.map((h, i) => `<div class="wf-bar${i < head ? ' wf-played' : i === head && active ? ' wf-head' : ''}" style="height:${h}px"></div>`).join('')}
            </div>
            <div class="progress-row">
                <span class="time" id="cur-${t.id}">${fmt(curT)}</span>
                <div class="progress-track" onclick="seek(event, ${t.id})">
                    <div class="progress-fill" id="pf-${t.id}" style="width:${pct}%"></div>
                </div>
                <span class="time right">${dur > 0 ? fmt(dur) : '--:--'}</span>
            </div>
            <div class="vol-row">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 010 7.07"/></svg>
                <input type="range" class="vol" min="0" max="100" step="1" value="80"
                    oninput="setVol(this.value,${t.id});document.getElementById('vv-${t.id}').textContent=this.value+'%'" />
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

render();
</script>
<?php endif; ?>
</body>
</html>
