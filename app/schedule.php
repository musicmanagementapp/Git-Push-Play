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
        .layout-container { display: flex; gap: 30px; max-width: 1200px; margin: 40px auto; align-items: flex-start; font-family: sans-serif; }
        .sidebar { width: 350px; flex-shrink: 0; }
        .calendar-gui { flex-grow: 1; }
        
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; cursor: pointer; }
        .event-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;}
        
        #toast { visibility: hidden; padding: 15px; border-radius: 4px; color: white; text-align: center; margin-bottom: 20px; }
        #toast.show { visibility: visible; }
        .success { background-color: #4CAF50; }
        .error { background-color: #f44336; }

        .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .cal-header button { width: auto; padding: 5px 15px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 5px; }
        th { background: #f4f4f4; text-align: center; padding: 10px 0; }
        td { height: 100px; vertical-align: top; cursor: pointer; transition: background 0.2s; }
        td:hover { background: #f9f9f9; }
        .day-num { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
        .cal-event { background: #000; color: #fff; font-size: 11px; padding: 2px 4px; border-radius: 3px; margin-bottom: 3px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    </style>
</head>
<body>

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
                <button id="prevMonth">&larr; Prev</button>
                <h2 id="monthLabel" style="margin:0;"></h2>
                <button id="nextMonth">Next &rarr;</button>
            </div>
            <table>
                <thead>
                    <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
                </thead>
                <tbody id="calBody"></tbody>
            </table>
        </div>
    </div>

    <script>
        // Grab the token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth(); 
        let globalEvents = [];

        // Recommended Output Escaping(?)
        function escapeHTML(str) {
            return str.replace(/[&<>'"]/g, 
                tag => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                }[tag])
            );
        }

        async function loadData() {
            try {
                const res = await fetch('assets/libs/calendar-data-events.php');
                globalEvents = await res.json();
                renderEventList();
                renderCalendar();
            } catch (err) {
                showToast('Failed to load events', 'error');
            }
        }

        function renderEventList() {
            const list = document.getElementById('eventList');
            if (globalEvents.length === 0) {
                list.innerHTML = '<p>No upcoming events.</p>';
                return;
            }
            list.innerHTML = globalEvents.map(ev => `
                <div class="event-card">
                    <div>
                        <strong>${escapeHTML(ev.title)}</strong><br>
                        <small>${escapeHTML(ev.date)} | ${escapeHTML(ev.created_by)}</small>
                    </div>
                    <button onclick="deleteEvent('${ev.id}')" style="background:#ff4444;color:white;border:none;padding:5px 10px;width:auto;">X</button>
                </div>
            `).join('');
        }

        function renderCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            document.getElementById('monthLabel').textContent = new Date(currentYear, currentMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
            
            const tbody = document.getElementById('calBody');
            tbody.innerHTML = '';
            
            let date = 1;
            for (let i = 0; i < 6; i++) {
                let row = document.createElement('tr');
                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement('td');
                    if (i === 0 && j < firstDay) {
                        
                    } else if (date > daysInMonth) {
                       
                    } else {
                        let cellDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        
                        cell.onclick = () => {
                            document.getElementById('date').value = cellDate;
                            document.getElementById('title').focus();
                        };

                        let contentHTML = `<span class="day-num">${date}</span>`;
                        
                        let dayEvents = globalEvents.filter(ev => ev.date === cellDate);
                        dayEvents.forEach(ev => {
                            contentHTML += `<div class="cal-event">${escapeHTML(ev.title)}</div>`;
                        });

                        cell.innerHTML = contentHTML;
                        date++;
                    }
                    row.appendChild(cell);
                }
                tbody.appendChild(row);
                if (date > daysInMonth) break; 
            }
        }

        document.getElementById('prevMonth').onclick = () => { currentMonth--; if(currentMonth < 0) { currentMonth = 11; currentYear--; } renderCalendar(); };
        document.getElementById('nextMonth').onclick = () => { currentMonth++; if(currentMonth > 11) { currentMonth = 0; currentYear++; } renderCalendar(); };

        document.getElementById('eventForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                action: 'create',
                title: document.getElementById('title').value,
                date: document.getElementById('date').value,
                created_by: document.getElementById('createdBy').value
            };

            const res = await fetch('assets/libs/CalData_events.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken 
                },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                document.getElementById('eventForm').reset();
                loadData(); 
            }
        });

        async function deleteEvent(id) {
            const res = await fetch('assets/libs/CalData_events.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken 
                },
                body: JSON.stringify({ action: 'delete', id: id })
            });
            const result = await res.json();
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) loadData(); 
        }

        function showToast(msg, type) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `show ${type}`;
            setTimeout(() => t.className = '', 3000);
        }

        loadData();
    </script>
</body>
</html>