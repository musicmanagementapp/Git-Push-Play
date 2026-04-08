<?php
include 'includes/_login.php';
// token validation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Interactive Schedule</title>
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
<style>
/* --- Body & Fonts --- */
body {
    background: linear-gradient(90deg, #aa83f6,#ffb689);
    margin: 0;
    font-family: "Belleza", sans-serif;
    color: black;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* --- Top Navbar --- */
.top-nav {
    width: 70%; 
    max-width: 1000px;
    margin: 0 auto;
    background: black;
    opacity: 0.8;
    padding: 15px 0;
    position: fixed;
    top: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    border-radius: 12px;
}

.top-nav ul {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    justify-content: center;
    gap: 40px;
}

.top-nav ul li a {
    color: #e0d0f0;
    text-decoration: none;
    font-size: 18px;
    font-family: "Belleza", sans-serif;
    transition: color 0.3s;
}

.top-nav ul li a:hover {
    color: #fff;
}

/* --- Main Container --- */
.main-container {
    background: #f3e0f0;
    border-radius: 20px;
    padding: 30px;
    max-width: 1200px;
    margin: 120px auto 50px auto; 
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    flex-grow: 1;
}

/* --- Layout --- */
.layout-container { 
    display: flex; 
    gap: 30px; 
    align-items: flex-start; 
}
.sidebar { width: 350px; flex-shrink: 0; }
.calendar-gui { flex-grow: 1; font-family: "Rouge Script", cursive; }

/* --- Buttons --- */
button { cursor: pointer; font-family: "Rouge Script", cursive; }
.cal-header button {
    background: #8b55a4;
    color: #fff;
    border: none;
    padding: 5px 12px; 
    border-radius: 8px;
    font-size: 14px;
}
.cal-header button:hover { background: #a66bbf; }

/* --- Form --- */
.form-group { margin-bottom: 15px; }
.form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
button[type="submit"] { width: 100%; padding: 10px; }

.checkbox-group { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 15px; 
    font-family: "Rouge Script", cursive; 
    font-size: 14px;
}

.checkbox-group label {
    display: flex;
    align-items: center; 
    cursor: pointer;
    font-family: "Rouge Script", cursive; 
    font-size: 22px; 
}

.checkbox-group input[type="checkbox"] {
    width: auto; 
    margin: 0 8px 0 0; 
}

/* --- Event Cards --- */
.event-card { 
    border: 1px solid #ccc; 
    padding: 15px; 
    margin-bottom: 10px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    border-radius: 12px; 
    background: #fff; 
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}

/* --- Toast --- */
#toast { 
    visibility: hidden; 
    padding: 15px; 
    border-radius: 4px; 
    color: #7a5e86; 
    text-align: center; 
    margin-bottom: 20px; 
}
#toast.show { visibility: visible; }
.success { background-color: #4CAF50; color: white !important;}
.error { background-color: #f44336; color: white !important;}

/* --- Calendar --- */
.cal-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 20px;
    background: #d6b8d9;
    padding: 10px 15px;
    border-radius: 15px;
}
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td { border: 1px solid #ccc; padding: 5px; }
th { background: #000; text-align: center; padding: 10px 0; color: #fff; }
td { height: 100px; vertical-align: top; cursor: pointer; transition: background 0.2s; border-radius: 8px; }
td:hover { background: #ad86bf; }
.day-num { font-weight: bold; display: block; margin-bottom: 5px; }
.cal-event { 
    background: #8b55a4; 
    font-size: 11px; 
    padding: 2px 4px; 
    border-radius: 3px; 
    margin-bottom: 3px; 
    overflow: hidden; 
    white-space: nowrap; 
    text-overflow: ellipsis;
    color: #fff;
}


/* --- Modal Styles --- */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; }
.modal-content { background: #f3e0f0; padding: 25px; border-radius: 15px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto; font-family: "Rouge Script", cursive; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #d6b8d9; padding-bottom: 10px; }
.modal-close { background: #ff4444; color: white; border: none; border-radius: 6px; padding: 6px 12px; font-family: "Rouge Script", cursive; cursor: pointer; font-weight: bold; }
.modal-event { background: white; margin-bottom: 10px; padding: 15px; border-radius: 8px; cursor: pointer; border: 1px solid #d6b8d9; transition: box-shadow 0.2s; }
.modal-event:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
.modal-event-time { font-weight: bold; color: #8b55a4; margin-right: 10px; }
.modal-event-details { display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc; font-size: 14px; line-height: 1.5; color: #444; }
</style>
</head>
<body>

<?php
if (basename($_SERVER['PHP_SELF']) != 'index.php') {
?>
<nav class="top-nav">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="demos.php">Demos</a></li>
        <li><a href="bandpage.php">Band Page</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="artist-profile.php">Artist Profile</a></li>
    </ul>
</nav>
<?php } ?>

<div class="main-container">
    <div class="layout-container">
        <div class="sidebar">
            <div id="toast"></div>
            <h2>Add Event</h2>
            <form id="eventForm">
                <input type="hidden" id="eventId" value="">
                <div class="form-group"><input type="text" id="title" placeholder="Event Title" required></div>
                <div class="form-group"><input type="date" id="date" required></div>
                <div class="form-group"><input type="time" id="time" required></div>
                <div class="form-group"><input type="text" id="createdBy" placeholder="Your Name" required></div>
                
                <div class="form-group">
                    <textarea id="description" placeholder="Event Description (Optional)" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label style="font-family: Rouge Script, cursive; font-size: 14px; font-weight: bold;">Tag Band Members:</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="band_members" value="Alex"> Alex</label>
                        <label><input type="checkbox" name="band_members" value="Sam"> Sam</label>
                        <label><input type="checkbox" name="band_members" value="Jordan"> Jordan</label>
                        <label><input type="checkbox" name="band_members" value="Casey"> Casey</label>
                    </div>
                </div>
                <button type="submit">Save Event</button>
            </form>
            <h2>Upcoming Events</h2>
            <div id="eventList">Loading...</div>
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
</div>

<div id="dayModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalDateTitle" style="margin:0; font-family: 'Rouge Script', cursive; font-size: 28px;"></h2>
            <button class="modal-close" onclick="closeDayModal()">Close</button>
        </div>
        <p style="font-size: 13px; color: #666; margin-top: 0; font-family: sans-serif;">Click an event to expand details.</p>
        <div id="modalEventsList"></div>
    </div>
</div>

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
    if(globalEvents.length===0){ list.innerHTML='<p>No upcoming events.</p>'; return; }
    
    list.innerHTML = globalEvents.map(ev=>`
        <div class="event-card">
            <div class="event-details" style="flex-grow:1; margin-right:15px;">
                <strong style="font-family: Rouge Script, cursive; font-size: 20px;">${escapeHTML(ev.title)}</strong><br>
                <small style="font-family: Rouge Script, cursive; color: #555; font-size: 16px;">${escapeHTML(ev.date)} @ ${escapeHTML(ev.time || '')} | By: ${escapeHTML(ev.created_by)}</small>
                
                ${ev.description ? `<p style="margin: 8px 0 5px 0; font-size: 13px; font-family: sans-serif;">${escapeHTML(ev.description)}</p>` : ''}
                
                ${ev.band_members && ev.band_members.length > 0 ? `<div style="margin-top: 5px;"><small style="background: #e0d0f0; padding: 2px 6px; border-radius: 4px; font-family: sans-serif; color: #5a3c6d;">Tagged: ${ev.band_members.map(escapeHTML).join(', ')}</small></div>` : ''}
            </div>
            <button onclick="deleteEvent('${ev.id}')" style="background:#ff4444;color:white;border:none;padding:5px 10px;border-radius:6px; cursor: pointer;" title="Delete Event">X</button>
        </div>`).join('');
}

function renderCalendar(){
    const firstDay = new Date(currentYear,currentMonth,1).getDay();
    const daysInMonth = new Date(currentYear,currentMonth+1,0).getDate();
    document.getElementById('monthLabel').textContent = new Date(currentYear,currentMonth).toLocaleString('default',{month:'long',year:'numeric'});
    const tbody = document.getElementById('calBody'); tbody.innerHTML='';
    let date = 1;
    for(let i=0;i<6;i++){
        let row=document.createElement('tr');
        for(let j=0;j<7;j++){
            let cell=document.createElement('td');
            if(i===0 && j<firstDay){} else if(date>daysInMonth){} else{
                let cellDate=`${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(date).padStart(2,'0')}`;
                
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
        band_members: checkedMembers
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span class="modal-event-time">${escapeHTML(ev.time || 'All Day')}</span>
                    <strong style="font-size: 20px; font-family: 'Rouge Script', cursive;">${escapeHTML(ev.title)}</strong>
                </div>
                <button onclick="loadEventIntoForm('${ev.id}', event)" style="background: #aa83f6; color: white; border: none; padding: 4px 10px; border-radius: 6px; font-family: sans-serif; font-size: 12px; cursor: pointer;">Edit</button>
            </div>
            <div id="details_${ev.id}" class="modal-event-details">
                <p style="margin: 0 0 10px 0; font-family: sans-serif;"><strong>Created By:</strong> ${escapeHTML(ev.created_by)}</p>
                ${ev.description ? `<p style="margin: 0 0 10px 0; font-family: sans-serif;"><strong>Description:</strong><br>${escapeHTML(ev.description)}</p>` : ''}
                ${ev.band_members && ev.band_members.length > 0 ? `<p style="margin: 0; font-family: sans-serif;"><span style="background: #e0d0f0; padding: 4px 8px; border-radius: 4px; color: #5a3c6d;"><strong>Tagged:</strong> ${ev.band_members.map(escapeHTML).join(', ')}</span></p>` : ''}
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
    document.getElementById('title').value = unescapeHTML(ev.title);
    document.getElementById('date').value = ev.date;
    document.getElementById('time').value = ev.time || '';
    document.getElementById('createdBy').value = unescapeHTML(ev.created_by);
    document.getElementById('description').value = unescapeHTML(ev.description || '');

    document.querySelectorAll('input[name="band_members"]').forEach(cb => cb.checked = false);
    
    if (ev.band_members) {
        ev.band_members.forEach(member => {
            const cb = document.querySelector(`input[name="band_members"][value="${unescapeHTML(member)}"]`);
            if (cb) cb.checked = true;
        });
    }

    closeDayModal();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    document.querySelector('#eventForm button[type="submit"]').textContent = 'Update Event';
}

function unescapeHTML(str) {
    if (!str) return '';
    return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, "'").replace(/&quot;/g, '"');
}

loadData();
</script>
</body>
</html>