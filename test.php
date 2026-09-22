<?php
    $password = "adminpassword321";
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    echo $hashedPassword;
?>