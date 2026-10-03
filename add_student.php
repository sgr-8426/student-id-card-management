<?php
require_once 'config.php';

$first_name = '';
$middle_name = '';
$last_name = '';
$enrollment_no = '';
$branch = '';
$course = '';
$dob = '';
$mobile = '';
$blood_group = '';
$address = '';
$error = '';

$static_branches = [
    'Computer Engineering',
    'Information Technology',
    'Electronics & Communication Engineering',
    'Electrical Engineering',
    'Mechanical Engineering',
    'Civil Engineering',
    'Other'
];

$static_courses = [
    'Bachelor of Engineering',
    'Master of Engineering',
    'Diploma',
    'Ph.D',
    'Other'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $enrollment_no = trim($_POST['enrollment_no'] ?? '');
    $branch = trim($_POST['branch'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($first_name === '' || $middle_name === '' || $last_name === '' || $dob === '' || 
        $mobile === '' || $address === '' || $branch === '' || 
        $blood_group === '' || $enrollment_no === '' || $course === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $first_name) || !preg_match('/^[A-Za-z\s]+$/', $middle_name) || !preg_match('/^[A-Za-z\s]+$/', $last_name)) {
        $error = 'Name cannot contain any special characters or numbers.';
    } elseif (!preg_match('/^\d{10}$/', $mobile)) {
        $error = 'Mobile number must be exactly 10 digits.';
    } elseif (!preg_match('/^\d{12}$/', $enrollment_no)) {
        $error = 'Enrollment No. must be exactly 12 digits.';
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = empty($error) ? 'Photo is required.' : $error;
    }

    if (empty($error)) {
        $check = mysqli_query($conn, "SELECT enrollment_no, mobile FROM students WHERE enrollment_no = '$enrollment_no' OR mobile = '$mobile'");
        while ($row = mysqli_fetch_assoc($check)) {
            if ($row['enrollment_no'] === $enrollment_no) {
                $error = 'Enrollment No. is already registered.';
            }
            if ($row['mobile'] === $mobile) {
                $error = 'Mobile number is already registered.';
            }
        }
    }

    $name = trim("$first_name $middle_name $last_name");
    $name = preg_replace('/\s+/', ' ', $name);

    $photo = null;
    if (empty($error) && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error uploading photo. Please try again.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = 'Photo must be less than 2MB.';
        } else {
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_exts)) {
                $error = 'Only JPG and PNG images are allowed.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }
                $photo = 'photo_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $photo)) {
                    $error = 'Failed to save uploaded photo.';
                    $photo = null;
                }
            }
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO students (
                    enrollment_no, name, dob, mobile, address, branch, course, blood_group, photo
                ) VALUES (
                    '$enrollment_no', '$name', '$dob', '$mobile', '$address', '$branch', '$course', '$blood_group', '$photo'
                )";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?msg=added");
            exit;
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
            if ($photo && file_exists(UPLOAD_DIR . $photo)) {
                unlink(UPLOAD_DIR . $photo);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="settings.php">Settings</a>
    </div>

    <h1>Add New Student</h1>

    <?php if (!empty($error)): ?>
        <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="add_student.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="enrollment_no">Enrollment No *</label>
            <input type="text" name="enrollment_no" id="enrollment_no" required pattern="\d{12}" title="Must be exactly 12 digits" maxlength="12" minlength="12" value="<?php echo htmlspecialchars($enrollment_no); ?>">
        </div>

        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" name="first_name" id="first_name" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed" value="<?php echo htmlspecialchars($first_name); ?>">
        </div>

        <div class="form-group">
            <label for="middle_name">Middle Name *</label>
            <input type="text" name="middle_name" id="middle_name" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed" value="<?php echo htmlspecialchars($middle_name); ?>">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" name="last_name" id="last_name" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed" value="<?php echo htmlspecialchars($last_name); ?>">
        </div>

        <div class="form-group">
            <label for="branch">Branch *</label>
            <select name="branch" id="branch" required>
                <option value="">-- Select Branch --</option>
                <?php foreach ($static_branches as $b): ?>
                    <option value="<?php echo $b; ?>" <?php if ($branch === $b) echo 'selected'; ?>><?php echo htmlspecialchars($b); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="course">Course *</label>
            <select name="course" id="course" required>
                <option value="">-- Select Course --</option>
                <?php foreach ($static_courses as $c): ?>
                    <option value="<?php echo $c; ?>" <?php if ($course === $c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth *</label>
            <input type="date" name="dob" id="dob" required value="<?php echo htmlspecialchars($dob); ?>">
        </div>

        <div class="form-group">
            <label for="mobile">Mobile Number *</label>
            <input type="text" name="mobile" id="mobile" required pattern="\d{10}" title="Must be exactly 10 digits" maxlength="10" minlength="10" value="<?php echo htmlspecialchars($mobile); ?>">
        </div>

        <div class="form-group">
            <label for="blood_group">Blood Group *</label>
            <select name="blood_group" id="blood_group" required>
                <option value="">-- Select Blood Group --</option>
                <?php
                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($blood_groups as $bg):
                ?>
                    <option value="<?php echo $bg; ?>" <?php if ($blood_group === $bg) echo 'selected'; ?>><?php echo $bg; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Address *</label>
            <textarea name="address" id="address" required><?php echo htmlspecialchars($address); ?></textarea>
        </div>

        <div class="form-group">
            <label for="photo">Photo *</label>
            <input type="file" name="photo" id="photo" accept=".jpg, .jpeg, .png" required>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</body>
</html>
