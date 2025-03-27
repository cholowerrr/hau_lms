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

// Define variables
$book_id = $student_id = $due_date = $action_type = "";
$book_id_err = $student_id_err = $due_date_err = $action_type_err = "";
$success_message = $error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action_type = $_POST['action_type'];

    // Validate book ID
    if (empty(trim($_POST["book_id"]))) {
        $book_id_err = "Please select a book.";
    } else {
        $book_id = intval($_POST["book_id"]);
    }

    // Validate student ID for borrowing
    if ($action_type == "issue") {
        if (empty(trim($_POST["student_id"]))) {
            $student_id_err = "Please select a student.";
        } else {
            $student_id = intval($_POST["student_id"]);

            // Check if student exists
            $sql = "SELECT id FROM students WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 0) {
                    $student_id_err = "Student not found.";
                }
                $stmt->close();
            }
        }
    }

    // Validate due date for borrowing
    if ($action_type == "issue") {
        if (empty(trim($_POST["due_date"]))) {
            $due_date_err = "Please select a due date.";
        } else {
            $due_date = trim($_POST["due_date"]);
            $today = date("Y-m-d");

            if ($due_date <= $today) {
                $due_date_err = "Due date must be after today.";
            }
        }
    }

    // Process the action if no errors
    if (empty($book_id_err) && empty($student_id_err) && empty($due_date_err)) {
        if ($action_type == "issue") {
            $conn->begin_transaction();
            try {
                // Get book condition
                $sql = "SELECT condition_status, status FROM books WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $condition_before = $row["condition_status"];
                $status = $row["status"];

                if ($status !== 'available') {
                    throw new Exception("This book is not available for borrowing.");
                }

                // Insert into borrowings table
                $sql = "INSERT INTO borrowings (book_id, user_id, student_id, borrow_date, due_date, condition_before, handled_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiisssi", $book_id, $_SESSION["id"], $student_id, $today, $due_date, $condition_before, $_SESSION["id"]);
                $stmt->execute();

                // Update book status
                $sql = "UPDATE books SET status = 'borrowed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();

                $conn->commit();
                $success_message = "Book issued successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error: " . $e->getMessage();
            }
        } elseif ($action_type == "return") {
            $conn->begin_transaction();
            try {
                // Check if book is borrowed
                $sql = "SELECT id FROM borrowings WHERE book_id = ? AND return_date IS NULL";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 0) {
                    throw new Exception("This book is not currently borrowed.");
                }

                // Update borrowings table with return date
                $sql = "UPDATE borrowings SET return_date = NOW() WHERE book_id = ? AND return_date IS NULL";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();

                // Update book status
                $sql = "UPDATE books SET status = 'available' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();

                $conn->commit();
                $success_message = "Book returned successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch available books for dropdown
$sql = "SELECT id, title, author FROM books ORDER BY title";
$available_books = $conn->query($sql);

// Fetch students for dropdown
$sql = "SELECT id, student_id, name FROM students ORDER BY name";
$students = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow/Return Book</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <header>
                <h1>Borrow or Return a Book</h1>
            </header>

            <?php if (!empty($success_message)) : ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php elseif (!empty($error_message)) : ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="content-box">
                <h2>Borrow or Return a Book</h2>
                
                <div class="form-group">
                    <label for="action_type">Choose Action:</label>
                    <select name="action_type" id="action_type" class="form-control">
                        <option value="issue">Issue Book</option>
                        <option value="return">Return Book</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $action_type_err; ?></span>
                </div>

                <div class="form-group">
                    <label for="book_id">Book:</label>
                    <select name="book_id" id="book_id" class="form-control">
                        <option value="">Select a Book</option>
                        <?php while ($row = $available_books->fetch_assoc()) { ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) . ' by ' . htmlspecialchars($row['author']) ?></option>
                        <?php } ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $book_id_err; ?></span>
                </div>

                <!-- Student Dropdown for Borrowing Only -->
                <div class="form-group" id="student_section">
                    <label for="student_id">Student (Only for Borrowing):</label>
                    <select name="student_id" id="student_id" class="form-control">
                        <option value="">Select a Student</option>
                        <?php while ($row = $students->fetch_assoc()) { ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['student_id']) . ')' ?></option>
                        <?php } ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $student_id_err; ?></span>
                </div>

                <!-- Due Date Section for Borrowing Only -->
                <div class="form-group" id="due_date_section">
                    <label for="due_date">Due Date (Only for Borrowing):</label>
                    <input type="date" name="due_date" id="due_date" class="form-control">
                    <span class="invalid-feedback"><?php echo $due_date_err; ?></span>
                </div>

                <button type="submit" class="btn primary-btn">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
