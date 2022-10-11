<?php
$mysqli = new mysqli("", "", "", "");
if ($mysqli->connect_errno) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
}
?>