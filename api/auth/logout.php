<?php
// api/auth/logout.php

session_start();
session_destroy();
header("Location: ../../forms/login.html");
exit();
?>