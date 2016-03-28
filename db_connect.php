<?php
//connect to database
$host = "localhost";
$username = "user";
$password = "password";
$database = "dbloi";

$connect = mysqli_connect($host, $username, $password, $database)
or die("Fout tijdens het verbinden met de database. De database dbLOI en de benodigde tabellen bestaan waarschijnlijk nog niet.");
?>