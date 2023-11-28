<?php
/*  Sign-up Page
•	validate the form data to ensure that the required fields are present and that they do not contain illegal data; if there is a problem, return to the sign-up form with a generic error message
•	if the data is good, add it to the database and return the user to the Main Page so they can login
•	for the user avatar image/graphic upload, move the image file to an appropriate location within the web application file structure, and save its location (URL) in the database
•	see http://www.php.net/manual/en/features.file-upload.post-method.php for more information on managing file uploads.
*/
session_start();
// include the database connection file
require_once("db.php");

if (isset($_SESSION["username"])) {
  header("Location: index.php");
  exit();
}
/*
echo "post: ";
print_r($_POST);
echo "</br>get: ";
print_r($_GET);*/
// check that there are no unsafe characters in the submitted data
function test_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
// regex to validate form elements
$screenNameRegex = "/^[\s\W]$/";
$emailRegex = "/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
$passwordRegex = "/\S{8,}$/";
$dobRegex = "/^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/";

$screenName = "";
$email = "";
$password = "";
$dob = "";

// if form submitted...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // make SQL connection - catch and report any errors
  try {
    $db = new PDO($attr, $db_user, $db_pwd, $options);
  } catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int) $e->getCode());
  }
  // track errors found while processing form data
  $error = array();
  //echo "is this working";

  $screenName = test_input($_POST["screenname"]);
  $email = test_input($_POST["email"]);
  $dob = test_input($_POST["dob"]);
  $password = test_input($_POST["password"]);

  // variable that will specify if all data is okay
  $dataOK = TRUE;

  // Validate screen name
  if ($screenName == null || $screenName == "" || preg_match($screenNameRegex, $screenName)) {
    $errors['screenName'] = "Invalid Screen Name $screenName";
    $dataOK = FALSE;
  }
  // Validate email format
  if ($email == null || $email == "" || !preg_match($emailRegex, $email)) {
    $errors['email'] = "Invalid email address: $email";
  } else {
    // If email valid, check if it is already taken.

    $query = "SELECT username FROM Users WHERE username ='$email'";
    $result = $db->query($query);

    if (!$result) {
      $errors['email'] = "Email address $email already exists.";
    }
  }

  // Validate password
  if ($password == null || $password == "" || !preg_match($passwordRegex, $password)) {
    $dataOK = FALSE;
    $errors['password'] = "Invalid password.";
  }
  // Validate birthday
  if ($dob == null || $dob == "" || !preg_match($dobRegex, $dob)) {
    $errors['dob'] = "Invalid birthday.";
    $dataOK = FALSE;
  }
  //If there are no errors so far, (user does not exist), we can try inserting a user
  if (empty($errors)) {
    $query = "INSERT INTO Users (screen_Name, username, password, dob, profile_photo) VALUES ('$screenName', '$email', '$password', '$dob', 'avatar_stub')";
    //print_r($query);
    $result = $db->exec($query);

    if (!$result) { // query unsuccessful
      $errors["Database Error:"] = "Failed to insert user";
    } else { // query successful (result has data), so upload file 
      // Directory where the avatars will be uploaded.
      $target_dir = "uploads/";
      $uploadOk = TRUE;

      // Fetch the image filetype
      $imageFileType = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
      //print_r($imageFileType);
      // get the uid from the last insert query.
      $uid = $db->lastInsertId();

      // Rename the user's image to "uploads/uid.filetype" e.g: "uploads/12.jpg"
      $target_file = $target_dir . $uid . "." . $imageFileType;

      // Check whether the file exists in the uploads directory
      if (file_exists($target_file)) {
        $errors["avatar"] = "Sorry, file already exists. ";
        $uploadOk = FALSE;
      }

      if (array_key_exists("avatar", $_FILES)) {
        // Check whether the file is too large - maximum ~1MB
        if ($_FILES["avatar"]["size"] > 1000000) {
          $errors["avatar"] = "Sorry, your file is too large - maximum size is 1MB.";
          $uploadOk = FALSE;
        }
      }
      // Check the file extension to be sure it is an image
      if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" || $imageFileType == "" || $imageFileType == null) {
        $errors["avatar"] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = FALSE;
      }
      // Check if $uploadOk still TRUE after validations
      if ($uploadOk) {
        // Move the user's avatar to the uploads directory and capture the result as $fileStatus.
        $fileStatus = move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file);

        // Check $fileStatus:
        if (!$fileStatus) {
          // The user's avatar file could not be moved
          // TODO 9a: add a suitable error message to errors array be displayed on the page
          $errors["Server Error"] = "Sorry, the file was unable to be uploaded successfully.";
          $uploadOK = FALSE;
        }
      }

      // Check if $uploadOk still TRUE after attempt to move
      if (!$uploadOk) {
        $query = "DELETE FROM Users WHERE user_ID='$uid'";
        $result = $db->exec($query);
        if (!$result) {
          $errors["Database Error"] = "could not delete user when avatar upload failed";
        }
        $db = null;
      } else {
        $query = "UPDATE Users SET profile_photo='$target_file' WHERE user_ID='$uid'";
        $result = $db->exec($query);
        if (!$result) {
          $errors["Database Error:"] = "could not update avatar_url";
        } else {
          $db = null;
          header("Location: index.php");
          exit();
        }
      } // image was uploadOk
    } // Insert user query worked
  } //validation was checked and no errors
  if (!empty($errors)) {
    foreach ($errors as $type => $message) {
      print("$type: $message \n<br />");
    }
  }
} // else form not submitted
?>


