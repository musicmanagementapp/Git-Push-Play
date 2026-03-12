<?php
  session_start();

  if(isset($_SESSION['UserLogin']) && $_SESSION['UserLogin']=="Yes") {
    header("location: index.php");
  }

  if($_SERVER["REQUEST_METHOD"] === "POST") {
    $Username = $_POST["username"];
    $Password = $_POST["password"];
    
    if ($Username == 'username' && $Password == 'password')
    {
        $_SESSION['UserLogin'] = "Yes";
        header("location: index.php");
    }
    else
    {
        //echo "oops";
    }
  }
  else
  {
    //echo "Not a post";
  }
  
  //echo "UserLogin = " . $_SESSION['UserLogin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p>Redirected to login page correctly!</p>
</body>
</html>

<?php

/* <form name="contactform" class=""  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 

FORM NEEDS TO USE THIS TO HAVE THE CORRECT LOGIN FUNCTIONALITY, BUT IT IS NOT WORKING PROPERLY YET. */
?>
