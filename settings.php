<?php
session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Fetch current settings securely
$query = "SELECT * FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

// Update System Name
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_name'])) {
    $system_name = mysqli_real_escape_string($conn, $_POST['system_name']);
    $update_query = "UPDATE settings SET system_name = ? WHERE id = 1";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 's', $system_name);

    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "System name updated successfully!";
    } else {
        $error_msg = "Error updating system name: " . mysqli_error($conn);
    }
}

// Update Admin Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password using prepared statements
    $user_query = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($user_result);

    if ($user_data && password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_password_query);
            mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $_SESSION['id']);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Password updated successfully!";
            } else {
                $error_msg = "Error updating password: " . mysqli_error($conn);
            }
        } else {
            $error_msg = "New passwords do not match!";
        }
    } else {
        $error_msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>System Settings</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>System Settings</h1>
            </header>

            <?php if ($success_msg): ?>
                <p class="success-message"><?= htmlspecialchars($success_msg); ?></p>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <p class="error-message"><?= htmlspecialchars($error_msg); ?></p>
            <?php endif; ?>

            <!-- Update System Name -->
            <section class="content-box">
                <h2>Update System Name</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="system_name">System Name:</label>
                        <input type="text" id="system_name" name="system_name" class="form-control" value="<?= htmlspecialchars($settings['system_name']); ?>" required />
                    </div>
                    <button type="submit" name="update_name" class="btn primary-btn">Update System Name</button>
                </form>
            </section>

            <!-- Change Admin Password -->
            <section class="content-box">
                <h2>Change Password</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required />
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                    </div>

                    <button type="submit" name="update_password" class="btn warning-btn">Change Password</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>

