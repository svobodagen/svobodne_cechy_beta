<?php
// admin/landing_pages.php
$dir = __DIR__ . "/landing_pages";
$uploadsDir = __DIR__ . "/../uploads";

if (!file_exists($dir)) { mkdir($dir, 0777, true); }
if (!file_exists($uploadsDir)) { mkdir($uploadsDir, 0777, true); }

// Helper to fix image URL for landing page (located in admin/landing_pages/)
function fixImgUrl($url) {
    if (empty($url)) return '../../images/sample_master.png';
    if (strpos($url, 'uploads/') !== false) {
        $filename = basename($url);
        return '../../uploads/' . $filename;
    }
    return $url;
}

// Helper to fix image URL for admin panel previews (located in admin/)
function fixAdminPreviewUrl($url) {
    if (empty($url)) return '../images/sample_master.png';
    if (strpos($url, 'uploads/') !== false) {
        $filename = basename($url);
        return '../uploads/' . $filename;
    }
    return $url;
}

// AJAX Image Upload Handler
if (isset($_GET['action']) && $_GET['action'] === 'upload') {
    header('Content-Type: application/json');
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        if (in_array($ext, $allowed)) {
            $newFilename = 'img_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetPath = $uploadsDir . '/' . $newFilename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                echo json_encode([
                    'success' => true, 
                    'url' => '../../uploads/' . $newFilename,
                    'admin_preview_url' => '../uploads/' . $newFilename
                ]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Neplatný typ souboru.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Chyba při nahrávání souboru.']);
    }
    exit;
}

