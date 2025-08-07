<?php
$host = 'localhost';
$dbname = 'campusbite';
$username = 'root';
$password = '';

$connectdb = mysqli_connect($host, $username, $password, $dbname);

if (!$connectdb) {
    die("Connection failed: " . mysqli_connect_error());
}
?>