<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "student_id_cards";

$conn = mysqli_connect($host, $user, $pass, $dbname);

define('UPLOAD_DIR', __DIR__ . '/uploads/');

function getSetting($conn, $key) {
    $query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = '$key'");
    if ($row = mysqli_fetch_assoc($query)) {
        return $row['setting_value'];
    }
    return '';
}

function updateSetting($conn, $key, $value) {
    mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value = '$value'");
}

?>
