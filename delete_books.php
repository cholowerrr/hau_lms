<?php
session_start();
include 'db.php';
include 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit();
}

$book_id = intval($_GET['id']);

// Check if the book exists
$query = "SELECT * FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Book not found.";
    exit();
}

// Delete the book
$delete_query = "DELETE FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($stmt, 'i', $book_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = "Book deleted successfully!";
    header("Location: books.php");
    exit();
} else {
    $_SESSION['error_message'] = "Error deleting book: " . mysqli_error($conn);
    header("Location: books.php");
    exit();
}
?>
