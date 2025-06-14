<?php

session_start();

$host = "sql_injection_lab_db"; // Use IP instead of localhost
$user = "root";
$password = "password"; // make sure this is correct
$dbname = "security_lab";

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
