<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo">
        <h2>HAU LMS</h2>
    </div>
    <nav class="navigation">
        <ul>
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'books.php') ? 'active' : ''; ?>">
                <a href="books.php">
                    <span class="icon">📚</span>
                    <span>Book Inventory</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'borrow.php' || $current_page == 'return.php') ? 'active' : ''; ?>">
                <a href="borrow.php">
                    <span class="icon">🔄</span>
                    <span>Borrow/Return</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                <a href="students.php">
                    <span class="icon">👥</span>
                    <span>Students</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'overdue.php') ? 'active' : ''; ?>">
                <a href="overdue.php">
                    <span class="icon">⏰</span>
                    <span>Overdue Books</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'conditions.php') ? 'active' : ''; ?>">
                <a href="conditions.php">
                    <span class="icon">🔍</span>
                    <span>Book Conditions</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="reports.php">
                    <span class="icon">📝</span>
                    <span>Reports</span>
                </a>
            </li>
            <?php if($_SESSION["role"] == "admin"): ?>
            <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <a href="users.php">
                    <span class="icon">👤</span>
                    <span>User Management</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php">
                    <span class="icon">⚙️</span>
                    <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>