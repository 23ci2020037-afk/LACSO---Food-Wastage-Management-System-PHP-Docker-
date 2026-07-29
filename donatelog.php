<?php
session_start();
session_destroy();
header("Location: login_signup.html"); // Your login/signup page
exit;
?>
