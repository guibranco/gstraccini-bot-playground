<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = notifications_db();

// Prepare SQL query to fetch unread notifications first, followed by read notifications
$sql = "
    SELECT Sender, SenderAvatar, Title, Type
    FROM Notifications
    WHERE UserId = :userId
    ORDER BY DateRead IS NULL DESC, DateSent DESC
";

// Replace this with the actual GitHub user ID of the logged-in user
$userId = 123456;

$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);

// Fetch and display the notifications in a Bootstrap dropdown
echo "<ul class='dropdown-menu dropdown-menu-end' aria-labelledby='notificationsDropdown'>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    ['Sender' => $sender, 'SenderAvatar' => $senderAvatar, 'Title' => $title, 'Type' => $type] = $row;

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
