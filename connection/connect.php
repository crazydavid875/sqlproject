<?php

$conn_IP = "localhost:3308";
$conn_userName = "root";
$conn_passwd = "1234";
$conn_db = "gameshop";

$sql = new mysqli($conn_IP,$conn_userName,$conn_passwd,$conn_db);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    echo mysqli_connect_error();
    exit();
}