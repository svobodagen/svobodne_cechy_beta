<?php
// admin/fb_publisher.php
require_once __DIR__ . '/../db.php';
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <script>if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') { location.replace('https://' + location.hostname + location.pathname + location.search + location.hash); }</script>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Automatizace FB skupin – Svobodné Cechy</title>
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
      --danger: #ef4444;
      --warning: #f59e0b;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; line-height: 1.5; }
    .container { max-width: 1320px; margin: auto; }

    /* Header */
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .nav-links { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
    .nav-link { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; }
    .nav-link:hover { text-decoration: underline; }

    h1 { font-family: 'Bungee', cursive; font-size: 2rem; color: var(--accent); margin-bottom: 0.3rem; letter-spacing: 0.5px; }
    p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.8rem; }

    /* Action bar */
    .top-actions { display: flex; gap: 0.8rem; margin-bottom: 1.8rem; flex-wrap: wrap; align-items: center; justify-content: space-between; }
    .btn-group { display: flex; gap: 0.6rem; flex-wrap: wrap; }

    /* Tabs */
    .tabs-nav { display: flex; gap: 0.5rem; border-bottom: 2px solid rgba(255,255,255,0.1); margin-bottom: 2rem; overflow-x: auto; padding-bottom: 2px; }
    .tab-btn { background: transparent; border: none; color: var(--text-muted); padding: 0.9rem 1.4rem; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem; font-weight: 700; cursor: pointer; border-radius: 8px 8px 0 0; display: inline-flex; align-items: center; gap: 0.6rem; transition: all 0.2s; white-space: nowrap; }
    .tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .tab-btn.active { color: var(--accent); border-bottom: 3px solid var(--accent); background: rgba(232,117,22,0.1); }
    .tab-badge { background: rgba(255,255,255,0.15); padding: 0.15rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 800; }
    .tab-btn.active .tab-badge { background: var(--accent); color: #000; }

    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: fadeIn 0.25s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

    /* Cards */
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 1.8rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    .card h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.2rem; color: #fff; display: flex; align-items: center; gap: 0.6rem; }
    .card h2 i { color: var(--accent); }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.3rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
    .btn-primary { background: var(--accent); color: #000; }
    .btn-primary:hover { background: var(--accent-hover); color: #fff; }
    .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid var(--border); }
    .btn-secondary:hover { background: rgba(255,255,255,0.2); }
    .btn-danger { background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); }
    .btn-danger:hover { background: var(--danger); color: #fff; }
    .btn-success { background: rgba(34,197,94,0.2); color: #4ade80; border: 1px solid rgba(34,197,94,0.4); }
    .btn-success:hover { background: var(--success); color: #000; }
    .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px; }

    /* Status Badges */
    .badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-naplanovano { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
    .badge-publikovano { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
    .badge-chyba { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
    .badge-zruseno { background: rgba(255,255,255,0.1); color: var(--text-muted); border: 1px solid var(--border); }

    /* Grid layout for items */
    .items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
    .item-card { background: var(--card-inner); border: 1px solid var(--border); border-radius: 12px; padding: 1.4rem; display: flex; flex-direction: column; justify-content: space-between; transition: border-color 0.2s; position: relative; }
    .item-card:hover { border-color: rgba(232,117,22,0.4); }
    .item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem; gap: 0.8rem; }
    .item-title { font-size: 1.05rem; font-weight: 700; color: #fff; line-height: 1.3; }
    .item-cat { font-size: 0.78rem; font-weight: 700; color: var(--accent); background: rgba(232,117,22,0.12); padding: 0.2rem 0.6rem; border-radius: 6px; white-space: nowrap; }
    .item-body { font-size: 0.88rem; color: #d1c7bc; margin-bottom: 1.2rem; flex-grow: 1; }
    .item-actions { display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem; }

    /* Task list row */
    .task-row { background: var(--card-inner); border: 1px solid var(--border); border-radius: 10px; padding: 1.2rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .task-row:hover { border-color: rgba(232,117,22,0.3); }
    .task-time-col { min-width: 140px; }
    .task-date { font-weight: 800; color: #fff; font-size: 1rem; }
    .task-time { font-size: 0.82rem; color: var(--text-muted); }
    .task-main-col { flex: 1; min-width: 250px; }
    .task-group-link { color: #60a5fa; text-decoration: none; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem; }
    .task-group-link:hover { text-decoration: underline; }
    .task-post-name { font-size: 0.88rem; color: #e2d9cf; margin-top: 0.2rem; }
    .task-snippet { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.3rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 500px; }
    .task-actions-col { display: flex; gap: 0.5rem; align-items: center; }

    /* Calendar Box */
    .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
    .cal-day-header { text-align: center; font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; padding: 0.5rem; }
    .cal-day { background: rgba(0,0,0,0.3); border: 1px solid var(--border); min-height: 105px; border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column; gap: 0.3rem; }
    .cal-day.other-month { opacity: 0.3; }
    .cal-day.today { border-color: var(--accent); background: rgba(232,117,22,0.06); }
    .cal-day-num { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.2rem; }
    .cal-task-pill { font-size: 0.72rem; padding: 0.2rem 0.4rem; border-radius: 4px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
    .cal-task-pill.naplanovano { background: rgba(245,158,11,0.25); color: #fbbf24; border-left: 3px solid #f59e0b; }
    .cal-task-pill.publikovano { background: rgba(34,197,94,0.25); color: #4ade80; border-left: 3px solid #22c55e; }

    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; opacity: 0; pointer-events: none; transition: opacity 0.2s; backdrop-filter: blur(4px); }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }
    .modal-box { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 2rem; box-shadow: 0 16px 40px rgba(0,0,0,0.7); position: relative; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
    .modal-header h3 { font-size: 1.3rem; font-weight: 700; color: #fff; }
    .modal-close { background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; padding: 0.2rem; }
    .modal-close:hover { color: #fff; }

    /* Forms */
    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 8px; color: #fff; font-size: 0.95rem; font-family: inherit; }
    .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(232,117,22,0.25); }
    textarea.form-control { resize: vertical; min-height: 110px; }
    .form-hint { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem; }

    /* Toast */
    .toast { position: fixed; bottom: 2rem; right: 2rem; background: #1f2937; border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 1rem 1.4rem; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 0.6rem; transform: translateY(100px); opacity: 0; transition: all 0.3s; z-index: 2000; }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.success { border-color: var(--success); color: #4ade80; }
    .toast.error { border-color: var(--danger); color: #f87171; }

    /* Empty state */
    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; color: rgba(255,255,255,0.15); display: block; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div class="container">
    
    <!-- Top Nav Header -->
    <div class="header-bar">
      <div>
        <div class="nav-links">
          <a href="../admin.html" class="nav-link"><i class="bi bi-arrow-left"></i> Rozcestník administrace</a>
          <span style="color:var(--border)">|</span>
          <a href="landing_pages.php" class="nav-link"><i class="bi bi-file-earmark-person"></i> Landing Pages</a>
          <a href="landing_stats.php" class="nav-link"><i class="bi bi-graph-up"></i> Analytika & Odkazy</a>
          <a href="landing_leads.php" class="nav-link"><i class="bi bi-envelope"></i> Zprávy z kampaní</a>
        </div>
      </div>
    </div>

    <!-- Title & Intro -->
    <h1>Automatizace FB Skupin</h1>
    <p class="subtitle">Správa odkazů na Facebook skupiny, příprava standardních příspěvků a kalendář pro automatické vkládání agentem.</p>

    <!-- Top Action Bar -->
    <div class="top-actions">
      <div class="btn-group">
        <button class="btn btn-primary" onclick="openScheduleModal()"><i class="bi bi-calendar-plus"></i> Naplánovat příspěvek</button>
        <button class="btn btn-secondary" onclick="openTemplateModal()"><i class="bi bi-file-earmark-plus"></i> Nová šablona příspěvku</button>
        <button class="btn btn-secondary" onclick="openGroupModal()"><i class="bi bi-people-fill"></i> Přidat FB skupinu</button>
      </div>
      <div>
        <span id="agent-status-badge" class="badge badge-naplanovano" style="cursor:pointer" onclick="refreshAllData()">
          <i class="bi bi-robot"></i> <span id="pending-count-label">0 úloh čeká na agenta</span>
        </span>
      </div>
    </div>

    <!-- Main Tabs Navigation -->
    <div class="tabs-nav">
      <button class="tab-btn active" onclick="switchTab('schedule', this)">
        <i class="bi bi-calendar-week"></i> Kalendář a Harmonogram
        <span class="tab-badge" id="badge-tasks-count">0</span>
      </button>
      <button class="tab-btn" onclick="switchTab('templates', this)">
        <i class="bi bi-file-text"></i> Standardní příspěvky
        <span class="tab-badge" id="badge-templates-count">0</span>
      </button>
      <button class="tab-btn" onclick="switchTab('groups', this)">
        <i class="bi bi-collection"></i> Seznam FB skupin
        <span class="tab-badge" id="badge-groups-count">0</span>
      </button>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 1: KALENDÁŘ A HARMONOGRAM -->
    <!-- ============================================================ -->
    <div id="tab-schedule" class="tab-pane active">
      
      <!-- View toggle & filters -->
      <div class="card" style="padding: 1.2rem 1.8rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
          <div style="display: flex; gap: 0.6rem;">
            <button id="btn-view-list" class="btn btn-sm btn-primary" onclick="setScheduleView('list')"><i class="bi bi-list-task"></i> Seznam</button>
            <button id="btn-view-cal" class="btn btn-sm btn-secondary" onclick="setScheduleView('calendar')"><i class="bi bi-calendar-month"></i> Kalendář</button>
          </div>
          <div style="display: flex; gap: 0.6rem; align-items: center;">
            <span style="font-size:0.85rem; color:var(--text-muted); font-weight:700;">Filtr stavu:</span>
            <select id="filter-task-status" class="form-control" style="padding:0.4rem 0.8rem; font-size:0.85rem; width:auto;" onchange="renderScheduleList()">
              <option value="all">Všechny stavy</option>
              <option value="naplanovano">Pouze Naplánováno (čeká)</option>
              <option value="publikovano">Pouze Publikováno</option>
              <option value="chyba">Pouze Chyba</option>
            </select>
          </div>
        </div>
      </div>

      <!-- List View Container -->
      <div id="schedule-list-view">
        <div id="tasks-list-container">
          <!-- Dynamicky naplněno JS -->
        </div>
      </div>

      <!-- Calendar View Container -->
      <div id="schedule-cal-view" class="card" style="display: none;">
        <div class="cal-header">
          <button class="btn btn-secondary btn-sm" onclick="changeCalMonth(-1)"><i class="bi bi-chevron-left"></i> Předchozí</button>
          <h2 id="cal-month-title" style="margin-bottom:0; font-size:1.3rem;">Září 2026</h2>
          <button class="btn btn-secondary btn-sm" onclick="changeCalMonth(1)">Následující <i class="bi bi-chevron-right"></i></button>
        </div>
        <div class="cal-grid">
          <div class="cal-day-header">Po</div>
          <div class="cal-day-header">Út</div>
          <div class="cal-day-header">St</div>
          <div class="cal-day-header">Čt</div>
          <div class="cal-day-header">Pá</div>
          <div class="cal-day-header">So</div>
          <div class="cal-day-header">Ne</div>
        </div>
        <div id="cal-grid-days" class="cal-grid" style="margin-top: 6px;">
          <!-- Dny kalendáře vygenerovány JS -->
        </div>
      </div>

    </div>

    <!-- ============================================================ -->
    <!-- TAB 2: STANDARDNÍ PŘÍSPĚVKY (ŠABLONY) -->
    <!-- ============================================================ -->
    <div id="tab-templates" class="tab-pane">
      <div class="items-grid" id="templates-grid-container">
        <!-- Dynamicky naplněno JS -->
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 3: SEZNAM FB SKUPIN -->
    <!-- ============================================================ -->
    <div id="tab-groups" class="tab-pane">
      <div class="items-grid" id="groups-grid-container">
        <!-- Dynamicky naplněno JS -->
      </div>
    </div>

  </div>

  <!-- ============================================================ -->
  <!-- MODAL: NAPLÁNOVAT PŘÍSPĚVEK -->
  <!-- ============================================================ -->
  <div class="modal-overlay" id="modal-schedule">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="modal-schedule-title"><i class="bi bi-calendar-plus" style="color:var(--accent)"></i> Naplánovat příspěvek</h3>
        <button class="modal-close" onclick="closeModal('modal-schedule')">&times;</button>
      </div>
      <form id="form-schedule" onsubmit="submitSchedule(event)">
        <input type="hidden" id="task-id" value="">
        
        <div class="form-group">
          <label>Facebook Skupina *</label>
          <select id="task-group-id" class="form-control" required>
            <option value="">-- Vyberte cílovou FB skupinu --</option>
          </select>
        </div>

        <div class="form-group">
          <label>Standardní příspěvek (šablona)</label>
          <select id="task-template-id" class="form-control" onchange="onScheduleTemplateChange()">
            <option value="">-- Vlastní text (nebo vyberte šablonu) --</option>
          </select>
          <div class="form-hint">Výběrem šablony se automaticky předvyplní text, landing page a obrázek níže.</div>
        </div>

        <div class="form-group">
          <label>Termín publikace (Datum a čas) *</label>
          <input type="datetime-local" id="task-scheduled-at" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Text příspěvku (specifický pro tento termín)</label>
          <textarea id="task-custom-text" class="form-control" placeholder="Zde můžete přizpůsobit text konkrétní skupině..."></textarea>
        </div>

        <div class="form-group">
          <label>Vlastní cílové URL / Odkaz (nepovinné)</label>
          <input type="url" id="task-target-url" class="form-control" placeholder="https://svobodnecechy.cz/...">
          <div class="form-hint">Pokud necháte prázdné, použije se odkaz ze šablony. UTM parametry se doplní automaticky.</div>
        </div>

        <div class="form-group">
          <label>Stav úlohy</label>
          <select id="task-status" class="form-control">
            <option value="naplanovano">Naplánováno (čeká na agenta)</option>
            <option value="publikovano">Publikováno</option>
            <option value="chyba">Chyba</option>
            <option value="zruseno">Zrušeno</option>
          </select>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:1.8rem;">
          <button type="button" class="btn btn-secondary" onclick="closeModal('modal-schedule')">Zrušit</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Uložit do kalendáře</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- MODAL: STANDARDNÍ PŘÍSPĚVEK (ŠABLONA) -->
  <!-- ============================================================ -->
  <div class="modal-overlay" id="modal-template">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="modal-template-title"><i class="bi bi-file-earmark-plus" style="color:var(--accent)"></i> Nová šablona příspěvku</h3>
        <button class="modal-close" onclick="closeModal('modal-template')">&times;</button>
      </div>
      <form id="form-template" onsubmit="submitTemplate(event)">
        <input type="hidden" id="tpl-id" value="">

        <div class="form-group">
          <label>Název šablony / interní označení *</label>
          <input type="text" id="tpl-title" class="form-control" placeholder="např. Jiří Pačinek – Sklářská huť a zahrada" required>
        </div>

        <div class="form-group">
          <label>Přiřazená Landing Page</label>
          <select id="tpl-landing-slug" class="form-control" onchange="onTemplateLandingChange()">
            <option value="">-- Žádná (zadat vlastní URL níže) --</option>
          </select>
          <div class="form-hint">Vyberte vytvořenou landing page mistra. URL se vygeneruje automaticky.</div>
        </div>

        <div class="form-group">
          <label>Cílová URL adresa odkazu</label>
          <input type="url" id="tpl-target-url" class="form-control" placeholder="https://svobodnecechy.cz/landing_pages/...">
        </div>

        <div class="form-group">
          <label>Text příspěvku pro Facebook</label>
          <textarea id="tpl-post-text" class="form-control" style="min-height:140px;" placeholder="Poutavý text příspěvku... např. Víte, jak vzniká české foukané sklo přímo na huti? 🔥 Mistr sklář Jiří Pačinek..."></textarea>
          <div class="form-hint">Text, který agent vloží do příspěvku před odkazem. Doporučujeme používat emotikony.</div>
        </div>

        <div class="form-group">
          <label>URL vkládaného obrázku (nepovinné)</label>
          <input type="url" id="tpl-image-url" class="form-control" placeholder="https://svobodnecechy.cz/uploads/...">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:1.8rem;">
          <button type="button" class="btn btn-secondary" onclick="closeModal('modal-template')">Zrušit</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Uložit šablonu</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- MODAL: FB SKUPINA -->
  <!-- ============================================================ -->
  <div class="modal-overlay" id="modal-group">
    <div class="modal-box">
      <div class="modal-header">
        <h3 id="modal-group-title"><i class="bi bi-people-fill" style="color:var(--accent)"></i> Přidat FB skupinu</h3>
        <button class="modal-close" onclick="closeModal('modal-group')">&times;</button>
      </div>
      <form id="form-group" onsubmit="submitGroup(event)">
        <input type="hidden" id="grp-id" value="">

        <div class="form-group">
          <label>Název Facebook skupiny *</label>
          <input type="text" id="grp-name" class="form-control" placeholder="např. Milovníci českého skla a designu" required>
        </div>

        <div class="form-group">
          <label>URL odkaz na skupinu *</label>
          <input type="url" id="grp-url" class="form-control" placeholder="https://www.facebook.com/groups/..." required>
        </div>

        <div class="form-group">
          <label>Kategorie / Zaměření</label>
          <input type="text" id="grp-category" class="form-control" placeholder="např. Sklářství, Kovářství, Bydlení, Řemesla">
        </div>

        <div class="form-group">
          <label>Poznámky k pravidlům skupiny</label>
          <textarea id="grp-notes" class="form-control" placeholder="např. Odkazy povoleny pouze o víkendu, vyžadují foto z výroby..."></textarea>
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:0.6rem;">
          <input type="checkbox" id="grp-active" checked style="width:18px; height:18px; accent-color:var(--accent);">
          <label for="grp-active" style="margin-bottom:0; cursor:pointer; text-transform:none; font-size:0.95rem; color:#fff;">Skupina je aktivní pro plánování</label>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:1.8rem;">
          <button type="button" class="btn btn-secondary" onclick="closeModal('modal-group')">Zrušit</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Uložit skupinu</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Element -->
  <div id="toast" class="toast"><i class="bi bi-check-circle-fill"></i> <span id="toast-text"></span></div>

  <script>
    // Global State
    let appData = {
      groups: [],
      templates: [],
      tasks: [],
      landing_pages: []
    };
    let currentScheduleView = 'list';
    let calDate = new Date();

    // Initial Load
    document.addEventListener('DOMContentLoaded', () => {
      refreshAllData();
    });

    function showToast(text, isError = false) {
      const t = document.getElementById('toast');
      const msg = document.getElementById('toast-text');
      msg.innerText = text;
      t.className = 'toast show ' + (isError ? 'error' : 'success');
      setTimeout(() => { t.className = 'toast'; }, 3500);
    }

    function switchTab(tabId, btn) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('tab-' + tabId).classList.add('active');
      if (tabId === 'schedule' && currentScheduleView === 'calendar') {
        renderCalendar();
      }
    }

    // Refresh Data from API
    function refreshAllData() {
      fetch('api_fb_publisher.php?action=get_all_data')
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            showToast(res.message || 'Chyba při načítání dat', true);
            return;
          }
          appData = res;
          updateBadges();
          renderScheduleList();
          renderTemplates();
          renderGroups();
          populateDropdowns();
          if (currentScheduleView === 'calendar') renderCalendar();
        })
        .catch(err => {
          console.error(err);
          showToast('Chyba spojení s API', true);
        });
    }

    function updateBadges() {
      document.getElementById('badge-tasks-count').innerText = appData.tasks.length;
      document.getElementById('badge-templates-count').innerText = appData.templates.length;
      document.getElementById('badge-groups-count').innerText = appData.groups.length;

      const now = new Date().toISOString();
      const pendingCount = appData.tasks.filter(t => t.status === 'naplanovano').length;
      document.getElementById('pending-count-label').innerText = `${pendingCount} úloh čeká na agenta`;
    }

    function populateDropdowns() {
      // Group dropdown in schedule modal
      const grpSel = document.getElementById('task-group-id');
      grpSel.innerHTML = '<option value="">-- Vyberte cílovou FB skupinu --</option>' +
        appData.groups.map(g => `<option value="${g.id}">${escapeHtml(g.name)} ${g.category ? '(' + escapeHtml(g.category) + ')' : ''}</option>`).join('');

      // Template dropdown in schedule modal
      const tplSel = document.getElementById('task-template-id');
      tplSel.innerHTML = '<option value="">-- Vlastní text (nebo vyberte šablonu) --</option>' +
        appData.templates.map(t => `<option value="${t.id}">${escapeHtml(t.title)}</option>`).join('');

      // Landing page dropdown in template modal
      const lpSel = document.getElementById('tpl-landing-slug');
      lpSel.innerHTML = '<option value="">-- Žádná (zadat vlastní URL níže) --</option>' +
        appData.landing_pages.map(lp => `<option value="${lp.slug}">${escapeHtml(lp.master_name || lp.slug)} (${lp.slug})</option>`).join('');
    }

    // ============================================================
    // SCHEDULE (TAB 1)
    // ============================================================
    function setScheduleView(view) {
      currentScheduleView = view;
      document.getElementById('btn-view-list').className = 'btn btn-sm ' + (view === 'list' ? 'btn-primary' : 'btn-secondary');
      document.getElementById('btn-view-cal').className = 'btn btn-sm ' + (view === 'calendar' ? 'btn-primary' : 'btn-secondary');
      document.getElementById('schedule-list-view').style.display = view === 'list' ? 'block' : 'none';
      document.getElementById('schedule-cal-view').style.display = view === 'calendar' ? 'block' : 'none';
      if (view === 'calendar') renderCalendar();
    }

    function renderScheduleList() {
      const container = document.getElementById('tasks-list-container');
      const statusFilter = document.getElementById('filter-task-status').value;
      let tasks = appData.tasks;
      if (statusFilter !== 'all') {
        tasks = tasks.filter(t => t.status === statusFilter);
      }

      if (!tasks.length) {
        container.innerHTML = `
          <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h3>Žádné naplánované příspěvky</h3>
            <p>Klikněte na tlačítko <strong>Naplánovat příspěvek</strong> a přidejte první položku do harmonogramu.</p>
          </div>`;
        return;
      }

      container.innerHTML = tasks.map(t => {
        const dt = new Date(t.scheduled_at);
        const dateStr = dt.toLocaleDateString('cs-CZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = dt.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
        const textSnippet = t.custom_text || (t.template_title ? 'Dle šablony: ' + t.template_title : 'Bez specifického textu');
        
        return `
          <div class="task-row">
            <div class="task-time-col">
              <div class="task-date"><i class="bi bi-clock"></i> ${timeStr}</div>
              <div class="task-time">${dateStr}</div>
              <div style="margin-top:0.4rem;">
                <span class="badge badge-${t.status}">${t.status}</span>
              </div>
            </div>
            <div class="task-main-col">
              <a href="${escapeHtml(t.group_url)}" target="_blank" class="task-group-link">
                <i class="bi bi-facebook"></i> ${escapeHtml(t.group_name || 'Neznámá skupina')}
                <i class="bi bi-box-arrow-up-right" style="font-size:0.75rem;"></i>
              </a>
              <div class="task-post-name"><strong>Příspěvek:</strong> ${escapeHtml(t.template_title || 'Vlastní příspěvek')}</div>
              <div class="task-snippet">${escapeHtml(textSnippet)}</div>
              ${t.log_message ? `<div style="font-size:0.75rem; color:#4ade80; margin-top:0.25rem;"><i class="bi bi-check2-circle"></i> ${escapeHtml(t.log_message)}</div>` : ''}
            </div>
            <div class="task-actions-col">
              ${t.status === 'naplanovano' ? `
                <button class="btn btn-sm btn-success" title="Označit jako publikováno" onclick="markTaskStatus(${t.id}, 'publikovano')">
                  <i class="bi bi-check-lg"></i> Hotovo
                </button>
              ` : ''}
              <button class="btn btn-sm btn-secondary" onclick="editScheduleTask(${t.id})"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-danger" onclick="deleteScheduleTask(${t.id})"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        `;
      }).join('');
    }

    function openScheduleModal(prefillGroupId = null, prefillTemplateId = null) {
      document.getElementById('form-schedule').reset();
      document.getElementById('task-id').value = '';
      document.getElementById('modal-schedule-title').innerHTML = '<i class="bi bi-calendar-plus" style="color:var(--accent)"></i> Naplánovat příspěvek';
      
      // Set default datetime to tomorrow at 10:00
      const d = new Date();
      d.setDate(d.getDate() + 1);
      d.setHours(10, 0, 0, 0);
      const tzOffset = d.getTimezoneOffset() * 60000;
      const localISODate = new Date(d.getTime() - tzOffset).toISOString().slice(0, 16);
      document.getElementById('task-scheduled-at').value = localISODate;

      if (prefillGroupId) document.getElementById('task-group-id').value = prefillGroupId;
      if (prefillTemplateId) {
        document.getElementById('task-template-id').value = prefillTemplateId;
        onScheduleTemplateChange();
      }

      openModal('modal-schedule');
    }

    function editScheduleTask(id) {
      const t = appData.tasks.find(x => x.id == id);
      if (!t) return;
      document.getElementById('task-id').value = t.id;
      document.getElementById('task-group-id').value = t.group_id;
      document.getElementById('task-template-id').value = t.template_id || '';
      document.getElementById('task-scheduled-at').value = t.scheduled_at.replace(' ', 'T').substring(0, 16);
      document.getElementById('task-custom-text').value = t.custom_text || '';
      document.getElementById('task-target-url').value = t.target_url || '';
      document.getElementById('task-status').value = t.status;
      document.getElementById('modal-schedule-title').innerHTML = '<i class="bi bi-pencil" style="color:var(--accent)"></i> Upravit naplánovaný příspěvek';
      openModal('modal-schedule');
    }

    function onScheduleTemplateChange() {
      const tplId = document.getElementById('task-template-id').value;
      if (!tplId) return;
      const tpl = appData.templates.find(x => x.id == tplId);
      if (tpl) {
        if (!document.getElementById('task-custom-text').value) {
          document.getElementById('task-custom-text').value = tpl.post_text || '';
        }
        if (!document.getElementById('task-target-url').value && tpl.target_url) {
          document.getElementById('task-target-url').value = tpl.target_url;
        }
      }
    }

    function submitSchedule(e) {
      e.preventDefault();
      const payload = {
        id: document.getElementById('task-id').value,
        group_id: document.getElementById('task-group-id').value,
        template_id: document.getElementById('task-template-id').value,
        scheduled_at: document.getElementById('task-scheduled-at').value.replace('T', ' ') + ':00',
        custom_text: document.getElementById('task-custom-text').value,
        target_url: document.getElementById('task-target-url').value,
        status: document.getElementById('task-status').value
      };

      fetch('api_fb_publisher.php?action=save_task', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showToast('Příspěvek úspěšně naplánován do kalendáře!');
          closeModal('modal-schedule');
          refreshAllData();
        } else {
          showToast(res.message || 'Chyba při ukládání', true);
        }
      });
    }

    function deleteScheduleTask(id) {
      if (!confirm('Opravdu smazat tuto naplánovanou úlohu z kalendáře?')) return;
      fetch(`api_fb_publisher.php?action=delete_task&id=${id}`)
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Úloha smazána.');
            refreshAllData();
          } else {
            showToast(res.message || 'Chyba při mazání', true);
          }
        });
    }

    function markTaskStatus(id, newStatus) {
      fetch('api_fb_publisher.php?action=update_task_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: newStatus, log_message: 'Ručně potvrzeno administrátorem' })
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showToast(`Stav změněn na ${newStatus}`);
          refreshAllData();
        }
      });
    }

    // ============================================================
    // CALENDAR VIEW LOGIC
    // ============================================================
    function changeCalMonth(delta) {
      calDate.setMonth(calDate.getMonth() + delta);
      renderCalendar();
    }

    function renderCalendar() {
      const year = calDate.getFullYear();
      const month = calDate.getMonth();
      const monthNames = ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"];
      document.getElementById('cal-month-title').innerText = `${monthNames[month]} ${year}`;

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      
      // Start day in Czech (Monday = 0)
      let startDayOfWeek = firstDay.getDay() - 1;
      if (startDayOfWeek === -1) startDayOfWeek = 6;

      const grid = document.getElementById('cal-grid-days');
      grid.innerHTML = '';

      // Prev month filler
      const prevMonthLastDay = new Date(year, month, 0).getDate();
      for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const d = prevMonthLastDay - i;
        grid.innerHTML += `<div class="cal-day other-month"><div class="cal-day-num">${d}</div></div>`;
      }

      // Current month days
      const today = new Date();
      for (let d = 1; d <= lastDay.getDate(); d++) {
        const isToday = (today.getDate() === d && today.getMonth() === month && today.getFullYear() === year);
        const dayStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        
        // Find tasks for this day
        const dayTasks = appData.tasks.filter(t => t.scheduled_at.startsWith(dayStr));

        let tasksHtml = dayTasks.map(t => {
          const time = t.scheduled_at.substring(11, 16);
          return `<div class="cal-task-pill ${t.status}" title="${escapeHtml(t.group_name)}: ${escapeHtml(t.template_title || '')}" onclick="editScheduleTask(${t.id})">
            ${time} ${escapeHtml(t.group_name || 'FB')}
          </div>`;
        }).join('');

        grid.innerHTML += `
          <div class="cal-day ${isToday ? 'today' : ''}" onclick="if(event.target === this) openScheduleForDate('${dayStr}')">
            <div class="cal-day-num">${d}</div>
            ${tasksHtml}
          </div>`;
      }
    }

    function openScheduleForDate(dateStr) {
      openScheduleModal();
      document.getElementById('task-scheduled-at').value = `${dateStr}T10:00`;
    }

    // ============================================================
    // TEMPLATES (TAB 2)
    // ============================================================
    function renderTemplates() {
      const container = document.getElementById('templates-grid-container');
      if (!appData.templates.length) {
        container.innerHTML = `
          <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="bi bi-file-earmark-text"></i>
            <h3>Žádné standardní šablony</h3>
            <p>Vytvořte si šablony příspěvků s předvyplněným textem, odkazem na Landing page a fotkou.</p>
            <button class="btn btn-primary" style="margin-top:1rem;" onclick="openTemplateModal()"><i class="bi bi-plus-lg"></i> Vytvořit první šablonu</button>
          </div>`;
        return;
      }

      container.innerHTML = appData.templates.map(t => {
        return `
          <div class="item-card">
            <div>
              <div class="item-header">
                <div class="item-title">${escapeHtml(t.title)}</div>
                ${t.landing_slug ? `<span class="item-cat">${escapeHtml(t.landing_slug)}</span>` : ''}
              </div>
              <div class="item-body">
                <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.5rem; word-break:break-all;">
                  <i class="bi bi-link-45deg"></i> ${t.target_url ? escapeHtml(t.target_url) : '(Bude vygenerováno z Landing page)'}
                </div>
                <div style="white-space:pre-wrap; line-height:1.4; max-height:120px; overflow-y:auto; background:rgba(0,0,0,0.3); padding:0.6rem 0.8rem; border-radius:6px; border:1px solid rgba(255,255,255,0.05);">${escapeHtml(t.post_text || 'Bez textu')}</div>
                ${t.image_url ? `<div style="margin-top:0.6rem; font-size:0.78rem; color:#60a5fa;"><i class="bi bi-image"></i> Má přiřazený obrázek</div>` : ''}
              </div>
            </div>
            <div class="item-actions">
              <button class="btn btn-sm btn-primary" onclick="openScheduleModal(null, ${t.id})"><i class="bi bi-calendar-plus"></i> Naplánovat</button>
              <button class="btn btn-sm btn-secondary" onclick="editTemplate(${t.id})"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-danger" onclick="deleteTemplate(${t.id})"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        `;
      }).join('');
    }

    function openTemplateModal() {
      document.getElementById('form-template').reset();
      document.getElementById('tpl-id').value = '';
      document.getElementById('modal-template-title').innerHTML = '<i class="bi bi-file-earmark-plus" style="color:var(--accent)"></i> Nová šablona příspěvku';
      openModal('modal-template');
    }

    function editTemplate(id) {
      const t = appData.templates.find(x => x.id == id);
      if (!t) return;
      document.getElementById('tpl-id').value = t.id;
      document.getElementById('tpl-title').value = t.title;
      document.getElementById('tpl-landing-slug').value = t.landing_slug || '';
      document.getElementById('tpl-target-url').value = t.target_url || '';
      document.getElementById('tpl-post-text').value = t.post_text || '';
      document.getElementById('tpl-image-url').value = t.image_url || '';
      document.getElementById('modal-template-title').innerHTML = '<i class="bi bi-pencil" style="color:var(--accent)"></i> Upravit šablonu';
      openModal('modal-template');
    }

    function onTemplateLandingChange() {
      const slug = document.getElementById('tpl-landing-slug').value;
      if (slug) {
        const proto = location.protocol;
        const host = location.host;
        document.getElementById('tpl-target-url').value = `${proto}//${host}/landing_pages/${slug}.html`;
      }
    }

    function submitTemplate(e) {
      e.preventDefault();
      const payload = {
        id: document.getElementById('tpl-id').value,
        title: document.getElementById('tpl-title').value,
        landing_slug: document.getElementById('tpl-landing-slug').value,
        target_url: document.getElementById('tpl-target-url').value,
        post_text: document.getElementById('tpl-post-text').value,
        image_url: document.getElementById('tpl-image-url').value
      };

      fetch('api_fb_publisher.php?action=save_template', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showToast('Šablona příspěvku uložena!');
          closeModal('modal-template');
          refreshAllData();
        } else {
          showToast(res.message || 'Chyba při ukládání', true);
        }
      });
    }

    function deleteTemplate(id) {
      if (!confirm('Opravdu smazat tuto šablonu příspěvku?')) return;
      fetch(`api_fb_publisher.php?action=delete_template&id=${id}`)
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Šablona smazána.');
            refreshAllData();
          } else {
            showToast(res.message || 'Chyba při mazání', true);
          }
        });
    }

    // ============================================================
    // GROUPS (TAB 3)
    // ============================================================
    function renderGroups() {
      const container = document.getElementById('groups-grid-container');
      if (!appData.groups.length) {
        container.innerHTML = `
          <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="bi bi-people"></i>
            <h3>Žádné evidované FB skupiny</h3>
            <p>Přidejte odkazy na Facebook skupiny, kam chcete pravidelně umisťovat příspěvky.</p>
            <button class="btn btn-primary" style="margin-top:1rem;" onclick="openGroupModal()"><i class="bi bi-plus-lg"></i> Přidat první skupinu</button>
          </div>`;
        return;
      }

      container.innerHTML = appData.groups.map(g => {
        return `
          <div class="item-card">
            <div>
              <div class="item-header">
                <div class="item-title">${escapeHtml(g.name)}</div>
                ${g.category ? `<span class="item-cat">${escapeHtml(g.category)}</span>` : ''}
              </div>
              <div class="item-body">
                <div style="margin-bottom: 0.6rem;">
                  <a href="${escapeHtml(g.group_url)}" target="_blank" class="task-group-link">
                    <i class="bi bi-facebook"></i> Otevřít skupinu na FB <i class="bi bi-box-arrow-up-right" style="font-size:0.75rem;"></i>
                  </a>
                </div>
                ${g.notes ? `<div style="font-size:0.82rem; color:var(--text-muted); background:rgba(0,0,0,0.3); padding:0.6rem; border-radius:6px;"><i class="bi bi-info-circle"></i> ${escapeHtml(g.notes)}</div>` : ''}
              </div>
            </div>
            <div class="item-actions">
              <button class="btn btn-sm btn-primary" onclick="openScheduleModal(${g.id}, null)"><i class="bi bi-calendar-plus"></i> Naplánovat sem</button>
              <button class="btn btn-sm btn-secondary" onclick="editGroup(${g.id})"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-danger" onclick="deleteGroup(${g.id})"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        `;
      }).join('');
    }

    function openGroupModal() {
      document.getElementById('form-group').reset();
      document.getElementById('grp-id').value = '';
      document.getElementById('grp-active').checked = true;
      document.getElementById('modal-group-title').innerHTML = '<i class="bi bi-people-fill" style="color:var(--accent)"></i> Přidat FB skupinu';
      openModal('modal-group');
    }

    function editGroup(id) {
      const g = appData.groups.find(x => x.id == id);
      if (!g) return;
      document.getElementById('grp-id').value = g.id;
      document.getElementById('grp-name').value = g.name;
      document.getElementById('grp-url').value = g.group_url;
      document.getElementById('grp-category').value = g.category || '';
      document.getElementById('grp-notes').value = g.notes || '';
      document.getElementById('grp-active').checked = g.is_active == 1;
      document.getElementById('modal-group-title').innerHTML = '<i class="bi bi-pencil" style="color:var(--accent)"></i> Upravit FB skupinu';
      openModal('modal-group');
    }

    function submitGroup(e) {
      e.preventDefault();
      const payload = {
        id: document.getElementById('grp-id').value,
        name: document.getElementById('grp-name').value,
        group_url: document.getElementById('grp-url').value,
        category: document.getElementById('grp-category').value,
        notes: document.getElementById('grp-notes').value,
        is_active: document.getElementById('grp-active').checked ? 1 : 0
      };

      fetch('api_fb_publisher.php?action=save_group', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showToast('Skupina uložena!');
          closeModal('modal-group');
          refreshAllData();
        } else {
          showToast(res.message || 'Chyba při ukládání', true);
        }
      });
    }

    function deleteGroup(id) {
      if (!confirm('Opravdu smazat tuto FB skupinu ze seznamu?')) return;
      fetch(`api_fb_publisher.php?action=delete_group&id=${id}`)
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            showToast('Skupina smazána.');
            refreshAllData();
          } else {
            showToast(res.message || 'Chyba při mazání', true);
          }
        });
    }

    // Modal helpers
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }
  </script>
</body>
</html>
