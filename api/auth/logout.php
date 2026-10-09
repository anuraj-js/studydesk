<?php
// api/auth/logout.php

session_start();
$_SESSION = [];
session_destroy();
header("Location: ../../forms/login.html");
exit();
?>