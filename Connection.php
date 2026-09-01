<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "MunchyKrunchy";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn) {
    //echo "Database connected successfully.";
} else {
    echo "Database connection failed.";
}
?>