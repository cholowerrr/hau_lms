<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = intval($_POST['book_id']);
    $student_id = intval($_POST['student_id']);
    
    // Step 1: Update the borrowings table (Mark as returned)
    $updateBorrowingQuery = "UPDATE borrowings 
                              SET return_date = CURDATE(), returned = 1 
                              WHERE book_id = ? AND student_id = ? AND returned = 0";
    $stmt = mysqli_prepare($conn, $updateBorrowingQuery);
    mysqli_stmt_bind_param($stmt, "ii", $book_id, $student_id);
    mysqli_stmt_execute($stmt);

    // Step 2: Update book status to 'available'
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $updateBookQuery = "UPDATE books 
                             SET status = 'available' 
                             WHERE id = ?";
        $stmtBook = mysqli_prepare($conn, $updateBookQuery);
        mysqli_stmt_bind_param($stmtBook, "i", $book_id);
        mysqli_stmt_execute($stmtBook);

        // Step 3: Update the user_id to match the student_id
        $updateUserQuery = "UPDATE borrowings AS br
                             JOIN students AS s ON br.student_id = s.id
                             JOIN users AS u ON s.name = u.name
                             SET br.user_id = u.id
                             WHERE br.book_id = ? AND br.student_id = ?";
        $stmtUser = mysqli_prepare($conn, $updateUserQuery);
        mysqli_stmt_bind_param($stmtUser, "ii", $book_id, $student_id);
        mysqli_stmt_execute($stmtUser);

        $_SESSION['success_message'] = "Book successfully returned, and student details updated!";
    } else {
        $_SESSION['error_message'] = "Error: Book not updated or already returned.";
    }

    header("Location: reports.php");
    exit();
}
