<?php
function generateSection($title, $items)
{
    if (count($items) === 0) {
        return "";
    }

    $sectionHtml = "<div class='section'>";
    $sectionHtml .= "<h2>" . htmlspecialchars($title) . "</h2>";

    foreach ($items as $item) {
        $sectionHtml .= "<div class='item'>";
        $sectionHtml .= "<p><strong>Sequence:</strong> " . htmlspecialchars($item['sequence']) . "</p>";
        $sectionHtml .= "<p><strong>ID:</strong> " . htmlspecialchars($item['id']) . "</p>";
        if (isset($item['comment'])) {
            $sectionHtml .= "<p><strong>Comment:</strong> " . htmlspecialchars($item['comment']) . "</p>";
        }
        if (isset($item['sender'])) {
            $sectionHtml .= "<p><strong>Sender:</strong> " . htmlspecialchars($item['sender']) . "</p>";
        }
        if (isset($item['url'])) {
            $sectionHtml .= "<p><strong>Link:</strong> <a href='" . htmlspecialchars($item['url']) . "'>" . htmlspecialchars($item['url']) . "</a></p>";
        }
        $sectionHtml .= "</div>";
    }

    $sectionHtml .= "</div>";
    return $sectionHtml;
}

$comments = [
    [
        'sequence' => 61,
        'id' => '688AC370-D1F2-11EF-9CEC-52182F409F2B',
        'url' => 'https://github.com/guibranco/webhooks/issues/1078/#issuecomment-2588201758',
        'comment' => '@gstraccini rerun workflows failed',
        'sender' => 'guibranco'
    ],
    [
        'sequence' => 62,
        'id' => '8FF58DA0-D1F2-11EF-92F3-627B8AA08F6B',
        'url' => 'https://github.com/guibranco/webhooks/issues/1078/#issuecomment-2588205815',
        'comment' => '@gstraccini rerun workflows failure',
        'sender' => 'guibranco'
    ]
];

$issues = [
    [
        'sequence' => 1,
        'id' => 'repo-123',
        'url' => 'https://github.com/guibranco/webhooks',
        'comment' => 'Repository processed successfully',
        'sender' => 'system'
    ]
];

$repositories = [
    [
        'sequence' => 1,
        'id' => 'repo-123',
        'url' => 'https://github.com/guibranco/webhooks',
        'comment' => 'Repository processed successfully',
        'sender' => 'system'
    ]
];

$dynamicSections = "";
$dynamicSections .= generateSection("Comments", $comments);
$dynamicSections .= generateSection("Issues", $issues);
$dynamicSections .= generateSection("Repositories", $repositories);

$emailTemplate = file_get_contents('email_html.template');
$emailTemplate = str_replace('{{dynamicSections}}', $dynamicSections, $emailTemplate);

echo $emailTemplate;
