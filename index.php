<?php
require_once 'config.php';

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$valid_sorts = ['name', 'enrollment_no', 'branch', 'course'];
if (!in_array($sort, $valid_sorts)) {
    $sort = 'name';
}

$next_order = $order == 'ASC' ? 'desc' : 'asc';

$sql = "SELECT * FROM students";

if ($search != '') {
    $sql .= " WHERE name LIKE '%$search%'";
}

if ($sort == 'branch') {
    $sql .= " ORDER BY branch $order";
} else {
    $sql .= " ORDER BY $sort $order";
}

$result = mysqli_query($conn, $sql);

function getSortIcon($col, $sort, $order) {
    if ($col == $sort) {
        return $order == 'ASC' ? '▲' : '▼';
    }
    return '';
}

function buildSortUrl($col, $search, $sort, $next_order) {
    $url = "?sort=$col&order=" . ($sort == $col ? $next_order : 'asc');
    if ($search !== '') {
        $url .= "&search=" . urlencode($search);
    }
    return $url;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="nav">
        <a href="index.php">Dashboard</a>
        <a href="add_student.php">Add Student</a>
        <a href="settings.php">Settings</a>
    </div>

    <h1>Student Dashboard</h1>

    <?php
    if (isset($_GET['msg'])) {
        $msg = $_GET['msg'];
        if ($msg == 'added') echo "<div class='msg-success'>Student added successfully</div>";
        elseif ($msg == 'updated') echo "<div class='msg-success'>Student updated successfully</div>";
        elseif ($msg == 'deleted') echo "<div class='msg-success'>Student deleted successfully</div>";
        elseif ($msg == 'enrolled') echo "<div class='msg-success'>Enrollment numbers generated successfully</div>";
        elseif ($msg == 'notfound') echo "<div class='msg-error'>Student not found</div>";
    }
    ?>

    <div class="toolbar">
        <form method="GET" action="index.php" class="inline-form" style="display:inline-block; margin-right: 10px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name...">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($order == 'ASC' ? 'asc' : 'desc'); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <a href="add_student.php" class="btn btn-primary">Add New Student</a>
        
        <span style="margin-left: 10px;">
            <a href="generate_pdf.php?action=all" class="btn btn-primary">Print All</a>
        </span>
    </div>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:20px; text-align:left;">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Photo</th>
                <th><a href="<?php echo buildSortUrl('enrollment_no', $search, $sort, $next_order); ?>">Enrollment No <?php echo getSortIcon('enrollment_no', $sort, $order); ?></a></th>
                <th><a href="<?php echo buildSortUrl('name', $search, $sort, $next_order); ?>">Name <?php echo getSortIcon('name', $sort, $order); ?></a></th>
                <th><a href="<?php echo buildSortUrl('branch', $search, $sort, $next_order); ?>">Branch <?php echo getSortIcon('branch', $sort, $order); ?></a></th>
                <th><a href="<?php echo buildSortUrl('course', $search, $sort, $next_order); ?>">Course <?php echo getSortIcon('course', $sort, $order); ?></a></th>
                <th>DOB</th>
                <th>Mobile</th>
                <th>Blood</th>
                <th>Address</th>
                <th>Printed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sno = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?php echo $sno++; ?></td>
                    <td>
                        <?php if ($row['photo'] && file_exists(UPLOAD_DIR . $row['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" width="40" height="45" class="thumb">
                        <?php else: ?>
                            No Photo
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['enrollment_no'] ? $row['enrollment_no'] : 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['branch'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['course'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['dob']))); ?></td>
                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                    <td><?php echo htmlspecialchars($row['blood_group'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(strlen($row['address']) > 20 ? substr($row['address'], 0, 20).'...' : $row['address']); ?></td>
                    <td>
                        <?php if ($row['is_printed']): ?>
                            <span style="color: green; font-weight: bold;">Yes</span><br>
                            <a href="toggle_printed.php?id=<?php echo $row['id']; ?>&status=0" style="font-size: 11px; color: #0056b3; text-decoration: none;">Mark Pending</a>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">No</span><br>
                            <a href="toggle_printed.php?id=<?php echo $row['id']; ?>&status=1" style="font-size: 11px; color: #0056b3; text-decoration: none;">Mark Printed</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                        <a href="generate_pdf.php?id=<?php echo $row['id']; ?>" class="btn">Print</a>
                        <form method="POST" action="delete_student.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            if (mysqli_num_rows($result) == 0) {
                echo "<tr><td colspan='12' style='text-align:center;'>No students found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
