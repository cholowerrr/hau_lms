<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

// Display success or error messages
if (isset($_SESSION['success_message'])) {
    echo '<p class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<p class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']);
}

// Fetch student data with department
$query = "SELECT student_id, name, email, course, department, year_level, contact_number, id FROM students";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching data: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Management</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Student Management</h1>
                <a href="add_students.php" class="btn primary-btn">Add New Student</a>
            </header>

            <div class="content-box">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Department</th> <!-- Added Department Column -->
                                <th>Year Level</th>
                                <th>Contact Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['student_id']); ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['course']); ?></td>
                                    <td><?= htmlspecialchars($row['department']); ?></td> <!-- Display Department -->
                                    <td><?= htmlspecialchars($row['year_level']); ?></td>
                                    <td><?= htmlspecialchars($row['contact_number']); ?></td>
                                    <td>
                                        <a href="edit_students.php?id=<?= htmlspecialchars($row['id']); ?>" class="action-link edit-link">Edit</a>
                                        <a href="delete_students.php?id=<?= htmlspecialchars($row['id']); ?>" class="action-link return-link" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="no-records">No students found.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