// Function to generate full HTML from section data array with dynamic section ordering and compact heading typography
function renderLandingPageHtml($data) {
    $slug = htmlspecialchars($data['slug'] ?? 'master');
    $masterName = htmlspecialchars($data['master_name'] ?? 'Mistr');
    $metaTitle = htmlspecialchars($data['meta_title'] ?? $masterName . ' – Svobodné Cechy');
    $metaDesc = htmlspecialchars($data['meta_desc'] ?? '');
    
    // 1. Hero
    $h_eyebrow = htmlspecialchars($data['hero']['eyebrow'] ?? '');
    $h_h1 = htmlspecialchars($data['hero']['h1'] ?? '');
    $h_sub = htmlspecialchars($data['hero']['subtitle'] ?? '');
    $h_btn1 = htmlspecialchars($data['hero']['btn_primary'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ');
    $h_btn2 = htmlspecialchars($data['hero']['btn_secondary'] ?? 'PODÍVAT SE, JAK SE PRACUJE SE SKLEM');
    $h_img = htmlspecialchars(fixImgUrl($data['hero']['image'] ?? ''));

    $heroSection = <<<HTML
    <section class="container hero">
      <div class="hero-content">
        <p class="eyebrow">{$h_eyebrow}</p>
        <h1>{$h_h1}</h1>
        <p class="subtitle">{$h_sub}</p>
        <div class="hero-buttons">
          <a href="#kontakt" class="btn btn-primary">{$h_btn1}</a>
          <a href="#realizace" class="btn btn-secondary">{$h_btn2}</a>
        </div>
      </div>
      <div class="hero-image"><img src="{$h_img}" alt="Fotografie z huti" /></div>
    </section>
HTML;

    // 2. UVP
    $uvp_eyebrow = htmlspecialchars($data['uvp']['eyebrow'] ?? '');
    $uvp_title = htmlspecialchars($data['uvp']['title'] ?? '');
    $uvp_sub = htmlspecialchars($data['uvp']['subtitle'] ?? '');
    $uvp_cards_html = "";
    foreach (($data['uvp']['items'] ?? []) as $item) {
        $t = htmlspecialchars($item['title'] ?? '');
        $d = htmlspecialchars($item['desc'] ?? '');
        $uvp_cards_html .= "<div class='uvp-card'><h3>{$t}</h3><p>{$d}</p></div>";
    }

    $uvpSection = <<<HTML
    <section id="uvp" class="uvp-section container">
      <div class="section-title">
        <p class="eyebrow">{$uvp_eyebrow}</p>
        <h2>{$uvp_title}</h2>
        <p>{$uvp_sub}</p>
      </div>
      <div class="uvp-grid">{$uvp_cards_html}</div>
    </section>
HTML;

    // 3. Master
    $m_eyebrow = htmlspecialchars($data['master']['eyebrow'] ?? '');
    $m_name = htmlspecialchars($data['master']['name'] ?? '');
    $m_title = htmlspecialchars($data['master']['title'] ?? '');
    $m_bio = htmlspecialchars($data['master']['bio'] ?? '');
    $m_quote = htmlspecialchars($data['master']['quote'] ?? '');
    $m_bio2 = htmlspecialchars($data['master']['bio2'] ?? '');
    $m_img = htmlspecialchars(fixImgUrl($data['master']['image'] ?? ''));

    $masterSection = <<<HTML
    <section id="mistr" class="master-section">
      <div class="container master-grid">
        <div class="master-photo"><img src="{$m_img}" alt="Mistr {$m_name}" /></div>
        <div class="master-info">
          <p class="eyebrow">{$m_eyebrow}</p>
          <h3>{$m_name}</h3>
          <p class="master-title">{$m_title}</p>
          <p>{$m_bio}</p>
          <div class="quote-box">{$m_quote}</div>
          <p>{$m_bio2}</p>
        </div>
      </div>
    </section>
HTML;

    // 4. Outcomes
    $o_eyebrow = htmlspecialchars($data['outcomes']['eyebrow'] ?? '');
    $o_title = htmlspecialchars($data['outcomes']['title'] ?? '');
    $o_sub = htmlspecialchars($data['outcomes']['subtitle'] ?? '');
    $outcomes_html = "";
    foreach (($data['outcomes']['items'] ?? []) as $item) {
        $icon = htmlspecialchars($item['icon'] ?? '🔥');
        $t = htmlspecialchars($item['title'] ?? '');
        $d = htmlspecialchars($item['desc'] ?? '');
        $outcomes_html .= "<div class='outcome-item'><h4>{$icon} {$t}</h4><p>{$d}</p></div>";
    }

    $outcomesSection = <<<HTML
    <section id="co-se-naucis" class="outcomes-section container">
      <div class="section-title">
        <p class="eyebrow">{$o_eyebrow}</p>
        <h2>{$o_title}</h2>
        <p>{$o_sub}</p>
      </div>
      <div class="outcomes-grid">{$outcomes_html}</div>
    </section>
HTML;

    // 5. Timeline
    $t_eyebrow = htmlspecialchars($data['timeline']['eyebrow'] ?? '');
    $t_title = htmlspecialchars($data['timeline']['title'] ?? '');
    $t_sub = htmlspecialchars($data['timeline']['subtitle'] ?? '');
    $t_disc = htmlspecialchars($data['timeline']['disclaimer'] ?? '');
    $timeline_html = "";
    foreach (($data['timeline']['steps'] ?? []) as $step) {
        $num = htmlspecialchars($step['num'] ?? '01');
        $st = htmlspecialchars($step['title'] ?? '');
        $sd = htmlspecialchars($step['desc'] ?? '');
        $timeline_html .= "<div class='timeline-step'><div class='step-number'>{$num}</div><div class='step-content'><h4>{$st}</h4><p>{$sd}</p></div></div>";
    }

    $timelineSection = <<<HTML
    <section id="jak-to-probiha" class="timeline-section">
      <div class="container">
        <div class="section-title">
          <p class="eyebrow">{$t_eyebrow}</p>
          <h2>{$t_title}</h2>
          <p>{$t_sub}</p>
        </div>
        <div class="timeline">{$timeline_html}</div>
        <p class="disclaimer-box">{$t_disc}</p>
      </div>
    </section>
HTML;

    // 6. Portfolio
    $p_eyebrow = htmlspecialchars($data['portfolio']['eyebrow'] ?? '');
    $p_title = htmlspecialchars($data['portfolio']['title'] ?? '');
    $p_sub = htmlspecialchars($data['portfolio']['subtitle'] ?? '');
    $portfolio_html = "";
    foreach (($data['portfolio']['items'] ?? []) as $item) {
        $pimg = htmlspecialchars(fixImgUrl($item['image'] ?? ''));
        $pcap = htmlspecialchars($item['caption'] ?? '');
        $portfolio_html .= "<div class='portfolio-item'><img src='{$pimg}' alt='{$pcap}' /><div class='portfolio-caption'>{$pcap}</div></div>";
    }

    $portfolioSection = <<<HTML
    <section id="realizace" class="portfolio-section container">
      <div class="section-title">
        <p class="eyebrow">{$p_eyebrow}</p>
        <h2>{$p_title}</h2>
        <p>{$p_sub}</p>
      </div>
      <div class="portfolio-grid">{$portfolio_html}</div>
    </section>
HTML;

    // 7. Testimonials
    $ts_eyebrow = htmlspecialchars($data['testimonials']['eyebrow'] ?? '');
    $ts_title = htmlspecialchars($data['testimonials']['title'] ?? '');
    $ts_sub = htmlspecialchars($data['testimonials']['subtitle'] ?? '');
    $testimonials_html = "";
    foreach (($data['testimonials']['items'] ?? []) as $item) {
        $quote = htmlspecialchars($item['quote'] ?? '');
        $name = htmlspecialchars($item['name'] ?? '');
        $role = htmlspecialchars($item['role'] ?? '');
        $testimonials_html .= "<div class='testimonial-card'><p class='quote-text'>{$quote}</p><div class='author-info'><div><strong>{$name}</strong><span>{$role}</span></div></div></div>";
    }

    $testimonialsSection = <<<HTML
    <section id="reference" class="testimonials-section">
      <div class="container">
        <div class="section-title">
          <p class="eyebrow">{$ts_eyebrow}</p>
          <h2>{$ts_title}</h2>
          <p>{$ts_sub}</p>
        </div>
        <div class="testimonials-grid">{$testimonials_html}</div>
      </div>
    </section>
HTML;

    // 8. FAQ
    $f_eyebrow = htmlspecialchars($data['faq']['eyebrow'] ?? '');
    $f_title = htmlspecialchars($data['faq']['title'] ?? '');
    $f_sub = htmlspecialchars($data['faq']['subtitle'] ?? '');
    $faq_html = "";
    foreach (($data['faq']['items'] ?? []) as $item) {
        $q = htmlspecialchars($item['q'] ?? '');
        $a = htmlspecialchars($item['a'] ?? '');
        $faq_html .= "<details><summary>{$q}</summary><p>{$a}</p></details>";
    }

    $faqSection = <<<HTML
    <section id="faq" class="faq-section container">
      <div class="section-title">
        <p class="eyebrow">{$f_eyebrow}</p>
        <h2>{$f_title}</h2>
        <p>{$f_sub}</p>
      </div>
      <div class="faq-list">{$faq_html}</div>
    </section>
HTML;

    // 9. Primary CTA Box
    $cta_title = htmlspecialchars($data['cta']['title'] ?? '');
    $cta_text = htmlspecialchars($data['cta']['text'] ?? '');
    $cta_btn = htmlspecialchars($data['cta']['btn'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ');

    $ctaSection = <<<HTML
    <div class="container">
      <div class="primary-cta-box">
        <h2>{$cta_title}</h2>
        <p>{$cta_text}</p>
        <a href="#kontakt" class="btn btn-primary">{$cta_btn}</a>
      </div>
    </div>
HTML;

    // 10. Contact
    $c_eyebrow = htmlspecialchars($data['contact']['eyebrow'] ?? '');
    $c_title = htmlspecialchars($data['contact']['title'] ?? '');
    $c_sub = htmlspecialchars($data['contact']['subtitle'] ?? '');
    $wa_num = htmlspecialchars($data['contact']['whatsapp_num'] ?? '420602763599');
    $wa_msg = urlencode($data['contact']['whatsapp_msg'] ?? 'Dobrý den, mám zájem o učednictví.');
    $ig_link = htmlspecialchars($data['contact']['instagram_link'] ?? 'https://instagram.com');
    $phone_parent = htmlspecialchars($data['contact']['phone_parent'] ?? '+420 602 763 599');

    $contactSection = <<<HTML
    <section id="kontakt" class="contact-section container">
      <div class="section-title">
        <p class="eyebrow">{$c_eyebrow}</p>
        <h2>{$c_title}</h2>
        <p>{$c_sub}</p>
      </div>
      <div class="contact-grid">
        <div class="contact-card">
          <h3>💬 Rychlá zpráva (pro zájemce)</h3>
          <div style="display:flex; flex-direction:column; gap:1rem; margin-top:1.5rem;">
            <a href="https://wa.me/{$wa_num}?text={$wa_msg}" class="btn-wa" target="_blank">NAPSAT NA WHATSAPP (+{$wa_num})</a>
            <a href="{$ig_link}" class="btn-ig" target="_blank">NAPSAT NA INSTAGRAM</a>
          </div>
          <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--color-glass-border);">
            <h4 style="font-family:var(--font-heading); font-size:1.25rem;">👨‍👩‍👦 Jste rodič?</h4>
            <p style="font-size:0.95rem; color:var(--text-muted);">Pokud se ptáte za svého syna nebo dceru, rádi vám vše vysvětlíme.</p>
            <a href="tel:{$wa_num}" class="btn btn-secondary" style="margin-top:1rem; width:100%;">CHCI SI ZAVOLAT ({$phone_parent})</a>
          </div>
        </div>
        <div class="contact-card">
          <h3>📝 Kontaktní formulář</h3>
          <form onsubmit="event.preventDefault(); alert('Děkujeme! Zprávu jsme přijali.');">
            <div class="form-group"><label>Jméno</label><input type="text" class="form-control" placeholder="Jan Novák" required /></div>
            <div class="form-group"><label>Jsem:</label><select class="form-control"><option>Budoucí učedník</option><option>Rodič</option></select></div>
            <div class="form-group"><label>Kontakt</label><input type="text" class="form-control" placeholder="Telefon nebo e-mail" required /></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">ODESLAT DOTAZ</button>
          </form>
        </div>
      </div>
    </section>
HTML;

    // Map section keys to HTML blocks
    $allSections = [
        'hero' => $heroSection,
        'uvp' => $uvpSection,
        'master' => $masterSection,
        'outcomes' => $outcomesSection,
        'timeline' => $timelineSection,
        'portfolio' => $portfolioSection,
        'testimonials' => $testimonialsSection,
        'faq' => $faqSection,
        'cta' => $ctaSection,
        'contact' => $contactSection
    ];

    // Default order
    $defaultOrder = ['hero', 'uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq', 'cta', 'contact'];
    $order = $data['section_order'] ?? $defaultOrder;

    $renderedMain = "";
    foreach ($order as $secKey) {
        if (isset($allSections[$secKey])) {
            $renderedMain .= $allSections[$secKey] . "\n";
        }
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{$metaTitle}</title>
  <meta name="description" content="{$metaDesc}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --color-primary: #0B0A08;
      --color-dark: #17120E;
      --color-cream: #F4EFE7;
      --color-white: #FFFDF8;
      --color-accent: #E87516;
      --color-glass: rgba(255, 255, 255, 0.05);
      --color-glass-border: rgba(255, 255, 255, 0.1);
      --text: #F4EFE7;
      --text-muted: #A39B8E;
      --font-heading: 'Cormorant Garamond', serif;
      --font-body: 'Inter', sans-serif;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { font-family: var(--font-body); background: linear-gradient(135deg, var(--color-primary), var(--color-dark)); color: var(--text); line-height: 1.6; font-size: 15px; }
    @media (min-width: 768px) { body { font-size: 17px; } }

    .container { max-width: 1100px; margin: auto; padding: 0 1.25rem; }

    /* Sticky Header */
    .site-header { position: sticky; top: 0; background: rgba(11, 10, 8, 0.92); backdrop-filter: blur(10px); z-index: 100; border-bottom: 1px solid rgba(232, 117, 22, 0.2); }
    .nav-container { display: flex; align-items: center; justify-content: space-between; padding: 0.8rem 1.25rem; max-width: 1100px; margin: auto; }
    .logo { font-family: var(--font-heading); font-size: clamp(1.1rem, 2.5vw, 1.4rem); font-weight: 700; color: var(--color-accent); text-decoration: none; letter-spacing: 1px; }
    .nav-menu { list-style: none; display: flex; gap: 1.2rem; align-items: center; }
    .nav-menu a { color: var(--text); text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: color .2s; }
    .nav-menu a:hover { color: var(--color-accent); }
    .cta-nav { background: var(--color-accent); color: #fff !important; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 700; }

    /* Balanced & Compact Headings Across ALL Sections */
    h1, h2, h3, h4 {
      line-height: 1.18;
      letter-spacing: -0.015em;
      overflow-wrap: break-word;
      word-break: break-word;
      margin-bottom: 0.4rem;
    }

    .hero { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 2.5rem; padding: 3.5rem 0 2.5rem; min-height: 60vh; }
    .hero-content { max-width: 580px; }
    .eyebrow { text-transform: uppercase; font-size: 0.78rem; letter-spacing: 2px; color: var(--color-accent); font-weight: 700; margin-bottom: 0.6rem; }
    .hero h1 { font-family: var(--font-heading); font-size: clamp(1.8rem, 4.5vw, 3rem); color: var(--color-white); margin-bottom: 0.8rem; }
    .subtitle { font-size: clamp(0.95rem, 2vw, 1.1rem); color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; }
    .hero-buttons { display: flex; gap: 0.8rem; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 1.4rem; border-radius: 6px; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: all .2s; min-height: 44px; cursor: pointer; border: none; }
    .btn-primary { background: var(--color-accent); color: #fff; }
    .btn-secondary { background: var(--color-glass); color: var(--text); border: 1px solid var(--color-accent); }
    .hero-image img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); border: 1px solid var(--color-glass-border); }

    .section-title { text-align: center; margin-bottom: 2rem; }
    .section-title { text-align: center; margin-bottom: 2.5rem; }
    .section-title h2 {
      font-family: var(--font-heading);
      font-size: clamp(1.6rem, 3.5vw, 2.4rem);
      color: var(--color-white);
      margin-bottom: 0.5rem;
    }
    .primary-cta-box h2 { font-family: var(--font-heading); font-size: clamp(1.6rem, 3.5vw, 2.4rem); color: var(--color-white); margin-bottom: 1rem; }
    .contact-card h3 { font-family: var(--font-heading); font-size: clamp(1.3rem, 2.8vw, 1.8rem); color: var(--color-white); margin-bottom: 1rem; }
    .master-info h3 { font-family: var(--font-heading); font-size: clamp(1.8rem, 4vw, 2.8rem); color: var(--color-white); margin-bottom: 0.5rem; }
    .section-title p { color: var(--text-muted); font-size: clamp(0.95rem, 2vw, 1.1rem); max-width: 680px; margin: auto; }

    .uvp-section { padding: 3.5rem 0; }
    .uvp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .uvp-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.5rem; }
    .uvp-card h3 { font-family: var(--font-heading); font-size: clamp(1.2rem, 2.5vw, 1.5rem); color: var(--color-accent); margin-bottom: 0.4rem; }

    .master-section { padding: 3.5rem 0; background: rgba(0,0,0,0.3); }
    .master-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 2.5rem; align-items: center; }
    .master-photo img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 14px; border: 1px solid var(--color-accent); }
    .master-title { color: var(--color-accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; font-size: 0.8rem; }
    .quote-box { font-family: var(--font-heading); font-style: italic; font-size: clamp(1.05rem, 2.5vw, 1.25rem); color: var(--color-cream); border-left: 3px solid var(--color-accent); padding-left: 0.9rem; margin: 1rem 0; line-height: 1.3; }

    .outcomes-section { padding: 3.5rem 0; }
    .outcomes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .outcome-item { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.25rem; }
    .outcome-item h4, .step-content h4 { font-family: var(--font-heading); font-size: clamp(1.1rem, 2.2vw, 1.4rem); color: var(--color-white); margin-bottom: 0.3rem; }

    .timeline-section { padding: 3.5rem 0; background: rgba(0,0,0,0.2); }
    .timeline { display: flex; flex-direction: column; gap: 1rem; max-width: 850px; margin: auto; }
    .timeline-step { display: grid; grid-template-columns: 50px 1fr; gap: 1rem; background: var(--color-glass); border: 1px solid var(--color-glass-border); border-left: 4px solid var(--color-accent); border-radius: 8px; padding: 1rem 1.2rem; align-items: center; }
    .step-number { font-family: var(--font-heading); font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; color: var(--color-accent); text-align: center; }
    .disclaimer-box { text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.5rem; font-style: italic; }

    .portfolio-section { padding: 3.5rem 0; }
    .portfolio-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
    .portfolio-item { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--color-glass-border); }
    .portfolio-item img { width: 100%; height: 220px; object-fit: cover; display: block; }
    .portfolio-caption { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); padding: 1rem 0.8rem 0.6rem; color: var(--color-white); font-weight: 600; font-size: 0.85rem; }

    .testimonials-section { padding: 3.5rem 0; background: rgba(0,0,0,0.3); }
    .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .testimonial-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.5rem; }
    .quote-text { font-style: italic; margin-bottom: 0.8rem; color: var(--color-cream); font-size: 0.9rem; }
    .author-info strong { color: var(--color-accent); display: block; font-size: 0.9rem; }

    .faq-section { padding: 3.5rem 0; }
    .faq-list { max-width: 800px; margin: auto; display: flex; flex-direction: column; gap: 0.7rem; }
    details { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 8px; padding: 0.9rem 1.1rem; }
    details[open] { border-color: var(--color-accent); background: rgba(232, 117, 22, 0.05); }
    summary { font-family: var(--font-heading); font-size: clamp(1.1rem, 2.2vw, 1.4rem); font-weight: 600; color: var(--color-white); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; line-height: 1.15; }
    summary::after { content: '+'; font-size: 1.3rem; color: var(--color-accent); margin-left: 0.5rem; }
    details[open] summary::after { content: '−'; }

    .primary-cta-box { background: linear-gradient(135deg, rgba(232,117,22,0.15), rgba(17,14,11,0.9)); border: 2px solid var(--color-accent); border-radius: 16px; padding: 2.5rem 1.2rem; text-align: center; margin: 2.5rem auto; max-width: 850px; }
    .primary-cta-box p { color: var(--text-muted); font-size: clamp(0.9rem, 1.8vw, 1.05rem); max-width: 620px; margin: 0 auto 1.2rem; }

    .contact-section { padding: 3rem 0 4.5rem; }
    .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
    .contact-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 14px; padding: 1.8rem; }
    .btn-wa { background: #25D366; color: #fff; text-decoration: none; font-weight: 700; border-radius: 6px; padding: 0.8rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem; font-size: 0.85rem; }
    .btn-ig { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: #fff; text-decoration: none; font-weight: 700; border-radius: 6px; padding: 0.8rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem; font-size: 0.85rem; }
    .form-group { margin-bottom: 0.9rem; }
    .form-control { width: 100%; padding: 0.7rem 0.85rem; background: rgba(0,0,0,0.5); border: 1px solid var(--color-glass-border); border-radius: 6px; color: #fff; font-size: 0.9rem; }

    .mobile-sticky-cta { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: rgba(11,10,8,0.95); border-top: 1px solid var(--color-accent); padding: 0.7rem 1rem; z-index: 999; }
    footer { border-top: 1px solid var(--color-glass-border); padding: 2rem 0; text-align: center; color: var(--text-muted); font-size: 0.8rem; }

    /* Mobile Responsive Compact View */
    @media (max-width: 900px) {
      .hero { grid-template-columns: 1fr; text-align: center; gap: 1.5rem; padding: 2rem 0 1.5rem; min-height: auto; }
      .hero-content { max-width: 100%; }
      .hero-buttons { justify-content: center; }
      .master-grid { grid-template-columns: 1fr; gap: 1.5rem; }
      .contact-grid { grid-template-columns: 1fr; gap: 1.5rem; }
      .nav-menu { display: none; }
      .mobile-sticky-cta { display: block; }
      .uvp-section, .master-section, .outcomes-section, .timeline-section, .portfolio-section, .testimonials-section, .faq-section { padding: 2.2rem 0; }
      .section-title { margin-bottom: 1.5rem; }
      .primary-cta-box { padding: 1.8rem 1rem; margin: 1.8rem auto; }
      .timeline-step { grid-template-columns: 40px 1fr; gap: 0.8rem; padding: 0.9rem 1rem; }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="nav-container">
      <a href="#" class="logo">SVOBODNÉ CECHY</a>
      <ul class="nav-menu">
        <li><a href="#uvp">Proč?</a></li>
        <li><a href="#mistr">O mistrovi</a></li>
        <li><a href="#co-se-naucis">Co se naučíš</a></li>
        <li><a href="#jak-to-probiha">Jak to probíhá</a></li>
        <li><a href="#realizace">Realizace</a></li>
        <li><a href="#reference">Reference</a></li>
        <li><a href="#faq">FAQ</a></li>
        <li><a href="#kontakt" class="cta-nav">ZJISTIT, JESTLI JE TO PRO MĚ</a></li>
      </ul>
    </div>
  </header>

  <main>
    {$renderedMain}
  </main>

  <div class="mobile-sticky-cta"><a href="#kontakt" class="btn btn-primary" style="width:100%;">ZJISTIT, JESTLI JE TO PRO MĚ</a></div>
  <footer><div class="container"><p>© 2026 Svobodné Cechy. Všechna práva vyhrazena.</p></div></footer>
</body>
</html>
HTML;
}

$message = "";
$messageType = "success";

// Handle Saving Visual Form Data
if (isset($_POST['save_sections_form'])) {
    $slug = basename($_POST['edit_slug']);
    $jsonPath = $dir . "/" . $slug . ".json";
    $htmlPath = $dir . "/" . $slug . ".html";

    $formData = json_decode($_POST['sections_json_data'], true);
    if (is_array($formData)) {
        file_put_contents($jsonPath, json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $generatedHtml = renderLandingPageHtml($formData);
        file_put_contents($htmlPath, $generatedHtml);
        $message = "Všechny sekce pro '{$slug}' byly úspěšně uloženy s kompaktní typografií!";
    } else {
        $message = "Chyba při zpracování dat sekcí.";
        $messageType = "error";
    }
}

// Handle Deleting Pages
if (isset($_GET['delete'])) {
    $deleteSlug = str_replace('.html', '', basename($_GET['delete']));
    @unlink($dir . "/" . $deleteSlug . ".html");
    @unlink($dir . "/" . $deleteSlug . ".json");
    header("Location: landing_pages.php?msg=deleted");
    exit;
}

// Handle Creating New Page
if (isset($_POST['create_new'])) {
    $name = trim($_POST['master_name']);
    $rawSlug = trim($_POST['slug']);
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $rawSlug)));
    if (empty($slug)) { $slug = "landing-" . time(); }

    $defaultJson = file_exists($dir . "/jiri-pacinek.json") ? file_get_contents($dir . "/jiri-pacinek.json") : "{}";
    $data = json_decode($defaultJson, true);
    $data['slug'] = $slug;
    $data['master_name'] = $name;
    $data['master']['name'] = mb_strtoupper($name);

    file_put_contents($dir . "/" . $slug . ".json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($dir . "/" . $slug . ".html", renderLandingPageHtml($data));

    header("Location: landing_pages.php?edit={$slug}&created=1");
    exit;
}

$editingSlug = isset($_GET['edit']) ? str_replace('.html', '', basename($_GET['edit'])) : null;
$editingData = null;
if ($editingSlug) {
    $jsonFile = $dir . "/" . $editingSlug . ".json";
    if (file_exists($jsonFile)) {
        $editingData = json_decode(file_get_contents($jsonFile), true);
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vizuální Editor Landing Pages – Svobodné Cechy</title>
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

    .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
    .msg.success { background: rgba(37, 211, 102, 0.15); border: 1px solid #25D366; color: #25D366; }

    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    .card h2 { font-size: 1.3rem; color: #fff; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
    td { padding: 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .badge-file { font-family: monospace; background: rgba(255,255,255,0.08); padding: 0.3rem 0.6rem; border-radius: 4px; color: #fff; }

    /* Actions */
    .btn-action { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer; transition: all .2s; }
    .btn-edit { background: #3b82f6; color: #fff; }
    .btn-edit:hover { background: #2563eb; }
    .btn-view { background: var(--accent); color: #fff; }
    .btn-view:hover { background: var(--accent-hover); }
    .btn-copy { background: rgba(255,255,255,0.1); color: var(--text); border: 1px solid var(--border); }
    .btn-delete { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

    /* Visual Editor Layout */
    .editor-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; }
    @media (max-width: 1024px) { .editor-layout { grid-template-columns: 1fr; } }

    /* Section Tabs */
    .section-tab-nav { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.8rem; }
    .tab-btn { background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid var(--border); padding: 0.6rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; }
    .tab-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
    .tab-btn.special-order { background: rgba(232,117,22,0.2); color: #fff; border-color: var(--accent); }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--accent); margin-bottom: 0.3rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 6px; color: #fff; font-size: 0.95rem; }
    textarea.form-control { min-height: 80px; resize: vertical; }

    /* Upload Box UI */
    .upload-row { display: flex; gap: 0.8rem; align-items: center; margin-top: 0.4rem; }
    .upload-btn { background: rgba(255,255,255,0.1); color: #fff; border: 1px dashed var(--accent); padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
    .upload-btn:hover { background: rgba(232,117,22,0.2); }
    .thumb-preview { width: 60px; height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border); background: #000; flex-shrink: 0; }

    .item-card { background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .item-card h4 { font-size: 0.9rem; color: #fff; margin-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.4rem; }

    .preview-iframe { width: 100%; height: 750px; border: 1px solid var(--border); border-radius: 8px; background: #fff; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>Vizuální Editor Landing Pages</h1>
    <p class="subtitle">Upravuj obsah sekcí, nahrávej fotky a měň pořadí sekcí – s kompaktní a vyváženou typografií nadpisů!</p>

    <?php if ($message): ?>
      <div class="msg <?= $messageType ?>">✓ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($editingSlug && $editingData): ?>
      <!-- VISUAL SECTION EDITOR -->
      <div class="card" style="border-color: var(--accent);">
        <h2><i class="bi bi-sliders" style="color: var(--accent);"></i> Vizuální Úprava a Řazení Sekcí: <span style="color:var(--accent);"><?= htmlspecialchars($editingSlug) ?>.html</span></h2>
        
        <form method="post" action="landing_pages.php?edit=<?= urlencode($editingSlug) ?>" id="visualEditorForm">
          <input type="hidden" name="edit_slug" value="<?= htmlspecialchars($editingSlug) ?>" />
          <input type="hidden" name="sections_json_data" id="sections_json_data" />

          <div class="editor-layout">
            <!-- LEFT: SECTION TABS & FORM FIELDS -->
            <div>
              <div class="section-tab-nav">
                <button type="button" class="tab-btn special-order active" onclick="showTab('tab-order')"><i class="bi bi-arrow-down-up"></i> ⚙️ POŘADÍ SEKCÍ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-hero')">1. HERO</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-uvp')">2. UVP</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-master')">3. MISTR</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-outcomes')">4. CO SE NAUČÍŠ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-timeline')">5. POSTUP</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-portfolio')">6. GALERIE FOTEK</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-testimonials')">7. REFERENCE</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-faq')">8. FAQ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-contact')">9. KONTAKT</button>
              </div>

              <!-- TAB ORDER: SECTIONS REORDERING -->
              <div id="tab-order" class="tab-content active">
                <h3 style="color:#fff; margin-bottom:1rem;">⚙️ Měnění Pořadí Sekcí na Stránce</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                  Kliknutím na tlačítka <strong>Nahoru ⬆️</strong> nebo <strong>Dolů ⬇️</strong> posuneš sekci na požadované místo.
                </p>
                <div id="order_list_container"></div>
              </div>

              <!-- TAB 1: HERO -->
              <div id="tab-hero" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🚀 Sekce 1: HERO (Úvodní obrazovka)</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text nad nadpisem)</label>
                  <input type="text" class="form-control" id="h_eyebrow" value="<?= htmlspecialchars($editingData['hero']['eyebrow'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Hlavní Nadpis (H1)</label>
                  <input type="text" class="form-control" id="h_h1" value="<?= htmlspecialchars($editingData['hero']['h1'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul (Dlouhý popis nabídky)</label>
                  <textarea class="form-control" id="h_sub"><?= htmlspecialchars($editingData['hero']['subtitle'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Text hlavního tlačítka (CTA)</label>
                  <input type="text" class="form-control" id="h_btn1" value="<?= htmlspecialchars($editingData['hero']['btn_primary'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Fotka v huti (Hero Obrázek)</label>
                  <div class="upload-row">
                    <img src="<?= htmlspecialchars(fixAdminPreviewUrl($editingData['hero']['image'] ?? '')) ?>" id="prev_h_img" class="thumb-preview" />
                    <input type="text" class="form-control" id="h_img" value="<?= htmlspecialchars($editingData['hero']['image'] ?? '') ?>" />
                    <label class="upload-btn">
                      <i class="bi bi-upload"></i> Nahrát fotku
                      <input type="file" accept="image/*" style="display:none;" onchange="uploadImage(this, 'h_img', 'prev_h_img')" />
                    </label>
                  </div>
                </div>
              </div>

              <!-- TAB 2: UVP -->
              <div id="tab-uvp" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">💎 Sekce 2: UVP (Proč toto učednictví)</h3>
                <div class="form-group">
                  <label>Nadpis sekce UVP</label>
                  <input type="text" class="form-control" id="uvp_title" value="<?= htmlspecialchars($editingData['uvp']['title'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce</label>
                  <input type="text" class="form-control" id="uvp_sub" value="<?= htmlspecialchars($editingData['uvp']['subtitle'] ?? '') ?>" />
                </div>
                <h4 style="color:#fff; margin:1rem 0 0.5rem;">Argumentační karty:</h4>
                <div id="uvp_cards_container">
                  <?php foreach (($editingData['uvp']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card uvp-item-box">
                      <h4>Karta #<?= $idx+1 ?></h4>
                      <div class="form-group"><label>Titulek karty</label><input type="text" class="form-control uvp-item-title" value="<?= htmlspecialchars($item['title']) ?>" /></div>
                      <div class="form-group"><label>Popis karty</label><textarea class="form-control uvp-item-desc"><?= htmlspecialchars($item['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 3: MISTR -->
              <div id="tab-master" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">👑 Sekce 3: O mistrovi</h3>
                <div class="form-group">
                  <label>Jméno mistra</label>
                  <input type="text" class="form-control" id="m_name" value="<?= htmlspecialchars($editingData['master']['name'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Titul mistra & Sklárna</label>
                  <input type="text" class="form-control" id="m_title" value="<?= htmlspecialchars($editingData['master']['title'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Příběh mistra (Popis)</label>
                  <textarea class="form-control" id="m_bio"><?= htmlspecialchars($editingData['master']['bio'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Citát mistra</label>
                  <textarea class="form-control" id="m_quote"><?= htmlspecialchars($editingData['master']['quote'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Fotka mistra</label>
                  <div class="upload-row">
                    <img src="<?= htmlspecialchars(fixAdminPreviewUrl($editingData['master']['image'] ?? '')) ?>" id="prev_m_img" class="thumb-preview" />
                    <input type="text" class="form-control" id="m_img" value="<?= htmlspecialchars($editingData['master']['image'] ?? '') ?>" />
                    <label class="upload-btn">
                      <i class="bi bi-upload"></i> Nahrát fotku mistra
                      <input type="file" accept="image/*" style="display:none;" onchange="uploadImage(this, 'm_img', 'prev_m_img')" />
                    </label>
                  </div>
                </div>
              </div>

              <!-- TAB 4: CO SE NAUČÍŠ -->
              <div id="tab-outcomes" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">📚 Sekce 4: Co se učedník naučí</h3>
                <div class="form-group">
                  <label>Nadpis sekce</label>
                  <input type="text" class="form-control" id="o_title" value="<?= htmlspecialchars($editingData['outcomes']['title'] ?? '') ?>" />
                </div>
                <div id="outcomes_container">
                  <?php foreach (($editingData['outcomes']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card outcome-item-box">
                      <h4>Dovednost #<?= $idx+1 ?></h4>
                      <div class="form-group"><label>Ikona (Emoji)</label><input type="text" class="form-control outcome-icon" value="<?= htmlspecialchars($item['icon']) ?>" /></div>
                      <div class="form-group"><label>Název dovednosti</label><input type="text" class="form-control outcome-title" value="<?= htmlspecialchars($item['title']) ?>" /></div>
                      <div class="form-group"><label>Popis dovednosti</label><textarea class="form-control outcome-desc"><?= htmlspecialchars($item['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 5: POSTUP -->
              <div id="tab-timeline" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🗺️ Sekce 5: Jak probíhá cesta k učednictví</h3>
                <div id="timeline_container">
                  <?php foreach (($editingData['timeline']['steps'] ?? []) as $idx => $step): ?>
                    <div class="item-card timeline-step-box">
                      <h4>Krok <?= htmlspecialchars($step['num']) ?></h4>
                      <div class="form-group"><label>Číslo</label><input type="text" class="form-control step-num" value="<?= htmlspecialchars($step['num']) ?>" /></div>
                      <div class="form-group"><label>Název kroku</label><input type="text" class="form-control step-title" value="<?= htmlspecialchars($step['title']) ?>" /></div>
                      <div class="form-group"><label>Popis kroku</label><textarea class="form-control step-desc"><?= htmlspecialchars($step['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 6: GALERIE -->
              <div id="tab-portfolio" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🖼️ Sekce 6: Galerie a Fotky z huti</h3>
                <div id="portfolio_container">
                  <?php foreach (($editingData['portfolio']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card portfolio-item-box">
                      <h4>Fotka #<?= $idx+1 ?></h4>
                      <div class="form-group">
                        <label>Fotka v galerii</label>
                        <div class="upload-row">
                          <img src="<?= htmlspecialchars(fixAdminPreviewUrl($item['image'] ?? '')) ?>" id="prev_p_img_<?= $idx ?>" class="thumb-preview" />
                          <input type="text" class="form-control p-img" id="p_img_<?= $idx ?>" value="<?= htmlspecialchars($item['image']) ?>" />
                          <label class="upload-btn">
                            <i class="bi bi-upload"></i> Nahrát fotku
                            <input type="file" accept="image/*" style="display:none;" onchange="uploadImage(this, 'p_img_<?= $idx ?>', 'prev_p_img_<?= $idx ?>')" />
                          </label>
                        </div>
                      </div>
                      <div class="form-group"><label>Popisek pod fotkou</label><input type="text" class="form-control p-cap" value="<?= htmlspecialchars($item['caption']) ?>" /></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 7: REFERENCE -->
              <div id="tab-testimonials" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">💬 Sekce 7: Reference</h3>
                <div id="testimonials_container">
                  <?php foreach (($editingData['testimonials']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card testimonial-item-box">
                      <h4>Reference #<?= $idx+1 ?></h4>
                      <div class="form-group"><label>Citace</label><textarea class="form-control ts-quote"><?= htmlspecialchars($item['quote']) ?></textarea></div>
                      <div class="form-group"><label>Jméno</label><input type="text" class="form-control ts-name" value="<?= htmlspecialchars($item['name']) ?>" /></div>
                      <div class="form-group"><label>Role / Vztah k dílně</label><input type="text" class="form-control ts-role" value="<?= htmlspecialchars($item['role']) ?>" /></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 8: FAQ -->
              <div id="tab-faq" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">❓ Sekce 8: Časté otázky</h3>
                <div id="faq_container">
                  <?php foreach (($editingData['faq']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card faq-item-box">
                      <h4>Otázka #<?= $idx+1 ?></h4>
                      <div class="form-group"><label>Otázka</label><input type="text" class="form-control faq-q" value="<?= htmlspecialchars($item['q']) ?>" /></div>
                      <div class="form-group"><label>Odpověď</label><textarea class="form-control faq-a"><?= htmlspecialchars($item['a']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- TAB 9: KONTAKT -->
              <div id="tab-contact" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">📱 Sekce 9: Kontakt a WhatsApp</h3>
                <div class="form-group">
                  <label>WhatsApp číslo (např. 420602763599 bez mezery)</label>
                  <input type="text" class="form-control" id="c_wa_num" value="<?= htmlspecialchars($editingData['contact']['whatsapp_num'] ?? '') ?>" />
                </div>
                <div class="form-group">
                  <label>Předvyplněná zpráva pro zájemce na WhatsApp</label>
                  <textarea class="form-control" id="c_wa_msg"><?= htmlspecialchars($editingData['contact']['whatsapp_msg'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Telefon pro rodiče</label>
                  <input type="text" class="form-control" id="c_phone" value="<?= htmlspecialchars($editingData['contact']['phone_parent'] ?? '') ?>" />
                </div>
              </div>

              <div style="margin-top: 2rem;">
                <button type="submit" name="save_sections_form" onclick="prepareJsonData()" class="btn-action btn-view" style="font-size: 1rem; padding: 0.8rem 2rem;">
                  <i class="bi bi-check-circle-fill"></i> Uložit všechny sekce, fotky a pořadí
                </button>
                <a href="landing_pages.php" class="btn-action btn-copy" style="margin-left: 1rem;">Zavřít editor</a>
              </div>
            </div>

            <!-- RIGHT: LIVE PREVIEW IFRAME -->
            <div>
              <h3 style="color:#fff; margin-bottom: 0.5rem;"><i class="bi bi-eye"></i> Živý Náhled Stránky</h3>
              <iframe id="livePreviewFrame" class="preview-iframe" src="landing_pages/<?= htmlspecialchars($editingSlug) ?>.html"></iframe>
            </div>
          </div>
        </form>
      </div>

      <script>
        let currentSectionOrder = <?= json_encode($editingData['section_order'] ?? ['hero', 'uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq', 'cta', 'contact']) ?>;

        const sectionLabels = {
          'hero': '🚀 HERO (Úvodní obrazovka)',
          'uvp': '💎 UVP (Proč toto učednictví)',
          'master': '👑 MISTR (O mistrovi)',
          'outcomes': '📚 CO SE NAUČÍŠ',
          'timeline': '🗺️ POSTUP (Timeline 5 kroků)',
          'portfolio': '🖼️ GALERIE FOTEK',
          'testimonials': '💬 REFERENCE',
          'faq': '❓ ČASTÉ OTÁZKY (FAQ)',
          'cta': '🎯 PRIMARY CTA BOX',
          'contact': '📱 KONTAKT A WHATSAPP'
        };

        function renderOrderList() {
          const container = document.getElementById('order_list_container');
          if (!container) return;
          container.innerHTML = '';
          currentSectionOrder.forEach((key, idx) => {
            const div = document.createElement('div');
            div.className = 'item-card';
            div.style.display = 'flex';
            div.style.justifyContent = 'space-between';
            div.style.alignItems = 'center';
            div.style.padding = '0.8rem 1.2rem';
            div.style.marginBottom = '0.6rem';
            div.innerHTML = `
              <span style="font-size:0.95rem;"><strong>${idx + 1}.</strong> ${sectionLabels[key] || key}</span>
              <div style="display:flex; gap:0.4rem;">
                <button type="button" class="btn-action btn-copy" onclick="moveSection(${idx}, -1)" ${idx === 0 ? 'disabled style="opacity:0.4;"' : ''}><i class="bi bi-arrow-up"></i> Nahoru</button>
                <button type="button" class="btn-action btn-copy" onclick="moveSection(${idx}, 1)" ${idx === currentSectionOrder.length - 1 ? 'disabled style="opacity:0.4;"' : ''}><i class="bi bi-arrow-down"></i> Dolů</button>
              </div>
            `;
            container.appendChild(div);
          });
        }

        function moveSection(index, direction) {
          const targetIndex = index + direction;
          if (targetIndex < 0 || targetIndex >= currentSectionOrder.length) return;
          const temp = currentSectionOrder[index];
          currentSectionOrder[index] = currentSectionOrder[targetIndex];
          currentSectionOrder[targetIndex] = temp;
          renderOrderList();
        }

        document.addEventListener('DOMContentLoaded', () => {
          renderOrderList();
        });

        function showTab(tabId) {
          document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
          document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
          document.getElementById(tabId).classList.add('active');
          event.target.classList.add('active');
        }

        function uploadImage(fileInput, targetInputId, previewImgId) {
          if (!fileInput.files || !fileInput.files[0]) return;
          const formData = new FormData();
          formData.append('file', fileInput.files[0]);

          fetch('landing_pages.php?action=upload', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              document.getElementById(targetInputId).value = data.url;
              if (previewImgId && document.getElementById(previewImgId)) {
                document.getElementById(previewImgId).src = data.admin_preview_url || data.url;
              }
              const iframe = document.getElementById('livePreviewFrame');
              if (iframe) { iframe.src = iframe.src; }
              alert('Fotka byla úspěšně nahrána!');
            } else {
              alert('Chyba při nahrávání: ' + (data.message || 'Neznámá chyba'));
            }
          })
          .catch(err => {
            alert('Chyba nahrávání souboru: ' + err.message);
          });
        }

        function prepareJsonData() {
          const data = {
            slug: <?= json_encode($editingSlug) ?>,
            master_name: document.getElementById('m_name').value,
            meta_title: document.getElementById('h_h1').value + ' | Svobodné Cechy',
            meta_desc: document.getElementById('h_sub').value,
            section_order: currentSectionOrder,
            hero: {
              eyebrow: document.getElementById('h_eyebrow').value,
              h1: document.getElementById('h_h1').value,
              subtitle: document.getElementById('h_sub').value,
              btn_primary: document.getElementById('h_btn1').value,
              btn_secondary: "PODÍVAT SE, JAK SE PRACUJE SE SKLEM",
              image: document.getElementById('h_img').value
            },
            uvp: {
              eyebrow: "Proč toto učednictví?",
              title: document.getElementById('uvp_title').value,
              subtitle: document.getElementById('uvp_sub').value,
              items: Array.from(document.querySelectorAll('.uvp-item-box')).map(box => ({
                title: box.querySelector('.uvp-item-title').value,
                desc: box.querySelector('.uvp-item-desc').value
              }))
            },
            master: {
              eyebrow: "KDO TĚ BUDE UČIT",
              name: document.getElementById('m_name').value,
              title: document.getElementById('m_title').value,
              bio: document.getElementById('m_bio').value,
              quote: document.getElementById('m_quote').value,
              bio2: "V dílně sází na poctivý přístup a předávání sklářského umění další generaci.",
              image: document.getElementById('m_img').value
            },
            outcomes: {
              eyebrow: "Praktické dovednosti",
              title: document.getElementById('o_title').value,
              subtitle: "Konkrétní znalosti a návyky, které si z dílny odneseš do života.",
              items: Array.from(document.querySelectorAll('.outcome-item-box')).map(box => ({
                icon: box.querySelector('.outcome-icon').value,
                title: box.querySelector('.outcome-title').value,
                desc: box.querySelector('.outcome-desc').value
              }))
            },
            timeline: {
              eyebrow: "Postup krok za krokem",
              title: "JAK PROBÍHÁ CESTA K UČEDNICTVÍ",
              subtitle: "Pět jasných kroků od prvního projevení zájmu až po začátek práce v dílně.",
              steps: Array.from(document.querySelectorAll('.timeline-step-box')).map(box => ({
                num: box.querySelector('.step-num').value,
                title: box.querySelector('.step-title').value,
                desc: box.querySelector('.step-desc').value
              })),
              disclaimer: "💡 Základní cesta vypadá takto. Konkrétní průběh se přizpůsobuje zájemci."
            },
            portfolio: {
              eyebrow: "Ukázka z prostředí",
              title: "CO VZNIKÁ V DÍLNĚ",
              subtitle: "Nahlédni do živého procesu sklářského umění.",
              items: Array.from(document.querySelectorAll('.portfolio-item-box')).map(box => ({
                image: box.querySelector('.p-img').value,
                caption: box.querySelector('.p-cap').value
              }))
            },
            testimonials: {
              eyebrow: "Zkušenosti a reference",
              title: "CO ŘÍKAJÍ LIDÉ, KTEŘÍ U TOHO BYLI",
              subtitle: "Reálné pohledy zástupců a rodin.",
              items: Array.from(document.querySelectorAll('.testimonial-item-box')).map(box => ({
                quote: box.querySelector('.ts-quote').value,
                name: box.querySelector('.ts-name').value,
                role: box.querySelector('.ts-role').value
              }))
            },
            faq: {
              eyebrow: "Odpovědi na dotazy",
              title: "ČASTÉ OTÁZKY",
              subtitle: "Vše, co tě může zajímat před prvním kontaktem.",
              items: Array.from(document.querySelectorAll('.faq-item-box')).map(box => ({
                q: box.querySelector('.faq-q').value,
                a: box.querySelector('.faq-a').value
              }))
            },
            cta: {
              title: "ZAJÍMÁ TĚ, JESTLI JE TO PRO TEBE?",
              text: "Nemusíš se hned k ničemu zavazovat. První krok je jednoduchý nezávazný rozhovor.",
              btn: document.getElementById('h_btn1').value
            },
            contact: {
              eyebrow: "První krok",
              title: "NAVÁŽEME KONTAKT",
              subtitle: "Zvol si způsob, který je pro tě nejsnadnější.",
              whatsapp_num: document.getElementById('c_wa_num').value,
              whatsapp_msg: document.getElementById('c_wa_msg').value,
              instagram_link: "https://instagram.com",
              phone_parent: document.getElementById('c_phone').value
            }
          };

          document.getElementById('sections_json_data').value = JSON.stringify(data);
        }
      </script>
    <?php endif; ?>

    <!-- LIST OF LANDING PAGES -->
    <div class="card">
      <h2><i class="bi bi-files" style="color: var(--accent);"></i> Přehled Landing Pages</h2>
      <table>
        <thead>
          <tr>
            <th>Název / Soubor</th>
            <th>Cesta k souboru (pro reklamu)</th>
            <th>Akce</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $files = array_diff(scandir($dir), ['..', '.', '.gitkeep']);
          $hasPages = false;
          foreach ($files as $file) {
            if (!str_ends_with($file, '.html')) continue;
            $hasPages = true;
            $slug = str_replace('.html', '', $file);
            $relPath = "admin/landing_pages/" . $file;
            echo "<tr>";
            echo "<td><span class='badge-file'><i class='bi bi-file-earmark-text'></i> " . htmlspecialchars($slug) . "</span></td>";
            echo "<td style='font-family:monospace; color:var(--text-muted); font-size:0.85rem;'>" . htmlspecialchars($relPath) . "</td>";
            echo "<td>";
            echo "<div style='display:flex; gap:0.5rem;'>";
            echo "<a class='btn-action btn-edit' href='landing_pages.php?edit=" . urlencode($slug) . "'><i class='bi bi-sliders'></i> Vizuálně upravit sekce, fotky & pořadí</a>";
            echo "<a class='btn-action btn-view' href='landing_pages/" . htmlspecialchars($file) . "' target='_blank'><i class='bi bi-box-arrow-up-right'></i> Zobrazit</a>";
            echo "<button class='btn-action btn-copy' onclick=\"navigator.clipboard.writeText(window.location.origin + '/" . htmlspecialchars($relPath) . "'); alert('Odkaz pro reklamu byl zkopírován!');\"><i class='bi bi-link-45deg'></i> Zkopírovat odkaz</button>";
            echo "<a class='btn-action btn-delete' href='landing_pages.php?delete=" . urlencode($file) . "' onclick=\"return confirm('Opravdu smazat stránku {$file}?');\"><i class='bi bi-trash'></i> Smazat</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
          }
          if (!$hasPages) {
            echo "<tr><td colspan='3' style='text-align:center; color:var(--text-muted); padding:2rem;'>Zatím nebyly vytvořeny žádné landing pages.</td></tr>";
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
            <label style="color:#a39b8e;">Jméno mistra / Název</label>
            <input type="text" name="master_name" class="form-control" placeholder="Např. Karel Novák" required />
          </div>
          <div>
            <label style="color:#a39b8e;">URL Slug (bez .html)</label>
            <input type="text" name="slug" class="form-control" placeholder="např. karel-novak" required />
          </div>
        </div>
        <input type="submit" name="create_new" class="btn-action btn-view" value="Vytvořit a upravit sekce" style="margin-top:1.5rem; padding:0.8rem 1.8rem;" />
      </form>
    </div>

  </div>
</body>
</html>
