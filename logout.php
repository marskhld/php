<?php 
session_start();

// Destroy the PHP session
session_unset();
session_destroy();

// redirect the user back to the main page
header("Location: index.php");
exit();

?>