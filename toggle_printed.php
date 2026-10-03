<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = isset($_GET['status']) ? intval($_GET['status']) : 1;
    
    if ($id > 0) {
        mysqli_query($conn, "UPDATE students SET is_printed = '$status' WHERE id = '$id'");
    }
}

header("Location: index.php");
exit;
?>
