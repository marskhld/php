<?php
session_start();
require_once("db.php");

if (!isset($_SESSION["username"])) {
  header("Location: login.php");
  exit();
} /*else { // check user that is logged in
  print_r($_SESSION["username"]);
}*/

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
  throw new PDOException($e->getMessage(), (int) $e->getCode());
}
/* // check values of global variables
echo "session: ";
print_r($_SESSION);
echo "</br> get: ";
print_r($_GET);
echo "</br> post ";
print_r($_POST);*/

if (!empty($_GET["answer_ID"])) {
  $votesQuery = "SELECT downvote, upvote, vote_ID FROM Votes WHERE user_ID='$_SESSION[user_ID]' AND answer_ID='$_GET[answer_ID]' "; // check if user already voted or not
  $_SESSION["answer_ID"] = $_GET["answer_ID"];
  $votesResults = $db->query($votesQuery);
  $votesRow = $votesResults->fetch(); // grab the data
  /*echo "</br> votesRow: ";
  ($votesRow);*/
}
if (!empty($_GET["q_ID"])) {
  $_SESSION["q_ID"] = $_GET["q_ID"];
}
// user upvoted an answer
if (isset($_POST["upvoteForm"])) {
  if (!empty($votesRow["downvote"])) { // user previously downvoted the answer
    $updateVote = "UPDATE Votes SET user_ID = '$_SESSION[user_ID]', answer_ID = '$_GET[answer_ID]', upvote = 1, downvote = 0 WHERE vote_ID='$votesRow[vote_ID]'"; // update from upvote->downvote
    $r = $db->exec($updateVote);
    if (!$r) {
      $error["Database error"] = "Failed to update from upvote to downvote";
    } else { // sucessful
      $db = null;
      header("Location: qManage.php");
      exit();
    }
  } elseif (!empty($votesRow["upvote"])) { // user clicked upvote when they previously already upvoted
    // delete the upvote
    $deleteVote = "DELETE FROM Votes WHERE vote_ID='$votesRow[vote_ID]'";
    $r = $db->exec($deleteVote);
    if (!$r) { // error
      $error["Database error"] = "Failed to delete downvote";
    } else { // error in deleting
      $db = null;
      header("Location: qManage.php");
      exit();
    }
  } else { // user did not previously upvote or downvote - insert upvote
    $upvoteQuery = "INSERT INTO Votes (user_ID, answer_ID, upvote) VALUES('$_SESSION[user_ID]', '$_GET[answer_ID]', 1)"; // query to upvote answer
    $upvoteResult = $db->exec($upvoteQuery); // insert upvote
    if (!$upvoteResult) { // error in upvoting
      $errors["Database error:"] = "Failed to upvote answer";
    } else { // success
      $db = null;
      header("Location: qManage.php"); // take to question detail page with new upvote posted
      exit();
    }
  }
}
// user downvoted an answer
if (isset($_POST["downvoteForm"])) { // user downvoted
  if (!empty($votesRow["upvote"])) {
    $updateVote = "UPDATE Votes SET user_ID = '$_SESSION[user_ID]', answer_ID = '$_GET[answer_ID]', upvote = 0, downvote = 1 WHERE vote_ID='$votesRow[vote_ID]'"; // update from upvote->downvote
    $r = $db->exec($updateVote);
    if (!$r) {
      $error["Database error"] = "Failed to update from downvote to upvote";
    } else { // sucessful
      $db = null;
      header("Location: qManage.php");
      exit();
    }
  } elseif (!empty($votesRow["downvote"])) { // user clicked downvote when they previously already downvoted
    // delete the downvote
    $deleteVote = "DELETE FROM Votes WHERE vote_ID='$votesRow[vote_ID]'";
    $r = $db->exec($deleteVote);
    if (!$r) { // error
      $error["Database error"] = "Failed to delete downvote";
    } else { // error in deleting
      $db = null;
      header("Location: qManage.php");
      exit();
    }
  } else { // both upvote and downvote are empty
    $downvoteQuery = "INSERT INTO Votes (user_ID, answer_ID, downvote) VALUES('$_SESSION[user_ID]', '$_GET[answer_ID]', 1)"; // query to downvote answer
    $downvoteResult = $db->exec($downvoteQuery);
    if (!$downvoteResult) {
      $errors["Database error:"] = "Failed to downvote answer";
    } else {
      $db = null;
      header("Location: qManage.php"); // take to question detail page with new answer posted
      exit();
    }
  }
} // vote updating code ends
if(isset($_GET["user_ID"])){
  $_SESSION["viewUserID"] = $_GET["user_ID"];
}

