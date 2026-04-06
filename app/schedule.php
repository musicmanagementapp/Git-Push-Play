<?php
session_start();
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
    width: 70%; /* smaller width */
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
.form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
button[type="submit"] { width: 100%; padding: 10px; }

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
.success { background-color: #4CAF50; }
.error { background-color: #f44336; }

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
    color: #8b55a4;
}

/* --- Footer --- */
.footer {
    width: 100%;
    padding: 20px 0;
    background: rgba(0,0,0,0.2);
    text-align: center;
    font-family: "Belleza", sans-serif;
    font-size: 16px;
    margin-top: auto;
}
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
                <div class="form-group"><input type="text" id="title" placeholder="Event Title" required></div>
                <div class="form-group"><input type="date" id="date" required></div>
                <div class="form-group"><input type="text" id="createdBy" placeholder="Your Name" required></div>
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

<div class="footer">
    <?= date("Y") ?> Gitpushplay
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth();
let globalEvents = [];

function escapeHTML(str){
    return str.replace(/[&<>'"]/g, tag=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[tag]));
}

async function loadData(){
    try{
        const res = await fetch('assets/libs/calendar-data-events.php');
        globalEvents = await res.json();
        renderEventList();
        renderCalendar();
    } catch(e){ showToast('Failed to load events','error'); }
}

function renderEventList(){
    const list = document.getElementById('eventList');
    if(globalEvents.length===0){ list.innerHTML='<p>No upcoming events.</p>'; return; }
    list.innerHTML = globalEvents.map(ev=>`
        <div class="event-card">
            <div>
                <strong>${escapeHTML(ev.title)}</strong><br>
                <small>${escapeHTML(ev.date)} | ${escapeHTML(ev.created_by)}</small>
            </div>
            <button onclick="deleteEvent('${ev.id}')" style="background:#ff4444;color:white;border:none;padding:5px 10px;width:auto;">X</button>
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
                cell.onclick=()=>{document.getElementById('date').value=cellDate; document.getElementById('title').focus();};
                let contentHTML=`<span class="day-num">${date}</span>`;
                let dayEvents=globalEvents.filter(ev=>ev.date===cellDate);
                dayEvents.forEach(ev=>{contentHTML+=`<div class="cal-event">${escapeHTML(ev.title)}</div>`;});
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
    const payload={
        action:'create',
        title:document.getElementById('title').value,
        date:document.getElementById('date').value,
        created_by:document.getElementById('createdBy').value
    };
    const res=await fetch('assets/libs/CalData_events.php',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':csrfToken},
        body:JSON.stringify(payload)
    });
    const result=await res.json();
    showToast(result.message,result.success?'success':'error');
    if(result.success){ document.getElementById('eventForm').reset(); loadData(); }
});

async function deleteEvent(id){
    const res=await fetch('assets/libs/CalData_events.php',{
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

loadData();
</script>
</body>
</html>
