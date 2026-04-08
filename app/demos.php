<?php include 'includes/_login.php'; ?>
<?php
$title       = "GitPushPlay — Demos";
$description = "Upload, listen, and share your song drafts with your band.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        .page { max-width: 920px; margin: 0 auto; padding: 40px 24px 80px; font-family: "Belleza", sans-serif; }

        .page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 32px; gap: 16px; flex-wrap: wrap; }
        .page-title { font-size: 32px; font-weight: 700; color: var(--text-primary); line-height: 1; }
        .page-title span { font-size: 13px; color: var(--text-muted); display: block; margin-top: 6px; letter-spacing: 0.04em; }

        .tabs { display: flex; border-bottom: 1px solid var(--border-subtle); margin-bottom: 28px; }
        .tab { padding: 10px 18px; font-size: 12px; letter-spacing: 0.06em; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: color 0.15s, border-color 0.15s; user-select: none; }
        .tab:hover { color: var(--text-primary); }
        .tab.active { color: var(--accent-primary); border-bottom-color: var(--accent-primary); }

        .upload-zone { position: relative; border: 1.5px dashed var(--border-strong); border-radius: 10px; padding: 36px 24px; text-align: center; cursor: pointer; background: var(--bg-secondary); transition: border-color 0.2s, background 0.2s; margin-bottom: 28px; font-family: "Caveat", cursive; font-size: 30px; }
        .upload-zone:hover, .upload-zone.drag-over { border-color: var(--accent-primary); background: var(--bg-elevated); }
        .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

        .upload-icon-wrap { width: 48px; height: 48px; border-radius: 50%; background: var(--bg-elevated); border: 1px solid var(--border-strong); margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; }
        .upload-icon-wrap svg { width: 22px; height: 22px; stroke: var(--accent-primary); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }

        .upload-text { font-size: 13px; color: var(--text-secondary); line-height: 1.7; }
        .upload-text strong { color: var(--accent-primary); font-weight: 500; }

        .upload-formats { display: flex; gap: 6px; justify-content: center; margin-top: 12px; flex-wrap: wrap; }
        .format-pill { font-size: 10px; padding: 2px 8px; border-radius: 20px; background: var(--bg-elevated); border: 0.5px solid var(--border-strong); color: var(--text-muted); letter-spacing: 0.06em; }

        .toolbar { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
        .search-wrap { position: relative; flex: 1; min-width: 200px; }
        .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; stroke: var(--text-muted); fill: none; stroke-width: 1.5; }
        .search-input { width: 100%; background: var(--bg-secondary); border: 0.5px solid var(--border-subtle); border-radius: 6px; padding: 8px 12px 8px 32px; font-family: inherit; font-size: 12px; color: var(--text-primary); outline: none; transition: border-color 0.15s; }
        .search-input::placeholder { color: var(--text-muted); }
        .search-input:focus { border-color: var(--accent-primary); }

        .filter-btn { padding: 7px 14px; font-size: 11px; background: var(--bg-secondary); border: 0.5px solid var(--border-subtle); border-radius: 6px; color: var(--text-muted); cursor: pointer; transition: all 0.15s; }
        .filter-btn:hover, .filter-btn.active { border-color: var(--accent-primary); color: var(--accent-primary); }

        .count-badge { font-size: 11px; color: var(--text-muted); margin-left: auto; white-space: nowrap; }

        .demo-list { display: flex; flex-direction: column; gap: 12px; font-family: "Caveat", cursive; font-size: 20px; }

        .demo-card { background: var(--bg-secondary); border: 0.5px solid var(--border-subtle); border-radius: 10px; padding: 18px 20px; transition: border-color 0.15s; }
        .demo-card:hover { border-color: var(--border-strong); }
        .demo-card.is-playing { border-color: var(--accent-primary); background: var(--bg-elevated); }

        .card-top { display: flex; align-items: center; gap: 14px; }
        .play-btn { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background: var(--bg-elevated); border: 1px solid var(--border-strong); color: var(--accent-primary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, color 0.15s, transform 0.1s; }
        .play-btn:hover { background: var(--accent-primary); color: var(--bg-main); }
        .play-btn:active { transform: scale(0.93); }
        .play-btn svg { width: 16px; height: 16px; fill: currentColor; }

        .card-meta { flex: 1; min-width: 0; }
        .card-title { font-size: 14px; font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-sub { font-size: 11px; color: var(--text-secondary); margin-top: 3px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .card-sub .dot { color: var(--text-muted); }
        .tag { display: inline-block; font-size: 10px; padding: 1px 7px; border-radius: 20px; background: var(--bg-elevated); border: 0.5px solid var(--border-strong); color: var(--text-muted); letter-spacing: 0.04em; }

        .card-actions { display: flex; gap: 6px; flex-shrink: 0; }

        .icon-btn { width: 32px; height: 32px; border-radius: 6px; background: transparent; border: 0.5px solid var(--border-subtle); color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
        .icon-btn svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        .icon-btn:hover { border-color: var(--accent-primary); color: var(--accent-primary); }
        .icon-btn.danger:hover { border-color: #7f2020; color: #e06060; }

        /* Smaller waveform */
        .waveform { display: flex; align-items: center; gap: 1px; height: 16px; margin: 12px 0 8px; cursor: pointer; padding: 2px 0; }
        .wf-bar { flex: 1; border-radius: 2px; background: #d6cde6; }
        .wf-bar.wf-played { background: #7d4fff; }
        .wf-bar.wf-head   { background: #ff914d; }

        .progress-row { display: flex; align-items: center; gap: 10px; }
        .time { font-size: 10px; color: var(--text-muted); min-width: 34px; }
        .time.right { text-align: right; }
        .progress-track { flex: 1; height: 3px; background: var(--border-subtle); border-radius: 2px; cursor: pointer; }
        .progress-fill { height: 100%; background: var(--accent-primary); border-radius: 2px; width: 0%; pointer-events: none; }

        .vol-row { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
        .vol-row svg { width: 13px; height: 13px; stroke: var(--text-muted); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }
        input[type="range"].vol { flex: 1; max-width: 100px; -webkit-appearance: none; height: 3px; border-radius: 2px; background: var(--border-strong); outline: none; cursor: pointer; }
        input[type="range"].vol::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; border-radius: 50%; background: var(--accent-primary); cursor: pointer; }
        input[type="range"].vol::-moz-range-thumb { width: 12px; height: 12px; border-radius: 50%; background: var(--accent-primary); border: none; cursor: pointer; }
        .vol-val { font-size: 10px; color: var(--text-muted); min-width: 28px; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); line-height: 2; }
        .empty-state strong { color: var(--text-secondary); display: block; font-size: 15px; margin-bottom: 6px; }
        #toast { position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%) translateY(20px); background: var(--bg-elevated); border: 1px solid var(--border-strong); border-radius: 6px; padding: 10px 20px; font-size: 12px; color: var(--text-primary); opacity: 0; pointer-events: none; transition: opacity 0.2s, transform 0.2s; z-index: 999; }
        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    </style>
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="page">
    <div class="page-header">
        <div class="page-title">
            Demo Vault
            <span>upload, listen, share your song drafts</span>
        </div>
    </div>

    <div class="tabs">
        <div class="tab active" data-tab="all">all demos</div>
        <div class="tab" data-tab="mine">my uploads</div>
        <div class="tab" data-tab="shared">shared with me</div>
    </div>

    <div class="upload-zone" id="uploadZone">
        <input type="file" id="fileInput" accept="audio/*" multiple />
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
            <span class="format-pill">AAC</span>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="search-input" type="text" id="searchInput" placeholder="search demos..." />
        </div>
        <button class="filter-btn active" data-sort="newest">newest</button>
        <button class="filter-btn" data-sort="oldest">oldest</button>
        <button class="filter-btn" data-sort="alpha">a–z</button>
        <span class="count-badge" id="countBadge">0 tracks</span>
    </div>

    <div class="demo-list" id="demoList">
        <div class="empty-state"><strong>no demos yet</strong>upload your first track above</div>
    </div>
</main>
<div id="toast"></div>


<script>
        let demos = [], nextId = 1, currentId = null, currentAudio = null, progressRaf = null;
        let activeTab = 'all', activeSort = 'newest', searchQuery = '';

        function fmt(s) {
            s = Math.max(0, Math.floor(s));
            return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        function genWave(n, seed) {
            return Array.from({ length: n }, (_, i) =>
                Math.round(Math.abs(Math.sin(seed * 0.9 + i * 0.38 + i * i * 0.008)) * 26 + 6 + Math.abs(Math.cos(i * 0.7 + seed)) * 8)
            );
        }

        function stopCurrent() {
            if (currentAudio) { currentAudio.pause(); currentAudio = null; }
            if (progressRaf) { cancelAnimationFrame(progressRaf); progressRaf = null; }
            currentId = null;
        }

        function tick(id) {
            const demo = demos.find(d => d.id === id);
            if (!demo || !currentAudio) return;
            const cur = currentAudio.currentTime, dur = currentAudio.duration || demo.duration;
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
            const demo = demos.find(d => d.id === id);
            if (!demo) return;
            if (currentId === id) {
                if (currentAudio && !currentAudio.paused) { currentAudio.pause(); cancelAnimationFrame(progressRaf); }
                else if (currentAudio) { currentAudio.play(); progressRaf = requestAnimationFrame(() => tick(id)); }
                render(); return;
            }
            stopCurrent();
            const audio = new Audio(demo.url);
            audio.volume = 0.8;
            currentAudio = audio; currentId = id;
            audio.addEventListener('loadedmetadata', () => { demo.duration = audio.duration; render(); });
            audio.play().then(() => { progressRaf = requestAnimationFrame(() => tick(id)); render(); })
                .catch(() => { showToast('could not play audio'); stopCurrent(); render(); });
            render();
        }

        function seek(e, id) {
            const demo = demos.find(d => d.id === id);
            if (!demo) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const dur = (currentAudio && currentId === id ? currentAudio.duration : null) || demo.duration;
            const t = pct * dur;
            if (currentAudio && currentId === id) currentAudio.currentTime = t;
            updateProgress(id, t, dur);
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('audio/')) { showToast(file.name + ' is not audio'); return; }
                const url = URL.createObjectURL(file);
                const id = nextId++;
                const demo = { id, title: file.name, uploader: 'you', duration: 0, url, tags: ['new'], uploadedAt: Date.now(), wave: genWave(60, id * 7 + file.name.length), tab: 'mine' };
                const probe = new Audio(url);
                probe.addEventListener('loadedmetadata', () => { demo.duration = probe.duration; render(); });
                demos.unshift(demo);
                showToast('uploaded: ' + file.name);
                render();
            });
        }

        function downloadDemo(id) {
            const demo = demos.find(d => d.id === id);
            if (!demo) return;
            const a = document.createElement('a');
            a.href = demo.url; a.download = demo.title;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            showToast('downloading: ' + demo.title);
        }

        function deleteDemo(id) {
            if (currentId === id) stopCurrent();
            const i = demos.findIndex(d => d.id === id);
            if (i > -1) { URL.revokeObjectURL(demos[i].url); demos.splice(i, 1); }
            showToast('demo removed'); render();
        }

     

        function copyShare(id) {
            const inp = document.getElementById('share-input-' + id);
            if (inp) navigator.clipboard.writeText(inp.value).catch(() => {});
            const msg = document.getElementById('copied-' + id);
            if (msg) { msg.style.display = 'block'; setTimeout(() => msg.style.display = 'none', 2000); }
        }

        function setVol(v, id) {
            if (currentId === id && currentAudio) currentAudio.volume = v / 100;
        }

        function getFiltered() {
            let list = demos.filter(d => activeTab === 'mine' ? d.tab === 'mine' : activeTab === 'shared' ? d.tab === 'shared' : true);
            if (searchQuery) {
                const q = searchQuery.toLowerCase();
                list = list.filter(d => d.title.toLowerCase().includes(q) || d.uploader.toLowerCase().includes(q) || d.tags.some(t => t.includes(q)));
            }
            if (activeSort === 'oldest') list = [...list].reverse();
            else if (activeSort === 'alpha') list = [...list].sort((a, b) => a.title.localeCompare(b.title));
            return list;
        }

        function render() {
            const filtered = getFiltered();
            document.getElementById('countBadge').textContent = filtered.length + ' track' + (filtered.length !== 1 ? 's' : '');
            const list = document.getElementById('demoList');

            if (filtered.length === 0) {
                list.innerHTML = `<div class="empty-state"><strong>${searchQuery ? 'no results' : 'no demos yet'}</strong>${searchQuery ? 'try a different search' : 'upload your first track above'}</div>`;
                return;
            }

            list.innerHTML = filtered.map(d => {
                const playing = currentId === d.id && currentAudio && !currentAudio.paused;
                const active = currentId === d.id;
                const dur = (active && currentAudio && currentAudio.duration) ? currentAudio.duration : d.duration;
                const curT = (active && currentAudio) ? currentAudio.currentTime : 0;
                const pct = dur > 0 ? Math.min(100, (curT / dur) * 100) : 0;
                const head = dur > 0 ? Math.floor((curT / dur) * d.wave.length) : 0;

                const playIcon = playing
                    ? `<svg viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1" fill="currentColor"/><rect x="14" y="4" width="4" height="16" rx="1" fill="currentColor"/></svg>`
                    : `<svg viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21" fill="currentColor"/></svg>`;

                return `
                <div class="demo-card${active ? ' is-playing' : ''}" id="card-${d.id}">
                    <div class="card-top">
                        <button class="play-btn" onclick="togglePlay(${d.id})">${playIcon}</button>
                        <div class="card-meta">
                            <div class="card-title">${d.title}</div>
                            <div class="card-sub">
                                <span>${d.uploader}</span><span class="dot">·</span>
                                <span>${dur > 0 ? fmt(dur) : '--:--'}</span>
                                ${d.tags.map(t => `<span class="tag">${t}</span>`).join('')}
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="icon-btn" onclick="downloadDemo(${d.id})" title="download">
                                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </button>
                            <button class="icon-btn" onclick="toggleShare(${d.id})" title="share" style="position:relative;">
                                <svg viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                <div class="share-pop" id="share-${d.id}">
                                    <div class="share-pop-label">share link</div>
                                    <div class="share-link-row">
                                        <input class="share-link-input" readonly id="share-input-${d.id}" value="gitpushplay.app/demos/${d.id}" />
                                        <button class="copy-btn" onclick="copyShare(${d.id})">copy</button>
                                    </div>
                                    <div class="copied-msg" id="copied-${d.id}">✓ copied!</div>
                                </div>
                            </button>
                            <button class="icon-btn danger" onclick="deleteDemo(${d.id})" title="remove">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="waveform" id="wf-${d.id}" onclick="seek(event, ${d.id})">
                        ${d.wave.map((h, i) => `<div class="wf-bar${i < head ? ' wf-played' : i === head && active ? ' wf-head' : ''}" style="height:${h}px"></div>`).join('')}
                    </div>
                    <div class="progress-row">
                        <span class="time" id="cur-${d.id}">${fmt(curT)}</span>
                        <div class="progress-track" onclick="seek(event, ${d.id})">
                            <div class="progress-fill" id="pf-${d.id}" style="width:${pct}%"></div>
                        </div>
                        <span class="time right">${dur > 0 ? fmt(dur) : '--:--'}</span>
                    </div>
                    <div class="vol-row">
                        <svg viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 010 7.07"/></svg>
                        <input type="range" class="vol" min="0" max="100" step="1" value="80"
                            oninput="setVol(this.value,${d.id});document.getElementById('vv-${d.id}').textContent=this.value+'%'" />
                        <span class="vol-val" id="vv-${d.id}">80%</span>
                    </div>
                </div>`;
            }).join('');
        }

        document.getElementById('fileInput').addEventListener('change', e => handleFiles(e.target.files));

        const zone = document.getElementById('uploadZone');
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });

        document.getElementById('searchInput').addEventListener('input', e => { searchQuery = e.target.value.trim(); render(); });

        document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
            t.classList.add('active'); activeTab = t.dataset.tab; render();
        }));

        document.querySelectorAll('[data-sort]').forEach(b => b.addEventListener('click', () => {
            document.querySelectorAll('[data-sort]').forEach(x => x.classList.remove('active'));
            b.classList.add('active'); activeSort = b.dataset.sort; render();
        }));

        document.addEventListener('click', e => {
            if (!e.target.closest('.card-actions')) document.querySelectorAll('.share-pop.open').forEach(p => p.classList.remove('open'));
        });

        render();
    </script>
</body>
</html>
