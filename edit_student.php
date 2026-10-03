<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header("Location: index.php?msg=notfound");
    exit;
}

$result = mysqli_query($conn, "SELECT s.* FROM students s WHERE s.id = '$id'");
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: index.php?msg=notfound");
    exit;
}

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

$errors = [];

$parts = explode(' ', trim($student['name']));
$first_name = $parts[0] ?? '';
$last_name = count($parts) > 1 ? array_pop($parts) : '';
array_shift($parts);
$middle_name = implode(' ', $parts);

$enrollment_no = $student['enrollment_no'];
$branch = $student['branch'];
$course = $student['course'] ?? '';
$dob = $student['dob'];
$mobile = $student['mobile'];
$blood_group = $student['blood_group'];
$address = $student['address'];

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

    if ($first_name === '' || $middle_name === '' || $last_name === '') {
        $errors[] = "First, Middle, and Last Name are required.";
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $first_name) || !preg_match('/^[A-Za-z\s]+$/', $middle_name) || !preg_match('/^[A-Za-z\s]+$/', $last_name)) {
        $errors[] = "Name cannot contain any special characters or numbers.";
    }

    if ($enrollment_no === '') {
        $errors[] = "Enrollment No is required.";
    } elseif (!preg_match('/^\d{12}$/', $enrollment_no)) {
        $errors[] = "Enrollment No. must be exactly 12 digits.";
    }

    if ($mobile === '') {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^\d{10}$/', $mobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }

    if ($dob === '') {
        $errors[] = "Date of Birth is required.";
    }
    if ($branch === '') {
        $errors[] = "Please select a branch.";
    }
    if ($course === '') {
        $errors[] = "Course is required.";
    }
    if ($blood_group === '') {
        $errors[] = "Blood group is required.";
    }
    if ($address === '') {
        $errors[] = "Address is required.";
    }

    if (empty($errors)) {
        $check = mysqli_query($conn, "SELECT enrollment_no, mobile FROM students WHERE (enrollment_no = '$enrollment_no' OR mobile = '$mobile') AND id != '$id'");
        while ($row = mysqli_fetch_assoc($check)) {
            if ($row['enrollment_no'] === $enrollment_no) {
                $errors[] = 'Enrollment No. is already registered to another student.';
            }
            if ($row['mobile'] === $mobile) {
                $errors[] = 'Mobile number is already registered to another student.';
            }
        }
    }

    $name = trim("$first_name $middle_name $last_name");
    $name = preg_replace('/\s+/', ' ', $name);

    $photo = $student['photo'];

    if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = $_FILES['photo']['name'];
            $file_size = $_FILES['photo']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed)) {
                $errors[] = "Invalid photo format. Only JPG, JPEG, PNG, and GIF files are allowed.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $errors[] = "Photo size must not exceed 2MB.";
            } else {
                $new_photo = time() . '_' . rand(1000, 9999) . '.' . $ext;

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0777, true);
                }

                if (move_uploaded_file($file_tmp, UPLOAD_DIR . $new_photo)) {
                    if (!empty($student['photo']) && file_exists(UPLOAD_DIR . $student['photo'])) {
                        @unlink(UPLOAD_DIR . $student['photo']);
                    }
                    $photo = $new_photo;
                } else {
                    $errors[] = "Failed to upload photo.";
                }
            }
        } else {
            $errors[] = "An error occurred while uploading the photo.";
        }
    }

    if (empty($errors)) {
        $update_sql = "UPDATE students SET 
            enrollment_no = '$enrollment_no',
            name = '$name', 
            dob = '$dob', 
            mobile = '$mobile', 
            address = '$address', 
            branch = '$branch', 
            course = '$course', 
            blood_group = '$blood_group', 
            photo = '$photo' 
            WHERE id = '$id'";

        if (mysqli_query($conn, $update_sql)) {
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $errors[] = "Database update failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="settings.php">Settings</a>
    </div>

    <h1>Edit Student</h1>

    <?php if (!empty($errors)): ?>
        <div class="msg-error">
            <ul style="margin-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_student.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="form-group">
            <label for="enrollment_no">Enrollment No: *</label>
            <input type="text" name="enrollment_no" id="enrollment_no" required pattern="\d{12}" title="Must be exactly 12 digits" maxlength="12" minlength="12" value="<?php echo htmlspecialchars($enrollment_no); ?>">
        </div>

        <div class="form-group">
            <label for="first_name">First Name: *</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed">
        </div>

        <div class="form-group">
            <label for="middle_name">Middle Name: *</label>
            <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name: *</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required pattern="^[A-Za-z\s]+$" title="Only alphabets allowed">
        </div>

        <div class="form-group">
            <label for="branch">Branch: *</label>
            <select name="branch" id="branch" required>
                <option value="">Select Branch</option>
                <?php foreach ($static_branches as $b): ?>
                    <option value="<?php echo $b; ?>" <?php if ($branch === $b) echo 'selected'; ?>><?php echo htmlspecialchars($b); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="course">Course: *</label>
            <select name="course" id="course" required>
                <option value="">Select Course</option>
                <?php foreach ($static_courses as $c): ?>
                    <option value="<?php echo $c; ?>" <?php if ($course === $c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth: *</label>
            <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($dob); ?>" required>
        </div>

        <div class="form-group">
            <label for="mobile">Mobile Number: *</label>
            <input type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required pattern="\d{10}" title="Must be exactly 10 digits" maxlength="10" minlength="10">
        </div>

        <div class="form-group">
            <label for="blood_group">Blood Group:</label>
            <select name="blood_group" id="blood_group" required>
                <option value="">Select Blood Group</option>
                <?php
                $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($blood_groups as $bg) {
                    $selected = ($blood_group == $bg) ? 'selected' : '';
                    echo "<option value=\"$bg\" $selected>$bg</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <textarea name="address" id="address" required><?php echo htmlspecialchars($address); ?></textarea>
        </div>

        <div class="form-group">
            <label>Photo:</label>
            <?php if (!empty($student['photo'])): ?>
                <div class='current-photo'>
                    <img src='uploads/<?php echo htmlspecialchars($student['photo']); ?>'>
                    <p>Current photo</p>
                </div>
            <?php endif; ?>
            <input type="file" name="photo" id="photo" accept="image/*">
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</body>
</html>
