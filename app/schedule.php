<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Build the band member list for the checkboxes
$calBandMembers = [];
foreach ($gpBandMembers as $mem) {
    $name = trim($mem['musician']['stageName'] ?? '');
    if ($name === '') {
        $name = trim(($mem['musician']['firstName'] ?? '') . ' ' . ($mem['musician']['lastName'] ?? ''));
    }
    if ($name !== '') {
        $calBandMembers[] = $name;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Interactive Schedule</title>
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Belleza&family=Rouge+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/schedule.css">
</head>
<body>

<?php
include 'includes/_header.php';
?>

<div class="main-container">

    <?php if (!$gpBand): ?>
        <p style="text-align:center; font-size:1.1rem; opacity:0.7; padding: 40px 0;">
            You need to join or create a band before you can use the calendar.
            <br><a href="settings.php" style="color:#aa83f6;">Go to Settings</a>
        </p>
    <?php else: ?>

    <div class="layout-container">
        <div class="sidebar">
            <div id="toast"></div>
            <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 20px;"><?= htmlspecialchars($gpBand['name']) ?> — Add Event</h2>
            <form id="eventForm">
                <input type="hidden" id="eventId" value="">
                <div class="form-group"><input type="text" id="title" placeholder="Event Title" required></div>
                <div class="form-group"><input type="date" id="date" required></div>
                <div class="form-group"><input type="time" id="time" required></div>

                <div class="form-group">
                    <label style="font-size: 14px; font-weight: bold; display:block; margin-bottom:6px;">Created By:</label>
                    <select id="createdBy" required style="width:100%; padding:0.65rem 0.85rem; border-radius:10px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.06); color:lavender; font-family:'Comic Sans MS', 'Segoe UI', cursive, sans-serif; font-size:1rem; outline:none;">
                        <?php foreach ($calBandMembers as $memberName): ?>
                            <option value="<?= htmlspecialchars($memberName) ?>"
                                <?= (($gpMusician['stageName'] ?? '') === $memberName || trim(($gpMusician['firstName'] ?? '') . ' ' . ($gpMusician['lastName'] ?? '')) === $memberName) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($memberName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <textarea id="description" placeholder="Event Description (Optional)" rows="3"></textarea>
                </div>

                <?php if (!empty($calBandMembers)): ?>
                <div class="form-group">
                    <label style="font-size: 14px; font-weight: bold; display:block; margin-bottom:6px;">Tag Band Members:</label>
                    <div class="checkbox-group">
                        <?php foreach ($calBandMembers as $memberName): ?>
                            <label>
                                <input type="checkbox" name="band_members" value="<?= htmlspecialchars($memberName) ?>">
                                <?= htmlspecialchars($memberName) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group" style="margin-top:4px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; font-weight:bold;">
                        <input type="checkbox" id="isPublic" style="width:16px;height:16px;accent-color:#aa83f6;cursor:pointer;">
                        Show on Public Band Page
                    </label>
                </div>

                <button type="submit">Save Event</button>
            </form>
        </div>

        <div class="calendar-gui">
            <div class="cal-header">
                <button id="prevMonth">&larr;</button>
                <h2 id="monthLabel" style="margin:0;"></h2>
                <button id="nextMonth">&rarr;</button>
            </div>
            <table>
                <thead>
                    <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
                </thead>
                <tbody id="calBody"></tbody>
            </table>
        </div>
    </div>

    <div class="events-row-section">
        <h2 class="events-row-title">Upcoming Events</h2>
        <div id="eventList">Loading...</div>
    </div>

    <?php endif; ?>
</div>

<div id="dayModal" class="modal-overlay">
    <div class="modal-content" style="background: linear-gradient(90deg, rgba(170, 131, 246, .03), rgba(255, 182, 137, .03))">
        <div class="modal-header">
            <h2 id="modalDateTitle" style="margin:0; font-size: 28px;"></h2>
            <button class="modal-close" onclick="closeDayModal()">Close</button>
        </div>
        <p style="font-size: 13px; opacity:0.6; margin-top: 0;">Click an event to expand details.</p>
        <div id="modalEventsList"></div>
    </div>
</div>

<?php if ($gpBand): ?>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth();
let globalEvents = [];

function escapeHTML(str){
    if(!str) return '';
    return str.replace(/[&<>'"]/g, tag=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[tag]));
}

async function loadData(){
    try{
        const res = await fetch('assets/libs/calendar-data-events.php');
        globalEvents = await res.json();
        globalEvents.sort((a, b) => {
            let datetimeA = a.date + (a.time || '00:00');
            let datetimeB = b.date + (b.time || '00:00');
            return datetimeA.localeCompare(datetimeB);
        });
        renderEventList();
        renderCalendar();
    } catch(e){ showToast('Failed to load events','error'); }
}

function renderEventList(){
    const list = document.getElementById('eventList');
    const today = new Date().toISOString().slice(0, 10);
    const upcoming = globalEvents.filter(ev => ev.date >= today);
    if(upcoming.length===0){ list.innerHTML='<p style="opacity:0.6;">No upcoming events.</p>'; return; }

    list.innerHTML = upcoming.map(ev=>`
        <div class="event-card" >
            <div class="event-details" style="flex-grow:1; margin-right:15px;">
                <strong style="font-size: 20px;">${escapeHTML(ev.title)}</strong><br>
                <small style="opacity:0.7; font-size: 15px;">${escapeHTML(ev.date)} @ ${escapeHTML(ev.time || '')} | By: ${escapeHTML(ev.created_by)}</small>
                ${ev.description ? `<p style="margin: 6px 0 4px; font-size: 13px; opacity:0.8;">${escapeHTML(ev.description)}</p>` : ''}
                ${ev.band_members && ev.band_members.length > 0 ? `<div style="margin-top:4px;"><small style="background:none; border:none; padding:2px 8px; border-radius:20px; color:#c8b8d8;">Tagged: ${ev.band_members.map(escapeHTML).join(', ')}</small></div>` : ''}
            </div>
            <button onclick="deleteEvent('${ev.id}')" style="background:rgba(255,68,68,0.15);color:#ffb3b3;border:1px solid rgba(255,68,68,0.35);padding:5px 10px;border-radius:6px;cursor:pointer;" title="Delete Event">✕</button>
        </div>`).join('');

}

function renderCalendar(){
    const firstDay = new Date(currentYear,currentMonth,1).getDay();
    const daysInMonth = new Date(currentYear,currentMonth+1,0).getDate();
    const todayStr = new Date().toISOString().slice(0, 10);
    document.getElementById('monthLabel').textContent = new Date(currentYear,currentMonth).toLocaleString('default',{month:'long',year:'numeric'});
    const tbody = document.getElementById('calBody'); tbody.innerHTML='';
    let date = 1;
    for(let i=0;i<6;i++){
        let row=document.createElement('tr');
        for(let j=0;j<7;j++){
            let cell=document.createElement('td');
            if(i===0 && j<firstDay){} else if(date>daysInMonth){} else{
                let cellDate=`${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(date).padStart(2,'0')}`;

                if(cellDate === todayStr) cell.classList.add('cal-today');

                cell.onclick=()=>{
                    document.getElementById('date').value=cellDate;
                    document.getElementById('title').focus();
                    openDayModal(cellDate);
                };

                let contentHTML=`<span class="day-num">${date}</span>`;
                let dayEvents=globalEvents.filter(ev=>ev.date===cellDate);
                dayEvents.forEach(ev=>{
                    contentHTML+=`<div class="cal-event">${ev.time ? escapeHTML(ev.time) + ' - ' : ''}${escapeHTML(ev.title)}</div>`;
                });
                cell.innerHTML=contentHTML; date++;
            }
            row.appendChild(cell);
        }
        tbody.appendChild(row);
        if(date>daysInMonth) break;
    }
}

document.getElementById('prevMonth').onclick=()=>{ currentMonth--; if(currentMonth<0){currentMonth=11; currentYear--;} renderCalendar();};
document.getElementById('nextMonth').onclick=()=>{ currentMonth++; if(currentMonth>11){currentMonth=0; currentYear++;} renderCalendar();};

document.getElementById('eventForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const checkedMembers = Array.from(document.querySelectorAll('input[name="band_members"]:checked')).map(cb => cb.value);
    
    const payload={
        action: document.getElementById('eventId').value ? 'update' : 'create',
        id: document.getElementById('eventId').value,
        title:document.getElementById('title').value,
        date:document.getElementById('date').value,
        time:document.getElementById('time').value,
        created_by:document.getElementById('createdBy').value,
        description:document.getElementById('description').value,
        band_members: checkedMembers,
        is_public: document.getElementById('isPublic').checked
    };
    const res=await fetch('assets/libs/calendar-data-events.php',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':csrfToken},
        body:JSON.stringify(payload)
    });
    const result=await res.json();
    showToast(result.message,result.success?'success':'error');
    if(result.success){
        document.getElementById('eventForm').reset();
        document.getElementById('eventId').value = '';
        document.getElementById('isPublic').checked = false;
        document.querySelector('#eventForm button[type="submit"]').textContent = 'Save Event';
        loadData(); 
    }
});

async function deleteEvent(id){
    if(!confirm("Are you sure you want to delete this event?")) return; 
    const res=await fetch('assets/libs/calendar-data-events.php',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':csrfToken},
        body:JSON.stringify({action:'delete',id:id})
    });
    const result=await res.json();
    showToast(result.message,result.success?'success':'error');
    if(result.success) loadData();
}

function showToast(msg,type){
    const t=document.getElementById('toast');
    t.textContent=msg;
    t.className=`show ${type}`;
    setTimeout(()=>t.className='',3000);
}

function openDayModal(dateStr) {
    const dayEvents = globalEvents.filter(ev => ev.date === dateStr);
    if (dayEvents.length === 0) return; 

    const displayDate = new Date(dateStr + 'T00:00:00').toLocaleDateString('default', { month: 'long', day: 'numeric', year: 'numeric' });
    document.getElementById('modalDateTitle').textContent = displayDate;
    
    const list = document.getElementById('modalEventsList');
    list.innerHTML = dayEvents.map(ev => `
        <div class="modal-event" onclick="toggleModalDetails('${ev.id}')">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span class="modal-event-time">${escapeHTML(ev.time || 'All Day')}</span>
                    <strong style="font-size: 20px;">${escapeHTML(ev.title)}</strong>
                </div>
                <button onclick="loadEventIntoForm('${ev.id}', event)" style="background:rgba(170,131,246,0.2);color:lavender;border:1px solid rgba(170,131,246,0.4);padding:4px 10px;border-radius:6px;font-size:12px;cursor:pointer;">Edit</button>
            </div>
            <div id="details_${ev.id}" class="modal-event-details">
                <p style="margin:0 0 8px;"><strong>Created By:</strong> ${escapeHTML(ev.created_by)}</p>
                ${ev.description ? `<p style="margin:0 0 8px;"><strong>Description:</strong><br>${escapeHTML(ev.description)}</p>` : ''}
                ${ev.band_members && ev.band_members.length > 0 ? `<p style="margin:0;"><span style="background:rgba(170,131,246,0.15);border:1px solid rgba(170,131,246,0.3);padding:3px 8px;border-radius:20px;color:#c8b8d8;"><strong>Tagged:</strong> ${ev.band_members.map(escapeHTML).join(', ')}</span></p>` : ''}
            </div>
        </div>
    `).join('');
    
    document.getElementById('dayModal').style.display = 'flex';
}

function toggleModalDetails(id) {
    const details = document.getElementById(`details_${id}`);
    details.style.display = (details.style.display === 'block') ? 'none' : 'block';
}

function closeDayModal() {
    document.getElementById('dayModal').style.display = 'none';
}

function loadEventIntoForm(id, e) {
    e.stopPropagation(); 
    
    const ev = globalEvents.find(event => event.id === id);
    if (!ev) return;

    document.getElementById('eventId').value = ev.id;
    document.getElementById('title').value = ev.title;
    document.getElementById('date').value = ev.date;
    document.getElementById('time').value = ev.time || '';
    document.getElementById('description').value = ev.description || '';

    const createdBySelect = document.getElementById('createdBy');
    if (createdBySelect) {
        const opt = Array.from(createdBySelect.options).find(o => o.value === ev.created_by);
        if (opt) createdBySelect.value = ev.created_by;
    }

    document.getElementById('isPublic').checked = ev.is_public === true || ev.is_public === 1;

    document.querySelectorAll('input[name="band_members"]').forEach(cb => cb.checked = false);

    if (ev.band_members) {
        ev.band_members.forEach(member => {
            const cb = document.querySelector(`input[name="band_members"][value="${member}"]`);
            if (cb) cb.checked = true;
        });
    }

    closeDayModal();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    document.querySelector('#eventForm button[type="submit"]').textContent = 'Update Event';
}

loadData();
</script>
<?php endif; ?>
</body>
</html>