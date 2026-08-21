<?php
// admin/landing_pages.php
$dir = __DIR__ . "/landing_pages";
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

$message = "";
$messageType = "success";

// Handle Saving Edits
if (isset($_POST['save_page_content'])) {
    $editFile = basename($_POST['edit_filename']);
    $content = $_POST['page_content'];
    $targetPath = $dir . "/" . $editFile;
    
    if (file_exists($targetPath) && str_ends_with($editFile, '.html')) {
        file_put_contents($targetPath, $content);
        $message = "Stránka '{$editFile}' byla úspěšně uložena.";
    } else {
        $message = "Chyba při ukládání souboru.";
        $messageType = "error";
    }
}

// Handle Deleting Pages
if (isset($_GET['delete'])) {
    $deleteFile = basename($_GET['delete']);
    $targetPath = $dir . "/" . $deleteFile;
    if (file_exists($targetPath) && str_ends_with($deleteFile, '.html')) {
        unlink($targetPath);
        header("Location: landing_pages.php?msg=deleted");
        exit;
    }
}

// Handle Creating New Pages
if (isset($_POST['create_new'])) {
    $name = trim($_POST['master_name']);
    $slug = trim($_POST['slug']);
    // sanitize slug
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $slug)));
    if (empty($slug)) { $slug = "landing-page-" . time(); }
    
    $filepath = $dir . "/{$slug}.html";
    
    // Copy rich template content if available, replacing placeholders
    $templateSource = file_exists(__DIR__ . "/landing_pages/jiri-pacinek.html") 
        ? file_get_contents(__DIR__ . "/landing_pages/jiri-pacinek.html") 
        : "";

    if (!empty($templateSource)) {
        $pageContent = str_replace(
            ['Jiřího Pačinka', 'Jiří Pačinek', 'JIŘÍ PAČINEK', 'jiri-pacinek'],
            [$name, $name, mb_strtoupper($name), $slug],
            $templateSource
        );
    } else {
        $pageContent = "<!DOCTYPE html><html lang='cs'><head><meta charset='UTF-8'><title>{$name} – Landing Page</title></head><body><h1>{$name}</h1></body></html>";
    }

    file_put_contents($filepath, $pageContent);
    header("Location: landing_pages.php?edit={$slug}.html&created=1");
    exit;
}

