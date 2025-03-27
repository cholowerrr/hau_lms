<?php
session_start();
include 'db.php';
include 'functions.php';
include 'sidebar.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Management</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>User Management</h1>
                <a href="add_users.php" class="btn primary-btn">Add New User</a>
            </header>

            <div class="content-box">
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['role']); ?></td>
                                    <td>
                                        <a href="edit_users.php?id=<?= $row['id']; ?>" class="action-link edit-link">Edit</a>
                                        <a href="delete_users.php?id=<?= $row['id']; ?>" class="action-link return-link" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="no-records">No users found.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