<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <title>Sign Up</title>
  <link rel="stylesheet" href="css/style.css" />
  <script src="js/eventHandler.js"></script>
</head>

<body>

  <header class="headingTab">
    <div class="logo">
      <a href="index.php"><img class="logoIcon" src="images/logo.png" alt="logo" /></a>
    </div>

    <div class="title">
      <h1>Register a new account</h1>
    </div>
  </header>

  <div id="signupMain">
    <form class="form" action="signUp.php" method="post" id="signupForm" name="signupForm" enctype="multipart/form-data">
      <div class="chooseAvatar">

        <h5><img class="avatar" src="images/icon.png" alt="avatar" />
          <input class="file-upload" type="file" accept="image/*" name="avatar" id="avatar" />
          <span>Click to upload your avatar.</span>
        </h5>
      </div>

      <p class="input-field">
        <label for="username">Screen name: </label>
        <input type="text" id="username" name="screenname" />
        <span id="uname_err" class="require hidden error-text">
          Screen name requirement: no spaces, no non-word characters</span>
      </p>

      <p class="input-field">
        <label for="email">Email: </label>
        <input type="text" id="email" name="email" value="<?= $email ?>" />
        <span id="email_err" class="require hidden error-text">Invalid Email Address - ensure that email address is in
          correct
          format.</span>
      </p>

      <p class="input-field">
        <label for="password">Password: </label>
        <input type="password" id="password" name="password" value="<?= $password ?>" />
        <span id="pwd_err" class="require hidden error-text">Password requirement: no spaces, must be 8
          characters or
          longer</span>
      </p>

      <p class="input-field">
        <label for="password2">Re-enter your password: </label>
        <input type="password" id="password2" name="password" />
        <span id="cpwd_err" class="require hidden error-text">Does not match password.</span>
      </p>

      <p class="input-field">
        <label for="dob">Birthday:</label>
        <input type="date" name="dob" id="dob" value="<?= $dob ?>" />
        <span id="dob_err" class="require hidden error-text">Invalid Date of Birth - ensure it is in proper
          formatting.</span>
      </p>

      <div class="container">
        <input class="button button1" type="submit" value="Submit" />
        <nav><a href="index.php"></a></nav>
      </div>

    </form>
  </div>
  <script src="js/signup.js"></script>
</body>

</html>