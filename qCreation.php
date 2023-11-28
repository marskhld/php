<?php 

session_start(); 
require_once("db.php");

if(!isset($_SESSION["username"])) {
  header("Location: login.php");
  exit();
} /*else{
  print_r($_SESSION);
}*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // clean up data
  function test_input($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  // make SQL connection - catch and report any errors
  try {
    $db = new PDO($attr, $db_user, $db_pwd, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
  $errors = array();
  $dataOK = TRUE;

  if (empty($_POST["newQuestion"])) {
    $errors["shortQuestion"] = "Question is required";
    $dataOK = FALSE;
  }
  else {
    $question = test_input($_POST["newQuestion"]);
    if(strlen($question)>200) {
      $errors["longQuestion"] = "Question is too long";
      $dataOK = FALSE;
    }
  }

  if($dataOK && empty($errors)) {
    $query = "INSERT INTO Questions (user_ID, question, time) VALUES ('$_SESSION[user_ID]', '$question', NOW())";
    //($query);
    $result = $db->exec($query); 
    if (!$result) {
      $errors["Database error"] = "Failed to insert question";
    }else{
      $db = null;
      header("Location: index.php");
      exit();
    }
  }
  foreach($errors as $type => $message){
    print("$type: $message \n<br/>");
  }
}

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <title>Question Creation</title>
  <script src="js/eventHandler.js"></script>
</head>

<body>
  <header class="headingTab">
    <div class="logo">
      <a href="index.php">
        <img src="images/logo.png" class="logoIcon" alt="site logo" />
      </a>
    </div>

    <div class="title">
      <h1>Create a Question</h1>
    </div>

    <nav class="dropdown">
      <button class="dropbtn"><img src="images/icon.png" alt="option" class="dropIcon" /><span>&#9662;</span></button>
      <div class="dropdown-content">
        <a href="qManage.php">Manage your questions</a>
        <a href="logout.php">Logout</a>
      </div>
    </nav>
  </header>

  <div id="pageContainer">
    <div id="bodyDiv" class="interactionBox">
      <form action="qCreation.php" method="post" id="questionForm">
        <div id="QAForm">
          <label for="newQuestion">Type your question below:</label>
          <textarea id="newQuestion" class="newQorA" name="newQuestion" rows="5" cols="200"></textarea>
          <br />
          <p id="charStyle">
            <span id="charTracker"><span id="charCount">0</span>/200 </span>
            ---------
            <span id="remainingChars">200</span> remaining characters left.
            <br /><span id="ans-err" class="hidden error-text"> Error: Question must be non-blank and 200 characters or
              fewer</span>
          </p>
          <input type="submit" id="submit" class="bigSubmit" value="Submit" />
        </div>
      </form>
    </div>
  </div>
  <script src="js/qCreation.js"></script>
</body>

</html>