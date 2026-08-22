<?php
// admin/landing_leads.php
require_once __DIR__ . '/../db.php';

// Auto-create database table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS landing_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        landing_slug VARCHAR(100) NOT NULL,
        master_name VARCHAR(150) DEFAULT NULL,
        email VARCHAR(255) NOT NULL,
        name VARCHAR(150) DEFAULT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        user_role VARCHAR(50) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'novy',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (landing_slug),
        INDEX (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (\PDOException $e) {}

// Actions: Delete or Change status
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM landing_leads WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: landing_leads.php?msg=deleted");
    exit;
}

if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $currStatus = $_GET['current'] ?? 'novy';
    $newStatus = ($currStatus === 'novy') ? 'vyreseno' : 'novy';
    $stmt = $pdo->prepare("UPDATE landing_leads SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    header("Location: landing_leads.php?msg=status_updated");
    exit;
}

// Fetch Leads with optional filtering
$search = trim($_GET['search'] ?? '');
$slugFilter = trim($_GET['slug'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM landing_leads WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (email LIKE ? OR name LIKE ? OR phone LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($slugFilter)) {
    $sql .= " AND landing_slug = ?";
    $params[] = $slugFilter;
}

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Get unique landing pages for filter dropdown
$slugsStmt = $pdo->query("SELECT DISTINCT landing_slug, master_name FROM landing_leads");
$availableSlugs = $slugsStmt->fetchAll();

// Stats
$totalLeads = count($leads);
$newLeadsCount = $pdo->query("SELECT COUNT(*) FROM landing_leads WHERE status = 'novy'")->fetchColumn();
$resolvedLeadsCount = $pdo->query("SELECT COUNT(*) FROM landing_leads WHERE status = 'vyreseno'")->fetchColumn();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Zprávy a Kontakty z Landing Pages – Svobodné Cechy</title>
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
    .container { max-width: 1280px; margin: auto; }
    
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .btn-back { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    
    h1 { font-family: 'Bungee', cursive; font-size: 1.8rem; color: var(--accent); margin-bottom: 0.3rem; }
    p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 1.2rem; text-align: center; }
    .stat-val { font-size: 2rem; font-weight: 700; color: var(--accent); font-family: 'Bungee', cursive; }
    .stat-lbl { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-top: 0.3rem; }

    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    
    /* Filters */
    .filter-bar { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; border: 1px solid var(--border); align-items: center; }
    .form-control { padding: 0.6rem 0.9rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 6px; color: #fff; font-size: 0.9rem; }

    /* Table */
    table { width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 0.9rem 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
    td { padding: 0.9rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.9rem; }
    
    .badge-status { display: inline-block; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 4px; text-transform: uppercase; }
    .badge-status.novy { background: rgba(232, 117, 22, 0.25); color: #e87516; border: 1px solid #e87516; }
    .badge-status.vyreseno { background: rgba(37, 211, 102, 0.25); color: #25D366; border: 1px solid #25D366; }

    .badge-step1 { font-size: 0.72rem; color: #fbbf24; background: rgba(245, 158, 11, 0.15); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.4rem; }
    .badge-step2 { font-size: 0.72rem; color: #4ade80; background: rgba(34, 197, 94, 0.15); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.4rem; }

    .btn-action { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.45rem 0.8rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.8rem; border: none; cursor: pointer; transition: all .2s; }
    .btn-wa { background: #25D366; color: #fff; }
    .btn-wa:hover { background: #1eb854; }
    .btn-phone { background: #3b82f6; color: #fff; }
    .btn-toggle { background: rgba(255,255,255,0.1); color: var(--text); border: 1px solid var(--border); }
    .btn-delete { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>Zprávy a Kontakty z Landing Pages</h1>
    <p class="subtitle">Přehled všech zanechaných e-mailů, telefonů a zpráv od zájemců o učednictví.</p>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-val"><?= $totalLeads ?></div>
        <div class="stat-lbl">Zobrazených leadů</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" style="color:#e87516;"><?= $newLeadsCount ?></div>
        <div class="stat-lbl">Nové nevyřešené</div>
      </div>
      <div class="stat-card">
        <div class="stat-val" style="color:#25D366;"><?= $resolvedLeadsCount ?></div>
        <div class="stat-lbl">Vyřešené kontakty</div>
      </div>
    </div>

    <div class="card">
      <!-- FILTERS -->
      <form method="get" action="landing_leads.php" class="filter-bar">
        <input type="text" name="search" class="form-control" style="flex:1; min-width:200px;" placeholder="Hledat e-mail, jméno, telefon..." value="<?= htmlspecialchars($search) ?>" />
        
        <select name="slug" class="form-control">
          <option value="">Všechny Landing Pages</option>
          <?php foreach ($availableSlugs as $s): ?>
            <option value="<?= htmlspecialchars($s['landing_slug']) ?>" <?= $slugFilter === $s['landing_slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['master_name'] ?: $s['landing_slug']) ?> (<?= htmlspecialchars($s['landing_slug']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <select name="status" class="form-control">
          <option value="">Všechny stavy</option>
          <option value="novy" <?= $statusFilter === 'novy' ? 'selected' : '' ?>>Nové</option>
          <option value="vyreseno" <?= $statusFilter === 'vyreseno' ? 'selected' : '' ?>>Vyřešené</option>
        </select>

        <button type="submit" class="btn-action" style="background:var(--accent); color:#fff; padding:0.6rem 1.2rem;">
          <i class="bi bi-funnel"></i> Filtrovat
        </button>
        <?php if ($search || $slugFilter || $statusFilter): ?>
          <a href="landing_leads.php" class="btn-action btn-toggle" style="text-decoration:none;">Zrušit filtr</a>
        <?php endif; ?>
      </form>

      <!-- TABLE -->
      <table>
        <thead>
          <tr>
            <th>Datum a Čas</th>
            <th>Landing Page (Mistr)</th>
            <th>E-mail</th>
            <th>Jméno / Telefon / Role</th>
            <th>Zpráva</th>
            <th>Stav</th>
            <th>Akce</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($leads)): ?>
            <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:2.5rem;">Zatím nebyly zaznamenány žádné zprávy ani e-maily.</td></tr>
          <?php else: ?>
            <?php foreach ($leads as $lead): ?>
              <?php 
              $hasDetails = !empty($lead['name']) || !empty($lead['phone']) || !empty($lead['message']);
              $waPhone = preg_replace('/[^0-9]/', '', $lead['phone'] ?? '');
              if (!empty($waPhone) && !str_starts_with($waPhone, '420') && strlen($waPhone) === 9) {
                  $waPhone = '420' . $waPhone;
              }
              ?>
              <tr>
                <td style="white-space:nowrap; color:var(--text-muted); font-size:0.85rem;">
                  <i class="bi bi-clock"></i> <?= date('d.m.Y H:i', strtotime($lead['created_at'])) ?>
                </td>
                <td>
                  <strong><?= htmlspecialchars($lead['master_name'] ?: $lead['landing_slug']) ?></strong>
                  <div style="font-family:monospace; font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($lead['landing_slug']) ?></div>
                </td>
                <td>
                  <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="color:var(--accent); font-weight:600; text-decoration:none;">
                    <?= htmlspecialchars($lead['email']) ?>
                  </a>
                  <?php if ($hasDetails): ?>
                    <span class="badge-step2" title="Uživatel dokončil 2. krok modal formuláře">✓ Krok 1+2</span>
                  <?php else: ?>
                    <span class="badge-step1" title="Uživatel vyplnil pouze 1. krok (e-mail)">⏱️ Pouze E-mail</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($lead['name']): ?>
                    <div style="font-weight:600; color:#fff;"><?= htmlspecialchars($lead['name']) ?></div>
                  <?php endif; ?>
                  <?php if ($lead['phone']): ?>
                    <div style="font-size:0.85rem; color:var(--text-muted);"><i class="bi bi-telephone"></i> <?= htmlspecialchars($lead['phone']) ?></div>
                  <?php endif; ?>
                  <?php if ($lead['user_role']): ?>
                    <div style="font-size:0.75rem; color:var(--accent); font-weight:600;"><?= htmlspecialchars($lead['user_role']) ?></div>
                  <?php endif; ?>
                  <?php if (!$lead['name'] && !$lead['phone'] && !$lead['user_role']): ?>
                    <span style="color:var(--text-muted); font-style:italic; font-size:0.8rem;">Neuvedeno</span>
                  <?php endif; ?>
                </td>
                <td style="max-width:240px; font-size:0.85rem;">
                  <?= !empty($lead['message']) ? nl2br(htmlspecialchars($lead['message'])) : '<span style="color:var(--text-muted); font-style:italic;">Bez textu</span>' ?>
                </td>
                <td>
                  <?php if ($lead['status'] === 'vyreseno'): ?>
                    <span class="badge-status vyreseno">Vyřešeno</span>
                  <?php else: ?>
                    <span class="badge-status novy">Nový</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                    <?php if (!empty($waPhone)): ?>
                      <a href="https://wa.me/<?= $waPhone ?>?text=Dobrý%20den,%20reaguji%20na%20váš%20dotaz%20ohledně%20učednictví..." target="_blank" class="btn-action btn-wa" title="Napsat na WhatsApp">
                        <i class="bi bi-whatsapp"></i> WA
                      </a>
                    <?php endif; ?>
                    <?php if (!empty($lead['phone'])): ?>
                      <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn-action btn-phone" title="Zavolat">
                        <i class="bi bi-telephone-fill"></i> Volat
                      </a>
                    <?php endif; ?>
                    <a href="landing_leads.php?toggle_status=<?= $lead['id'] ?>&current=<?= $lead['status'] ?>" class="btn-action btn-toggle" title="Změnit stav">
                      <i class="bi bi-check2-circle"></i>
                    </a>
                    <a href="landing_leads.php?delete=<?= $lead['id'] ?>" onclick="return confirm('Opravdu smazat tento kontakt?');" class="btn-action btn-delete" title="Smazat">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
