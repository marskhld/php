<?php
  session_start();
  /*if(isset($_SESSION["username"])) // check if logged in
  {
    print_r($_SESSION);
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
  // database connection
  try {
      $db = new PDO($attr, $db_user, $db_pwd, $options);
  } catch (PDOException $e) {
      throw new PDOException($e->getMessage(), (int)$e->getCode());
  }
  // Retrieve the questions 
  $q = "SELECT distinct U.screen_Name AS UserScreenName, 
                        U.user_ID,
                        U.profile_photo AS avatar, 
                        Q.question, Q.q_ID, 
                        count(A.answer) AS NumOfAnswers, 
                        Q.time 
                FROM Users AS U 
                JOIN Questions AS Q ON U.user_ID=Q.user_ID 
                left JOIN Answers AS A ON Q.q_ID=A.q_ID 
                GROUP BY Q.q_ID 
                ORDER BY Q.time desc 
                LIMIT 5";
  $results = $db->query($q);
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <title>Main Page</title>
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>
  <header class="headingTab">
    <div class="logo">
      <img src="images/logo.png" class="logoIcon" alt="site logo" />
    </div>

    <div class="title">
      <h1>Main Page</h1>
    </div>
    <?php 
        if(isset($_SESSION["username"])){ // user is logged in
        ?>
    <nav class="dropdown">
      <button class="dropbtn"><img src="<?=$_SESSION["avatar"]?>" alt="option" class="dropIcon image" /><span>&#9662;</span></button>
      <div class="dropdown-content">
          <a href="qCreation.php">Create a question</a>
          <a href="qManage.php">Manage your questions</a>
          <a href="logout.php">Logout</a>
        <?php 
        }else{ // user not logged in
        ?>
        <nav class="dropdown">
      <button class="dropbtn"><img src="images/icon.png" alt="option" class="dropIcon image" /><span>&#9662;</span></button>
      <div class="dropdown-content">
          <a href="login.php">Login</a>
          <a href="signUp.php">Sign up</a>
      </div>
    </nav>
        <?php 
        }
        ?>
  </header>

  <main id="main">
    <?php
    while($row = $results->fetch())
    {
      ?>
    <div class="left-column">
      <section class="QuestionTime">
        <h3><?=$row["time"]?> 
        </h3>
      </section>
    </div>

    <div class="right-column">

      <div class="Question">

        <section class="qLink">
          <h2>
            <?php
              if (isset($_SESSION["username"])) {
                  $_SESSION["q_ID"] = $row['q_ID'];
                  $questionDetailLink = 'questionDetail.php?q_ID=' . $row['q_ID'] .'&question='.$row["question"];
                  echo '<a href="' . $questionDetailLink . '">Question: ' . $row["question"] . '</a>';
              }else{ 
                echo 'Question: '. $row['question'];
              } ?>
          </h2>
        </section>

        <div class="users">
          <img src="<?=$row["avatar"]?>" class="image" alt="avatarIcon" />
          <?php
          if(isset($_SESSION["username"])){
            $qManage = 'qManage.php?userScreenName='.$row["UserScreenName"] . '&user_ID='.$row["user_ID"];
            echo '<a href="'. $qManage .'"><span class="userScreenName">'. $row["UserScreenName"] .'</span></a>';
          }else{
            echo '<p class="padding" ><span class="userScreenName">'. $row["UserScreenName"] . '</span></p>';
          }
          ?>
          <span class="answerIcon">
            <?=$row["NumOfAnswers"]?> Answer
          </span>
        </div>

      </div>
    </div>
    <?php
    }
    ?>
  </main>

</body>

</html>