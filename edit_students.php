<?php
// Start session to manage user login
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "config.php";

// Automatically fix incorrect user_id for all students
$sql_fix = "UPDATE borrowings AS br
            JOIN students AS s ON br.user_id = s.id
            SET br.user_id = s.id
            WHERE br.book_id IN (SELECT DISTINCT book_id FROM borrowings)";
if (!$conn->query($sql_fix)) {
    echo "Error updating user IDs: " . $conn->error;
}

// Automatically mark overdue books as returned
$sql_overdue = "UPDATE borrowings SET returned = 1 WHERE return_date < CURDATE() AND returned = 0";
if (!$conn->query($sql_overdue)) {
    echo "Error updating overdue books: " . $conn->error;
}

// Function to count books by status
function countBooksByStatus($conn, $status) {
    $sql = "SELECT COUNT(*) as count FROM books WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["count"];
}

// Count total books
$sql = "SELECT COUNT(*) as total FROM books";
$result = $conn->query($sql);
$totalBooks = $result->fetch_assoc()["total"];

// Count borrowed books
$borrowedBooks = countBooksByStatus($conn, "borrowed");

// Count available books
$availableBooks = countBooksByStatus($conn, "available");

// Count overdue books using safer query
$sql = "SELECT COUNT(*) as overdue FROM borrowings 
        WHERE return_date < CURDATE() AND returned = 0 AND book_id IN 
        (SELECT id FROM books)";
$result = $conn->query($sql);
$overdueBooks = $result->fetch_assoc()["overdue"];

// Get recently borrowed books using correct student data
$sql = "SELECT b.title, s.name AS student_name, br.borrow_date, br.due_date 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.id 
        JOIN students s ON br.user_id = s.id 
        WHERE br.returned = 0 
        ORDER BY br.borrow_date DESC LIMIT 5";
$recentBorrowings = $conn->query($sql);

// Get books with poor condition
$sql = "SELECT id, title, condition_status FROM books WHERE condition_status IN ('Poor', 'Damaged') LIMIT 5";
$poorConditionBooks = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAU Library Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <header>
                <h1>HAU Library Management System</h1>
                <div class="user-info">
                    <span>Welcome To HAU Library, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <div class="dashboard">
                <h2>Dashboard Overview</h2>

                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total Books</h3>
                        <p class="stat-number"><?php echo $totalBooks; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Available Books</h3>
                        <p class="stat-number"><?php echo $availableBooks; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Borrowed Books</h3>
                        <p class="stat-number"><?php echo $borrowedBooks; ?></p>
                    </div>
                    <div class="stat-card alert">
                        <h3>Overdue Books</h3>
                        <p class="stat-number"><?php echo $overdueBooks; ?></p>
                    </div>
                </div>

                <div class="recent-activity">
                    <div class="activity-section">
                        <h3>Recent Borrowings</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Borrower</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($recentBorrowings->num_rows > 0) {
                                    while ($row = $recentBorrowings->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["student_name"]) . "</td>"; // Fixed here
                                        echo "<td>" . htmlspecialchars($row["borrow_date"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["due_date"]) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No recent borrowings</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="activity-section">
                        <h3>Books in Poor Condition</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Book ID</th>
                                    <th>Title</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($poorConditionBooks->num_rows > 0) {
                                    while ($row = $poorConditionBooks->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                                        echo "<td class='condition-" . strtolower($row["condition_status"]) . "'>" . htmlspecialchars($row["condition_status"]) . "</td>";
                                        echo "<td><a href='update_condition.php?id=" . $row["id"] . "' class='btn'>Update</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No books in poor condition</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="borrow.php" class="action-btn">Issue Book</a>
                        <a href="return.php" class="action-btn">Return Book</a>
                        <a href="add_books.php" class="action-btn">Add New Book</a>
                        <a href="overdue.php" class="action-btn alert-btn">View Overdue Books</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
