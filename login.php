<?php 
session_start();

/*if(isset($_SESSION["username"])){ // check if logged in
  print_r($_SESSION["username"]);
}*/

// include the database connection file
require_once("db.php");
// check that there are no unsafe characters in the submitted data
function test_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$usernameRegex =  "/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
$passwordRegex = "/^(\S*)?\d+(\S*)?$/";
// track errors found while processing form data
$error = array();
// Check if a form was sent:
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
  $username = $_POST['username'];
  $password = $_POST['pass'];

  // make SQL connection - catch and report any errors
  try {
      $db = new PDO($attr, $db_user, $db_pwd, $options);
  } catch (PDOException $e) {
      throw new PDOException($e->getMessage(), (int)$e->getCode());
  }
  // variable that will specify if all data is okay
  $dataOK = TRUE;

  // Before using form data for anything, validate it!
  $usernameMatch = preg_match($usernameRegex, $username);
  if($username == null || $username == "" || $usernameMatch == false) 
  {
    $error['username'] = "Invalid username.";
  }

  $pswdLen = strlen($password);
  $passwordMatch = preg_match($passwordRegex, $password);
  if($password == null || $password == "" || $pswdLen < 8 || $passwordMatch == false) 
  {
    $errors['password'] = "Invalid password.\n<br />";
  }

  if(count($error) == 0) {
      $q = "SELECT username, profile_photo, user_ID FROM Users WHERE username ='$username' AND password='$password'";
      //print_r($q);
      $r = $db->query($q);
      // check if there's a result. The row will be false if there's no match.
      if(!$r){ // query has an error
        $errors["Database Error"] = "Could not retrieve user information";
      } 
      elseif($row = $r->fetch()) //data found - user exists!
      {
        //print_r($row);
        session_start();
        $_SESSION["user_ID"] = $row["user_ID"];
        $_SESSION["avatar"] = $row["profile_photo"];
        $_SESSION["username"] = $username;
        $_SESSION["loginStatus"] = TRUE;
        // close the database connection
        $db = null;

        // go to next page
        header("Location: index.php");
        exit();

      // result $r had no matches
      } else {
          $error['Login'] = "The email/password combination was incorrect. Login failed.";
      }
      $r = null;
      $db=null;
  }else{
    $error['Login Failed'] = "You entered invalid data while logging in.";
  }
}
// Error printing should be done in a better part of the HTML document.
foreach ($error as $type => $message) {
    echo "$type: $message<br />\n";
}
?>


<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css" />
  <script src="js/eventHandler.js"></script>
</head>

<body>

  <header class="headingTab">
    <div class="logo"><a href="index.php"><img class="logoIcon" src="images/logo.png" alt="logo" /></a>
    </div>
    <div class="title">
      <h1>Log In</h1>
    </div>
  </header>

  <form id="loginMain" action="login.php" method="post">
    <div class="loginElements">
      <div class="item_1">
        <label for="username">Username: </label><br />
        <input type="text" id="username" name="username" />
      </div>
      <p class="center">
        <span id="email_err" class="require hidden error-text">Invalid Email Address - ensure that email address
          is in correct format.</span>
      </p>

      <div class="item_1">
        <label for="pass">Password: </label><br />
        <input type="password" id="pass" name="pass" />
      </div>
      <p class="center">
        <span id="pwd_err" class="require hidden error-text">Password requirement: no spaces, must be 8
          characters
          or
          longer</span>
      </p>

      <div class="container">
        <input class="button button1" type="submit" value="Log in" />
        <nav><a href="index.php"></a></nav>
      </div>

      <div class="centered-header">
        <h2 id="login_or">OR</h2>
      </div>
    </div>
  </form>

  <form class="loginMain button1" action="signUp.php" method="get">
    <div class="container">
      <input class="button button1" id="loginBut" type="submit" value="Create an account" />
      <nav><a href=""></a>
      </nav>
    </div>
  </form>
  <script src="js/login.js"></script>
</body>

</html>