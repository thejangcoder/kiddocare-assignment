<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "northwindmysql";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: ". $conn->connect_error);
}

// Change character set to utf8
$conn->set_charset("utf8");
?>