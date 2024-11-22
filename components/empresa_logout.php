<?php
include '../components/connect.php';
session_start();
session_unset();
session_destroy();
header('location:../empresa/empresa_login.php');
?> 