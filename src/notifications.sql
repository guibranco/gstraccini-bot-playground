CREATE TABLE Notifications (
    ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Sequential ID starting from 1000000000',
    Title VARCHAR(255) NOT NULL COMMENT 'Notification title, max 255 characters',
    Context TEXT NOT NULL COMMENT 'Notification content, supports HTML',
    UserId BIGINT UNSIGNED NOT NULL COMMENT 'Target GitHub user ID for the notification',
    Type ENUM('issue', 'pull_request', 'repository', 'comms', 'other') NOT NULL COMMENT 'Notification type',
    DateSent DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT 'The date and time the notification was sent',
    DateRead DATETIME DEFAULT NULL COMMENT 'The date and time the notification was read; NULL if unread',
    Sender VARCHAR(255) NOT NULL COMMENT 'Either system/bot or GitHub handler',
    SenderId BIGINT UNSIGNED DEFAULT NULL COMMENT 'GitHub user ID of the sender (NULL if system/bot)',
    SenderAvatar VARCHAR(500) DEFAULT NULL COMMENT 'URL to the avatar image of the sender (GitHub or system)',
    INDEX idx_UserId (UserId),
    INDEX idx_Type (Type),
    INDEX idx_DateRead (DateRead)
) AUTO_INCREMENT = 1000000000
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
