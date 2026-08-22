<?php
// notification_helper.php
require_once __DIR__ . '/db.php';

// Auto-create notification_logs table if not exists
try {
    global $pdo;
    if (isset($pdo)) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS notification_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipient VARCHAR(255) DEFAULT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            body_snippet TEXT DEFAULT NULL,
            status ENUM('success', 'failed', 'no_recipient') NOT NULL DEFAULT 'failed',
            error_info TEXT DEFAULT NULL,
            sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (status),
            INDEX (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
} catch (\Exception $e) {
    // Ignore schema check error if DB connection fails temporarily
}

function get_notification_email() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT content_value FROM site_content WHERE key_name = 'notification_email'");
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val ? trim($val) : '';
    } catch (\Exception $e) {
        return '';
    }
}

function set_notification_email($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO site_content (key_name, content_value) VALUES ('notification_email', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
        $stmt->execute([trim($email)]);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 0) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'beta.svobodnecechy.cz';
    return $protocol . $host;
}

function log_notification($recipient, $subject, $bodySnippet, $status, $errorInfo = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO notification_logs (recipient, subject, body_snippet, status, error_info, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            mb_substr($recipient, 0, 255),
            mb_substr($subject, 0, 255),
            mb_substr(strip_tags($bodySnippet), 0, 1000),
            $status,
            $errorInfo
        ]);
        return $pdo->lastInsertId();
    } catch (\Exception $e) {
        return false;
    }
}

function send_admin_notification($subject, $bodyHtml, $overrideRecipient = null) {
    $to = $overrideRecipient !== null ? trim($overrideRecipient) : get_notification_email();

    if (empty($to)) {
        log_notification('', $subject, $bodyHtml, 'no_recipient', 'Nebyl nastaven žádný notifikační e-mail v administraci.');
        return false;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        log_notification($to, $subject, $bodyHtml, 'failed', "Neplatný formát e-mailové adresy: '{$to}'");
        return false;
    }

    $senderEmail = "info@svobodnecechy.cz";
    
    // Headers setup with standard \n for maximum mail() compatibility on Linux/Active24
    $headers  = "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\n";
    $headers .= "From: Svobodné Cechy <{$senderEmail}>\n";
    $headers .= "Reply-To: {$to}\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\n";

    $fullHtml = '
    <!DOCTYPE html>
    <html lang="cs">
    <head>
      <meta charset="UTF-8">
      <title>' . htmlspecialchars($subject) . '</title>
    </head>
    <body style="font-family: \'Plus Jakarta Sans\', Arial, sans-serif; background-color: #0f172a; color: #f8fafc; margin: 0; padding: 24px;">
      <div style="max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 12px; padding: 28px; border: 1px solid #334155; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div style="border-bottom: 2px solid #ff7b1c; padding-bottom: 14px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
          <h2 style="color: #ff7b1c; margin: 0; font-size: 20px; font-weight: 700;">🔥 Svobodné Cechy</h2>
          <span style="color: #94a3b8; font-size: 13px;">Nová notifikace</span>
        </div>
        ' . $bodyHtml . '
        <div style="margin-top: 32px; padding-top: 18px; border-top: 1px solid #334155; font-size: 12px; color: #64748b; text-align: center;">
          Tato zpráva byla automaticky vygenerována systémem <strong>Svobodné Cechy</strong>.<br>
          Správa notifikací: <a href="' . get_base_url() . '/admin/notification_log.php" style="color:#ff7b1c;">Zobrazit logy notifikací</a>
        </div>
      </div>
    </body>
    </html>
    ';

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    if (function_exists('error_clear_last')) {
        @error_clear_last();
    }

    $sent = false;
    try {
        $sent = mail($to, $encodedSubject, $fullHtml, $headers, "-f" . $senderEmail);
    } catch (\Throwable $e) {
        $sent = false;
    }

    if (!$sent) {
        try {
            $sent = mail($to, $encodedSubject, $fullHtml, $headers);
        } catch (\Throwable $e) {
            $sent = false;
        }
    }

    $lastErr = error_get_last();
    if (!$sent) {
        $errMessage = $lastErr ? $lastErr['message'] : 'Funkce mail() vrátila false. Poskytovatel hostingu (Active24) buď vyžaduje specifickou From adresu, nebo selhala lokální poštovní fronta.';
        log_notification($to, $subject, $bodyHtml, 'failed', $errMessage);
        return false;
    } else {
        log_notification($to, $subject, $bodyHtml, 'success', 'E-mail byl úspěšně předán poštovnímu serveru (mail() vráti true).');
        return true;
    }
}
