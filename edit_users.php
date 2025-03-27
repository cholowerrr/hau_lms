<?php
session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<div class='error-message'>User not found.</div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Update query
    $update_query = "UPDATE users SET username = ?, name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ssssi', $username, $name, $email, $role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "User updated successfully!";
    } else {
        $error_message = "Error updating user: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Edit User</h1>
                <a href="users.php" class="btn secondary-btn">Back to Users</a>
            </header>

            <div class="content-box">
                <?php if (isset($success_message)) { ?>
                    <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
                <?php } elseif (isset($error_message)) { ?>
                    <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
                <?php } ?>

                <form action="" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required />
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="librarian" <?= $user['role'] == 'librarian' ? 'selected' : ''; ?>>Librarian</option>
                            <option value="student_assistant" <?= $user['role'] == 'student_assistant' ? 'selected' : ''; ?>>Student Assistant</option>
                        </select>
                    </div>

                    <div class="submit-group">
                        <button type="submit" class="btn primary-btn">Update User</button>
                        <a href="users.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
