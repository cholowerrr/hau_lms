<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Display a message box for success or error messages
 * 
 * @param string $message The message to display
 * @param string $type The type of message (success or error)
 * @return string HTML for the message box
 */
function displayMessage($message, $type = 'success') {
    if (empty($message)) {
        return '';
    }
    
    $class = ($type == 'success') ? 'success-message' : 'error-message';
    return "<div class='$class'>$message</div>";
}

/**
 * Get a setting value from the database
 * 
 * @param object $conn Database connection
 * @param string $key Setting key
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value
 */
function getSetting($conn, $key, $default = null) {
    $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['setting_value'];
    }
    
    return $default;
}

/**
 * Update a setting in the database
 * 
 * @param object $conn Database connection
 * @param string $key Setting key
 * @param mixed $value New setting value
 * @return bool True if successful, false otherwise
 */
function updateSetting($conn, $key, $value) {
    $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

/**
 * Check if the current user has admin role
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION["role"]) && $_SESSION["role"] == "admin";
}

/**
 * Calculate the fine for an overdue book
 * 
 * @param object $conn Database connection
 * @param string $due_date Due date in Y-m-d format
 * @param string $return_date Return date in Y-m-d format (defaults to today)
 * @return float Fine amount
 */
function calculateFine($conn, $due_date, $return_date = null) {
    if ($return_date === null) {
        $return_date = date('Y-m-d');
    }
    
    // If not overdue, no fine
    if (strtotime($return_date) <= strtotime($due_date)) {
        return 0;
    }
    
    // Get fine rate from settings
    $fine_rate = getSetting($conn, 'fine_rate', 10); // Default to 10 PHP per day
    
    // Calculate days overdue
    $due = new DateTime($due_date);
    $return = new DateTime($return_date);
    $days_overdue = $return->diff($due)->days;
    
    return $days_overdue * $fine_rate;
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Log an action to the database
 * 
 * @param object $conn Database connection
 * @param string $action Action description
 * @param int $user_id ID of the user performing the action
 * @return bool True if successful, false otherwise
 */
function logAction($conn, $action, $user_id) {
    $sql = "INSERT INTO logs (user_id, action, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $action);
    return $stmt->execute();
}

/**
 * Redirect to a specified page
 * 
 * @param string $url Target URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display all error messages stored in session and clear them
 */
function displayErrors() {
    if (!empty($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo displayMessage($error, 'error');
        }
        unset($_SESSION['errors']);
    }
}

/**
 * Display all success messages stored in session and clear them
 */
function displaySuccess() {
    if (!empty($_SESSION['success'])) {
        foreach ($_SESSION['success'] as $message) {
            echo displayMessage($message, 'success');
        }
        unset($_SESSION['success']);
    }
}

?>