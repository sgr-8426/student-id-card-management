<?php
require_once 'config.php';

$success = '';
$error = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $success = 'Settings saved successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';

    if ($college_name === '') {
        $error = 'College Name is required.';
    } else {
        if (isset($_FILES['college_logo']) && $_FILES['college_logo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['college_logo']['tmp_name'];
            $file_name = $_FILES['college_logo']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Invalid logo format. Allowed types: JPG, JPEG, PNG, GIF.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }

                $new_logo_name = 'logo_' . time() . '.' . $file_ext;
                $destination = UPLOAD_DIR . $new_logo_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    updateSetting($conn, 'college_logo', $new_logo_name);
                } else {
                    $error = 'Failed to upload college logo.';
                }
            }
        } elseif (isset($_FILES['college_logo']) && $_FILES['college_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = 'An error occurred while uploading the logo file.';
        }
        
        if (empty($error) && isset($_FILES['principal_signature']) && $_FILES['principal_signature']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['principal_signature']['tmp_name'];
            $file_name = $_FILES['principal_signature']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Invalid signature format. Allowed types: JPG, JPEG, PNG, GIF.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }

                $new_sig_name = 'sig_' . time() . '.' . $file_ext;
                $destination = UPLOAD_DIR . $new_sig_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    updateSetting($conn, 'principal_signature', $new_sig_name);
                } else {
                    $error = 'Failed to upload principal signature.';
                }
            }
        } elseif (isset($_FILES['principal_signature']) && $_FILES['principal_signature']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = 'An error occurred while uploading the signature file.';
        }

        if (empty($error)) {
            updateSetting($conn, 'college_name', $college_name);
            header('Location: settings.php?msg=saved');
            exit;
        }
    }
}

$college_name = getSetting($conn, 'college_name');
$college_logo = getSetting($conn, 'college_logo');
$principal_signature = getSetting($conn, 'principal_signature');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="settings.php">Settings</a>
    </div>

    <h1>Settings</h1>

    <?php if ($success): ?>
        <div class="msg-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="settings.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="college_name">College Name:</label>
            <input type="text" id="college_name" name="college_name" value="<?php echo htmlspecialchars($college_name); ?>" required>
        </div>

        <div class="form-group">
            <label for="college_logo">College Logo:</label>
            <input type="file" id="college_logo" name="college_logo" accept="image/*">
            <?php if (!empty($college_logo) && file_exists(UPLOAD_DIR . $college_logo)): ?>
                <div class="current-photo" style="margin-left: 185px;">
                    <img src="uploads/<?php echo htmlspecialchars($college_logo); ?>" alt="Current College Logo" style="max-height: 80px; width: auto; object-fit: contain;">
                    <br><small style="color: #666;">Current file: <?php echo htmlspecialchars($college_logo); ?></small>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="principal_signature">Principal Signature:</label>
            <input type="file" id="principal_signature" name="principal_signature" accept="image/*">
            <?php if (!empty($principal_signature) && file_exists(UPLOAD_DIR . $principal_signature)): ?>
                <div class="current-photo" style="margin-left: 185px;">
                    <img src="uploads/<?php echo htmlspecialchars($principal_signature); ?>" alt="Current Signature" style="max-height: 80px; width: auto; object-fit: contain;">
                    <br><small style="color: #666;">Current file: <?php echo htmlspecialchars($principal_signature); ?></small>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</body>
</html>