$editingFile = isset($_GET['edit']) ? basename($_GET['edit']) : null;
$editingContent = "";
if ($editingFile && file_exists($dir . "/" . $editingFile)) {
    $editingContent = file_get_contents($dir . "/" . $editingFile);
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Správa a Editace Landing Pages – Svobodné Cechy</title>
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
    .btn-back { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.95rem; }
    .btn-back:hover { text-decoration: underline; }
    
    h1 { font-family: 'Bungee', cursive; font-size: 1.8rem; color: var(--accent); margin-bottom: 0.3rem; }
    p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; }

    .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
    .msg.success { background: rgba(37, 211, 102, 0.15); border: 1px solid #25D366; color: #25D366; }
    .msg.error { background: rgba(255, 77, 77, 0.15); border: 1px solid #ff4d4d; color: #ff4d4d; }

    /* Main Grid */
    .admin-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; }

    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
    .card h2 { font-size: 1.3rem; color: #fff; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; }

    /* Table */
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    td { padding: 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    
    .badge-file { font-family: monospace; background: rgba(255,255,255,0.08); padding: 0.3rem 0.6rem; border-radius: 4px; color: #fff; font-size: 0.9rem; }

    /* Actions */
    .actions-cell { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .btn-action { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer; transition: all .2s; }
    .btn-view { background: var(--accent); color: #fff; }
    .btn-view:hover { background: var(--accent-hover); }
    .btn-edit { background: #3b82f6; color: #fff; }
    .btn-edit:hover { background: #2563eb; }
    .btn-copy { background: rgba(255,255,255,0.1); color: var(--text); border: 1px solid var(--border); }
    .btn-copy:hover { background: rgba(255,255,255,0.2); }
    .btn-delete { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4); }
    .btn-delete:hover { background: #ef4444; color: #fff; }

    /* Editor Section */
    .editor-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
    @media (max-width: 900px) { .editor-section { grid-template-columns: 1fr; } }

    textarea.code-editor {
      width: 100%;
      height: 600px;
      background: #070605;
      color: #7dd3fc;
      font-family: 'Courier New', Courier, monospace;
      font-size: 0.9rem;
      padding: 1.2rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      resize: vertical;
      line-height: 1.5;
    }
    textarea.code-editor:focus { outline: none; border-color: var(--accent); }

    .preview-frame {
      width: 100%;
      height: 600px;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 8px;
    }

    /* Form Fields */
    label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-top: 1rem; }
    input[type="text"] { width: 100%; padding: 0.8rem 1rem; margin-top: 0.3rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 6px; color: #fff; font-size: 0.95rem; }
    input[type="submit"] { margin-top: 1.2rem; background: var(--accent); color: #fff; border: none; padding: 0.8rem 1.6rem; border-radius: 6px; font-weight: 700; cursor: pointer; }
    input[type="submit"]:hover { background: var(--accent-hover); }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>Správa a Editace Landing Pages Mistrů</h1>
    <p class="subtitle">Zde můžeš vytvářet, upravovat a spravovat soukromé přistávací stránky určené pro marketingové kampaně jednotlivých mistrů.</p>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
      <div class="msg success">✓ Landing page byla úspěšně smazána.</div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="msg <?= $messageType ?>">✓ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($editingFile): ?>
      <!-- EDITOR SECTION -->
      <div class="card" style="border-color: var(--accent); margin-bottom: 2rem;">
        <h2><i class="bi bi-pencil-square" style="color: var(--accent);"></i> Úprava stránky: <span style="color:var(--accent);"><?= htmlspecialchars($editingFile) ?></span></h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
          Můžeš upravit libovolný text nebo HTML kód. Vpravo vidíš okamžitý živý náhled.
        </p>

        <form method="post" action="landing_pages.php?edit=<?= urlencode($editingFile) ?>">
          <input type="hidden" name="edit_filename" value="<?= htmlspecialchars($editingFile) ?>" />
          
          <div class="editor-section">
            <div>
              <label style="margin-top:0; margin-bottom:0.5rem; color:#fff;">HTML Kód stránky:</label>
              <textarea name="page_content" id="codeEditor" class="code-editor" oninput="updatePreview()"><?= htmlspecialchars($editingContent) ?></textarea>
            </div>
            <div>
              <label style="margin-top:0; margin-bottom:0.5rem; color:#fff;">Živý Náhled (Preview):</label>
              <iframe id="livePreview" class="preview-frame" src="landing_pages/<?= htmlspecialchars($editingFile) ?>"></iframe>
            </div>
          </div>

          <div style="display: flex; gap: 1rem; margin-top: 1.5rem; align-items: center;">
            <input type="submit" name="save_page_content" value="Uložit změny v kódu" class="btn-action btn-view" style="font-size:1rem; padding:0.8rem 1.8rem;" />
            <a href="landing_pages.php" class="btn-action btn-copy">Zavřít editor / Zpět na seznam</a>
          </div>
        </form>
      </div>

      <script>
        function updatePreview() {
          const code = document.getElementById('codeEditor').value;
          const iframe = document.getElementById('livePreview');
          const doc = iframe.contentDocument || iframe.contentWindow.document;
          doc.open();
          doc.write(code);
          doc.close();
        }
      </script>

    <?php endif; ?>

    <div class="admin-grid">
      <!-- LIST OF LANDING PAGES -->
      <div class="card">
        <h2><i class="bi bi-files" style="color: var(--accent);"></i> Vytvořené Landing Pages</h2>
        <table>
          <thead>
            <tr>
              <th>Soubor / Mistr</th>
              <th>Relativní cesta (pro reklamu)</th>
              <th>Akce</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $files = array_diff(scandir($dir), ['..', '.', '.gitkeep']);
            if (empty($files)) {
              echo "<tr><td colspan='3' style='text-align:center; color:var(--text-muted); padding:2rem;'>Zatím nebyly vytvořeny žádné landing pages. Vytvoř první níže!</td></tr>";
            } else {
              foreach ($files as $file) {
                if (!str_ends_with($file, '.html')) continue;
                $relPath = "admin/landing_pages/" . $file;
                echo "<tr>";
                echo "<td><span class='badge-file'><i class='bi bi-file-earmark-code'></i> " . htmlspecialchars($file) . "</span></td>";
                echo "<td style='font-family:monospace; color:var(--text-muted); font-size:0.85rem;'>" . htmlspecialchars($relPath) . "</td>";
                echo "<td>";
                echo "<div class='actions-cell'>";
                echo "<a class='btn-action btn-edit' href='landing_pages.php?edit=" . urlencode($file) . "'><i class='bi bi-pencil-fill'></i> Upravit</a>";
                echo "<a class='btn-action btn-view' href='landing_pages/" . htmlspecialchars($file) . "' target='_blank'><i class='bi bi-box-arrow-up-right'></i> Zobrazit</a>";
                echo "<button class='btn-action btn-copy' onclick=\"navigator.clipboard.writeText(window.location.origin + '/" . htmlspecialchars($relPath) . "'); alert('Odkaz pro reklamu byl zkopírován!');\"><i class='bi bi-link-45deg'></i> Zkopírovat odkaz</button>";
                echo "<a class='btn-action btn-delete' href='landing_pages.php?delete=" . urlencode($file) . "' onclick=\"return confirm('Opravdu chceš smazat stránku {$file}?');\"><i class='bi bi-trash'></i> Smazat</a>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
              }
            }
            ?>
          </tbody>
        </table>
      </div>

      <!-- CREATE NEW LANDING PAGE -->
      <div class="card">
        <h2><i class="bi bi-plus-circle" style="color: var(--accent);"></i> Vytvořit novou Landing Page pro dalšího mistra</h2>
        <form method="post" action="landing_pages.php">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div>
              <label for="master_name">Jméno mistra / Název kampaně</label>
              <input type="text" id="master_name" name="master_name" placeholder="Např. Karel Novák nebo Sklo Pačinek" required />
            </div>
            <div>
              <label for="slug">URL Slug (název souboru bez .html)</label>
              <input type="text" id="slug" name="slug" placeholder="např. karel-novak" required />
            </div>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.8rem;">
            Při vytvoření se automaticky použije kompletní 10-sekční struktura (Hero, UVP, O mistrovi, Co se naučíš, Timeline, Galerie, Reference, FAQ, WhatsApp kontakt a Formulář).
          </p>
          <input type="submit" name="create_new" value="Vytvořit a otevřít v editoru" />
        </form>
      </div>

    </div>
  </div>
</body>
</html>
