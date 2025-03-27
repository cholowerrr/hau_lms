<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$book_id = $title = $author = $isbn = $category = $publication_year = $publisher = $status = $condition_status = $shelf_location = "";
$error_message = $success_message = "";

// Check if a book ID is passed for editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = $_GET['id'];
    
    // Fetch book details
    $sql = "SELECT * FROM books WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $book = $result->fetch_assoc();
            $title = $book['title'];
            $author = $book['author'];
            $isbn = $book['isbn'];
            $category = $book['category'];
            $publication_year = $book['publication_year'];
            $publisher = $book['publisher'];
            $status = $book['status'];
            $condition_status = $book['condition_status'];
            $shelf_location = $book['shelf_location'];
        } else {
            $error_message = "Book not found.";
        }
        $stmt->close();
    }
} else {
    $error_message = "Invalid book ID.";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $publication_year = mysqli_real_escape_string($conn, $_POST['publication_year']);
    $publisher = mysqli_real_escape_string($conn, $_POST['publisher']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $condition_status = mysqli_real_escape_string($conn, $_POST['condition_status']);
    $shelf_location = mysqli_real_escape_string($conn, $_POST['shelf_location']);

    // Update the book details
    $query = "UPDATE books 
              SET title=?, author=?, isbn=?, category=?, publication_year=?, publisher=?, 
                  status=?, condition_status=?, shelf_location=? 
              WHERE id=?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssssissssi", $title, $author, $isbn, $category, $publication_year, 
                           $publisher, $status, $condition_status, $shelf_location, $book_id);

        if ($stmt->execute()) {
            $success_message = "Book updated successfully!";
        } else {
            $error_message = "Error updating book: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Book</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Edit Book</h1>
                <a href="books.php" class="btn secondary-btn">Back to Books</a>
            </header>

            <?php if ($success_message) : ?>
                <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
            <?php elseif ($error_message) : ?>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <div class="content-box">
                <form action="edit_books.php" method="post">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($book_id) ?>">

                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" class="form-control" required />

                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" value="<?= htmlspecialchars($author) ?>" class="form-control" required />

                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($isbn) ?>" class="form-control" required />

                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>" class="form-control" required />

                    <label for="publication_year">Publication Year</label>
                    <input type="number" id="publication_year" name="publication_year" value="<?= htmlspecialchars($publication_year) ?>" class="form-control" min="1900" max="2099" required />

                    <label for="publisher">Publisher</label>
                    <input type="text" id="publisher" name="publisher" value="<?= htmlspecialchars($publisher) ?>" class="form-control" required />

                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="available" <?= ($status == 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="borrowed" <?= ($status == 'borrowed') ? 'selected' : '' ?>>Borrowed</option>
                        <option value="reserved" <?= ($status == 'reserved') ? 'selected' : '' ?>>Reserved</option>
                        <option value="maintenance" <?= ($status == 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                    </select>

                    <label for="condition_status">Condition</label>
                    <select id="condition_status" name="condition_status" class="form-control" required>
                        <option value="Excellent" <?= ($condition_status == 'Excellent') ? 'selected' : '' ?>>Excellent</option>
                        <option value="Good" <?= ($condition_status == 'Good') ? 'selected' : '' ?>>Good</option>
                        <option value="Fair" <?= ($condition_status == 'Fair') ? 'selected' : '' ?>>Fair</option>
                        <option value="Poor" <?= ($condition_status == 'Poor') ? 'selected' : '' ?>>Poor</option>
                        <option value="Damaged" <?= ($condition_status == 'Damaged') ? 'selected' : '' ?>>Damaged</option>
                    </select>

                    <label for="shelf_location">Shelf Location</label>
                    <input type="text" id="shelf_location" name="shelf_location" value="<?= htmlspecialchars($shelf_location) ?>" class="form-control" required />

                    <button type="submit" class="btn primary-btn">Update Book</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
