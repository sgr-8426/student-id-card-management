<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $result = mysqli_query($conn, "SELECT photo FROM students WHERE id = '$id'");
    
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['photo'] && file_exists(UPLOAD_DIR . $row['photo'])) {
            unlink(UPLOAD_DIR . $row['photo']);
        }
        
        mysqli_query($conn, "DELETE FROM students WHERE id = '$id'");
    }
}

header("Location: index.php?msg=deleted");
exit;
