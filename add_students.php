<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year_level = (int) $_POST['year_level'];
    $contact_number = trim($_POST['contact_number']);
    $department = trim($_POST['department']);

    if (empty($student_id) || empty($name) || empty($email) || empty($course) || empty($year_level) || empty($contact_number) || empty($department)) {
        $error_message = "All fields are required!";
    } else {
        // Check if student ID already exists
        $check_query = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $check_query->bind_param("s", $student_id);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $error_message = "Student ID already exists!";
        } else {
            // Insert new student using prepared statement
            $insert_query = $conn->prepare("INSERT INTO students (student_id, name, email, course, department, year_level, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_query->bind_param("sssssis", $student_id, $name, $email, $course, $department, $year_level, $contact_number);

            if ($insert_query->execute()) {
                $success_message = "Student added successfully!";
            } else {
                $error_message = "Error adding student: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Student</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Add New Student</h1>
                <a href="students.php" class="btn secondary-btn">Back to Students</a>
            </header>

            <div class="content-box">
                <?php if (!empty($success_message)) { ?>
                    <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
                <?php } elseif (!empty($error_message)) { ?>
                    <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
                <?php } ?>

                <form action="add_students.php" method="post" class="student-form">
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="course">Course</label>
                        <input type="text" id="course" name="course" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select name="department" id="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="SBA">SBA</option>
                            <option value="SEA">SEA</option>
                            <option value="SED">SED</option>
                            <option value="SNAMS">SNAMS</option>
                            <option value="HTM">HTM</option>
                            <option value="SOC">SOC</option>
                            <option value="SAS">SAS</option>
                            <option value="CJEF">CJEF</option>
                            <option value="BED">BED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <input type="number" id="year_level" name="year_level" class="form-control" min="1" max="5" required />
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" required />
                    </div>

                    <button type="submit" class="btn primary-btn">Add Student</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
