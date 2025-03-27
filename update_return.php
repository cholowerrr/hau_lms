<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_id'])) {
    $borrow_id = mysqli_real_escape_string($conn, $_POST['borrow_id']);
    $return_date = date('Y-m-d'); // Current date

    // Update the returned status and return date
    $update_query = "UPDATE borrowings SET return_date = '$return_date', returned = 1 WHERE id = '$borrow_id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Book marked as returned.";
    } else {
        $_SESSION['error_message'] = "Error updating record: " . mysqli_error($conn);
    }
}

header("Location: reports.php");
exit();
?>
