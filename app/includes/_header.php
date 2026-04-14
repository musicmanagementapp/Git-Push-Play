<?php
if (basename($_SERVER['PHP_SELF']) != 'index.php') {
?>
<nav class="top-nav">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="bandpage.php">Band Page</a></li>
        <li><a href="schedule.php">Calendar</a></li>
        <li><a href="artist-profile.php">Artist Profile</a></li>
    </ul>
</nav>
<?php } else { ?>


<header class="site-header">
    <div class="header-container">
     <link href="https://fonts.googleapis.com/css2?family=Belleza&display=swap" rel="stylesheet">
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
               <li><a href="bandpage.php">Band Page</a></li>
                <li><a href="schedule.php">Calendar</a></li>
                <li><a href="artist-profile.php">Artist Profile</a></li>
                
  
            </ul>
        </nav>

</header>
<?php } ?>


