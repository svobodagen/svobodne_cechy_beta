<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Správa landing pages mistrů – Svobodné Cechy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../styles.css?v=202608220043" />
  <style>
    body { background: #0b0a08; color: #f4efe7; font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; }
    .container { max-width: 960px; margin: auto; background: #17120e; padding: 2.5rem; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    h1 { font-family: 'Bungee', cursive; font-size: 1.8rem; color: #e87516; margin-bottom: 0.5rem; }
    p.subtitle { color: #a39b8e; font-size: 0.95rem; margin-bottom: 2rem; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .btn-back { color: #e87516; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-back:hover { text-decoration: underline; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: #e87516; padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
    a.btn-action { display: inline-flex; align-items: center; gap: 0.4rem; background: #e87516; color: #fff; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: background .2s; }
    a.btn-action:hover { background: #d0640d; }
    button.btn-copy { display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(255,255,255,0.1); color: #f4efe7; border: 1px solid rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; }
    button.btn-copy:hover { background: rgba(255,255,255,0.2); }
    .form-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1.5rem; margin-top: 3rem; }
    .form-card h2 { font-size: 1.3rem; margin-bottom: 1rem; color: #fff; }
    label { display: block; margin-top: 1rem; font-size: 0.85rem; font-weight: 600; color: #a39b8e; }
    input[type="text"] { width: 100%; padding: 0.75rem 1rem; margin-top: 0.3rem; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; color: #fff; font-size: 0.95rem; }
    input[type="submit"] { margin-top: 1.5rem; background: #e87516; color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; font-weight: 700; cursor: pointer; }
    input[type="submit"]:hover { background: #d0640d; }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>Správa soukromých landing pages mistrů</h1>
    <p class="subtitle">Tyto přístupové stránky jsou určeny pro propagační kampaně (reklamu). Nejsou veřejně dohledatelné na hlavním webu.</p>

    <table>
      <thead>
        <tr>
          <th>Název stránky / Mistr</th>
          <th>Cesta k souboru</th>
          <th>Akce</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $dir = __DIR__ . "/landing_pages";
        if (file_exists($dir)) {
          $files = array_diff(scandir($dir), ['..', '.', '.gitkeep']);
          if (empty($files)) {
            echo "<tr><td colspan='3' style='text-align:center; color:#a39b8e;'>Zatím nebyly vytvořeny žádné landing pages.</td></tr>";
          } else {
            foreach ($files as $file) {
              $urlPath = "admin/landing_pages/" . $file;
              echo "<tr>";
              echo "<td><strong>" . htmlspecialchars($file) . "</strong></td>";
              echo "<td style='font-family:monospace; color:#a39b8e; font-size:0.85rem;'>" . htmlspecialchars($urlPath) . "</td>";
              echo "<td style='display:flex; gap:0.5rem;'>";
              echo "<a class='btn-action' href='landing_pages/" . htmlspecialchars($file) . "' target='_blank'><i class='bi bi-box-arrow-up-right'></i> Zobrazit</a>";
              echo "<button class='btn-copy' onclick=\"navigator.clipboard.writeText(window.location.origin + '/" . htmlspecialchars($urlPath) . "'); alert('Odkaz zkopírován do schránky!');\"><i class='bi bi-link-45deg'></i> Zkopírovat odkaz</button>";
              echo "</td>";
              echo "</tr>";
            }
          }
        }
        ?>
      </tbody>
    </table>

    <div class="form-card">
      <h2>Vytvořit novou landing page pro mistra</h2>
      <form method="post" action="">
        <div>
          <label for="master_name">Jméno mistra</label>
          <input type="text" id="master_name" name="master_name" placeholder="Např. Jiří Pačinek" required />
        </div>
        <div>
          <label for="slug">URL slug (název souboru bez .html)</label>
          <input type="text" id="slug" name="slug" placeholder="např. jiri-pacinek" required />
        </div>
        <input type="submit" name="create" value="Vytvořit novou stránku" />
      </form>

      <?php
      if (isset($_POST['create'])) {
          $name = trim($_POST['master_name']);
          $slug = trim($_POST['slug']);
          $filepath = $dir . "/{$slug}.html";
          
          $template = <<<HTML
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$name} – Svobodné Cechy</title>
  <link rel="stylesheet" href="../../styles.css?v=202608220043">
</head>
<body style="background:#0b0a08; color:#f4efe7; font-family:sans-serif; padding:2rem;">
  <div style="max-width:800px; margin:auto; background:#17120e; padding:2rem; border-radius:12px; border:1px solid #e87516;">
    <h1 style="color:#e87516;">Učednictví u mistra {$name}</h1>
    <p>Vítejte na soukromé landing page mistra <strong>{$name}</strong>.</p>
    <div style="margin-top:2rem;">
      <a href="https://wa.me/420602763599" style="background:#e87516; color:#fff; padding:0.8rem 1.5rem; text-decoration:none; border-radius:6px; font-weight:bold;">Kontaktovat přes WhatsApp</a>
    </div>
  </div>
</body>
</html>
HTML;
          file_put_contents($filepath, $template);
          echo "<p style='color:#25d366; margin-top:1rem; font-weight:600;'>✓ Stránka pro mistra {$name} byla úspěšně vytvořena: {$slug}.html</p>";
          echo "<script>setTimeout(() => window.location.reload(), 1500);</script>";
      }
      ?>
    </div>
  </div>
</body>
</html>
