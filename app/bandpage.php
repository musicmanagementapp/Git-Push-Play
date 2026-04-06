<?php include 'includes/_login.php'; ?> 
<?php
$title       = "Band Dashboard";
$description = "Manage your band stats, uploads, and schedule.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
     <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* --- Main Layout --- */
        .dashboard {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            font-family: "Belleza", sans-serif;
        }

        /* --- Section Buttons --- */
        .button-group {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .button-group button {
            padding: 12px 25px;
            cursor: pointer;
            border: none;
            background: linear-gradient(90deg, #7d4fff, #ff914d);
            color: #fff;
            border-radius: 12px;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .button-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* --- Sections --- */
        .section {
            display: none;
            margin-top: 20px;
            animation: fadeIn 0.3s ease-in-out;
        }

        .active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Card Style --- */
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        h2 {
            color: #7d4fff;
            margin-bottom: 15px;
            text-align: center;
        }

        /* --- Band Stats --- */
        .stats p {
            font-size: 16px;
            margin: 8px 0;
            text-align: center;
            font-weight: bold;
            color: #333;
        }

        /* --- Uploads Section --- */
        .uploads button {
            background: #7d4fff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .uploads button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        /* --- Calendar Section --- */
        .calendar {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 12px;
            max-width: 600px;
            margin: 0 auto 20px;
            background: #f8eafc;
        }

        .event {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #fff;
            transition: background 0.2s;
        }

        .event:last-child {
            border-bottom: none;
        }

        .event:hover {
            background: #e5d0f0;
        }

        .add-event {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .add-event input[type="text"],
        .add-event input[type="date"] {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 200px;
        }

        .add-event button {
            background: linear-gradient(90deg, #7d4fff, #ff914d);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .add-event button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .add-event input[type="text"], .add-event input[type="date"] {
                width: 100%;
            }
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

    <div id="stats" class="section active card stats">
        <h2>Band Stats</h2>
        <p>Views: 0</p>
        <p>Followers: 0</p>
        <p>Streams: 0</p>
    </div>

    <div id="uploads" class="section card uploads">
        <h2>Uploads</h2>
        <p>No uploads yet.</p>
        <button>Add Upload (coming soon)</button>
    </div>

    <div id="calendar" class="section card calendar-section">
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
