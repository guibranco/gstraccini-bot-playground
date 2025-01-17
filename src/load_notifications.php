<?php
// Database connection details
$host = 'localhost';
$dbname = 'your_database_name';
$user = 'your_database_user';
$password = 'your_database_password';

// Create a MySQL connection
$mysqli = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Prepare SQL query to fetch unread notifications first, followed by read notifications
$sql = "
    SELECT Sender, SenderAvatar, Title, Type
    FROM Notifications
    WHERE UserId = ?
    ORDER BY DateRead IS NULL DESC, DateSent DESC
";

// Replace this with the actual GitHub user ID of the logged-in user
$userId = 123456;

// Prepare and bind the statement
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $userId); // 'i' stands for integer
    $stmt->execute();
    
    // Bind the results to variables
    $stmt->bind_result($sender, $senderAvatar, $title, $type);
    
    // Fetch and display the notifications in a Bootstrap dropdown
    echo "<ul class='dropdown-menu dropdown-menu-end' aria-labelledby='notificationsDropdown'>";

    while ($stmt->fetch()) {
        // Determine FontAwesome icon based on the notification type
        $iconClass = getIconClass($type);

        // Generate URL based on type
        $linkUrl = generateNotificationLink($type);

        echo "<li>";
        echo "<a class='dropdown-item' href='" . htmlspecialchars($linkUrl) . "'>";
        echo "<div class='d-flex align-items-center'>";
        echo "<img src='" . htmlspecialchars($senderAvatar) . "' alt='" . htmlspecialchars($sender) . "' class='rounded-circle me-2' width='40' height='40'>";
        echo "<div>";
        echo "<h6 class='mb-0'>" . htmlspecialchars($title) . "</h6>";
        echo "<small class='text-muted'><i class='fa-solid $iconClass me-1'></i>" . ucfirst($type) . "</small>";
        echo "</div>";
        echo "</div>";
        echo "</a>";
        echo "</li>";
        echo "<li><hr class='dropdown-divider'></li>";
    }

    echo "</ul>";
    
    // Close the statement
    $stmt->close();
} else {
    echo "Error: " . $mysqli->error;
}

// Close the database connection
$mysqli->close();

/**
 * Get FontAwesome icon class based on notification type
 */
function getIconClass($type) {
    switch ($type) {
        case 'issue':
            return 'fa-bug';
        case 'pull_request':
            return 'fa-code-branch';
        case 'repository':
            return 'fa-folder';
        case 'comms':
            return 'fa-comments';
        case 'other':
        default:
            return 'fa-info-circle';
    }
}

/**
 * Generate notification link based on type
 */
function generateNotificationLink($type) {
    switch ($type) {
        case 'issue':
            return '/issues'; // Adjust with actual URL pattern
        case 'pull_request':
            return '/pull-requests'; // Adjust with actual URL pattern
        case 'repository':
            return '/repositories'; // Adjust with actual URL pattern
        case 'comms':
            return '/communications'; // Adjust with actual URL pattern
        case 'other':
        default:
            return '/notifications'; // Default or general notifications URL
    }
}
?>