$error = array();
$dataOK = TRUE;
$q = "SELECT 
    Q.question, 
    Q.q_ID,
    Q.time AS q_Time, 
    IFNULL(U.screen_Name, 0) AS screen_Name, 
    U.user_ID AS user_ID,
    IFNULL(U.profile_photo, 0) AS avatar,
    IFNULL(A.answer, 0) AS answer,
    A.answer_ID AS answer_ID,
    IFNULL (A.time, 0) AS time,
    IFNULL(sum(V.upvote > 0), 0) AS upvotes, 
    IFNULL(sum(V.downvote > 0), 0) AS downvotes
  FROM Votes AS V
  RIGHT JOIN Answers AS A ON A.answer_ID=V.answer_ID
  RIGHT JOIN Questions AS Q ON Q.q_ID=A.q_ID
  LEFT JOIN Users AS U ON A.user_ID=U.user_ID
  WHERE Q.user_ID='$_SESSION[viewUserID]' 
  GROUP BY A.answer_ID, Q.q_ID";

$results = $db->query($q);
$user = "SELECT screen_Name, profile_photo AS avatar FROM Users WHERE user_ID='$_SESSION[viewUserID]'";
//print_r($user);
$r = $db->query($user);
$userRow = $r->fetch();

?>


<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <title>Question Management</title>
</head>

<body>
  <header class="headingTab">
    <div class="logo">
      <a href="index.php">
        <img src="images/logo.png" class="logoIcon" alt="site logo" />
      </a>
    </div>

    <div class="title">
      <h1>Manage Questions</h1>
    </div>
    <?php
    if (isset($_SESSION["username"])) { // user is logged in
      ?>
      <nav class="dropdown">
        <button class="dropbtn"><img src="<?= $_SESSION["avatar"] ?>" alt="option"
            class="dropIcon" /><span>&#9662;</span></button>
        <div class="dropdown-content">

          <a href="qCreation.php">Create a question</a>
          <a href="qManage.php">Manage your questions</a>
          <a href="logout.php">Logout</a>
          <?php
    } else { // user not logged in
      ?>
          <nav class="dropdown">
            <button class="dropbtn"><img src="images/icon.png" alt="option"
                class="dropIcon" /><span>&#9662;</span></button>
            <div class="dropdown-content">
              <a href="login.php">Login</a>
              <a href="signUp.php">Sign up</a>
              <a href="logout.php">Logout</a>
            </div>
          </nav>
          <?php
    }
    ?>
  </header>


  <div id="widePageContainer">
    <div id="bodyDivWide">

      <div id="userIdDiv">
        <img src="<?= $userRow["avatar"] ?>" alt=" icon" class="askerIcon" />
        <span class="askerUsername">
          <?= $userRow["screen_Name"] ?>
        </span>
      </div><br />

      <table id="questionsTable">
        <tr>
          <?php
          while ($row = $results->fetch()) {
            if ($row["answer"] != 0) {
              ?>
              <td class="questionsTableRow">
                <div class="date">
                  <h3>
                    <?= $row["q_Time"] ?>
                  </h3>
                </div>

                <div class="questionManageContents">
                  <h3>
                    <a href="questionDetail.php?q_ID=<?= $row["q_ID"] ?>">
                      <?= $row["question"] ?>
                    </a>
                  </h3>
                  <hr />

                  <div class="userInfo">
                    <img src="<?= $row["avatar"] ?>" alt="icon" class="answererIcon" />

                    <h4>
                      <span class="username">
                        <a href="qManage.php?userScreenName=<?=$row["screen_Name"]?>&user_ID=<?=$row["user_ID"]?>&answer_ID=<?= $row["answer_ID"]?>">
                          <?= $row["screen_Name"] ?>
                        </a>
                      </span>
                      <span class="votes"> &nbsp;(
                        <?= $row["upvotes"] - $row["downvotes"] ?> Net Votes)
                      </span>
                    </h4>
                  </div>

                  <div class="answerContainer">
                    <div class="answerContents">
                      <?= $row["answer"] ?>
                    </div>
                  </div>
                  <form action="qManage.php?answer_ID=<?= $row["answer_ID"] ?>" method="post">
                    <div class="votingButtons">
                      <button type="submit" name="upvoteForm" class="upvote">
                        &Hat;
                        <?= $row["upvotes"] ?>
                      </button>
                      <button type="submit" name="downvoteForm" class="downvote">
                        &#8964;
                        <?= $row["downvotes"] ?>
                      </button>
                    </div><br />
                  </form>

                  <span class="moreAnswers"><a href="questionDetail.php">Load More Answers &#9662;</a></span>
                  <hr />
                </div>

              </td>
              <?php
            } else { // no answers
              ?>
              <td class="questionsTableRow">
                <div class="date">
                  <h3>
                    <?= $row["q_Time"] ?>
                  </h3>
                </div>

                <div class="questionManageContents">
                  <h3>
                    <a href="questionDetail.php?q_ID=<?= $row["q_ID"] ?>">
                      <?= $row["question"] ?>
                    </a>
                  </h3>
                  <hr />
                  <h5>     No answers yet.</h5>
                  <hr />
                </div>
              </td>
              <?php
            }
          }
          ?>
        </tr>
      </table>
    </div>
  </div>

</body>

</html>