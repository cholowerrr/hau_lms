<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch report data
$book_count_query = "SELECT COUNT(*) AS total_books FROM books";
$student_count_query = "SELECT COUNT(*) AS total_students FROM students";
$borrowed_count_query = "SELECT COUNT(*) AS borrowed_books FROM books WHERE status = 'borrowed'";
$overdue_count_query = "SELECT COUNT(*) AS overdue_books FROM borrowings WHERE due_date < CURDATE() AND returned = 0";

// Execute queries
$book_count = mysqli_fetch_assoc(mysqli_query($conn, $book_count_query))['total_books'];
$student_count = mysqli_fetch_assoc(mysqli_query($conn, $student_count_query))['total_students'];
$borrowed_count = mysqli_fetch_assoc(mysqli_query($conn, $borrowed_count_query))['borrowed_books'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, $overdue_count_query))['overdue_books'];

// Calculate total fines for overdue books (Php 10 per day)
$fine_rate = 10;
$fines_query = "SELECT SUM(DATEDIFF(CURDATE(), due_date) * $fine_rate) AS total_fines
                FROM borrowings 
                WHERE due_date < CURDATE() AND returned = 0";
                
$fines_result = mysqli_query($conn, $fines_query);
$total_fines = mysqli_fetch_assoc($fines_result)['total_fines'] ?? 0;
$total_fines = $total_fines > 0 ? $total_fines : 0;

// Fetch book conditions report
$condition_query = "SELECT condition_status, COUNT(*) AS count FROM books GROUP BY condition_status";
$condition_result = mysqli_query($conn, $condition_query);

// Fetch borrowing reports with fines for each student
$borrowing_query = "SELECT b.title, s.name AS student_name, br.borrow_date, br.due_date, br.return_date, br.returned,
                    CASE 
                        WHEN br.returned = 1 THEN 0
                        WHEN CURDATE() > br.due_date THEN DATEDIFF(CURDATE(), br.due_date) * $fine_rate
                        ELSE 0
                    END AS fine
                    FROM borrowings br
                    JOIN books b ON br.book_id = b.id
                    JOIN students s ON br.student_id = s.id
                    ORDER BY br.borrow_date DESC
                    LIMIT 10";
$borrowing_result = mysqli_query($conn, $borrowing_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Library Reports</h1>
            </header>

            <!-- Overview Reports -->
            <section class="reports-overview">
                <h2>Library Overview Report</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Books</td>
                            <td><?= $book_count ?></td>
                        </tr>
                        <tr>
                            <td>Total Students</td>
                            <td><?= $student_count ?></td>
                        </tr>
                        <tr>
                            <td>Borrowed Books</td>
                            <td><?= $borrowed_count ?></td>
                        </tr>
                        <tr>
                            <td>Overdue Books</td>
                            <td><?= $overdue_count ?></td>
                        </tr>
                        <tr>
                            <td>Total Fines (Php)</td>
                            <td>Php <?= number_format($total_fines, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Book Condition Report -->
            <section>
                <h2>Book Condition Report</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Condition</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($condition_result)) { ?>
                            <tr>
                                <td><?= $row['condition_status'] ?></td>
                                <td><?= $row['count'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <!-- Recent Borrowing Report -->
            <section>
                <h2>Recent Borrowings</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Student Name</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Fine (Php)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($borrowing_result)) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                                <td><?= htmlspecialchars($row['due_date']) ?></td>
                                <td><?= $row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned' ?></td>
                                <td><?= $row['returned'] ? 'Returned' : 'Pending' ?></td>
                                <td>Php <?= number_format($row['fine'], 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>
</html>
