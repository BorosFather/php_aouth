<?php
session_start();
session_destroy();
header('Location: google-login.php');
exit();
?>