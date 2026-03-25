<?php include 'includes/_login.php'; ?>  <!-- remove if page is public -->
<?php
$title       = "Band Dashboard";
$description = "Manage your band stats, uploads, and schedule.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .dashboard {
            padding: 20px;
        }

        .button-group {
            margin-bottom: 20px;
        }

        .button-group button {
            padding: 10px 15px;
            margin-right: 10px;
            cursor: pointer;
            border: none;
            background-color: #333;
            color: #fff;
            border-radius: 5px;
        }

        .button-group button:hover {
            background-color: #555;
        }

        .section {
            display: none;
            margin-top: 20px;
        }

        .active {
            display: block;
        }

        .calendar {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
        }

        .event {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .event:last-child {
            border-bottom: none;
        }

        .add-event {
            margin-top: 15px;
        }

        .add-event input {
            padding: 5px;
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <?php include 'includes/_header.php'; ?>

    <main class="dashboard">

        <div class="button-group">
            <button onclick="showSection('stats')">Stats</button>
            <button onclick="showSection('uploads')">Uploads</button>
            <button onclick="showSection('calendar')">Calendar</button>
        </div>

        <div id="stats" class="section active">
            <h2>Band Stats</h2>
            <p>Views: 0</p>
            <p>Followers: 0</p>
            <p>Streams: 0</p>
        </div>

        <div id="uploads" class="section">
            <h2>Uploads</h2>
            <p>No uploads yet.</p>
            <button>Add Upload (coming soon)</button>
        </div>

        <div id="calendar" class="section">
            <h2>Band Calendar</h2>

            <div class="calendar" id="eventList">
                <div class="event">No events yet.</div>
            </div>
            <div class="add-event">
                <input type="date" id="eventDate">
                <input type="text" id="eventText" placeholder="Event description">
                <button onclick="addEvent()">Add Event</button>
            </div>
        </div>

    </main>

    <?php include 'includes/_footer.php'; ?>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(sec => sec.classList.remove('active'));

            document.getElementById(sectionId).classList.add('active');
        }

        function addEvent() {
            const date = document.getElementById('eventDate').value;
            const text = document.getElementById('eventText').value;

            if (!date || !text) {
                alert("Please enter both date and event.");
                return;
            }

            const eventList = document.getElementById('eventList');

            if (eventList.children.length === 1 && eventList.children[0].innerText === "No events yet.") {
                eventList.innerHTML = "";
            }

            const newEvent = document.createElement('div');
            newEvent.classList.add('event');
            newEvent.innerText = date + " - " + text;

            eventList.appendChild(newEvent);

            document.getElementById('eventDate').value = "";
            document.getElementById('eventText').value = "";
        }
    </script>

</body>
</html>
