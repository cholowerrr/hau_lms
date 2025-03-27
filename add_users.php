<?php
session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Check for existing username
    $check_query = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Username already exists!";
    } else {
        // Insert user
        $insert_query = "INSERT INTO users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssss', $username, $password, $name, $email, $role);
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "User added successfully!";
        } else {
            $error_message = "Error adding user: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New User</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Add New User</h1>
                <a href="users.php" class="btn secondary-btn">Back to Users</a>
            </header>

            <div class="content-box">
                <?php if (isset($success_message)) { ?>
                    <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
                <?php } elseif (isset($error_message)) { ?>
                    <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
                <?php } ?>

                <form action="add_users.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="librarian">Librarian</option>
                            <option value="student_assistant">Student Assistant</option>
                        </select>
                    </div>

                    <div class="submit-group">
                        <button type="submit" class="btn primary-btn">Add User</button>
                        <a href="users.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
