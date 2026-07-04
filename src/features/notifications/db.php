<?php

declare(strict_types=1);

/**
 * Minimal SQLite-backed storage for notifications. Good enough for this playground;
 * swap for a real database (see notifications.sql) in production.
 */
function notifications_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $directory = __DIR__ . '/storage';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $isNew = !file_exists($directory . '/notifications.sqlite');

    $pdo = new PDO('sqlite:' . $directory . '/notifications.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS Notifications (
            ID INTEGER PRIMARY KEY AUTOINCREMENT,
            Title VARCHAR(255) NOT NULL,
            Context TEXT NOT NULL,
            UserId INTEGER NOT NULL,
            Type VARCHAR(20) NOT NULL CHECK (Type IN (\'issue\', \'pull_request\', \'repository\', \'comms\', \'other\')),
            DateSent DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            DateRead DATETIME DEFAULT NULL,
            Sender VARCHAR(255) NOT NULL,
            SenderId INTEGER DEFAULT NULL,
            SenderAvatar VARCHAR(500) DEFAULT NULL
        )
    ');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_UserId ON Notifications (UserId)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_Type ON Notifications (Type)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_DateRead ON Notifications (DateRead)');

    if ($isNew) {
        notifications_seed($pdo);
    }

    return $pdo;
}

function notifications_seed(PDO $pdo): void
{
    $stmt = $pdo->prepare('
        INSERT INTO Notifications (Title, Context, UserId, Type, DateSent, DateRead, Sender, SenderId, SenderAvatar)
        VALUES (:title, :context, :userId, :type, :dateSent, :dateRead, :sender, :senderId, :senderAvatar)
    ');

    $sampleUserId = 123456;

    $rows = [
        [
            'title' => 'Fixed a typo in README',
            'context' => 'Pull request #42 was opened.',
            'userId' => $sampleUserId,
            'type' => 'pull_request',
            'dateSent' => '2026-07-04 09:15:00',
            'dateRead' => null,
            'sender' => 'octocat',
            'senderId' => 583231,
            'senderAvatar' => 'https://avatars.githubusercontent.com/u/583231?v=4',
        ],
        [
            'title' => 'Bug: build fails on Windows',
            'context' => 'Issue #17 was opened.',
            'userId' => $sampleUserId,
            'type' => 'issue',
            'dateSent' => '2026-07-03 18:42:00',
            'dateRead' => null,
            'sender' => 'gstraccini-bot',
            'senderId' => null,
            'senderAvatar' => 'https://avatars.githubusercontent.com/in/357968?v=4',
        ],
        [
            'title' => 'New repository forked',
            'context' => 'gstraccini-bot-playground was forked.',
            'userId' => $sampleUserId,
            'type' => 'repository',
            'dateSent' => '2026-07-02 11:05:00',
            'dateRead' => '2026-07-02 12:00:00',
            'sender' => 'gstraccini-bot',
            'senderId' => null,
            'senderAvatar' => 'https://avatars.githubusercontent.com/in/357968?v=4',
        ],
        [
            'title' => 'Welcome to GStraccini Bot',
            'context' => 'Thanks for installing the app!',
            'userId' => $sampleUserId,
            'type' => 'comms',
            'dateSent' => '2026-07-01 08:00:00',
            'dateRead' => '2026-07-01 08:05:00',
            'sender' => 'System',
            'senderId' => null,
            'senderAvatar' => 'https://ui-avatars.com/api/?name=System&background=059669&color=fff',
        ],
    ];

    foreach ($rows as $row) {
        $stmt->execute([
            ':title' => $row['title'],
            ':context' => $row['context'],
            ':userId' => $row['userId'],
            ':type' => $row['type'],
            ':dateSent' => $row['dateSent'],
            ':dateRead' => $row['dateRead'],
            ':sender' => $row['sender'],
            ':senderId' => $row['senderId'],
            ':senderAvatar' => $row['senderAvatar'],
        ]);
    }
}
