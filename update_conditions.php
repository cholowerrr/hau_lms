<?php
session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: conditions.php");
    exit();
}

$book_id = intval($_GET['id']);

// Fetch book data
$query = "SELECT * FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "<div class='error-message'>Book not found.</div>";
    exit();
}

$book = mysqli_fetch_assoc($result);

// Update book condition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_condition = mysqli_real_escape_string($conn, $_POST['condition_status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $user_id = $_SESSION['id'];

    // Log previous condition to the condition_logs table
    $log_query = "INSERT INTO condition_logs (book_id, previous_condition, new_condition, checked_by, notes) VALUES (?, ?, ?, ?, ?)";
    $log_stmt = mysqli_prepare($conn, $log_query);
    mysqli_stmt_bind_param($log_stmt, 'issis', $book_id, $book['condition_status'], $new_condition, $user_id, $notes);
    mysqli_stmt_execute($log_stmt);

    // Update book condition
    $update_query = "UPDATE books SET condition_status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $new_condition, $book_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['success_message'] = "Book condition updated successfully!";
        header("Location: conditions.php");
        exit();
    } else {
        echo "<div class='error-message'>Error updating condition: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Book Condition</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Update Book Condition</h1>
                <a href="conditions.php" class="btn secondary-btn">Back to Conditions</a>
            </header>

            <div class="content-box">
                <form method="post" class="form-box">
                    <div class="form-group">
                        <label>📚 Book Title:</label>
                        <p><?= htmlspecialchars($book['title']); ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>✍️ Author:</label>
                        <p><?= htmlspecialchars($book['author']); ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>🟢 Current Condition:</label>
                        <span class="status-badge status-<?= strtolower($book['condition_status']); ?>">
                            <?= htmlspecialchars($book['condition_status']); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="condition_status">🆕 New Condition:</label>
                        <select name="condition_status" id="condition_status" class="form-control" required>
                            <option value="Excellent" <?= $book['condition_status'] === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                            <option value="Good" <?= $book['condition_status'] === 'Good' ? 'selected' : ''; ?>>Good</option>
                            <option value="Fair" <?= $book['condition_status'] === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                            <option value="Poor" <?= $book['condition_status'] === 'Poor' ? 'selected' : ''; ?>>Poor</option>
                            <option value="Damaged" <?= $book['condition_status'] === 'Damaged' ? 'selected' : ''; ?>>Damaged</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">📝 Notes (Optional):</label>
                        <textarea name="notes" id="notes" class="form-control" rows="5" placeholder="Add notes if necessary"></textarea>
                    </div>

                    <div class="submit-group">
                        <button type="submit" class="btn primary-btn">Update Condition</button>
                        <a href="conditions.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
