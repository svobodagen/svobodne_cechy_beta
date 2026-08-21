<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Správa landing pages mistrů</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {font-family: 'Inter', sans-serif; background:#f5f5f5; padding:2rem;}
    .container {max-width:900px;margin:auto;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
    h1 {margin-bottom:1rem;}
    table {width:100%;border-collapse:collapse;margin-top:1rem;}
    th, td {padding:0.8rem;border-bottom:1px solid #ddd; text-align:left;}
    a.btn {display:inline-block;background:#e87516;color:#fff;padding:0.5rem 1rem;border-radius:4px;text-decoration:none;}
    form {margin-top:2rem;}
    label {display:block;margin-top:0.5rem;}
    input, textarea {width:100%;padding:0.5rem;margin-top:0.2rem;}
  </style>
</head>
<body>
  <div class="container">
    <h1>Správa soukromých landing pages mistrů</h1>
    <p>Stránky jsou uloženy v <code>admin/landing_pages/</code> a nejsou veřejně přístupné.</p>
    <table>
      <thead>
        <tr><th>Název</th><th>Akce</th></tr>
      </thead>
      <tbody>
        <?php
        $dir = __DIR__ . "/landing_pages";
        $files = array_diff(scandir($dir), ['..', '.']);
        foreach ($files as $file) {
          echo "<tr><td>{$file}</td><td><a class='btn' href='landing_pages/{$file}' target='_blank'>Zobrazit</a></td></tr>";
        }
        ?>
      </tbody>
    </table>
    <h2>Vytvořit novou stránku</h2>
    <form method="post" action="">
      <label for="master_name">Jméno mistra</label>
      <input type="text" id="master_name" name="master_name" required />
      <label for="slug">URL slug (bez .html)</label>
      <input type="text" id="slug" name="slug" required />
      <input type="submit" name="create" value="Vytvořit" class="btn" style="margin-top:1rem;" />
    </form>
    <?php
    if (isset($_POST['create'])) {
        $name = trim($_POST['master_name']);
        $slug = trim($_POST['slug']);
        // Inline HTML template for the landing page
        $template = <<<HTML
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$name} – Svobodné Cechy</title>
  <link rel="stylesheet" href="../styles.css?v=202608220043">
</head>
<body>
  <header>
    <h1>{$name}</h1>
  </header>
  <main>
    <p>Vítejte na stránce mistra <strong>{$name}</strong>. Další obsah lze upravit v administraci.</p>
    <img src="../images/sample_master.png" alt="Sample image" style="max-width:100%;height:auto;"/>
  </main>
  <footer>
    <p>&copy; Svobodné Cechy</p>
  </footer>
</body>
</html>
HTML;
        $content = $template;
        $filepath = $dir . "/{$slug}.html";
        file_put_contents($filepath, $content);
        echo "<p style='color:green;'>Stránka vytvořena: {$slug}.html</p>";
    }
    ?>
  </div>
</body>
</html>
