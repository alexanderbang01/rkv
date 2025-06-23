<?php
$dbhost = "127.0.0.1";
$dbuser = "root";
$dbpass = "";
$db = "rkv";

$conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n" . $conn->error);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
