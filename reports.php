<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch overview data
$book_count_query = "SELECT COUNT(*) AS total_books FROM books";
$student_count_query = "SELECT COUNT(*) AS total_students FROM students";
$borrowed_count_query = "SELECT COUNT(*) AS borrowed_books FROM books WHERE status = 'borrowed'";
$overdue_count_query = "SELECT COUNT(*) AS overdue_books FROM borrowings WHERE due_date < CURDATE() AND returned = 0";

$book_count = mysqli_fetch_assoc(mysqli_query($conn, $book_count_query))['total_books'];
$student_count = mysqli_fetch_assoc(mysqli_query($conn, $student_count_query))['total_students'];
$borrowed_count = mysqli_fetch_assoc(mysqli_query($conn, $borrowed_count_query))['borrowed_books'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, $overdue_count_query))['overdue_books'];

// Calculate fines
$fine_rate = 10;
$fines_query = "SELECT SUM(DATEDIFF(CURDATE(), due_date) * $fine_rate) AS total_fines
                FROM borrowings 
                WHERE due_date < CURDATE() AND returned = 0";
$fines_result = mysqli_query($conn, $fines_query);
$total_fines = mysqli_fetch_assoc($fines_result)['total_fines'] ?? 0;

// Book condition report
$condition_query = "SELECT condition_status, COUNT(*) AS count FROM books GROUP BY condition_status";
$condition_result = mysqli_query($conn, $condition_query);

// Borrowing Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = "";

if ($filter === 'returned') {
    $filter_condition = "AND br.return_date IS NOT NULL";
} elseif ($filter === 'pending') {
    $filter_condition = "AND br.return_date IS NULL AND CURDATE() <= br.due_date";
} elseif ($filter === 'overdue') {
    $filter_condition = "AND br.return_date IS NULL AND CURDATE() > br.due_date";
}

// Borrowing report query
$borrowing_query = "SELECT b.title, s.name AS student_name, br.borrow_date, br.due_date, br.return_date,
                    CASE 
                        WHEN br.return_date IS NOT NULL THEN 'Returned'
                        WHEN CURDATE() > br.due_date THEN 'Overdue'
                        ELSE 'Pending'
                    END AS status,
                    CASE 
                        WHEN br.return_date IS NOT NULL THEN 0
                        WHEN CURDATE() > br.due_date THEN DATEDIFF(CURDATE(), br.due_date) * 10
                        ELSE 0
                    END AS fine
                    FROM borrowings br
                    JOIN books b ON br.book_id = b.id
                    JOIN students s ON br.student_id = s.id
                    WHERE 1 $filter_condition
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

            <!-- Library Overview Report -->
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

            <!-- Filter Section -->
            <section>
                <h2>Recent Borrowings</h2>
                <div class="filter-group">
                    <label for="filter">Filter by:</label>
                    <select id="filter" onchange="location = this.value;">
                        <option value="?filter=all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="?filter=returned" <?= $filter === 'returned' ? 'selected' : '' ?>>Returned</option>
                        <option value="?filter=pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="?filter=overdue" <?= $filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>

                <!-- Borrowing Report Table -->
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
                        <?php if (mysqli_num_rows($borrowing_result) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($borrowing_result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                                    <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                                    <td><?= htmlspecialchars($row['due_date']) ?></td>
                                    <td><?= $row['return_date'] ? htmlspecialchars($row['return_date']) : 'Not Returned' ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($row['status']) ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>Php <?= number_format($row['fine'], 2) ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="no-records">No records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        document.getElementById('filter').addEventListener('change', function() {
            window.location.href = this.value;
        });
    </script>
</body>
</html>
