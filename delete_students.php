<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);

    // Check if the student ID is unique (except for the current student)
    $check_query = "SELECT * FROM students WHERE student_id = '$student_id' AND id != '$id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error_message'] = "Student ID already exists!";
        header("Location: edit-student.php?id=$id");
        exit();
    }

    // Update student details
    $query = "UPDATE students SET 
                student_id = '$student_id', 
                name = '$name', 
                email = '$email', 
                course = '$course', 
                year_level = '$year_level', 
                contact_number = '$contact_number'
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Student updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating student: " . mysqli_error($conn);
    }

    header("Location: students.php");
    exit();
}
?>
