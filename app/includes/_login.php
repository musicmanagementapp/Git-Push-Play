<?php

ini_set('session.gc_maxlifetime', 7200);
session_set_cookie_params(7200);
session_start();

if (!isset($_SESSION['UserLogin']) || $_SESSION['UserLogin'] != "Yes") {
    header("location: login.php");
}

?>
