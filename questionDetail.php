<?php // DONE!!!!!
session_start();
//print_r($_SESSION["username"]);

if (!isset($_SESSION["username"])) { // user not logged in
  header("Location: login.php");
  exit();
} else { // user is logged in
  // load the database
  require_once("db.php"); // grab connection variables

  try { // establish database connection
    $db = new PDO($attr, $db_user, $db_pwd, $options);
  } catch (PDOException $e) { // catch any errors
    throw new PDOException($e->getMessage(), (int) $e->getCode());
  }
  /*echo "</br> get: ";
  print_r($_GET);
  echo "</br> post ";
  print_r($_POST);
  echo "</br>  sess: ";
  print_r($_SESSION);*/

  if (!empty($_GET["answer_ID"])) {
    $votesQuery = "SELECT downvote, upvote, vote_ID FROM Votes WHERE user_ID='$_SESSION[user_ID]' AND answer_ID='$_GET[answer_ID]' "; // check if user already voted or not
    $_SESSION["answer_ID"] = $_GET["answer_ID"];
    $votesResults = $db->query($votesQuery);
    $votesRow = $votesResults->fetch(); // grab the data
    /*echo "</br> votesRow: ";
    print_r($votesRow);*/
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
        header("Location: questionDetail.php");
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
        header("Location: questionDetail.php");
        exit();
      }
    } else { // user did not previously upvote or downvote - insert upvote
      $upvoteQuery = "INSERT INTO Votes (user_ID, answer_ID, upvote) VALUES('$_SESSION[user_ID]', '$_GET[answer_ID]', 1)"; // query to upvote answer
      $upvoteResult = $db->exec($upvoteQuery); // insert upvote
      if (!$upvoteResult) { // error in upvoting
        $errors["Database error:"] = "Failed to upvote answer";
      } else { // success
        $db = null;
        header("Location: questionDetail.php"); // take to question detail page with new upvote posted
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
        header("Location: questionDetail.php");
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
        header("Location: questionDetail.php");
        exit();
      }
    } else { // both upvote and downvote are empty
      $downvoteQuery = "INSERT INTO Votes (user_ID, answer_ID, downvote) VALUES('$_SESSION[user_ID]', '$_GET[answer_ID]', 1)"; // query to downvote answer
      $downvoteResult = $db->exec($downvoteQuery);
      if (!$downvoteResult) {
        $errors["Database error:"] = "Failed to downvote answer";
      } else {
        $db = null;
        header("Location: questionDetail.php"); // take to question detail page with new answer posted
        exit();
      }
    }
  }

  // run query to retrieve data related to posted answers
  $query = "SELECT 
              Q.question, 
              Q.time, 
              Q.user_ID,
              IFNULL(U.screen_Name, 0) AS screen_Name, 
              IFNULL(U.profile_photo, 0) AS avatar,
              IFNULL(A.answer, 0) AS answer, 
              A.answer_ID,
              IFNULL (A.time, 0) AS time,
              IFNULL(sum(V.upvote > 0), 0) AS upvotes, 
              IFNULL(sum(V.downvote > 0), 0) AS downvotes
            FROM Votes AS V
            RIGHT JOIN Answers AS A ON A.answer_ID=V.answer_ID
            RIGHT JOIN Questions AS Q ON Q.q_ID=A.q_ID
            LEFT JOIN Users AS U ON A.user_ID=U.user_ID
            WHERE Q.q_ID='$_SESSION[q_ID]' 
            GROUP BY A.answer_ID, Q.q_ID";
  $resultQ = $db->query($query);	// run query and store results
  $rowQ = $resultQ->fetch();

  $_SESSION["answer_ID"] = $rowQ["answer_ID"];

  /*echo "</br> rowQ: ";
  print_r($rowQ); // view results of fetched data for qDetail page*/

  // to display question
  $qQ = "SELECT Q.question FROM Questions AS Q WHERE q_ID = '$_SESSION[q_ID]'";
  $qQResult = $db->query($qQ);	// run query and store results
  $qQRow = $qQResult->fetch();


  if ($_SERVER["REQUEST_METHOD"] == "POST") { // answer form was submitted

    /*echo "</br> post </br>";
    print_r($_POST);
    echo "</br>  sess </br>";
    print_r($_SESSION);*/

    //checks that there are no unsafe characters in the submitted data
    function test_input($data)
    {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }
    $errors = array();
    $dataOK = TRUE;

    if (empty($_POST["newAnswer"])) { // no answer entered
      $errors["shortAnswer"] = "Answer is required";
      $dataOK = FALSE;
    } else {
      $answer = test_input($_POST["newAnswer"]); // validate answer
      if (strlen($answer) > 1500) {
        $errors["longAnswer"] = "Answer is too long";
        $dataOK = FALSE;
      }
    }

    if ($dataOK && empty($errors)) { // data is valid and no errors recorded, so insert data into database
      $query = "INSERT INTO Answers (user_ID, q_ID, answer, time)  VALUES ('$_SESSION[user_ID]', '$_SESSION[q_ID]',  '$answer', NOW())";
      //print_r($query);
      $result = $db->exec($query);
      if (!$result) {
        $errors["Database error:"] = "Failed to insert answer";
      } else {
        $db = null;
        header("Location: questionDetail.php"); // take to question detail page with new answer posted
        exit();
      }
    }
    foreach ($errors as $type => $message) {
      print("$type: $message \n<br/>");
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="utf-8" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <title>Question Details</title>
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
      <h1>Question Details</h1>
    </div>

    <nav class="dropdown">
      <button class="dropbtn">
        <img src="<?=$_SESSION["avatar"]?>" alt="option" class="dropIcon" />
        <span>&#9662;</span>
      </button>
      <div class="dropdown-content">
        <a href="qCreation.php">Create a question</a>
        <a href="qManage.php">Manage your questions</a>
        <a href="logout.php">Logout</a>
      </div>
    </nav>
  </header>

  <div id="pageContainer">
    <div id="bodyDiv">
      <h3>Question:
        <?= $rowQ["question"] ?>
      </h3>
      <p class="sortButton"><button>Highest rated &#9662;</button></p>
      <?php
      if ($rowQ["answer"] == 0) { // if answer is zero
        print("<h4>No answers yet.</h4>");
      } else { // answers exist, so...
        do { // iterate thru the following code 
          ?>
          <table>
            <tr>
              <td class="detailAnswer">
                <div class="userInfo">
                  <img src="<?= $rowQ["avatar"] ?>" alt="icon" class="answererIcon" />
                  <h4>
                    <span class="username">
                      <a href="qManage.php?user_ID=<?= $rowQ["user_ID"] ?>">
                        <?= $rowQ["screen_Name"] ?>
                      </a>
                    </span>
                  </h4>
                </div>

                <div class=" answerContainer">
                  <div class="totalVotes">
                    <?= $rowQ["upvotes"] + $rowQ["downvotes"] ?>
                  </div>
                  <div class="answerContents">
                    <?= $rowQ["answer"] ?>
                  </div>
                </div>

                <form action="questionDetail.php?answer_ID=<?= $rowQ["answer_ID"] ?>" method="post">
                  <div class="votingButtons">
                    <button type="submit" name="upvoteForm" class="upvote">
                      &Hat;
                      <?= $rowQ["upvotes"] ?>
                    </button>
                    <button type="submit" name="downvoteForm" class="downvote">
                      &#8964;
                      <?= $rowQ["downvotes"] ?>
                    </button>
                  </div>
                </form>
              </td>
            </tr>

          </table>
          <?php
        } while ($rowQ = $resultQ->fetch()); // repeat loop while there is still data to fetch from results
      }
      ?>
      <p class="moreAnswers">Click for more answers &#9662;</p>


      <form action="questionDetail.php" method="post" id="detailForm">
        <div><label for="newAnswer" class="formLabel">Add your answer</label></div>
        <div><textarea id="newAnswer" class="newQorA" name="newAnswer" rows="5" cols="1500"></textarea></div>
        <p><br />
          <span id="charTracker"><span id="charCount">0</span>/1500</span>
          --------------
          <span id="remainingChars">1500</span> remaining characters left.
          <span id="ans-err" class="hidden error-text"> Error: Answer must be non-blank and 1500 characters or
            fewer</span>
        </p>
        <div id="submitButton">
          <input type="submit" id="submit" value="Submit" />
        </div>
      </form><br />
    </div>
  </div>
  <script src="js/qDetail.js"></script>
</body>

</html>