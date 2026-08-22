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

function get_smtp_settings() {
    global $pdo;
    $defaults = [
        'enabled' => '0',
        'host' => 'smtp.seznam.cz',
        'port' => '465',
        'user' => '',
        'pass' => '',
        'secure' => 'ssl',
        'from' => 'info@svobodnecechy.cz',
        'from_name' => 'Svobodné Cechy'
    ];
    try {
        $stmt = $pdo->query("SELECT key_name, content_value FROM site_content WHERE key_name LIKE 'smtp_%'");
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($defaults as $k => $v) {
            if (isset($rows['smtp_' . $k])) {
                $defaults[$k] = $rows['smtp_' . $k];
            }
        }
    } catch (\Exception $e) {}
    return $defaults;
}

function save_smtp_settings($settings) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO site_content (key_name, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
        foreach ($settings as $k => $v) {
            $stmt->execute(['smtp_' . $k, trim($v)]);
        }
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

/**
 * Lightweight Pure PHP SMTP Socket Mailer Class
 * Zero dependencies, direct socket connection with real SMTP error codes.
 */
class SimpleSmtpMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $timeout = 12;

    public function __construct($host, $port = 587, $username = '', $password = '', $encryption = 'tls') {
        $this->host = $host;
        $this->port = (int)$port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = strtolower($encryption);
    }

    public function send($fromEmail, $fromName, $toEmail, $subject, $bodyHtml) {
        $socketHost = $this->host;
        if ($this->encryption === 'ssl') {
            $socketHost = 'ssl://' . $this->host;
        }

        $socket = @fsockopen($socketHost, $this->port, $errno, $errstr, $this->timeout);
        if (!$socket) {
            return ['success' => false, 'error' => "Nelze se připojit k SMTP serveru {$this->host}:{$this->port} ($errstr)"];
        }

        stream_set_timeout($socket, $this->timeout);
        $res = $this->readResponse($socket);

        // EHLO
        $this->cmd($socket, "EHLO " . gethostname());

        // STARTTLS
        if ($this->encryption === 'tls') {
            $res = $this->cmd($socket, "STARTTLS");
            if (strpos($res, '220') !== 0) {
                fclose($socket);
                return ['success' => false, 'error' => "SMTP STARTTLS selhalo: " . $res];
            }
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
            $crypto = @stream_socket_enable_crypto($socket, true, $cryptoMethod);
            if (!$crypto) {
                fclose($socket);
                return ['success' => false, 'error' => "Šifrování TLS selhalo při vyjednávání spojení se serverem {$this->host}."];
            }
            $this->cmd($socket, "EHLO " . gethostname());
        }

        // AUTH LOGIN
        if (!empty($this->username)) {
            $res = $this->cmd($socket, "AUTH LOGIN");
            if (strpos($res, '334') !== 0) {
                fclose($socket);
                return ['success' => false, 'error' => "SMTP AUTH LOGIN odmítnuto: " . $res];
            }
            $res = $this->cmd($socket, base64_encode($this->username));
            if (strpos($res, '334') !== 0) {
                fclose($socket);
                return ['success' => false, 'error' => "SMTP Uživatelské jméno odmítnuto: " . $res];
            }
            $res = $this->cmd($socket, base64_encode($this->password));
            if (strpos($res, '235') !== 0) {
                fclose($socket);
                return ['success' => false, 'error' => "SMTP Autentizace selhala (špatné heslo nebo jméno): " . $res];
            }
        }

        // MAIL FROM
        $res = $this->cmd($socket, "MAIL FROM:<{$fromEmail}>");
        if (strpos($res, '250') !== 0) {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP MAIL FROM odmítnut serverem: " . $res];
        }

        // RCPT TO
        $res = $this->cmd($socket, "RCPT TO:<{$toEmail}>");
        if (strpos($res, '250') !== 0 && strpos($res, '251') !== 0) {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP RCPT TO odmítnut pro příjemce {$toEmail}: " . $res];
        }

        // DATA
        $res = $this->cmd($socket, "DATA");
        if (strpos($res, '354') !== 0) {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP DATA příkaz odmítnut: " . $res];
        }

        // Prepare MIME headers & message body
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $mime = "MIME-Version: 1.0\r\n";
        $mime .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mime .= "From: {$encodedFromName} <{$fromEmail}>\r\n";
        $mime .= "Reply-To: <{$fromEmail}>\r\n";
        $mime .= "To: <{$toEmail}>\r\n";
        $mime .= "Subject: {$encodedSubject}\r\n";
        $mime .= "Date: " . date('r') . "\r\n";
        $mime .= "X-Mailer: Svobodne Cechy Direct SMTP Mailer\r\n\r\n";
        $mime .= $bodyHtml . "\r\n.";

        $res = $this->cmd($socket, $mime);
        if (strpos($res, '250') !== 0) {
            fclose($socket);
            return ['success' => false, 'error' => "SMTP Odeslání zprávy selhalo: " . $res];
        }

        $this->cmd($socket, "QUIT");
        fclose($socket);

        return ['success' => true, 'info' => "Zpráva doručena na SMTP server ({$res})"];
    }

    private function cmd($socket, $command) {
        fwrite($socket, $command . "\r\n");
        return $this->readResponse($socket);
    }

    private function readResponse($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return trim($response);
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

    // 1. Try Direct SMTP if enabled
    $smtp = get_smtp_settings();
    if ($smtp['enabled'] === '1' && !empty($smtp['host'])) {
        $mailer = new SimpleSmtpMailer(
            $smtp['host'],
            $smtp['port'],
            $smtp['user'],
            $smtp['pass'],
            $smtp['secure']
        );
        $res = $mailer->send($smtp['from'], $smtp['from_name'], $to, $subject, $fullHtml);
        if ($res['success']) {
            log_notification($to, $subject, $bodyHtml, 'success', '[SMTP OK] ' . $res['info']);
            return true;
        } else {
            log_notification($to, $subject, $bodyHtml, 'failed', '[SMTP CHYBA] ' . $res['error']);
            return false;
        }
    }

    // 2. Fallback to PHP mail()
    $senderEmail = !empty($smtp['from']) ? $smtp['from'] : "info@svobodnecechy.cz";
    $senderName = !empty($smtp['from_name']) ? $smtp['from_name'] : "Svobodné Cechy";

    $headers  = "MIME-Version: 1.0\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\n";
    $headers .= "From: =?UTF-8?B?" . base64_encode($senderName) . "?= <{$senderEmail}>\n";
    $headers .= "Reply-To: {$to}\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\n";

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
        $errMessage = $lastErr ? $lastErr['message'] : 'Funkce mail() vrátila false. Poštovní server lokálně odmítl odeslat e-mail. Doporučujeme v administraci zapnout SMTP server (Active24 SMTP, Seznam, Gmail apod.).';
        log_notification($to, $subject, $bodyHtml, 'failed', '[PHP mail() CHYBA] ' . $errMessage);
        return false;
    } else {
        log_notification($to, $subject, $bodyHtml, 'success', '[PHP mail() OK] E-mail byl předán lokální poštovní frontě.');
        return true;
    }
}
