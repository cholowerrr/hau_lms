<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch overdue books
$query = "SELECT borrowings.*, books.title, students.name AS student_name 
          FROM borrowings 
          JOIN books ON borrowings.book_id = books.id 
          JOIN students ON borrowings.student_id = students.id 
          WHERE borrowings.returned = 0 AND borrowings.due_date < CURDATE()";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Overdue Books</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Overdue Books</h1>
            </header>

            <div class="content-box">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Borrower</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Fine Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']); ?></td>
                                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                                    <td><?= htmlspecialchars($row['borrow_date']); ?></td>
                                    <td><?= htmlspecialchars($row['due_date']); ?></td>
                                    <td>₱<?= number_format($row['fine_amount'], 2); ?></td>
                                    <td>
                                        <a href="return.php?id=<?= $row['id']; ?>" class="action-link return-link">Return</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="no-records">No overdue books found.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
