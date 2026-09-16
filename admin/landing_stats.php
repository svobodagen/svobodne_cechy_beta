<?php
// admin/landing_stats.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../notification_helper.php';

// Fetch available landing pages for filters
$slugsStmt = $pdo->query("SELECT DISTINCT slug, master_name FROM landing_pages ORDER BY created_at DESC");
$availableLandingPages = $slugsStmt ? $slugsStmt->fetchAll() : [];

$selectedSlug = $_GET['slug'] ?? '';
$initialSession = $_GET['session'] ?? '';
$initialTab = $_GET['tab'] ?? 'generator';
if (!empty($initialSession)) {
    $initialTab = 'visitors';
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <script>if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') { location.replace('https://' + location.hostname + location.pathname + location.search + location.hash); }</script>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Analytika a Generátor odkazů – Svobodné Cechy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    :root {
      --bg: #0b0a08;
      --card-bg: #17120e;
      --card-inner: #221a14;
      --accent: #e87516;
      --accent-hover: #d0640d;
      --accent-rgb: 232, 117, 22;
      --text: #f4efe7;
      --text-muted: #a39b8e;
      --border: rgba(255,255,255,0.1);
      --success: #22c55e;
      --blue: #3b82f6;
      --purple: #a855f7;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; line-height: 1.5; }
    .container { max-width: 1320px; margin: auto; }

    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .nav-links { display: flex; gap: 1rem; align-items: center; }
    .nav-link { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; }
    .nav-link:hover { text-decoration: underline; }

    h1 { font-family: 'Bungee', cursive; font-size: 2rem; color: var(--accent); margin-bottom: 0.3rem; letter-spacing: 0.5px; }
    p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.8rem; }

    /* Tabs Navigation */
    .tabs-nav { display: flex; gap: 0.5rem; border-bottom: 2px solid rgba(255,255,255,0.1); margin-bottom: 2rem; overflow-x: auto; padding-bottom: 2px; }
    .tab-btn { background: transparent; border: none; color: var(--text-muted); padding: 0.9rem 1.4rem; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem; font-weight: 700; cursor: pointer; border-radius: 8px 8px 0 0; display: inline-flex; align-items: center; gap: 0.6rem; transition: all 0.2s; white-space: nowrap; }
    .tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .tab-btn.active { color: var(--accent); border-bottom: 3px solid var(--accent); background: rgba(232,117,22,0.1); }

    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: fadeIn 0.25s ease-out; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

    /* Cards */
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    .card h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.2rem; color: #fff; display: flex; align-items: center; gap: 0.6rem; }
    .card h2 i { color: var(--accent); }

    /* Form Controls */
    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 8px; color: #fff; font-size: 0.95rem; font-family: inherit; }
    .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(232,117,22,0.25); }

    /* Channel presets */
    .presets-box { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; margin-bottom: 1rem; }
    .preset-pill { background: rgba(255,255,255,0.08); border: 1px solid var(--border); color: #fff; padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem; }
    .preset-pill:hover, .preset-pill.active { background: var(--accent); color: #000; border-color: var(--accent); font-weight: 700; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.4rem; border-radius: 8px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-primary { background: var(--accent); color: #000; }
    .btn-primary:hover { background: var(--accent-hover); color: #fff; }
    .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid var(--border); }
    .btn-secondary:hover { background: rgba(255,255,255,0.2); }
    .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; }

    /* Result Box in Generator */
    .link-result-box { background: #07090e; border: 1px solid #3b82f6; border-radius: 10px; padding: 1.2rem; margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
    .link-url-display { font-family: monospace; font-size: 0.95rem; color: #60a5fa; word-break: break-all; background: rgba(0,0,0,0.4); padding: 0.8rem 1rem; border-radius: 6px; border: 1px solid rgba(59,130,246,0.3); }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 1.4rem; text-align: center; }
    .stat-val { font-size: 2.2rem; font-weight: 700; color: var(--accent); font-family: 'Bungee', cursive; }
    .stat-lbl { font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; margin-top: 0.3rem; font-weight: 600; }

    /* Funnel visual */
    .funnel-container { display: flex; flex-direction: column; gap: 0.8rem; margin: 1.5rem 0; }
    .funnel-step { background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 10px; padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
    .funnel-bar { position: absolute; top:0; left:0; bottom:0; background: linear-gradient(90deg, rgba(232,117,22,0.15), rgba(232,117,22,0.35)); z-index: 1; border-radius: 10px; transition: width 0.6s ease; }
    .funnel-content { position: relative; z-index: 2; display: flex; align-items: center; gap: 1rem; width: 100%; justify-content: space-between; }
    .funnel-label { font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; }
    .funnel-stats { display: flex; align-items: center; gap: 1.2rem; }
    .funnel-count { font-size: 1.2rem; font-weight: 800; color: #fff; font-family: 'Bungee', cursive; }
    .funnel-pct { font-size: 0.85rem; font-weight: 700; color: var(--accent); background: rgba(232,117,22,0.2); padding: 0.2rem 0.6rem; border-radius: 12px; }

    /* Tables */
    .table-responsive { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border); }
    table { width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.25); }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 0.85rem 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.78rem; letter-spacing: 0.5px; }
    td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.88rem; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(255,255,255,0.02); }

    /* Badges */
    .badge { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 4px; text-transform: uppercase; }
    .badge-source { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .badge-device { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; }
    .badge-step1 { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .badge-step2 { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
    .badge-wa { background: rgba(37, 211, 102, 0.2); color: #25D366; }
    .badge-web { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
    .badge-none { background: rgba(255, 255, 255, 0.05); color: #94a3b8; }

    /* Modal for Visitor Timeline */
    .timeline-modal { display: none; position: fixed; inset:0; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
    .timeline-box { background: var(--card-bg); border: 1px solid var(--accent); border-radius: 14px; max-width: 720px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.8); }
    .timeline-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .timeline-header h3 { font-size: 1.2rem; font-weight: 700; color: var(--accent); }
    .timeline-body { padding: 1.5rem; overflow-y: auto; }
    .close-btn { background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; line-height: 1; }

    /* Timeline items */
    .timeline-list { position: relative; padding-left: 28px; border-left: 2px solid rgba(232,117,22,0.3); margin-left: 10px; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-dot { position: absolute; left: -35px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: var(--accent); border: 2px solid #000; }
    .timeline-dot.step { background: #22c55e; box-shadow: 0 0 10px rgba(34,197,94,0.5); }
    .timeline-time { font-family: monospace; font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.2rem; }
    .timeline-desc { font-size: 0.92rem; font-weight: 600; color: #f8fafc; }
    .timeline-sub { font-size: 0.8rem; color: #94a3b8; margin-top: 0.2rem; }

    /* Toast notification */
    #toast { position: fixed; bottom: 24px; right: 24px; background: #22c55e; color: #000; padding: 0.8rem 1.4rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(0,0,0,0.5); z-index: 2000; display: none; align-items: center; gap: 0.5rem; }

    /* Visitor checkboxes & bulk toolbar */
    .vis-checkbox { width: 17px; height: 17px; cursor: pointer; accent-color: var(--accent); }
    #vis-bulk-bar { display: none; align-items: center; gap: 1rem; margin-bottom: 1rem; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); border-radius: 8px; padding: 0.7rem 1.2rem; }
    #vis-bulk-bar.show { display: flex; }
    #vis-bulk-count { font-weight: 700; color: #f87171; font-size: 0.95rem; }
    .btn-danger { background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid rgba(239,68,68,0.4); }
    .btn-danger:hover { background: #ef4444; color: #fff; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <div class="nav-links">
        <a href="../admin.html" class="nav-link"><i class="bi bi-arrow-left"></i> Rozcestník</a>
        <span style="color:rgba(255,255,255,0.2);">|</span>
        <a href="landing_leads.php" class="nav-link"><i class="bi bi-envelope-paper"></i> Zprávy a kontakty</a>
        <span style="color:rgba(255,255,255,0.2);">|</span>
        <a href="landing_pages.php" class="nav-link"><i class="bi bi-file-earmark-code"></i> Správa Landing Pages</a>
      </div>
      <div>
        <span class="badge" style="background:rgba(232,117,22,0.15); color:var(--accent); border:1px solid var(--accent); padding:0.4rem 0.8rem; font-size:0.85rem;">
          <i class="bi bi-shield-check"></i> Interní přesné měření bez AdBlocku
        </span>
      </div>
    </div>

    <h1>Analytika a Generátor odkazů</h1>
    <p class="subtitle">Sledování návštěvnosti, chování zájemců v sekcích, kliknutí na tlačítka a tvorba kampaňových odkazů pro Facebook a sociální sítě.</p>

    <!-- TABS NAVIGATION -->
    <div class="tabs-nav">
      <button class="tab-btn <?= ($initialTab === 'generator') ? 'active' : '' ?>" onclick="switchTab('generator')">
        <i class="bi bi-link-45deg"></i> 1. Generátor odkazů (kampaně)
      </button>
      <button class="tab-btn <?= ($initialTab === 'visitors') ? 'active' : '' ?>" onclick="switchTab('visitors')">
        <i class="bi bi-people"></i> 2. Přehledy návštěvníků & časové osy
      </button>
      <button class="tab-btn <?= ($initialTab === 'funnel') ? 'active' : '' ?>" onclick="switchTab('funnel')">
        <i class="bi bi-bar-chart-steps"></i> 3. Statistiky & Konverzní trychtýř
      </button>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: GENERÁTOR ODKAZŮ                                          -->
    <!-- ================================================================= -->
    <div id="tab-generator" class="tab-pane <?= ($initialTab === 'generator') ? 'active' : '' ?>">
      <div class="card">
        <h2><i class="bi bi-magic"></i> Rychlé vytvoření kampaňového odkazu</h2>
        <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:1.5rem;">
          Když vkládáte odkaz na Facebook (do skupin, komentářů, příspěvků) nebo na Instagram, označte si ho štítkem.
          Systém pak přesně pozná, odkud zájemce přišel a kolik lidí z daného místa skutečně odeslalo formulář.
        </p>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem;">
          <div class="form-group">
            <label>1. Vyberte Landing page (mistra)</label>
            <select id="gen_slug" class="form-control" onchange="updateGeneratedLink()">
              <?php foreach ($availableLandingPages as $lp): ?>
                <option value="<?= htmlspecialchars($lp['slug']) ?>" <?= ($selectedSlug === $lp['slug']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($lp['master_name'] ?: $lp['slug']) ?> (<?= htmlspecialchars($lp['slug']) ?>)
                </option>
              <?php endforeach; ?>
              <?php if (empty($availableLandingPages)): ?>
                <option value="jiri-pacinek">Jiří Pačinek (jiri-pacinek)</option>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group">
            <label>2. Kanál / Šablona použití</label>
            <div class="presets-box">
              <span class="preset-pill active" onclick="setChannel('facebook', 'fb-')"><i class="bi bi-facebook"></i> FB Skupina</span>
              <span class="preset-pill" onclick="setChannel('facebook_profile', 'fb-profil')"><i class="bi bi-person-badge"></i> FB Profil / Zeď</span>
              <span class="preset-pill" onclick="setChannel('instagram', 'ig-bio')"><i class="bi bi-instagram"></i> Instagram</span>
              <span class="preset-pill" onclick="setChannel('whatsapp', 'wa-zprava')"><i class="bi bi-whatsapp"></i> WhatsApp</span>
              <span class="preset-pill" onclick="setChannel('qr_code', 'plakat-')"><i class="bi bi-qr-code"></i> Leták / Plakát</span>
              <span class="preset-pill" onclick="setChannel('other', '')"><i class="bi bi-tag"></i> Vlastní štítek</span>
            </div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-top:0.5rem;">
          <div class="form-group">
            <label>3. Název zdroje (tag v odkazu)</label>
            <input type="text" id="gen_tag" class="form-control" placeholder="např. fb-skupina-sklo nebo fb-liberecko" oninput="updateGeneratedLink()" />
            <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">Doporučujeme malá písmena bez mezer a diakritiky (např. fb-turnovsko, ig-bio, letak-skola).</small>
          </div>
          <div class="form-group">
            <label>Popis / Poznámka k odkazu (pro vás v adminu)</label>
            <input type="text" id="gen_label" class="form-control" placeholder="např. FB skupina Sklářství a řemesla ČR" />
          </div>
        </div>

        <div class="link-result-box">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <strong style="color:#60a5fa; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.5px;">
              <i class="bi bi-check2-circle"></i> Váš vygenerovaný odkaz k vložení:
            </strong>
            <button type="button" class="btn btn-primary btn-sm" onclick="copyGeneratedLink()">
              <i class="bi bi-clipboard-check"></i> Kopírovat do schránky
            </button>
          </div>
          <div id="gen_result_url" class="link-url-display">https://svobodnecechy.cz/...</div>
          <div style="text-align:right; margin-top:0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="saveGeneratedLink()">
              <i class="bi bi-bookmark-plus"></i> Uložit tento odkaz do seznamu
            </button>
          </div>
        </div>
      </div>

      <!-- SEZNAM ULOŽENÝCH ODKAZŮ SE STATISTIKAMI -->
      <div class="card">
        <h2><i class="bi bi-list-stars"></i> Seznam vytvořených odkazů a jejich výsledky</h2>
        <div class="table-responsive">
          <table id="links-table">
            <thead>
              <tr>
                <th>Štítek / Zdroj</th>
                <th>Kanál</th>
                <th>Cílový odkaz</th>
                <th style="text-align:center;">Návštěv</th>
                <th style="text-align:center;">Klik na CTA</th>
                <th style="text-align:center;">Leady</th>
                <th style="text-align:center;">Konverze</th>
                <th style="text-align:right;">Akce</th>
              </tr>
            </thead>
            <tbody id="links-tbody">
              <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Načítám uložené odkazy...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: PŘEHLED NÁVŠTĚVNÍKŮ (VISITOR LOG)                          -->
    <!-- ================================================================= -->
    <div id="tab-visitors" class="tab-pane <?= ($initialTab === 'visitors') ? 'active' : '' ?>">
      <div class="card">
        <h2><i class="bi bi-person-lines-fill"></i> Návštěvníci a jejich časové osy</h2>
        <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:1.5rem;">
          Zde vidíte každého jednotlivého návštěvníka stránky. Kliknutím na řádek můžete rozbalit podrobnou časovou osu jeho kroků (jaké sekce prošel, které tlačítko stiskl a kam až došel ve formuláři).
        </p>

        <!-- FILTERS -->
        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center; margin-bottom:1.5rem; background:rgba(0,0,0,0.3); padding:1rem; border-radius:8px; border:1px solid var(--border);">
          <div>
            <select id="vis_slug" class="form-control" style="width:auto; min-width:180px;" onchange="loadVisitors()">
              <option value="">Všechny Landing Pages</option>
              <?php foreach ($availableLandingPages as $lp): ?>
                <option value="<?= htmlspecialchars($lp['slug']) ?>" <?= ($selectedSlug === $lp['slug']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($lp['master_name'] ?: $lp['slug']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <select id="vis_filter" class="form-control" style="width:auto; min-width:180px;" onchange="loadVisitors()">
              <option value="all">Všichni návštěvníci</option>
              <option value="cta_clicked">Stiskli tlačítko (CTA)</option>
              <option value="leads">Odeslali kontakty (Leady)</option>
              <option value="abandoned_step1">Opuštěný Krok 1 (pouze e-mail)</option>
            </select>
          </div>
          <div style="flex-grow:1;">
            <input type="text" id="vis_search" class="form-control" placeholder="Hledat podle zdroje, kódu příspěvku (KAL-...), e-mailu, jména..." oninput="debounce(loadVisitors, 300)()" />
          </div>
          <button class="btn btn-secondary btn-sm" onclick="loadVisitors()"><i class="bi bi-arrow-clockwise"></i> Obnovit</button>
        </div>

        <!-- BULK DELETE TOOLBAR -->
        <div id="vis-bulk-bar">
          <i class="bi bi-trash3" style="color:#ef4444; font-size:1.1rem;"></i>
          <span id="vis-bulk-count">0 vybráno</span>
          <button class="btn btn-danger btn-sm" onclick="bulkDeleteVisitors()">
            <i class="bi bi-trash-fill"></i> Smazat vybrané
          </button>
          <button class="btn btn-secondary btn-sm" onclick="clearVisitorSelection()">
            <i class="bi bi-x"></i> Zrušit výběr
          </button>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th style="width:38px; text-align:center;">
                  <input type="checkbox" class="vis-checkbox" id="vis-check-all" title="Vybrat vše" onchange="toggleAllVisitors(this)">
                </th>
                <th>Čas návštěvy</th>
                <th>Zdroj (Kampaň)</th>
                <th>Zařízení</th>
                <th>Dosažená sekce</th>
                <th>Stisknuté tlačítko</th>
                <th>Stav formuláře</th>
                <th>Doba</th>
                <th style="text-align:right;">Akce</th>
              </tr>
            </thead>
            <tbody id="visitors-tbody">
              <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-muted);">Načítám návštěvníky...</td></tr>
            </tbody>
          </table>
        </div>

        <div id="vis_pagination" style="display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; font-size:0.9rem; color:var(--text-muted);">
          <div id="vis_count_info">Zobrazeno 0 návštěv</div>
          <div style="display:flex; gap:0.5rem;">
            <button id="btn_prev" class="btn btn-secondary btn-sm" onclick="changeVisitorPage(-1)" disabled>Předchozí</button>
            <button id="btn_next" class="btn btn-secondary btn-sm" onclick="changeVisitorPage(1)" disabled>Další</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: STATISTIKY & KONVERZNÍ TRYCHTÝŘ                            -->
    <!-- ================================================================= -->
    <div id="tab-funnel" class="tab-pane <?= ($initialTab === 'funnel') ? 'active' : '' ?>">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; gap:1rem; align-items:center;">
          <select id="stat_slug" class="form-control" style="width:auto; min-width:200px;" onchange="loadStats()">
            <option value="">Všechny Landing Pages</option>
            <?php foreach ($availableLandingPages as $lp): ?>
              <option value="<?= htmlspecialchars($lp['slug']) ?>" <?= ($selectedSlug === $lp['slug']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($lp['master_name'] ?: $lp['slug']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <select id="stat_days" class="form-control" style="width:auto;" onchange="loadStats()">
            <option value="7">Posledních 7 dní</option>
            <option value="30" selected>Posledních 30 dní</option>
            <option value="90">Posledních 90 dní</option>
            <option value="365">Poslední rok</option>
          </select>
        </div>
        <button class="btn btn-secondary btn-sm" onclick="loadStats()"><i class="bi bi-arrow-clockwise"></i> Aktualizovat data</button>
      </div>

      <!-- OVERVIEW METRICS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-val" id="stat_total_vis">-</div>
          <div class="stat-lbl">Návštěvníků celkem</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" id="stat_mobile_pct">-</div>
          <div class="stat-lbl">Mobilní zařízení</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" id="stat_cta_clicks">-</div>
          <div class="stat-lbl">Kliknutí na tlačítka (CTA)</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" id="stat_leads_count">-</div>
          <div class="stat-lbl">Získané kontakty (Leady)</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" id="stat_avg_time">-</div>
          <div class="stat-lbl">Průměrný čas na webu</div>
        </div>
      </div>

      <!-- FUNNEL VISUAL -->
      <div class="card">
        <h2><i class="bi bi-funnel"></i> Konverzní trychtýř (Krok za krokem)</h2>
        <p style="color:var(--text-muted); font-size:0.92rem; margin-bottom:1.5rem;">
          Sledujte, v jakém bodě zájemci nejčastěji odpadávají a kolik procent postoupí až k dokončení formuláře či na WhatsApp.
        </p>

        <div class="funnel-container" id="funnel-steps-box">
          <!-- Populated dynamically via JS -->
        </div>
      </div>

      <!-- SOURCES & SECTIONS BREAKDOWN -->
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap:2rem;">
        <!-- Sources -->
        <div class="card">
          <h2><i class="bi bi-share"></i> Výkonnost zdrojů (FB skupiny a kampaně)</h2>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Zdroj</th>
                  <th style="text-align:center;">Návštěv</th>
                  <th style="text-align:center;">Klik CTA</th>
                  <th style="text-align:center;">Leady</th>
                  <th style="text-align:right;">Konverze</th>
                </tr>
              </thead>
              <tbody id="stat-sources-tbody">
                <tr><td colspan="5" style="text-align:center; padding:1.5rem; color:var(--text-muted);">Načítám zdroje...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Buttons & Sections -->
        <div class="card">
          <h2><i class="bi bi-cursor"></i> Nejúspěšnější tlačítka a sekce</h2>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Tlačítko</th>
                  <th>Sekce</th>
                  <th style="text-align:right;">Počet kliknutí</th>
                </tr>
              </thead>
              <tbody id="stat-buttons-tbody">
                <tr><td colspan="3" style="text-align:center; padding:1.5rem; color:var(--text-muted);">Načítám tlačítka...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: VISITOR TIMELINE -->
  <div id="timelineModal" class="timeline-modal" onclick="if(event.target === this) closeTimelineModal()">
    <div class="timeline-box">
      <div class="timeline-header">
        <h3 id="tml_title"><i class="bi bi-clock-history"></i> Časová osa návštěvníka</h3>
        <button class="close-btn" onclick="closeTimelineModal()">&times;</button>
      </div>
      <div class="timeline-body">
        <div id="tml_visitor_meta" style="background:#0f172a; border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:1rem; margin-bottom:1.5rem; font-size:0.88rem; color:#cbd5e1;">
          <!-- Meta details -->
        </div>
        <div class="timeline-list" id="tml_events_list">
          <!-- Events list -->
        </div>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div id="toast"><i class="bi bi-check-circle-fill"></i> Odkaz byl zkopírován do schránky!</div>

  <script>
    let activeChannelPrefix = 'fb-';
    let visitorPage = 1;
    let initialParamSession = '<?= htmlspecialchars($initialSession) ?>';

    function switchTab(tabId) {
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

      const targetBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick')?.includes(tabId));
      if (targetBtn) targetBtn.classList.add('active');

      const targetPane = document.getElementById('tab-' + tabId);
      if (targetPane) targetPane.classList.add('active');

      if (tabId === 'generator') loadLinks();
      if (tabId === 'visitors') loadVisitors();
      if (tabId === 'funnel') loadStats();
    }

    /* ---- LINK GENERATOR LOGIC ---- */
    function setChannel(ch, prefix) {
      document.querySelectorAll('.preset-pill').forEach(p => p.classList.remove('active'));
      event.currentTarget.classList.add('active');
      activeChannelPrefix = prefix;

      const tagInput = document.getElementById('gen_tag');
      const current = tagInput.value.replace(/^(fb-|ig-|wa-|plakat-)/, '');
      tagInput.value = prefix + current;
      updateGeneratedLink();
    }

    function updateGeneratedLink() {
      const slug = document.getElementById('gen_slug').value;
      const tag = document.getElementById('gen_tag').value.trim() || 'facebook';
      const cleanTag = tag.toLowerCase().replace(/[^a-z0-9_-]/g, '');

      const baseUrl = window.location.origin;
      // The landing page files are in /admin/landing_pages/<slug>.html or /landing_pages/<slug>.html
      const fullUrl = baseUrl + '/admin/landing_pages/' + slug + '.html?zdroj=' + encodeURIComponent(cleanTag);
      document.getElementById('gen_result_url').innerText = fullUrl;
    }

    function copyGeneratedLink() {
      const urlText = document.getElementById('gen_result_url').innerText;
      navigator.clipboard.writeText(urlText).then(() => {
        showToast('Odkaz byl zkopírován do schránky!');
      });
    }

    function showToast(msg) {
      const toast = document.getElementById('toast');
      toast.innerText = msg;
      toast.style.display = 'inline-flex';
      setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }

    function saveGeneratedLink() {
      const slug = document.getElementById('gen_slug').value;
      const tag = document.getElementById('gen_tag').value.trim() || 'facebook';
      const label = document.getElementById('gen_label').value.trim() || tag;
      const fullUrl = document.getElementById('gen_result_url').innerText;

      fetch('api_landing_leads.php?action=save_link', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          landing_slug: slug,
          source_tag: tag,
          label: label,
          full_url: fullUrl
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast('✅ Odkaz byl uložen do seznamu.');
          loadLinks();
        } else {
          alert(data.message || 'Chyba při ukládání');
        }
      });
    }

    function loadLinks() {
      const slug = document.getElementById('gen_slug').value;
      fetch('api_landing_leads.php?action=get_links&slug=' + encodeURIComponent(slug))
      .then(res => res.json())
      .then(data => {
        const tbody = document.getElementById('links-tbody');
        if (!data.links || data.links.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Zatím nemáte vytvořené žádné uložené odkazy pro tuto landing page. Vytvořte první výše!</td></tr>';
          return;
        }

        let html = '';
        data.links.forEach(l => {
          const visits = parseInt(l.visits_count) || 0;
          const cta = parseInt(l.cta_clicks_count) || 0;
          const leads = parseInt(l.leads_count) || 0;
          const cr = visits > 0 ? ((leads / visits) * 100).toFixed(1) + ' %' : '0.0 %';

          html += `<tr>
            <td>
              <strong style="color:#f59e0b;">${escapeHtml(l.label)}</strong>
              <div style="font-family:monospace; font-size:0.75rem; color:#94a3b8;">${escapeHtml(l.source_tag)}</div>
            </td>
            <td><span class="badge badge-device">${escapeHtml(l.channel || 'other')}</span></td>
            <td>
              <div style="display:flex; align-items:center; gap:0.4rem;">
                <span style="font-family:monospace; font-size:0.78rem; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(l.full_url)}</span>
                <button class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText('${escapeHtml(l.full_url)}'); showToast('Odkaz zkopírován!');"><i class="bi bi-clipboard"></i></button>
              </div>
            </td>
            <td style="text-align:center; font-weight:700;">${visits}</td>
            <td style="text-align:center; color:#38bdf8; font-weight:700;">${cta}</td>
            <td style="text-align:center; color:#22c55e; font-weight:700;">${leads}</td>
            <td style="text-align:center;"><span class="badge" style="background:rgba(34,197,94,0.15); color:#22c55e;">${cr}</span></td>
            <td style="text-align:right;">
              <button class="btn btn-secondary btn-sm" style="color:#ef4444;" onclick="deleteLink(${l.id})"><i class="bi bi-trash"></i></button>
            </td>
          </tr>`;
        });
        tbody.innerHTML = html;
      });
    }

    function deleteLink(id) {
      if (!confirm('Opravdu chcete tento odkaz smazat ze seznamu?')) return;
      fetch('api_landing_leads.php?action=delete_link&id=' + id)
      .then(res => res.json())
      .then(data => {
        loadLinks();
      });
    }

    /* ---- VISITORS & TIMELINE LOGIC ---- */
    function loadVisitors() {
      const slug = document.getElementById('vis_slug').value;
      const filter = document.getElementById('vis_filter').value;
      const search = document.getElementById('vis_search').value.trim();

      const url = `api_landing_leads.php?action=get_visitors&slug=${encodeURIComponent(slug)}&filter=${encodeURIComponent(filter)}&search=${encodeURIComponent(search)}&page=${visitorPage}`;
      fetch(url)
      .then(res => res.json())
      .then(data => {
        const tbody = document.getElementById('visitors-tbody');
        if (!data.visitors || data.visitors.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2.5rem; color:var(--text-muted);">Žádní návštěvníci neodpovídají zadanému filtru.</td></tr>';
          document.getElementById('vis_count_info').innerText = '0 návštěv';
          clearVisitorSelection();
          return;
        }

        document.getElementById('vis_count_info').innerText = `Zobrazeno ${data.visitors.length} z celkem ${data.total} návštěv`;
        document.getElementById('btn_prev').disabled = (data.page <= 1);
        document.getElementById('btn_next').disabled = (data.page * data.limit >= data.total);
        clearVisitorSelection();

        let html = '';
        data.visitors.forEach(v => {
          const dateStr = formatDateTime(v.created_at);
          const durSec = parseInt(v.duration_seconds) || 0;
          const durStr = durSec >= 60 ? Math.floor(durSec/60) + 'm ' + (durSec%60) + 's' : durSec + 's';

          let statusBadge = '<span class="badge badge-none">Prohlížel</span>';
          if (v.form_status === 'step3_web_clicked') statusBadge = '<span class="badge badge-web">🌐 Proklik na web dílny</span>';
          else if (v.form_status === 'whatsapp_sent') statusBadge = '<span class="badge badge-wa">💬 WhatsApp odeslán</span>';
          else if (v.form_status === 'step2_completed' || v.form_status === 'step3_viewed') statusBadge = '<span class="badge badge-step2">✅ Formulář dokončen</span>';
          else if (v.form_status === 'step1_email') statusBadge = '<span class="badge badge-step1">⚠️ Pouze e-mail (Krok 1)</span>';

          const devIcon = v.device_type === 'mobile' ? '<i class="bi bi-phone"></i> Mobil' : (v.device_type === 'tablet' ? '<i class="bi bi-tablet"></i> Tablet' : '<i class="bi bi-laptop"></i> PC');
          const safeSession = escapeHtml(v.session_id);

          html += `<tr>
            <td style="text-align:center;">
              <input type="checkbox" class="vis-checkbox vis-row-check" data-session="${safeSession}" onchange="updateVisitorSelection()">
            </td>
            <td style="color:#cbd5e1; font-size:0.82rem; font-family:monospace;">${dateStr}</td>
            <td>
              <div style="display:flex; flex-direction:column; gap:0.4rem; align-items:flex-start;">
                <span class="badge badge-source">${escapeHtml(v.source || 'direct')}</span>
                
                ${(v.utm_content || (v.post_details && v.post_details.post_code)) ? `
                  <div style="display:inline-flex; align-items:center; gap:0.35rem; background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.4); border-radius:6px; padding:0.25rem 0.55rem; font-family:monospace; font-size:0.78rem; color:#d8b4fe;">
                    <i class="bi bi-calendar-event" style="color:#a855f7;"></i>
                    <strong>${escapeHtml(v.utm_content || v.post_details.post_code)}</strong>
                    <button type="button" onclick="navigator.clipboard.writeText('${escapeHtml(v.utm_content || v.post_details.post_code)}'); showToast('Kód zkopírován!'); event.stopPropagation();" title="Kopírovat kód" style="background:none; border:none; color:#c4b5fd; cursor:pointer; padding:0 0.2rem; font-size:0.85rem; display:inline-flex; align-items:center;">
                      <i class="bi bi-clipboard"></i>
                    </button>
                  </div>
                ` : ''}

                ${v.post_details ? `
                  <div style="font-size:0.75rem; color:#cbd5e1; background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:6px; padding:0.35rem 0.6rem; line-height:1.45; max-width:320px;">
                    <div><i class="bi bi-people-fill" style="color:#60a5fa;"></i> <strong>Skupina:</strong> ${escapeHtml(v.post_details.group_name)}</div>
                    <div><i class="bi bi-clock-fill" style="color:#eab308;"></i> <strong>Plánováno:</strong> ${formatDateTime(v.post_details.scheduled_at)}</div>
                    ${v.post_details.template_title ? `<div><i class="bi bi-file-earmark-text-fill" style="color:#34d399;"></i> <strong>Šablona:</strong> ${escapeHtml(v.post_details.template_title)}</div>` : ''}
                  </div>
                ` : ''}
              </div>
            </td>
            <td><span class="badge badge-device">${devIcon}</span></td>
            <td><strong>${escapeHtml(v.max_section || '-')}</strong></td>
            <td>
              ${v.clicked_button ? `<span style="color:#38bdf8; font-weight:600;">${escapeHtml(v.clicked_button)}</span><div style="font-size:0.75rem; color:#94a3b8;">sekce: ${escapeHtml(v.clicked_section || '-')}</div>` : '<span style="color:#64748b;">-</span>'}
            </td>
            <td>${statusBadge}</td>
            <td style="font-size:0.85rem; color:#cbd5e1;">${durStr}</td>
            <td style="text-align:right; white-space:nowrap;">
              <button class="btn btn-secondary btn-sm" onclick="openTimelineModal('${safeSession}')" style="margin-right:0.3rem;">
                <i class="bi bi-clock-history"></i> Osa
              </button>
              <button class="btn btn-danger btn-sm" onclick="deleteVisitor('${safeSession}')" title="Smazat tohoto návštěvníka">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>`;
        });
        tbody.innerHTML = html;
      });
    }

    function changeVisitorPage(delta) {
      visitorPage += delta;
      if (visitorPage < 1) visitorPage = 1;
      loadVisitors();
    }

    /* ---- VISITOR DELETE (single & bulk) ---- */
    function deleteVisitor(sessionId) {
      if (!confirm('Opravdu chcete smazat tohoto návštěvníka a jeho celou časovou osu?')) return;
      fetch(`api_landing_leads.php?action=delete_visitor&session_id=${encodeURIComponent(sessionId)}`)
        .then(r => r.json())
        .then(d => {
          if (d.success) { showToast('Návštěvník byl smazán.'); loadVisitors(); }
          else alert(d.message || 'Chyba při mazání');
        });
    }

    function bulkDeleteVisitors() {
      const checked = [...document.querySelectorAll('.vis-row-check:checked')];
      if (!checked.length) return;
      if (!confirm(`Opravdu smazat ${checked.length} vybraných návštěvníků a jejich časové osy?`)) return;
      const ids = checked.map(cb => cb.dataset.session);
      fetch('api_landing_leads.php?action=bulk_delete_visitors', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_ids: ids })
      })
        .then(r => r.json())
        .then(d => {
          if (d.success) { showToast(`Smazáno ${d.deleted} návštěvníků.`); loadVisitors(); }
          else alert(d.message || 'Chyba při hromadném mazání');
        });
    }

    function toggleAllVisitors(masterCb) {
      document.querySelectorAll('.vis-row-check').forEach(cb => cb.checked = masterCb.checked);
      updateVisitorSelection();
    }

    function updateVisitorSelection() {
      const checked = document.querySelectorAll('.vis-row-check:checked').length;
      const all = document.querySelectorAll('.vis-row-check').length;
      const bar = document.getElementById('vis-bulk-bar');
      document.getElementById('vis-bulk-count').innerText = `${checked} vybráno`;
      bar.classList.toggle('show', checked > 0);
      const masterCb = document.getElementById('vis-check-all');
      if (masterCb) { masterCb.checked = checked === all && all > 0; masterCb.indeterminate = checked > 0 && checked < all; }
    }

    function clearVisitorSelection() {
      document.querySelectorAll('.vis-row-check').forEach(cb => cb.checked = false);
      const masterCb = document.getElementById('vis-check-all');
      if (masterCb) { masterCb.checked = false; masterCb.indeterminate = false; }
      document.getElementById('vis-bulk-bar').classList.remove('show');
      document.getElementById('vis-bulk-count').innerText = '0 vybráno';
    }

    function openTimelineModal(sessionId) {
      fetch('api_landing_leads.php?action=get_timeline&session_id=' + encodeURIComponent(sessionId))
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert('Nepodařilo se načíst data návštěvníka');
          return;
        }

        const s = data.session || {};
        const metaEl = document.getElementById('tml_visitor_meta');
        const durSec = parseInt(s.duration_seconds) || 0;
        const durStr = durSec >= 60 ? Math.floor(durSec/60) + ' min ' + (durSec%60) + ' s' : durSec + ' s';

        let contactBox = '';
        if (s.email) {
          contactBox = `<div style="margin-top:0.8rem; padding-top:0.8rem; border-top:1px solid rgba(255,255,255,0.1); color:#fff;">
            <strong>Kontaktní údaje:</strong> ${escapeHtml(s.name || 'Jméno nezadáno')} | 
            <a href="mailto:${escapeHtml(s.email)}" style="color:#60a5fa;">${escapeHtml(s.email)}</a> | 
            ${s.phone ? `<a href="tel:${escapeHtml(s.phone)}" style="color:#22c55e;">${escapeHtml(s.phone)}</a>` : 'Telefon nezadán'}
            ${s.message ? `<div style="margin-top:0.3rem; font-style:italic; color:#cbd5e1;">"${escapeHtml(s.message)}"</div>` : ''}
          </div>`;
        }

        metaEl.innerHTML = `
          <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
            <div><strong>Zdroj:</strong> <span class="badge badge-source">${escapeHtml(s.source || 'direct')}</span></div>
            ${(s.utm_content || (s.post_details && s.post_details.post_code)) ? `
              <div><strong>Kód příspěvku:</strong> <span class="badge" style="background:rgba(139,92,246,0.2); color:#d8b4fe; border:1px solid rgba(139,92,246,0.4);"><i class="bi bi-tag-fill"></i> ${escapeHtml(s.utm_content || s.post_details.post_code)}</span></div>
            ` : ''}
            <div><strong>Zařízení:</strong> ${escapeHtml(s.device_type || 'desktop')}</div>
            <div><strong>Čas na webu:</strong> ${durStr}</div>
            <div><strong>Kampaň:</strong> ${escapeHtml(s.landing_slug || '')}</div>
          </div>
          ${s.post_details ? `
            <div style="margin-top:0.75rem; padding:0.6rem 0.85rem; background:rgba(139,92,246,0.1); border:1px solid rgba(139,92,246,0.3); border-radius:6px; font-size:0.82rem; color:#cbd5e1; line-height:1.5;">
              <div style="font-weight:700; color:#d8b4fe; margin-bottom:0.25rem;"><i class="bi bi-info-circle-fill"></i> Podrobnosti z Facebook kalendáře:</div>
              <div>👥 <strong>Skupina:</strong> ${escapeHtml(s.post_details.group_name)}</div>
              <div>📅 <strong>Plánovaný čas:</strong> ${formatDateTime(s.post_details.scheduled_at)}</div>
              ${s.post_details.template_title ? `<div>📝 <strong>Šablona:</strong> ${escapeHtml(s.post_details.template_title)}</div>` : ''}
            </div>
          ` : ''}
          ${contactBox}
        `;

        const listEl = document.getElementById('tml_events_list');
        if (!data.events || data.events.length === 0) {
          listEl.innerHTML = '<div style="color:var(--text-muted); font-size:0.9rem;">Žádné zaznamenané události pro tuto relaci.</div>';
        } else {
          let eHtml = '';
          data.events.forEach(ev => {
            const time = formatDateTime(ev.created_at);
            const isHighlight = ev.event_type.startsWith('form_') || ev.event_type === 'step3_web_click';
            
            let label = escapeHtml(ev.event_label || '');
            let desc = label;
            let sub = '';

            if (ev.event_type === 'page_view') {
              desc = `Příchod na stránku`;
              sub = `Zdroj: ${escapeHtml(s.source || 'direct')}`;
            } else if (ev.event_type === 'scroll_section') {
              desc = `Doscrolloval do sekce: [${escapeHtml(ev.event_section || '')}]`;
              sub = label !== ev.event_section ? label : '';
            } else if (ev.event_type === 'btn_click') {
              desc = `Kliknul na tlačítko: "${label}"`;
              sub = `Umístění tlačítka: sekce [${escapeHtml(ev.event_section || '')}]`;
            } else if (ev.event_type === 'modal_open') {
              desc = `Otevřel kontaktní formulář`;
              sub = `Vyvoláno ze sekce: ${escapeHtml(ev.event_section || '')}`;
            } else if (ev.event_type === 'form_step1') {
              desc = `✅ Dokončil Krok 1 (zadal e-mail: ${label})`;
            } else if (ev.event_type === 'form_step2') {
              desc = `✅ Dokončil Krok 2 (kontaktní údaje: ${label})`;
            } else if (ev.event_type === 'form_whatsapp') {
              desc = `💬 Kliknul na WhatsApp a odeslal údaje`;
            } else if (ev.event_type === 'form_step3') {
              desc = `🎉 Zobrazena děkovná obrazovka (Fáze 3)`;
            } else if (ev.event_type === 'step3_web_click') {
              desc = `🌐 Kliknul na web dílny v poděkování: ${label}`;
            }

            eHtml += `
              <div class="timeline-item">
                <div class="timeline-dot ${isHighlight ? 'step' : ''}"></div>
                <div class="timeline-time">${time}</div>
                <div class="timeline-desc" ${isHighlight ? 'style="color:#38bdf8;"' : ''}>${desc}</div>
                ${sub ? `<div class="timeline-sub">${sub}</div>` : ''}
              </div>
            `;
          });
          listEl.innerHTML = eHtml;
        }

        document.getElementById('timelineModal').style.display = 'flex';
      });
    }

    function closeTimelineModal() {
      document.getElementById('timelineModal').style.display = 'none';
    }

    /* ---- STATS & FUNNEL LOGIC ---- */
    function loadStats() {
      const slug = document.getElementById('stat_slug').value;
      const days = document.getElementById('stat_days').value;

      fetch(`api_landing_leads.php?action=get_stats&slug=${encodeURIComponent(slug)}&days=${days}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        const ov = data.overview || {};
        const total = parseInt(ov.total_sessions) || 0;
        const mobile = parseInt(ov.mobile_sessions) || 0;
        const cta = parseInt(ov.cta_clicked_count) || 0;
        const leads = parseInt(ov.step2_count) || 0;
        const avgSec = Math.round(parseFloat(ov.avg_duration) || 0);

        document.getElementById('stat_total_vis').innerText = total;
        document.getElementById('stat_mobile_pct').innerText = total > 0 ? Math.round((mobile/total)*100) + ' %' : '0 %';
        document.getElementById('stat_cta_clicks').innerText = cta;
        document.getElementById('stat_leads_count').innerText = leads;
        document.getElementById('stat_avg_time').innerText = avgSec >= 60 ? Math.floor(avgSec/60) + 'm ' + (avgSec%60) + 's' : avgSec + 's';

        // Render Funnel
        const funnelBox = document.getElementById('funnel-steps-box');
        const step1 = parseInt(ov.step1_count) || 0;
        const wa = parseInt(ov.whatsapp_count) || 0;
        const webClick = parseInt(ov.web_click_count) || 0;

        const steps = [
          { label: '1. Příchod na stránku (Zobrazení)', count: total, pct: 100 },
          { label: '2. Kliknutí na CTA tlačítko', count: cta, pct: total > 0 ? Math.round((cta/total)*100) : 0 },
          { label: '3. Fáze 1: Zadal e-mail', count: step1, pct: total > 0 ? Math.round((step1/total)*100) : 0 },
          { label: '4. Fáze 2: Kompletní kontakt (Jméno, telefon)', count: leads, pct: total > 0 ? Math.round((leads/total)*100) : 0 },
          { label: '5. Proklik na WhatsApp', count: wa, pct: total > 0 ? Math.round((wa/total)*100) : 0 },
          { label: '6. Fáze 3: Kliknutí na web dílny v poděkování', count: webClick, pct: total > 0 ? Math.round((webClick/total)*100) : 0 }
        ];

        let fHtml = '';
        steps.forEach(s => {
          fHtml += `
            <div class="funnel-step">
              <div class="funnel-bar" style="width:${s.pct}%;"></div>
              <div class="funnel-content">
                <div class="funnel-label">${s.label}</div>
                <div class="funnel-stats">
                  <div class="funnel-count">${s.count}</div>
                  <div class="funnel-pct">${s.pct} %</div>
                </div>
              </div>
            </div>
          `;
        });
        funnelBox.innerHTML = fHtml;

        // Render Sources Table
        const srcTbody = document.getElementById('stat-sources-tbody');
        if (!data.sources || data.sources.length === 0) {
          srcTbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:1.5rem; color:var(--text-muted);">Zatím žádná data o zdrojích.</td></tr>';
        } else {
          let sHtml = '';
          data.sources.forEach(src => {
            const v = parseInt(src.visits) || 0;
            const c = parseInt(src.cta_clicks) || 0;
            const l = parseInt(src.leads_count) || 0;
            const cr = v > 0 ? ((l / v) * 100).toFixed(1) + ' %' : '0.0 %';
            sHtml += `<tr>
              <td><span class="badge badge-source">${escapeHtml(src.source || 'direct')}</span></td>
              <td style="text-align:center; font-weight:700;">${v}</td>
              <td style="text-align:center; color:#38bdf8;">${c}</td>
              <td style="text-align:center; color:#22c55e; font-weight:700;">${l}</td>
              <td style="text-align:right;"><span class="badge" style="background:rgba(34,197,94,0.15); color:#22c55e;">${cr}</span></td>
            </tr>`;
          });
          srcTbody.innerHTML = sHtml;
        }

        // Render Buttons Table
        const btnTbody = document.getElementById('stat-buttons-tbody');
        if (!data.buttons || data.buttons.length === 0) {
          btnTbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:1.5rem; color:var(--text-muted);">Zatím žádná kliknutí na tlačítka.</td></tr>';
        } else {
          let bHtml = '';
          data.buttons.forEach(b => {
            bHtml += `<tr>
              <td><strong style="color:#f8fafc;">${escapeHtml(b.button_name || '')}</strong></td>
              <td><span class="badge badge-device">${escapeHtml(b.section_name || 'hero')}</span></td>
              <td style="text-align:right; font-weight:700; color:#38bdf8;">${b.clicks}×</td>
            </tr>`;
          });
          btnTbody.innerHTML = bHtml;
        }
      });
    }

    // Utility helpers
    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatDateTime(dt) {
      if (!dt) return '-';
      const d = new Date(dt.replace(' ', 'T'));
      if (isNaN(d.getTime())) return dt;
      return d.toLocaleDateString('cs-CZ') + ' ' + d.toLocaleTimeString('cs-CZ', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // Initial run
    document.addEventListener('DOMContentLoaded', () => {
      updateGeneratedLink();
      loadLinks();

      if (initialParamSession) {
        openTimelineModal(initialParamSession);
      }
    });
  </script>
</body>
</html>
