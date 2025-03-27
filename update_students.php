<?php
session_start();
include 'db.php';
include 'functions.php';

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $department = mysqli_real_escape_string($conn, $_POST['department']); // Department added
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);

    // Check if student exists
    $check_query = "SELECT * FROM students WHERE id = '$id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) === 0) {
        $_SESSION['error_message'] = "Student not found!";
        header("Location: students.php");
        exit();
    }

    // Update student details including department
    $update_query = "UPDATE students 
                    SET student_id = '$student_id', 
                        name = '$name', 
                        email = '$email', 
                        course = '$course', 
                        department = '$department', 
                        year_level = '$year_level', 
                        contact_number = '$contact_number'
                    WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Student updated successfully!";
        header("Location: students.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating student: " . mysqli_error($conn);
        header("Location: edit_students.php?id=$id");
        exit();
    }
} else {
    // Redirect if accessed without form submission
    header("Location: students.php");
    exit();
}
?>
