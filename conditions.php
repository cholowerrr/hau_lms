<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch books in poor or damaged condition
$query = "SELECT * FROM books WHERE condition_status IN ('Poor', 'Damaged')";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Book Conditions</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Book Conditions</h1>
            </header>

            <div class="content-box">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Condition</th>
                                <th>Shelf Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']); ?></td>
                                    <td><?= htmlspecialchars($row['author']); ?></td>
                                    <td class="<?= $row['condition_status'] === 'Damaged' ? 'status-bad' : 'status-warning'; ?>">
                                        <?= htmlspecialchars($row['condition_status']); ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['shelf_location']); ?></td>
                                    <td>
                                        <a href="update_conditions.php?id=<?= $row['id']; ?>" class="action-link edit-link">Update</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="no-records">No books with poor or damaged conditions found.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
