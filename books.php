<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories for dropdown
$category_query = "SELECT DISTINCT category FROM books";
$category_result = mysqli_query($conn, $category_query);

// Get selected category from filter
$category_filter = isset($_GET['category_filter']) ? mysqli_real_escape_string($conn, $_GET['category_filter']) : 'all';

// Adjust query based on selected category
$query = "SELECT * FROM books";
if ($category_filter != 'all') {
    $query .= " WHERE category = '$category_filter'";
}
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Books Management</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Books Management</h1>
                <a href="add_books.php" class="btn primary-btn">Add New Book</a>
            </header>

            <!-- Filter Form for Categories -->
            <form method="GET" class="filter-form">
                <label for="category_filter">Filter by Category:</label>
                <select name="category_filter" id="category_filter" class="form-control" onchange="this.form.submit()">
                    <option value="all" <?= $category_filter == "all" ? "selected" : ""; ?>>All Categories</option>
                    <?php while ($cat = mysqli_fetch_assoc($category_result)) { ?>
                        <option value="<?= htmlspecialchars($cat['category']); ?>" <?= $category_filter == $cat['category'] ? "selected" : ""; ?>>
                            <?= htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php } ?>
                </select>
            </form>

            <div class="content-box">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Publication Year</th>
                                <th>Status</th>
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
                                    <td><?= htmlspecialchars($row['isbn']); ?></td>
                                    <td><?= htmlspecialchars($row['category']); ?></td>
                                    <td><?= htmlspecialchars($row['publication_year']); ?></td>
                                    <td><?= htmlspecialchars($row['status']); ?></td>
                                    <td class="<?= $row['condition_status'] === 'Poor' || $row['condition_status'] === 'Damaged' ? 'status-bad' : 'status-good'; ?>">
                                        <?= htmlspecialchars($row['condition_status']); ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['shelf_location']); ?></td>
                                    <td>
                                        <a href="edit_books.php?id=<?= $row['id']; ?>" class="action-link edit-link">Edit</a>
                                        <a href="delete_books.php?id=<?= $row['id']; ?>" class="action-link return-link" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="no-records">No books found in this category.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
