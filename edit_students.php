<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$student_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM students WHERE id = '$student_id'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: students.php");
    exit();
}

// Department options
$departments = ['SBA', 'SEA', 'SED', 'SNAMS', 'HTM', 'SOC', 'SAS', 'CJEF', 'BED'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Student</title>
    <link rel="stylesheet" href="styles.css" />
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Edit Student</h1>
                <a href="students.php" class="btn secondary-btn">Back to Students</a>
            </header>

            <div class="content-box">
                <form action="update_students.php" method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']); ?>" />

                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" class="form-control" value="<?= htmlspecialchars($student['student_id']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($student['name']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="course">Course</label>
                        <input type="text" id="course" name="course" class="form-control" value="<?= htmlspecialchars($student['course']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?= $dept ?>" <?= $student['department'] === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <input type="number" id="year_level" name="year_level" class="form-control" value="<?= htmlspecialchars($student['year_level']); ?>" min="1" max="5" required />
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" value="<?= htmlspecialchars($student['contact_number']); ?>" required />
                    </div>

                    <div class="submit-group">
                        <button type="submit" class="btn primary-btn">Update Student</button>
                        <a href="students.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
