<?php

// server should keep session data for 1 hour
ini_set('session.gc_maxlifetime', 7200);

// each client should remember their session id for 1 hour
session_set_cookie_params(7200);

session_start();
  if(isset($_SESSION['UserLogin']) && $_SESSION['UserLogin'] == "Yes") {
    
  }
  else
  {
    header("location: login.php");
  }

?>