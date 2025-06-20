<?php
/**
 * GitHub PR Processing HTML Email Generator
 *
 * This script processes pull request data and sends HTML formatted emails
 * with visual improvements over plain text emails.
 */

class GitHubPRProcessingEmailGenerator
{
    private $prItems = [];
    private $logoUrl = 'https://your-domain.com/path/to/logo.png'; // Replace with your actual logo URL
    private $version = '1.0.0'; // Replace with your actual version
    private $emailSubject = 'GitHub PR Processing Report';

    /**
     * Add a PR item to be included in the email
     */
    public function addPRItem($sequence, $deliveryId, $prUrl, $states, $result)
    {
        // Extract PR number from URL
        preg_match('#/pull/(\d+)(?:/|$|\?)#', $prUrl, $matches);
        $prNumber = isset($matches[1]) ? $matches[1] : 'Unknown';

        // Extract repository name from URL
        preg_match('/github\.com\/([^\/]+\/[^\/]+)\/pull/', $prUrl, $repoMatches);
        $repository = isset($repoMatches[1]) ? $repoMatches[1] : 'Unknown Repository';
        $this->prItems[] = [
            'sequence' => $sequence,
            'deliveryId' => $deliveryId,
            'prUrl' => $prUrl,
            'prNumber' => $prNumber,
            'repository' => $repository,
            'states' => $states,
            'result' => $result,
            'resultType' => stripos($result, 'updated') !== false ? 'updated' : 'processed'
        ];
    }

    /**
     * Parse plain text email content into structured data
     */
    public function parseFromPlainText($plainText)
    {
        $items = preg_split('/\r?\n={90,}\r?\n/', $plainText);

        foreach ($items as $item) {
            if (empty(trim($item)))
                continue;

            // Extract sequence
            preg_match('/Sequence:\s*(\d+)/', $item, $seqMatches);
            $sequence = isset($seqMatches[1]) ? $seqMatches[1] : '';

            // Extract delivery ID
            preg_match('/Delivery ID:\s*([\w-]+)/', $item, $deliveryMatches);
            $deliveryId = isset($deliveryMatches[1]) ? $deliveryMatches[1] : '';

            // Extract PR URL
            preg_match('/(https:\/\/github\.com\/[^\s:]+)/', $item, $urlMatches);
            $prUrl = isset($urlMatches[1]) ? $urlMatches[1] : '';
            // Extract result
            preg_match('/Item\s+(processed|updated[^!]*)!/', $item, $resultMatches);
            $result = isset($resultMatches[0]) ? $resultMatches[0] : '';

            // Extract states
            $states = [];
            $lines = explode("\n", $item);
            foreach ($lines as $line) {
                if (preg_match('/^(State|Triggering|PR State):/', $line)) {
                    $states[] = trim($line);
                }
            }

            if (!empty($sequence) && !empty($prUrl)) {
                $this->addPRItem($sequence, $deliveryId, $prUrl, $states, $result);
            }
        }

        return $this;
    }

