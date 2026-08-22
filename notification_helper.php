<?php
// notification_helper.php
require_once __DIR__ . '/db.php';

function get_notification_email() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT content_value FROM site_content WHERE key_name = 'notification_email'");
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return $val ? trim($val) : '';
    } catch (Exception $e) {
        return '';
    }
}

function set_notification_email($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO site_content (key_name, content_value) VALUES ('notification_email', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
        $stmt->execute([trim($email)]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 0) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'beta.svobodnecechy.cz';
    return $protocol . $host;
}

function send_admin_notification($subject, $bodyHtml) {
    $to = get_notification_email();
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Svobodné Cechy <notifikace@svobodnecechy.cz>\r\n";
    $headers .= "Reply-To: " . $to . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

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
          Nastavení notifikačního e-mailu lze upravit v administraci.
        </div>
      </div>
    </body>
    </html>
    ';

    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $fullHtml, $headers);
}
