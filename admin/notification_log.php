<?php
// admin/notification_log.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../notification_helper.php';

// Handle Action: Save Email
$msg = '';
$msgType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_email') {
        $email = trim($_POST['notification_email'] ?? '');
        set_notification_email($email);
        $msg = "Notifikační e-mail byl uložen.";
        $msgType = "success";
    }

    if ($action === 'send_test') {
        $testRecipient = trim($_POST['test_recipient'] ?? get_notification_email());
        if (empty($testRecipient)) {
            $msg = "Zadejte cílový e-mail pro testovací notifikaci.";
            $msgType = "error";
        } else {
            $subject = "🧪 Testovací notifikace systému Svobodné Cechy (" . date('H:i:s') . ")";
            $body = "
              <h3 style='color:#ff7b1c; margin-top:0;'>Testovací notifikace</h3>
              <p>Tento e-mail ověřuje plnou funkčnost odesílání notifikací z vaší aplikace <strong>Svobodné Cechy</strong>.</p>
              <table style='width:100%; border-collapse:collapse; color:#cbd5e1; font-size:13px; margin:16px 0;'>
                <tr><td style='padding:6px 0; border-bottom:1px solid #334155; width:160px; color:#94a3b8;'><strong>Čas odeslání:</strong></td><td style='padding:6px 0; border-bottom:1px solid #334155;'>" . date('d.m.Y H:i:s') . "</td></tr>
                <tr><td style='padding:6px 0; border-bottom:1px solid #334155; color:#94a3b8;'><strong>Server (HTTP Host):</strong></td><td style='padding:6px 0; border-bottom:1px solid #334155;'>" . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'lokální') . "</td></tr>
                <tr><td style='padding:6px 0; color:#94a3b8;'><strong>Cílový e-mail:</strong></td><td style='padding:6px 0;'>" . htmlspecialchars($testRecipient) . "</td></tr>
              </table>
              <div style='background:#0f172a; padding:12px; border-radius:8px; border:1px solid #334155; font-family:monospace; font-size:12px; color:#a7f3d0;'>
                ✓ Pokud jste obdrželi tento e-mail, systém notifikací funguje správně!
              </div>
            ";

            $res = send_admin_notification($subject, $body, $testRecipient);
            if ($res) {
                $msg = "Testovací e-mail byl odeslán! Zkontrolujte schránku {$testRecipient} (i složku SPAM).";
                $msgType = "success";
            } else {
                $msg = "Odeslání testovacího e-mailu selhalo. Podrobnosti naleznete v tabulce logů níže.";
                $msgType = "error";
            }
        }
    }

    if ($action === 'clear_logs') {
        try {
            $pdo->exec("TRUNCATE TABLE notification_logs");
            $msg = "Historie logů byla vymazána.";
            $msgType = "success";
        } catch (\Exception $e) {
            $msg = "Chyba při mazání logů: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// Fetch current notification email
$currentEmail = get_notification_email();

// Filter logs
$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM notification_logs WHERE 1=1";
$params = [];
if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY sent_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Server Diagnostics Info
$mailFunctionExists = function_exists('mail');
$sendmailPath = ini_get('sendmail_path');
$disabledFunctions = ini_get('disabled_functions');
$isMailDisabled = in_array('mail', array_map('trim', explode(',', $disabledFunctions)));
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notifikace a Logy e-mailů – Svobodné Cechy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    :root {
      --bg: #0b0a08;
      --card-bg: #17120e;
      --accent: #e87516;
      --accent-hover: #d0640d;
      --text: #f4efe7;
      --text-muted: #a39b8e;
      --border: rgba(255,255,255,0.1);
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; }
    .container { max-width: 1200px; margin: auto; }
    
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .btn-back { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    
    h1 { font-family: 'Bungee', cursive; font-size: 1.8rem; color: var(--accent); margin-bottom: 0.3rem; }
    p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; }

    .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 1.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    
    .alert { padding: 1rem 1.2rem; border-radius: 8px; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem; }
    .alert-success { background: rgba(37, 211, 102, 0.15); border: 1px solid #25D366; color: #25D366; }
    .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; }
    .alert-info { background: rgba(59, 130, 246, 0.15); border: 1px solid #3b82f6; color: #60a5fa; }

    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.9rem; }
    .form-control { width: 100%; padding: 0.7rem 0.9rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 8px; color: #fff; font-size: 0.95rem; }

    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.7rem 1.3rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; transition: all .2s; }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-primary:hover { background: var(--accent-hover); }
    .btn-secondary { background: rgba(255,255,255,0.1); color: var(--text); border: 1px solid var(--border); }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }

    /* Table */
    table { width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 0.9rem 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
    td { padding: 0.9rem 1rem; border-bottom: 1px solid var(--border); vertical-align: top; font-size: 0.88rem; }
    
    .badge { display: inline-block; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 4px; text-transform: uppercase; }
    .badge-success { background: rgba(37, 211, 102, 0.25); color: #25D366; border: 1px solid #25D366; }
    .badge-failed { background: rgba(239, 68, 68, 0.25); color: #ef4444; border: 1px solid #ef4444; }
    .badge-no_recipient { background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid #fbbf24; }

    .code-box { font-family: monospace; font-size: 0.82rem; background: rgba(0,0,0,0.4); padding: 0.4rem 0.6rem; border-radius: 4px; color: #a7f3d0; word-break: break-all; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>📧 Správa Notifikací a Diagnostika E-mailů</h1>
    <p class="subtitle">Evidence odeslaných e-mailových notifikací, kontrola doručení a testovací rozhraní.</p>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType ?>">
        <i class="bi bi-<?= $msgType === 'success' ? 'check-circle' : ($msgType === 'error' ? 'exclamation-triangle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <div class="grid-2">
      <!-- CARD 1: Nastavení Notifikačního E-mailu -->
      <div class="card" style="margin-bottom:0;">
        <h3 style="color:var(--accent); margin-bottom:1rem; font-size:1.1rem; display:flex; align-items:center; gap:0.5rem;">
          <i class="bi bi-envelope-at"></i> Cílový Notifikační E-mail
        </h3>
        <form method="post">
          <input type="hidden" name="action" value="save_email" />
          <div class="form-group">
            <label>E-mail pro notifikace z webu i landing pages</label>
            <input type="email" name="notification_email" value="<?= htmlspecialchars($currentEmail) ?>" class="form-control" placeholder="např. admin@svobodnecechy.cz" required />
            <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.4rem;">
              Na tento e-mail budou chodit kopie všech kontaktních formulářů, zájemců i poptávek.
            </small>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;"><i class="bi bi-save"></i> Uložit notifikační e-mail</button>
        </form>
      </div>

      <!-- CARD 2: Testovací Odeslání -->
      <div class="card" style="margin-bottom:0;">
        <h3 style="color:var(--accent); margin-bottom:1rem; font-size:1.1rem; display:flex; align-items:center; gap:0.5rem;">
          <i class="bi bi-send-check"></i> Odeslat Testovací E-mail
        </h3>
        <form method="post">
          <input type="hidden" name="action" value="send_test" />
          <div class="form-group">
            <label>Testovací příjemce</label>
            <input type="email" name="test_recipient" value="<?= htmlspecialchars($currentEmail) ?>" class="form-control" placeholder="např. vas-email@seznam.cz" required />
          </div>
          <button type="submit" class="btn btn-secondary" style="width:100%;"><i class="bi bi-paperplane-fill"></i> Odeslat testovací zprávu hned</button>
        </form>
      </div>
    </div>

    <!-- SERVER DIAGNOSTICS CARD -->
    <div class="card">
      <h3 style="color:var(--accent); margin-bottom:1rem; font-size:1.1rem; display:flex; align-items:center; gap:0.5rem;">
        <i class="bi bi-cpu"></i> Diagnostika Poštovního Serveru (PHP mail)
      </h3>
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; font-size:0.9rem;">
        <div>
          <strong style="color:var(--text-muted);">Funkce mail():</strong><br>
          <?php if ($mailFunctionExists && !$isMailDisabled): ?>
            <span style="color:#25D366; font-weight:700;"><i class="bi bi-check-circle-fill"></i> Povolena v PHP</span>
          <?php else: ?>
            <span style="color:#ef4444; font-weight:700;"><i class="bi bi-x-circle-fill"></i> Zakázána na serveru</span>
          <?php endif; ?>
        </div>
        <div>
          <strong style="color:var(--text-muted);">Poskytovatel webhostingu:</strong><br>
          <span style="color:#60a5fa; font-weight:600;">Active24 (db.r4.active24.cz)</span>
        </div>
        <div>
          <strong style="color:var(--text-muted);">Odesílací adresa (From):</strong><br>
          <code>info@svobodnecechy.cz</code>
        </div>
        <div>
          <strong style="color:var(--text-muted);">Cesta sendmail (php.ini):</strong><br>
          <span class="code-box"><?= htmlspecialchars($sendmailPath ?: 'Není definována / standardní MTA') ?></span>
        </div>
      </div>
    </div>

    <!-- NOTIFICATION LOGS TABLE -->
    <div class="card">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
        <div>
          <h3 style="color:var(--accent); font-size:1.2rem;">Historie Odeslaných Notifikací</h3>
          <p style="color:var(--text-muted); font-size:0.85rem;">Posledních 100 pokusů o odeslání notifikace</p>
        </div>
        <div style="display:flex; gap:0.8rem; align-items:center;">
          <form method="get" style="display:flex; gap:0.5rem;">
            <select name="status" class="form-control" onchange="this.form.submit()" style="padding:0.4rem 0.8rem;">
              <option value="">Všechny statusy</option>
              <option value="success" <?= $statusFilter === 'success' ? 'selected' : '' ?>>Úspěšné (success)</option>
              <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Chyby (failed)</option>
              <option value="no_recipient" <?= $statusFilter === 'no_recipient' ? 'selected' : '' ?>>Chybějící e-mail</option>
            </select>
          </form>
          <form method="post" onsubmit="return confirm('Opravdu chcete vymazat celou historii logů?');">
            <input type="hidden" name="action" value="clear_logs" />
            <button type="submit" class="btn btn-danger" style="padding:0.45rem 0.9rem; font-size:0.8rem;"><i class="bi bi-trash"></i> Vymazat logy</button>
          </form>
        </div>
      </div>

      <?php if (empty($logs)): ?>
        <div style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
          <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:0.5rem;"></i>
          Zatím nebyly zaznamenány žádné notifikace.
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th style="width:70px;">ID</th>
              <th style="width:140px;">Datum a Čas</th>
              <th style="width:200px;">Příjemce</th>
              <th>Předmět a náhled</th>
              <th style="width:110px;">Status</th>
              <th>Detail / Chyba</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td>#<?= $log['id'] ?></td>
                <td style="white-space:nowrap;"><?= date('d.m.Y H:i:s', strtotime($log['sent_at'])) ?></td>
                <td>
                  <?php if ($log['recipient']): ?>
                    <a href="mailto:<?= htmlspecialchars($log['recipient']) ?>" style="color:#60a5fa; text-decoration:none;">
                      <?= htmlspecialchars($log['recipient']) ?>
                    </a>
                  <?php else: ?>
                    <span style="color:#94a3b8; font-style:italic;">(Nezadaný)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color:#fff; font-size:0.9rem; display:block; margin-bottom:0.2rem;">
                    <?= htmlspecialchars($log['subject']) ?>
                  </strong>
                  <span style="color:var(--text-muted); font-size:0.8rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                    <?= htmlspecialchars($log['body_snippet']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($log['status'] === 'success'): ?>
                    <span class="badge badge-success"><i class="bi bi-check-lg"></i> Úspěch</span>
                  <?php elseif ($log['status'] === 'failed'): ?>
                    <span class="badge badge-failed"><i class="bi bi-x-lg"></i> Chyba</span>
                  <?php else: ?>
                    <span class="badge badge-no_recipient"><i class="bi bi-exclamation-triangle"></i> Bez e-mailu</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.82rem; color: #d1d5db;">
                  <?= htmlspecialchars($log['error_info'] ?: 'OK') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