    /**
     * Generate HTML email content
     */
    public function generateHtmlEmail()
    {
        $currentDate = date('F j, Y');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->emailSubject}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            background-color: #007bff;
            color: #ffffff;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #ddd;
            margin-top: 20px;
            font-size: 0.8em;
            color: #666;
        }
        .pr-item {
            background-color: #fff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #0366d6;
        }
        .pr-item.processed {
            border-left-color: #2cbe4e;
        }
        .pr-item.updated {
            border-left-color: #f9c513;
        }
        .pr-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .pr-title {
            font-weight: bold;
            font-size: 1.1em;
        }
        .pr-meta {
            color: #666;
            font-size: 0.9em;
        }
        .pr-states {
            margin-top: 10px;
        }
        .pr-state-item {
            padding: 8px;
            background-color: #f6f8fa;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .pr-result {
            font-weight: bold;
            margin-top: 10px;
            padding: 8px;
            background-color: #f0fff4;
            border-radius: 4px;
            color: #22863a;
        }
        .pr-result.updated {
            background-color: #fff8c5;
            color: #735c0f;
        }
        a {
            color: #0366d6;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{$this->logoUrl}" alt="Logo" class="logo">
        <h1>{$this->emailSubject}</h1>
    </div>
    
    <div class="content">
HTML;

        // Add PR items
        foreach ($this->prItems as $item) {
            $resultClass = $item['resultType'];

            $html .= <<<HTML
        <div class="pr-item {$resultClass}">
            <div class="pr-header">
                <div class="pr-title">
                    <a href="{$item['prUrl']}">PR #{$item['prNumber']}</a>
                    <div>Repository: {$item['repository']}</div>
                </div>
                <div class="pr-meta">
                    Sequence: {$item['sequence']} | Delivery ID: {$item['deliveryId']}
                </div>
            </div>
            
            <div class="pr-states">
HTML;

            // Add state items
            foreach ($item['states'] as $state) {
                $html .= <<<HTML
                <div class="pr-state-item">
                    {$state}
                </div>
HTML;
            }

            $html .= <<<HTML
            </div>
            
            <div class="pr-result {$resultClass}">
                {$item['result']}
            </div>
        </div>
HTML;
        }

        $html .= <<<HTML
    </div>
    
    <div class="footer">
        <p>Generated on {$currentDate}</p>
        <div class="footer_link">
            <p>This email was generated automatically by <a href="https://github.com/guibranco/webhooks">Webhooks Handler v{$this->version}</a>.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Send email with HTML content
     */
    public function sendEmail($to, $from = null)
    {
        $subject = $this->emailSubject;
        $htmlContent = $this->generateHtmlEmail();

        // Create a random boundary
        $boundary = md5(time());

        // Headers
        $headers = "From: " . ($from ?: "PR Processing System <noreply@yourdomain.com>") . "\r\n";
        $headers .= "Reply-To: " . ($from ?: "noreply@yourdomain.com") . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

        // Email body
        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

        // Plain text version (fallback)
        $plainText = "GitHub PR Processing Report\r\n\r\n";
        foreach ($this->prItems as $item) {
            $plainText .= "PR #{$item['prNumber']} - {$item['prUrl']}\r\n";
            $plainText .= "Sequence: {$item['sequence']} | Delivery ID: {$item['deliveryId']}\r\n\r\n";
            foreach ($item['states'] as $state) {
                $plainText .= "- {$state}\r\n";
            }
            $plainText .= "\r\n{$item['result']}\r\n\r\n";
            $plainText .= "---------------------------------------\r\n\r\n";
        }

        $message .= $plainText . "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $htmlContent . "\r\n";
        $message .= "--{$boundary}--";

        // Send email
        return mail($to, $subject, $message, $headers);
    }

    /**
     * Set the logo URL
     */
    public function setLogoUrl($url)
    {
        $this->logoUrl = $url;
        return $this;
    }

    /**
     * Set version number
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Set email subject
     */
    public function setEmailSubject($subject)
    {
        $this->emailSubject = $subject;
        return $this;
    }
}

// Example usage
$emailGenerator = new GitHubPRProcessingEmailGenerator();

$method = isset($_GET['method']) ? $_GET['method'] : 'plain_text';
// Check if the method is valid
if (!in_array($method, ['plain_text', 'manual'])) {
    // Display an error message
    echo "Invalid method specified. Please use 'plain_text' or 'manual'.<br />";

    // Display links for both options as a HTML list
    echo "<ul>";
    echo "<li><a href='?method=plain_text'>Parse from plain text</a></li>";
    echo "<li><a href='?method=manual'>Add items manually</a></li>";
    echo "</ul>";
    exit;
}

// Load the plain text email content
if ($method === 'plain_text') {
    // Method 1: Parse from plain text
    $plainTextEmail = file_get_contents(__DIR__ . '/email_plain_text.txt');
    $emailGenerator->parseFromPlainText($plainTextEmail);
} elseif ($method === 'manual') {
    // Method 2: Add items manually
    $emailGenerator->addPRItem(
        '5233',
        '7561D800-24C1-11F0-84F4-82823A891F09',
        'https://github.com/GuilhermeStracini/POC-GHActions-CI-PHPLaravel/pull/246',
        [
            'Triggering review of #245 - Sender: depfu[bot] 🔄',
            'PR State: closed ⛔'
        ],
        'Item processed!'
    );
}

// Customize (optional)
$emailGenerator
    ->setLogoUrl('https://bot.straccini.com/images/logo-white.png')
    ->setVersion('1.2.3')
    ->setEmailSubject('[TEST] CRON Job Report');

// Generate and send
$html = $emailGenerator->generateHtmlEmail();
$success = $emailGenerator->sendEmail('guilherme@guilhermebranco.com.br');

if ($success) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email. Please check your mail configuration.";
}
