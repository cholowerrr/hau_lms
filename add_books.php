<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $publication_year = mysqli_real_escape_string($conn, $_POST['publication_year']);
    $publisher = mysqli_real_escape_string($conn, $_POST['publisher']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $condition_status = mysqli_real_escape_string($conn, $_POST['condition_status']);
    $shelf_location = mysqli_real_escape_string($conn, $_POST['shelf_location']);
    $added_by = $_SESSION['id'];

    $query = "INSERT INTO books (title, author, isbn, category, publication_year, publisher, status, condition_status, shelf_location, added_by) 
              VALUES ('$title', '$author', '$isbn', '$category', '$publication_year', '$publisher', '$status', '$condition_status', '$shelf_location', '$added_by')";

    if (mysqli_query($conn, $query)) {
        $success_message = "Book added successfully!";
    } else {
        $error_message = "Error adding book: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Book</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Add New Book</h1>
                <a href="books.php" class="btn secondary-btn">Back to Books</a>
            </header>

            <div class="content-box">
                <?php if (isset($success_message)) { ?>
                    <p class="success-message"><?= $success_message ?></p>
                <?php } elseif (isset($error_message)) { ?>
                    <p class="error-message"><?= $error_message ?></p>
                <?php } ?>

                <form action="add_books.php" method="post">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="publication_year">Publication Year</label>
                        <input type="number" id="publication_year" name="publication_year" class="form-control" min="1900" max="2099" required />
                    </div>

                    <div class="form-group">
                        <label for="publisher">Publisher</label>
                        <input type="text" id="publisher" name="publisher" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="reserved">Reserved</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="condition_status">Condition</label>
                        <select id="condition_status" name="condition_status" class="form-control" required>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="shelf_location">Shelf Location</label>
                        <input type="text" id="shelf_location" name="shelf_location" class="form-control" required />
                    </div>

                    <button type="submit" class="btn primary-btn">Add Book</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
