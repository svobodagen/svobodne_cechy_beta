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

// Helper to convert typography steps (Stupeň 1 - Stupeň 6) to fluid CSS clamp values
function getTypoCssValues($typo) {
    $scales = [
        'hero_h1' => [
            'step-1' => 'clamp(1.5rem, 4vw, 2.2rem)',
            'step-2' => 'clamp(1.75rem, 4.5vw, 2.8rem)',
            'step-3' => 'clamp(2.0rem, 5vw, 3.4rem)',
            'step-4' => 'clamp(2.25rem, 5.5vw, 4.0rem)',
            'step-5' => 'clamp(2.5rem, 6vw, 4.6rem)',
            'step-6' => 'clamp(2.8rem, 6.5vw, 5.2rem)',
        ],
        'section_h2' => [
            'step-1' => 'clamp(1.3rem, 3vw, 1.8rem)',
            'step-2' => 'clamp(1.5rem, 3.5vw, 2.2rem)',
            'step-3' => 'clamp(1.7rem, 4vw, 2.6rem)',
            'step-4' => 'clamp(1.9rem, 4.5vw, 3.0rem)',
            'step-5' => 'clamp(2.1rem, 5vw, 3.4rem)',
            'step-6' => 'clamp(2.4rem, 5.5vw, 3.8rem)',
        ],
        'card_h3' => [
            'step-1' => 'clamp(1.05rem, 2vw, 1.2rem)',
            'step-2' => 'clamp(1.15rem, 2.2vw, 1.35rem)',
            'step-3' => 'clamp(1.25rem, 2.5vw, 1.5rem)',
            'step-4' => 'clamp(1.35rem, 2.8vw, 1.7rem)',
            'step-5' => 'clamp(1.5rem, 3vw, 1.9rem)',
            'step-6' => 'clamp(1.65rem, 3.2vw, 2.1rem)',
        ],
        'body_text' => [
            'step-minus-2' => '0.75rem',
            'step-minus-1' => '0.85rem',
            'step-1' => '0.95rem',
            'step-2' => '1rem',
            'step-3' => '1.05rem',
            'step-4' => '1.15rem',
            'step-5' => '1.25rem',
            'step-6' => '1.35rem',
        ],
        'eyebrow' => [
            'step-1' => '0.7rem',
            'step-2' => '0.78rem',
            'step-3' => '0.85rem',
            'step-4' => '0.95rem',
            'step-5' => '1.05rem',
            'step-6' => '1.15rem',
        ],
        'subtitle' => [
            'step-1' => 'clamp(0.85rem, 1.8vw, 0.95rem)',
            'step-2' => 'clamp(0.9rem, 1.9vw, 1.05rem)',
            'step-3' => 'clamp(0.95rem, 2vw, 1.1rem)',
            'step-4' => 'clamp(1.05rem, 2.2vw, 1.25rem)',
            'step-5' => 'clamp(1.15rem, 2.5vw, 1.4rem)',
            'step-6' => 'clamp(1.25rem, 2.8vw, 1.55rem)',
        ]
    ];

    $h1_step = $typo['hero_h1'] ?? 'step-3';
    $h2_step = $typo['section_h2'] ?? 'step-3';
    $h3_step = $typo['card_h3'] ?? 'step-3';
    $body_step = $typo['body_text'] ?? 'step-3';
    $eyebrow_step = $typo['eyebrow'] ?? 'step-3';
    $subtitle_step = $typo['subtitle'] ?? 'step-3';

    return [
        'h1' => $scales['hero_h1'][$h1_step] ?? $scales['hero_h1']['step-3'],
        'h2' => $scales['section_h2'][$h2_step] ?? $scales['section_h2']['step-3'],
        'h3' => $scales['card_h3'][$h3_step] ?? $scales['card_h3']['step-3'],
        'body' => $scales['body_text'][$body_step] ?? $scales['body_text']['step-3'],
        'eyebrow' => $scales['eyebrow'][$eyebrow_step] ?? $scales['eyebrow']['step-3'],
        'subtitle' => $scales['subtitle'][$subtitle_step] ?? $scales['subtitle']['step-3'],
        'h1_step' => $h1_step,
        'h2_step' => $h2_step,
        'h3_step' => $h3_step,
        'body_step' => $body_step,
        'eyebrow_step' => $eyebrow_step,
        'subtitle_step' => $subtitle_step
    ];
}

// 5 Curated Color Themes (Background, Accent, Cards, Text)
function getThemeCssVariables($themeKey) {
    $themes = [
        'amber' => [
            'name' => '🔥 Sklářský Žár (Tmavě oranžová & Jantar)',
            'primary' => '#0B0A08',
            'dark' => '#17120E',
            'cream' => '#F4EFE7',
            'white' => '#FFFDF8',
            'accent' => '#E87516',
            'glass' => 'rgba(255, 255, 255, 0.05)',
            'glass_border' => 'rgba(255, 255, 255, 0.1)',
            'text' => '#F4EFE7',
            'text_muted' => '#A39B8E',
            'btn_hover' => '#d0640d',
            'body_bg' => 'linear-gradient(135deg, #0B0A08, #17120E)'
        ],
        'cyan' => [
            'name' => '💎 Křišťálová Noc (Tmavě modrá & Tyrkys)',
            'primary' => '#050B11',
            'dark' => '#0C1622',
            'cream' => '#E0F2FE',
            'white' => '#FFFFFF',
            'accent' => '#00D2C4',
            'glass' => 'rgba(0, 210, 196, 0.06)',
            'glass_border' => 'rgba(0, 210, 196, 0.2)',
            'text' => '#E0F2FE',
            'text_muted' => '#8BAAC4',
            'btn_hover' => '#00b3a6',
            'body_bg' => 'linear-gradient(135deg, #050B11, #0C1622)'
        ],
        'gold' => [
            'name' => '👑 Zlatý Cech (Tmavé Antracitové & Zlato)',
            'primary' => '#11100E',
            'dark' => '#1C1A17',
            'cream' => '#FBF7EE',
            'white' => '#FFFFFF',
            'accent' => '#D4AF37',
            'glass' => 'rgba(212, 175, 55, 0.06)',
            'glass_border' => 'rgba(212, 175, 55, 0.2)',
            'text' => '#FBF7EE',
            'text_muted' => '#B5A895',
            'btn_hover' => '#b89628',
            'body_bg' => 'linear-gradient(135deg, #11100E, #1C1A17)'
        ],
        'ruby' => [
            'name' => '🍷 Královská Dílna (Rubínové & Měď)',
            'primary' => '#120709',
            'dark' => '#1E0D11',
            'cream' => '#FFF0F2',
            'white' => '#FFFFFF',
            'accent' => '#E14D4D',
            'glass' => 'rgba(225, 77, 77, 0.06)',
            'glass_border' => 'rgba(225, 77, 77, 0.2)',
            'text' => '#FFF0F2',
            'text_muted' => '#C499A0',
            'btn_hover' => '#c83b3b',
            'body_bg' => 'linear-gradient(135deg, #120709, #1E0D11)'
        ],
        'light' => [
            'name' => '☀️ Světlé Studio (Moderní Světlý Ateliér)',
            'primary' => '#F8F6F0',
            'dark' => '#EEEBE1',
            'cream' => '#211E1B',
            'white' => '#12100E',
            'accent' => '#B85D0D',
            'glass' => 'rgba(0, 0, 0, 0.04)',
            'glass_border' => 'rgba(0, 0, 0, 0.12)',
            'text' => '#211E1B',
            'text_muted' => '#665F57',
            'btn_hover' => '#9c4c07',
            'body_bg' => 'linear-gradient(135deg, #F8F6F0, #EEEBE1)'
        ]
    ];
    return $themes[$themeKey] ?? $themes['amber'];
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

// Function to generate full HTML from section data array with dynamic section ordering, odd/even section margins, themes, and 3-step modal contact form
function renderLandingPageHtml($data) {
    $slug = htmlspecialchars($data['slug'] ?? 'master');
    $masterName = htmlspecialchars($data['master_name'] ?? 'Mistr');
    $metaTitle = htmlspecialchars($data['meta_title'] ?? $masterName . ' – Svobodné Cechy');
    $metaDesc = htmlspecialchars($data['meta_desc'] ?? '');
    
    // Typography Configuration
    $typoConf = getTypoCssValues($data['typography'] ?? []);
    $h1_clamp = $typoConf['h1'];
    $h2_clamp = $typoConf['h2'];
    $h3_clamp = $typoConf['h3'];
    $body_size = $typoConf['body'];
    $eyebrow_size = $typoConf['eyebrow'];
    $subtitle_size = $typoConf['subtitle'];

    // Design Configuration (Themes, Odd/Even Margins, Sticky CTA, Button Sizes)
    $design = $data['design'] ?? [];
    $themeKey = $design['color_theme'] ?? 'amber';
    $tvars = getThemeCssVariables($themeKey);

    $btnSizeKey = $design['btn_size'] ?? 'm';
    $btnSizeMap = [
        's'  => ['pv' => '0.6rem',  'ph' => '1.2rem', 'fs' => '0.9rem',  'pv_m' => '0.45rem', 'ph_m' => '0.9rem',  'fs_m' => '0.8rem'],
        'm'  => ['pv' => '0.9rem',  'ph' => '1.8rem', 'fs' => '1.05rem', 'pv_m' => '0.6rem',  'ph_m' => '1.2rem',  'fs_m' => '0.85rem'],
        'l'  => ['pv' => '1.15rem', 'ph' => '2.4rem', 'fs' => '1.2rem',  'pv_m' => '0.75rem', 'ph_m' => '1.5rem',  'fs_m' => '0.92rem'],
        'xl' => ['pv' => '1.4rem',  'ph' => '3.0rem', 'fs' => '1.35rem', 'pv_m' => '0.9rem',  'ph_m' => '1.8rem',  'fs_m' => '1.0rem']
    ];
    $bS = $btnSizeMap[$btnSizeKey] ?? $btnSizeMap['m'];

    $oddSidePad = htmlspecialchars($design['odd_side_padding'] ?? '1.2rem');
    $oddVertPad = htmlspecialchars($design['odd_vert_padding'] ?? '2.8rem');
    $evenSidePad = htmlspecialchars($design['even_side_padding'] ?? '0.6rem');
    $evenVertPad = htmlspecialchars($design['even_vert_padding'] ?? '2.2rem');

    $stickyCtaEnabled = !isset($design['sticky_cta_enabled']) || $design['sticky_cta_enabled'] == true;
    $hideInHero = isset($design['sticky_cta_hide_in_hero']) && $design['sticky_cta_hide_in_hero'] == true;
    $hideInContact = isset($design['sticky_cta_hide_in_contact']) && $design['sticky_cta_hide_in_contact'] == true;

    // Section CTA configuration & Same text setting
    $secCtaData = $data['section_cta'] ?? [];
    $secCtaSameText = !isset($secCtaData['same_text']) || $secCtaData['same_text'] == true;
    $secCtaVisibility = $secCtaData['visibility'] ?? [];
    $secCtaTexts = $secCtaData['texts'] ?? [];

    // 3-Phase Modal Contact Texts
    $mc = $data['modal_contact'] ?? [];
    $m_s1_title = htmlspecialchars($mc['step1_title'] ?? 'Zanechte nám svůj e-mail');
    $m_s1_text = htmlspecialchars($mc['step1_text'] ?? 'Zašleme vám podrobné informace o učednictví a propojíme vás s mistrem.');
    $m_s1_btn = htmlspecialchars($mc['step1_btn'] ?? 'POKRAČOVAT');

    $m_s2_title = htmlspecialchars($mc['step2_title'] ?? 'Doplňující údaje (volitelné)');
    $m_s2_text = htmlspecialchars($mc['step2_text'] ?? 'Chcete-li, abychom vám přímo zavolali nebo poslali zprávu, vyplňte vaše jméno a telefon.');
    $m_s2_btn = htmlspecialchars($mc['step2_btn'] ?? 'ODESLAT VŠECHNO');
    $m_s2_wa_text = htmlspecialchars($mc['step2_wa_text'] ?? 'NAPSAT ROVNOU NA WHATSAPP');

    $m_s3_title = htmlspecialchars($mc['step3_title'] ?? 'Děkujeme za váš zájem!');
    $m_s3_text = htmlspecialchars($mc['step3_text'] ?? 'Vaši zprávu jsme v pořádku přijali. Ozveme se vám do 24 hodin.');
    $m_s3_btn = htmlspecialchars($mc['step3_btn'] ?? 'ZAVŘÍT');
    $m_s3_web_url = htmlspecialchars($mc['step3_web_url'] ?? '');
    $m_s3_web_btn_text = htmlspecialchars($mc['step3_web_btn_text'] ?? 'Přejít na web');
    $m_s3_web_desc = htmlspecialchars($mc['step3_web_desc'] ?? '');
    $m_s3_web_box_display = !empty($mc['step3_web_url']) ? 'block' : 'none';
    $m_s3_web_desc_display = !empty($mc['step3_web_desc']) ? 'block' : 'none';

    // 1. Hero
    $h_eyebrow = htmlspecialchars($data['hero']['eyebrow'] ?? 'UČEDNICTVÍ U MISTRA SKLÁŘE');
    $h_h1 = htmlspecialchars($data['hero']['h1'] ?? 'NAUČ SE ŘEMESLO PŘÍMO OD MISTRA');
    $h_sub = htmlspecialchars($data['hero']['subtitle'] ?? '');
    $h_btn1 = htmlspecialchars($data['hero']['btn_primary'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ');
    $h_btn2 = htmlspecialchars($data['hero']['btn_secondary'] ?? 'PODÍVAT SE, JAK SE PRACUJE SE SKLEM');
    $h_btn2_show = $data['hero']['btn_secondary_show'] ?? true;
    $h_btn2_html = $h_btn2_show ? "<a href=\"#realizace\" class=\"btn btn-secondary\">{$h_btn2}</a>" : "";
    $h_img = htmlspecialchars(fixImgUrl($data['hero']['image'] ?? ''));

    $heroSection = <<<HTML
    <section class="hero-wrapper">
      <div class="container hero">
        <div class="hero-content">
          <p class="eyebrow">{$h_eyebrow}</p>
          <h1>{$h_h1}</h1>
          <p class="subtitle">{$h_sub}</p>
        </div>
        <div class="hero-image"><img src="{$h_img}" alt="Fotografie z huti" /></div>
      </div>
      <div class="container hero-buttons-row">
        <a href="#kontakt" class="btn btn-primary" onclick="openLeadModal(event)">{$h_btn1}</a>
        {$h_btn2_html}
      </div>
    </section>
HTML;

    // Helper closure to build section CTA button HTML
    $getSecCtaHtml = function($secKey) use ($h_btn1, $secCtaSameText, $secCtaVisibility, $secCtaTexts) {
        $show = $secCtaVisibility[$secKey] ?? true;
        $dispStyle = $show ? '' : ' style="display:none;"';
        $txt = $secCtaSameText ? $h_btn1 : ($secCtaTexts[$secKey] ?? $h_btn1);
        if (trim((string)$txt) === '') $txt = $h_btn1;
        $txtHtml = htmlspecialchars($txt);
        return <<<HTML
      <div class="section-cta-wrap"{$dispStyle}>
        <a href="#kontakt" class="btn btn-primary" onclick="openLeadModal(event)">{$txtHtml}</a>
      </div>
HTML;
    };

    // 2. UVP
    $uvp_eyebrow = htmlspecialchars($data['uvp']['eyebrow'] ?? 'Proč toto učednictví?');
    $uvp_title = htmlspecialchars($data['uvp']['title'] ?? 'SKUTEČNÉ ŘEMESLO. SKUTEČNÝ MISTR. SKUTEČNÁ PRÁCE.');
    $uvp_sub = htmlspecialchars($data['uvp']['subtitle'] ?? '');
    $uvp_cards_html = "";
    foreach (($data['uvp']['items'] ?? []) as $item) {
        $t = htmlspecialchars($item['title'] ?? '');
        $d = htmlspecialchars($item['desc'] ?? '');
        $uvp_cards_html .= "<div class='uvp-card'><h3>{$t}</h3><p>{$d}</p></div>";
    }
    $uvpCtaHtml = $getSecCtaHtml('uvp');

    $uvpSection = <<<HTML
    <section id="uvp" class="uvp-section container">
      <div class="section-title">
        <p class="eyebrow">{$uvp_eyebrow}</p>
        <h2>{$uvp_title}</h2>
        <p class="subtitle">{$uvp_sub}</p>
      </div>
      <div class="uvp-grid">{$uvp_cards_html}</div>
      {$uvpCtaHtml}
    </section>
HTML;

    // 3. Master
    $m_eyebrow = htmlspecialchars($data['master']['eyebrow'] ?? 'KDO TĚ BUDE UČIT');
    $m_name = htmlspecialchars($data['master']['name'] ?? 'JIŘÍ PAČINEK');
    $m_title = htmlspecialchars($data['master']['title'] ?? 'MISTR SKLÁŘ & ZAKLADATEL SKLÁRNY PAČINEK GLASS');
    $m_bio = htmlspecialchars($data['master']['bio'] ?? '');
    $m_quote = htmlspecialchars($data['master']['quote'] ?? '');
    $m_bio2 = htmlspecialchars($data['master']['bio2'] ?? 'V dílně sází na poctivý přístup a předávání sklářského umění další generaci.');
    $m_img = htmlspecialchars(fixImgUrl($data['master']['image'] ?? ''));
    $masterCtaHtml = $getSecCtaHtml('master');

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
      <div class="container">
        {$masterCtaHtml}
      </div>
    </section>
HTML;

    // 4. Outcomes
    $o_eyebrow = htmlspecialchars($data['outcomes']['eyebrow'] ?? 'Praktické dovednosti');
    $o_title = htmlspecialchars($data['outcomes']['title'] ?? 'CO SE UČEDNÍK SKUTEČNĚ NAUČÍ');
    $o_sub = htmlspecialchars($data['outcomes']['subtitle'] ?? 'Konkrétní znalosti a návyky, které si z dílny odneseš do života.');
    $outcomes_html = "";
    foreach (($data['outcomes']['items'] ?? []) as $item) {
        $icon = htmlspecialchars($item['icon'] ?? '🔥');
        $t = htmlspecialchars($item['title'] ?? '');
        $d = htmlspecialchars($item['desc'] ?? '');
        $outcomes_html .= "<div class='outcome-item'><h4>{$icon} {$t}</h4><p>{$d}</p></div>";
    }
    $outcomesCtaHtml = $getSecCtaHtml('outcomes');

    $outcomesSection = <<<HTML
    <section id="co-se-naucis" class="outcomes-section container">
      <div class="section-title">
        <p class="eyebrow">{$o_eyebrow}</p>
        <h2>{$o_title}</h2>
        <p class="subtitle">{$o_sub}</p>
      </div>
      <div class="outcomes-grid">{$outcomes_html}</div>
      {$outcomesCtaHtml}
    </section>
HTML;

    // 5. Timeline
    $t_eyebrow = htmlspecialchars($data['timeline']['eyebrow'] ?? 'Postup krok za krokem');
    $t_title = htmlspecialchars($data['timeline']['title'] ?? 'JAK PROBÍHÁ CESTA K UČEDNICTVÍ');
    $t_sub = htmlspecialchars($data['timeline']['subtitle'] ?? 'Pět jasných kroků od prvního projevení zájmu až po začátek práce v dílně.');
    $t_disc = htmlspecialchars($data['timeline']['disclaimer'] ?? '💡 Základní cesta vypadá takto. Konkrétní průběh se přizpůsobuje zájemci.');
    $timeline_html = "";
    foreach (($data['timeline']['steps'] ?? []) as $step) {
        $num = htmlspecialchars($step['num'] ?? '01');
        $st = htmlspecialchars($step['title'] ?? '');
        $sd = htmlspecialchars($step['desc'] ?? '');
        $timeline_html .= "<div class='timeline-step'><div class='step-number'>{$num}</div><div class='step-content'><h4>{$st}</h4><p>{$sd}</p></div></div>";
    }
    $timelineCtaHtml = $getSecCtaHtml('timeline');

    $timelineSection = <<<HTML
    <section id="jak-to-probiha" class="timeline-section">
      <div class="container">
        <div class="section-title">
          <p class="eyebrow">{$t_eyebrow}</p>
          <h2>{$t_title}</h2>
          <p class="subtitle">{$t_sub}</p>
        </div>
        <div class="timeline">{$timeline_html}</div>
        <p class="disclaimer-box">{$t_disc}</p>
        {$timelineCtaHtml}
      </div>
    </section>
HTML;

    foreach (($data['testimonials']['items'] ?? []) as $item) {
    }
    // 6. Portfolio
    $p_eyebrow = htmlspecialchars($data['portfolio']['eyebrow'] ?? 'Ukázka z prostředí');
    $p_title = htmlspecialchars($data['portfolio']['title'] ?? 'CO VZNIKÁ V DÍLNĚ');
    $p_sub = htmlspecialchars($data['portfolio']['subtitle'] ?? 'Nahlédni do živého procesu sklářského umění.');
    $portfolio_html = "";
    foreach (($data['portfolio']['items'] ?? []) as $item) {
        $pimg = htmlspecialchars(fixImgUrl($item['image'] ?? ''));
        $pcap = htmlspecialchars($item['caption'] ?? '');
        $pitem_ratio = htmlspecialchars($item['aspect_ratio'] ?? '4/3');
        $pitem_fit = htmlspecialchars($item['object_fit'] ?? 'cover');
        $pitem_pos = htmlspecialchars($item['object_position'] ?? 'center');
        $ratio_css = ($pitem_ratio === 'auto') ? 'auto' : $pitem_ratio;
        $img_style = "width:100%; height:auto; aspect-ratio:{$ratio_css}; object-fit:{$pitem_fit}; object-position:{$pitem_pos}; display:block;";
        $portfolio_html .= "<div class='portfolio-item'><img src='{$pimg}' alt='{$pcap}' style='{$img_style}' /><div class='portfolio-caption'>{$pcap}</div></div>";
    }
    $portfolioCtaHtml = $getSecCtaHtml('portfolio');

    $portfolioSection = <<<HTML
    <section id="realizace" class="portfolio-section container">
      <div class="section-title">
        <p class="eyebrow">{$p_eyebrow}</p>
        <h2>{$p_title}</h2>
        <p class="subtitle">{$p_sub}</p>
      </div>
      <div class="portfolio-grid">{$portfolio_html}</div>
      {$portfolioCtaHtml}
    </section>
HTML;

    // 7. Testimonials
    $ts_eyebrow = htmlspecialchars($data['testimonials']['eyebrow'] ?? 'Zkušenosti a reference');
    $ts_title = htmlspecialchars($data['testimonials']['title'] ?? 'CO ŘÍKAJÍ LIDÉ, KTEŘÍ U TOHO BYLI');
    $ts_sub = htmlspecialchars($data['testimonials']['subtitle'] ?? 'Reálné pohledy zástupců a rodin.');
    $testimonials_html = "";
    foreach (($data['testimonials']['items'] ?? []) as $item) {
        $title = htmlspecialchars($item['title'] ?? '');
        $quote = htmlspecialchars($item['quote'] ?? '');
        $name = htmlspecialchars($item['name'] ?? '');
        $role = htmlspecialchars($item['role'] ?? '');
        $titleHtml = $title !== '' ? "<h4 class='testimonial-title' style='margin-top:0; margin-bottom:0.5rem; font-family:var(--font-heading); font-size:var(--card-h3-clamp); color:var(--color-white);'>{$title}</h4>" : "";
        $testimonials_html .= "<div class='testimonial-card'>{$titleHtml}<p class='quote-text'>{$quote}</p><div class='author-info'><div><strong>{$name}</strong><span>{$role}</span></div></div></div>";
    }
    $testimonialsCtaHtml = $getSecCtaHtml('testimonials');

    $testimonialsSection = <<<HTML
    <section id="reference" class="testimonials-section">
      <div class="container">
        <div class="section-title">
          <p class="eyebrow">{$ts_eyebrow}</p>
          <h2>{$ts_title}</h2>
          <p class="subtitle">{$ts_sub}</p>
        </div>
        <div class="testimonials-grid">{$testimonials_html}</div>
        {$testimonialsCtaHtml}
      </div>
    </section>
HTML;

    // 8. FAQ
    $f_eyebrow = htmlspecialchars($data['faq']['eyebrow'] ?? 'Odpovědi na dotazy');
    $f_title = htmlspecialchars($data['faq']['title'] ?? 'ČASTÉ OTÁZKY');
    $f_sub = htmlspecialchars($data['faq']['subtitle'] ?? 'Vše, co tě může zajímat před prvním kontaktem.');
    $faq_html = "";
    foreach (($data['faq']['items'] ?? []) as $item) {
        $q = htmlspecialchars($item['q'] ?? '');
        $a = htmlspecialchars($item['a'] ?? '');
        $faq_html .= "<details><summary>{$q}</summary><p>{$a}</p></details>";
    }
    $faqCtaHtml = $getSecCtaHtml('faq');

    $faqSection = <<<HTML
    <section id="faq" class="faq-section container">
      <div class="section-title">
        <p class="eyebrow">{$f_eyebrow}</p>
        <h2>{$f_title}</h2>
        <p class="subtitle">{$f_sub}</p>
      </div>
      <div class="faq-list">{$faq_html}</div>
      {$faqCtaHtml}
    </section>
HTML;

    // 9. Primary CTA Box
    $cta_title = htmlspecialchars($data['cta']['title'] ?? 'ZAJÍMÁ TĚ, JESTLI JE TO PRO TEBE?');
    $cta_text = htmlspecialchars($data['cta']['text'] ?? 'Nemusíš se hned k ničemu zavazovat. První krok je jednoduchý nezávazný rozhovor.');
    $cta_btn = htmlspecialchars($data['cta']['btn'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ');

    $ctaSection = <<<HTML
    <div class="container" id="kontakt">
      <div class="primary-cta-box">
        <h2>{$cta_title}</h2>
        <p>{$cta_text}</p>
        <button type="button" class="btn btn-primary" onclick="openLeadModal(event)">{$cta_btn}</button>
      </div>
    </div>
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
        'cta' => $ctaSection
    ];

    // Default order
    $defaultOrder = ['hero', 'uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq', 'cta'];
    $order = $data['section_order'] ?? $defaultOrder;

    $sectionVisibility = $data['section_visibility'] ?? [];
    $renderedMain = "";
    $visibleIdx = 0; // count only visible sections for odd/even assignment
    foreach ($order as $idx => $secKey) {
        // Skip hidden sections
        if (isset($sectionVisibility[$secKey]) && $sectionVisibility[$secKey] === false) {
            continue;
        }
        if (isset($allSections[$secKey])) {
            $isOdd = ($visibleIdx % 2 === 0);
            $secClass = $isOdd ? 'section-odd' : 'section-even';
            $block = $allSections[$secKey];
            if (preg_match('/class=["\']([^"\']*)["\']/', $block)) {
                $block = preg_replace('/class=["\']([^"\']*)["\']/', 'class="$1 ' . $secClass . '"', $block, 1);
            } else {
                $block = preg_replace('/<([a-z0-9]+)/i', '<$1 class="' . $secClass . '"', $block, 1);
            }
            $renderedMain .= $block . "\n";
            $visibleIdx++;
        }
    }

    $stickyCtaDisplay = $stickyCtaEnabled ? 'block' : 'none';
    $hideInHeroJson = json_encode($hideInHero);
    $hideInContactJson = json_encode($hideInContact);

    return <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{$metaTitle}</title>
  <meta name="description" content="{$metaDesc}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --color-primary: {$tvars['primary']};
      --color-dark: {$tvars['dark']};
      --color-cream: {$tvars['cream']};
      --color-white: {$tvars['white']};
      --color-accent: {$tvars['accent']};
      --color-glass: {$tvars['glass']};
      --color-glass-border: {$tvars['glass_border']};
      --text: {$tvars['text']};
      --text-muted: {$tvars['text_muted']};
      --btn-hover: {$tvars['btn_hover']};
      --font-heading: 'Cormorant Garamond', serif;
      --font-body: 'Inter', sans-serif;

      /* Configured Fluid Typography Steps */
      --hero-h1-clamp: {$h1_clamp};
      --section-h2-clamp: {$h2_clamp};
      --card-h3-clamp: {$h3_clamp};
      --body-text-size: {$body_size};
      --eyebrow-size: {$eyebrow_size};
      --subtitle-size: {$subtitle_size};

      /* Alternating Odd & Even Section Margins & Padding */
      --odd-side-padding: {$oddSidePad};
      --odd-vert-padding: {$oddVertPad};
      --even-side-padding: {$evenSidePad};
      --even-vert-padding: {$evenVertPad};

      /* Configured Button Size (Desktop & Mobile) */
      --btn-pad-v: {$bS['pv']};
      --btn-pad-h: {$bS['ph']};
      --btn-font-size: {$bS['fs']};
      --btn-pad-v-mob: {$bS['pv_m']};
      --btn-pad-h-mob: {$bS['ph_m']};
      --btn-font-size-mob: {$bS['fs_m']};
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { font-family: var(--font-body); background: {$tvars['body_bg']}; color: var(--text); line-height: 1.5; font-size: var(--body-text-size); }
    @media (min-width: 768px) { body { font-size: var(--body-text-size); } }

    .container { max-width: 1050px; margin: auto; padding: 0 1.2rem; }

    /* Alternating Section Dynamics */
    .section-odd {
      padding-top: var(--odd-vert-padding);
      padding-bottom: var(--odd-vert-padding);
      padding-left: var(--odd-side-padding);
      padding-right: var(--odd-side-padding);
    }
    .section-even {
      padding-top: var(--even-vert-padding);
      padding-bottom: var(--even-vert-padding);
      padding-left: var(--even-side-padding);
      padding-right: var(--even-side-padding);
      background: rgba(0, 0, 0, 0.15);
    }

    /* Sticky Header */
    .site-header { position: sticky; top: 0; background: rgba(11, 10, 8, 0.92); backdrop-filter: blur(10px); z-index: 100; border-bottom: 1px solid var(--color-glass-border); }
    .nav-container { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.2rem; max-width: 1050px; margin: auto; }
    .logo { font-family: var(--font-heading); font-size: clamp(1.05rem, 2.2vw, 1.3rem); font-weight: 700; color: var(--color-accent); text-decoration: none; letter-spacing: 1px; }
    .nav-menu { list-style: none; display: flex; gap: 1.2rem; align-items: center; }
    .nav-menu a { color: var(--text); text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: color .2s; }
    .nav-menu a:hover { color: var(--color-accent); }
    .cta-nav { background: var(--color-accent); color: #fff !important; padding: 0.5rem 0.9rem; border-radius: 6px; font-weight: 700; cursor: pointer; }

    /* STRICT TIGHT LINE HEIGHTS & FLUID FONT SIZES FOR ALL HEADINGS */
    h1, h2, h3, h4 {
      line-height: 1.15;
      letter-spacing: -0.02em;
      overflow-wrap: break-word;
      word-break: break-word;
      margin-bottom: 0.4rem;
    }

    .hero-wrapper { padding-bottom: 2.5rem; }
    .hero { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 2.5rem; padding: 3.5rem 0 1.5rem; min-height: 55vh; }
    .hero-content { max-width: 580px; }
    .eyebrow { text-transform: uppercase; font-size: var(--eyebrow-size); letter-spacing: 2px; color: var(--color-accent); font-weight: 700; margin-bottom: 0.6rem; }
    .hero h1 { font-family: var(--font-heading); font-size: var(--hero-h1-clamp); color: var(--color-white); margin-bottom: 0.8rem; }
    .subtitle { font-size: var(--subtitle-size); color: var(--text-muted); margin-bottom: 0; line-height: 1.5; }
    .hero-buttons-row { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center; padding-top: 1.8rem; width: 100%; text-align: center; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: {$bS['pv']} {$bS['ph']}; font-size: {$bS['fs']}; border-radius: 6px; font-weight: 700; text-decoration: none; transition: all .2s; min-height: 42px; cursor: pointer; border: none; text-align: center; }
    @media (max-width: 768px) {
      .btn { padding: {$bS['pv_m']} {$bS['ph_m']} !important; font-size: {$bS['fs_m']} !important; }
    }
    .btn-primary { background: var(--color-accent); color: #fff; }
    .btn-primary:hover { background: var(--btn-hover); }
    .btn-secondary { background: var(--color-glass); color: var(--text); border: 1px solid var(--color-accent); }
    .section-cta-wrap { display: flex; justify-content: center; align-items: center; text-align: center; margin-top: 2.5rem; padding-top: 0.5rem; width: 100%; }
    .hero-image img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); border: 1px solid var(--color-glass-border); }

    .section-title { text-align: center; margin-bottom: 2.5rem; }
    .section-title h2 {
      font-family: var(--font-heading);
      font-size: var(--section-h2-clamp);
      color: var(--color-white);
      margin-bottom: 0.5rem;
    }
    .primary-cta-box h2 { font-family: var(--font-heading); font-size: var(--section-h2-clamp); color: var(--color-white); margin-bottom: 1rem; }
    .contact-card h3 { font-family: var(--font-heading); font-size: var(--section-h2-clamp); color: var(--color-white); margin-bottom: 1rem; }
    .master-info h3 { font-family: var(--font-heading); font-size: var(--hero-h1-clamp); color: var(--color-white); margin-bottom: 0.5rem; }
    .section-title p { color: var(--text-muted); font-size: clamp(0.95rem, 2vw, 1.1rem); max-width: 680px; margin: auto; }

    .uvp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .uvp-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.5rem; }
    .uvp-card h3 { font-family: var(--font-heading); font-size: var(--card-h3-clamp); color: var(--color-accent); margin-bottom: 0.4rem; }

    .master-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 2.5rem; align-items: center; }
    .master-photo img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 14px; border: 1px solid var(--color-accent); }
    .master-title { color: var(--color-accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; font-size: var(--eyebrow-size); }
    .quote-box { font-family: var(--font-heading); font-style: italic; font-size: clamp(1.05rem, 2.5vw, 1.25rem); color: var(--color-cream); border-left: 3px solid var(--color-accent); padding-left: 0.9rem; margin: 1rem 0; line-height: 1.3; }

    .outcomes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .outcome-item { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.25rem; }
    .outcome-item h4, .step-content h4 { font-family: var(--font-heading); font-size: var(--card-h3-clamp); color: var(--color-white); margin-bottom: 0.3rem; }

    .timeline { display: flex; flex-direction: column; gap: 1rem; max-width: 850px; margin: auto; }
    .timeline-step { display: grid; grid-template-columns: 50px 1fr; gap: 1rem; background: var(--color-glass); border: 1px solid var(--color-glass-border); border-left: 4px solid var(--color-accent); border-radius: 8px; padding: 1rem 1.2rem; align-items: center; }
    .step-number { font-family: var(--font-heading); font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; color: var(--color-accent); text-align: center; }
    .disclaimer-box { text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.5rem; font-style: italic; }

    .portfolio-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
    .portfolio-item { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--color-glass-border); }
    .portfolio-item img { width: 100%; height: auto; object-fit: cover; display: block; }
    .portfolio-caption { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); padding: 1rem 0.8rem 0.6rem; color: var(--color-white); font-weight: 600; font-size: 0.85rem; }

    .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.2rem; }
    .testimonial-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 12px; padding: 1.5rem; }
    .quote-text { font-style: italic; margin-bottom: 0.8rem; color: var(--color-cream); font-size: 0.9rem; }
    .author-info strong { color: var(--color-accent); display: block; font-size: 0.9rem; }

    .faq-list { max-width: 800px; margin: auto; display: flex; flex-direction: column; gap: 0.7rem; }
    details { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 8px; padding: 0.9rem 1.1rem; }
    details[open] { border-color: var(--color-accent); background: rgba(232, 117, 22, 0.05); }
    summary { font-family: var(--font-heading); font-size: var(--card-h3-clamp); font-weight: 600; color: var(--color-white); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; line-height: 1.15; }
    summary::after { content: '+'; font-size: 1.3rem; color: var(--color-accent); margin-left: 0.5rem; }
    details[open] summary::after { content: '−'; }

    .primary-cta-box { background: linear-gradient(135deg, rgba(232,117,22,0.15), rgba(17,14,11,0.9)); border: 2px solid var(--color-accent); border-radius: 16px; padding: 2.5rem 1.2rem; text-align: center; margin: 2.5rem auto; max-width: 850px; }
    .primary-cta-box p { color: var(--text-muted); font-size: clamp(0.9rem, 1.8vw, 1.05rem); max-width: 620px; margin: 0 auto 1.2rem; }

    .contact-card { background: var(--color-glass); border: 1px solid var(--color-glass-border); border-radius: 14px; padding: 2rem; }
    .btn-wa { background: #25D366; color: #fff; text-decoration: none; font-weight: 700; border-radius: 6px; padding: 0.8rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem; font-size: 0.85rem; }
    .btn-ig { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: #fff; text-decoration: none; font-weight: 700; border-radius: 6px; padding: 0.8rem; text-align: center; display: flex; justify-content: center; gap: 0.5rem; font-size: 0.85rem; }
    .form-group { margin-bottom: 1rem; text-align: left; }
    .form-control { width: 100%; padding: 0.75rem 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid var(--color-glass-border); border-radius: 6px; color: #fff; font-size: 0.95rem; }
    textarea.form-control { min-height: 80px; resize: vertical; }

    .mobile-sticky-cta {
      display: {$stickyCtaDisplay};
      position: fixed;
      bottom: 1.2rem;
      left: 50%;
      transform: translateX(-50%);
      width: calc(100% - 2rem);
      max-width: 500px;
      background: rgba(17, 14, 11, 0.94);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid var(--color-accent);
      border-radius: 50px;
      padding: 0.5rem 0.6rem;
      z-index: 999;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.7), 0 0 15px rgba(232, 117, 22, 0.25);
      text-align: center;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .mobile-sticky-cta .btn {
      width: 100%;
      border-radius: 40px;
      box-shadow: 0 4px 15px rgba(232, 117, 22, 0.4);
    }
    footer { border-top: 1px solid var(--color-glass-border); padding: 2rem 0; text-align: center; color: var(--text-muted); font-size: 0.8rem; }

    /* Lead Modal Popup Styles */
    .lead-modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(8px);
      z-index: 10000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .lead-modal-box {
      background: var(--color-dark);
      border: 2px solid var(--color-accent);
      border-radius: 16px;
      max-width: 480px;
      width: 100%;
      padding: 2.2rem 1.8rem;
      position: relative;
      box-shadow: 0 20px 50px rgba(0,0,0,0.9);
      color: var(--text);
      animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: translateY(15px) scale(0.95); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .lead-modal-close {
      position: absolute;
      top: 0.8rem; right: 1rem;
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-size: 1.8rem;
      cursor: pointer;
      line-height: 1;
    }
    .lead-modal-close:hover { color: var(--color-accent); }
    .modal-step h3 { font-family: var(--font-heading); font-size: var(--section-h2-clamp); color: var(--color-white); margin-bottom: 0.5rem; text-align: center; }
    .modal-step p { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.4rem; text-align: center; line-height: 1.4; }

    /* Mobile Responsive Custom Margins & Padding */
    @media (max-width: 900px) {
      .section-odd {
        padding-top: calc(var(--odd-vert-padding) * 0.65);
        padding-bottom: calc(var(--odd-vert-padding) * 0.65);
        padding-left: var(--odd-side-padding);
        padding-right: var(--odd-side-padding);
      }
      .section-even {
        padding-top: calc(var(--even-vert-padding) * 0.65);
        padding-bottom: calc(var(--even-vert-padding) * 0.65);
        padding-left: var(--even-side-padding);
        padding-right: var(--even-side-padding);
      }
      .hero { grid-template-columns: 1fr; text-align: center; gap: 1.5rem; min-height: auto; }
      .hero-content { max-width: 100%; }
      .hero-buttons-row { justify-content: center; padding-top: 1.2rem; }
      .hero-buttons { justify-content: center; }
      .master-grid { grid-template-columns: 1fr; gap: 1.5rem; }
      .contact-grid { grid-template-columns: 1fr; gap: 1.5rem; }
      .nav-menu { display: none; }
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
        <li><a href="#kontakt" class="cta-nav" onclick="openLeadModal(event)">ZJISTIT, JESTLI JE TO PRO MĚ</a></li>
      </ul>
    </div>
  </header>

  <main>
    {$renderedMain}
  </main>

  <div class="mobile-sticky-cta"><a href="#kontakt" class="btn btn-primary" style="width:100%;" onclick="openLeadModal(event)">ZJISTIT, JESTLI JE TO PRO MĚ</a></div>
  <footer><div class="container"><p>© 2026 Svobodné Cechy. Všechna práva vyhrazena.</p></div></footer>

  <!-- LEAD CAPTURE POPUP MODAL (3 PHASES) -->
  <div id="leadModal" class="lead-modal-overlay">
    <div class="lead-modal-box">
      <button type="button" class="lead-modal-close" onclick="closeLeadModal()">&times;</button>
      
      <!-- STEP 1: EMAIL CAPTURE -->
      <div id="modalStep1" class="modal-step">
        <h3>{$m_s1_title}</h3>
        <p>{$m_s1_text}</p>
        <form onsubmit="submitStep1(event)">
          <div class="form-group">
            <label style="display:block; font-size:0.85rem; margin-bottom:0.3rem; color:var(--color-accent); font-weight:700;">Váš E-mail *</label>
            <input type="email" id="m_email" name="email" autocomplete="email" class="form-control" placeholder="např. jan.novak@seznam.cz" required />
          </div>
          <button type="submit" id="m_s1_submit_btn" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">{$m_s1_btn}</button>
        </form>
      </div>

      <!-- STEP 2: ADDITIONAL DETAILS & WHATSAPP -->
      <div id="modalStep2" class="modal-step" style="display:none;">
        <h3>{$m_s2_title}</h3>
        <p>{$m_s2_text}</p>
        <form onsubmit="submitStep2(event)">
          <div class="form-group">
            <label style="display:block; font-size:0.85rem; margin-bottom:0.3rem; color:var(--color-accent); font-weight:700;">Jméno a příjmení</label>
            <input type="text" id="m_name" name="name" autocomplete="name" class="form-control" placeholder="Jan Novák" />
          </div>
          <div class="form-group">
            <label style="display:block; font-size:0.85rem; margin-bottom:0.3rem; color:var(--color-accent); font-weight:700;">Telefon</label>
            <input type="tel" id="m_phone" name="phone" autocomplete="tel" class="form-control" placeholder="+420 602 123 456" />
          </div>
          <div class="form-group">
            <label style="display:block; font-size:0.85rem; margin-bottom:0.3rem; color:var(--color-accent); font-weight:700;">Jsem:</label>
            <select id="m_role" class="form-control">
              <option value="Budoucí učedník">Budoucí učedník</option>
              <option value="Rodič">Rodič</option>
            </select>
          </div>
          <div class="form-group">
            <label style="display:block; font-size:0.85rem; margin-bottom:0.3rem; color:var(--color-accent); font-weight:700;">Zpráva nebo dotaz (volitelné)</label>
            <textarea id="m_msg" class="form-control" placeholder="Mám zájem o bližší informace..."></textarea>
          </div>
          <button type="submit" id="m_s2_submit_btn" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">{$m_s2_btn}</button>
        </form>
        <div style="text-align:center; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--color-glass-border);">
          <button type="button" onclick="submitStep2AndWhatsApp(event, 'https://wa.me/{$wa_num}?text={$wa_msg}')" class="btn-wa" style="width:100%; border:none; cursor:pointer;">{$m_s2_wa_text}</button>
        </div>
      </div>

      <!-- STEP 3: THANK YOU -->
      <div id="modalStep3" class="modal-step" style="display:none;">
        <h3>{$m_s3_title}</h3>
        <p id="m_s3_text_el">{$m_s3_text}</p>
        <div id="m_s3_web_box" style="margin-top:1.2rem; display:{$m_s3_web_box_display};">
          <p id="m_s3_web_desc_el" style="font-size:0.95rem; opacity:0.9; margin-bottom:0.8rem; color:var(--color-text-muted, #d1d5db); display:{$m_s3_web_desc_display};">{$m_s3_web_desc}</p>
          <a id="m_s3_web_link_el" href="{$m_s3_web_url}" target="_blank" rel="noopener" class="btn btn-secondary" style="display:block; text-align:center; text-decoration:none; width:100%; margin-bottom:0.5rem; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:0.75rem 1rem; border-radius:8px; font-weight:600; transition:all .2s;">{$m_s3_web_btn_text}</a>
        </div>
        <button type="button" id="m_s3_close_btn" class="btn btn-primary" onclick="closeLeadModal()" style="width:100%; margin-top:1rem;">{$m_s3_btn}</button>
      </div>
    </div>
  </div>

  <script>
    let currentLeadId = null;

    function openLeadModal(e) {
      if (e && e.preventDefault) e.preventDefault();
      const modal = document.getElementById('leadModal');
      if (modal) {
        modal.style.display = 'flex';
        const emailInput = document.getElementById('m_email');
        if (emailInput) setTimeout(() => emailInput.focus(), 100);
      }
    }

    function closeLeadModal() {
      const modal = document.getElementById('leadModal');
      if (modal) modal.style.display = 'none';
    }

    function submitStep1(e) {
      e.preventDefault();
      const btn = document.getElementById('m_s1_submit_btn');
      if (btn) btn.disabled = true;

      const email = document.getElementById('m_email').value;

      fetch('../api_landing_leads.php?action=capture_email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email,
          landing_slug: '{$slug}',
          master_name: '{$masterName}'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.lead_id) currentLeadId = data.lead_id;
        document.getElementById('modalStep1').style.display = 'none';
        document.getElementById('modalStep2').style.display = 'block';
      })
      .catch(err => {
        document.getElementById('modalStep1').style.display = 'none';
        document.getElementById('modalStep2').style.display = 'block';
      });
    }

    function submitStep2(e) {
      e.preventDefault();
      const btn = document.getElementById('m_s2_submit_btn');
      if (btn) btn.disabled = true;

      fetch('../api_landing_leads.php?action=update_lead', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          lead_id: currentLeadId,
          email: document.getElementById('m_email').value,
          landing_slug: '{$slug}',
          name: document.getElementById('m_name').value,
          phone: document.getElementById('m_phone').value,
          user_role: document.getElementById('m_role').value,
          message: document.getElementById('m_msg').value
        })
      })
      .then(res => res.json())
      .then(data => {
        document.getElementById('modalStep2').style.display = 'none';
        document.getElementById('modalStep3').style.display = 'block';
      })
      .catch(err => {
        document.getElementById('modalStep2').style.display = 'none';
        document.getElementById('modalStep3').style.display = 'block';
      });
    }

    function submitStep2AndWhatsApp(e, waUrl) {
      if (e && e.preventDefault) e.preventDefault();

      // Pre-open new tab to avoid popup blocker
      const waWin = window.open('about:blank', '_blank');

      const nameVal = document.getElementById('m_name').value.trim();
      const msgVal = document.getElementById('m_msg').value.trim();

      fetch('../api_landing_leads.php?action=update_lead', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          lead_id: currentLeadId,
          email: document.getElementById('m_email').value,
          landing_slug: '{$slug}',
          name: nameVal,
          phone: document.getElementById('m_phone').value,
          user_role: document.getElementById('m_role').value,
          message: msgVal
        })
      })
      .then(res => res.json())
      .then(data => {
        if (waWin) waWin.location.href = waUrl;
        document.getElementById('modalStep2').style.display = 'none';
        document.getElementById('modalStep3').style.display = 'block';
      })
      .catch(err => {
        if (waWin) waWin.location.href = waUrl;
        document.getElementById('modalStep2').style.display = 'none';
        document.getElementById('modalStep3').style.display = 'block';
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const cta = document.querySelector('.mobile-sticky-cta');
      if (cta) {
        const hideInHero = {$hideInHeroJson};
        const hideInContact = {$hideInContactJson};

        function updateCtaVisibility() {
          let hide = false;
          if (hideInHero) {
            const hero = document.querySelector('.hero');
            if (hero) {
              const rect = hero.getBoundingClientRect();
              if (rect.bottom > 100) hide = true;
            }
          }
          if (hideInContact) {
            const contact = document.querySelector('#kontakt');
            if (contact) {
              const rect = contact.getBoundingClientRect();
              if (rect.top < window.innerHeight - 50) hide = true;
            }
          }
          cta.style.display = hide ? 'none' : '{$stickyCtaDisplay}';
        }

        window.addEventListener('scroll', updateCtaVisibility, { passive: true });
        updateCtaVisibility();
      }

      // Attach openLeadModal handler to all CTA links & buttons
      document.querySelectorAll('a[href="#kontakt"], .cta-nav').forEach(el => {
        el.addEventListener('click', openLeadModal);
      });
    });
  </script>
</body>
</html>
HTML;
}

$message = "";
$messageType = "success";
$savedActiveTab = "tab-order";

// Handle Saving Visual Form Data
if (isset($_POST['save_sections_form'])) {
    $slug = basename($_POST['edit_slug']);
    $jsonPath = $dir . "/" . $slug . ".json";
    $htmlPath = $dir . "/" . $slug . ".html";

    if (!empty($_POST['active_tab'])) {
        $savedActiveTab = basename($_POST['active_tab']);
    }

    $formData = json_decode($_POST['sections_json_data'], true);
    if (is_array($formData)) {
        file_put_contents($jsonPath, json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $generatedHtml = renderLandingPageHtml($formData);
        file_put_contents($htmlPath, $generatedHtml);
        $message = "Všechny sekce, fotky, pořadí, barevná témata a 3-fázový kontaktní formulář pro '{$slug}' byly úspěšně uloženy!";
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
      --subtitle-size: 0.95rem;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; }
    .container { max-width: 1280px; margin: auto; }
    
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .btn-back { color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
    
    h1 { font-family: 'Bungee', cursive; font-size: 1.8rem; color: var(--accent); margin-bottom: 0.3rem; }
    p.subtitle { color: var(--text-muted); font-size: var(--subtitle-size); margin-bottom: 2rem; }

    .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
    .msg.success { background: rgba(37, 211, 102, 0.15); border: 1px solid #25D366; color: #25D366; }

    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4); margin-bottom: 2rem; }
    .card h2 { font-size: 1.3rem; color: #fff; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
    th { background: rgba(232,117,22,0.15); color: var(--accent); padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
    td { padding: 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .badge-file { font-family: monospace; background: rgba(255,255,255,0.08); padding: 0.3rem 0.6rem; border-radius: 4px; color: #fff; }

    /* Typo Badges on form labels */
    .badge-typo {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
      margin-left: 0.4rem;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .badge-typo.h1 { background: rgba(232, 117, 22, 0.25); color: #e87516; border: 1px solid #e87516; }
    .badge-typo.h2 { background: rgba(59, 130, 246, 0.25); color: #60a5fa; border: 1px solid #3b82f6; }
    .badge-typo.h3 { background: rgba(168, 85, 247, 0.25); color: #c084fc; border: 1px solid #a855f7; }
    .badge-typo.body { background: rgba(34, 197, 94, 0.25); color: #4ade80; border: 1px solid #22c55e; }
    .badge-typo.eyebrow { background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid #f59e0b; }

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
    .tab-btn.special-design { background: rgba(59,130,246,0.2); color: #fff; border-color: #3b82f6; }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: flex; align-items: center; flex-wrap: wrap; gap: 0.3rem; font-size: 0.85rem; font-weight: 600; color: var(--accent); margin-bottom: 0.3rem; }
    .form-control { width: 100%; padding: 0.75rem 1rem; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 6px; color: #fff; font-size: 0.95rem; }
    textarea.form-control { min-height: 80px; resize: vertical; }

    /* Theme Option Radio Cards */
    .theme-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-top: 0.5rem; }
    .theme-card-option { border: 2px solid var(--border); border-radius: 8px; padding: 0.8rem 1rem; cursor: pointer; transition: all .2s; background: rgba(0,0,0,0.3); }
    .theme-card-option:hover { border-color: var(--accent); }
    .theme-card-option.selected { border-color: var(--accent); background: rgba(232,117,22,0.15); }
    .theme-swatches { display: flex; gap: 0.4rem; margin-top: 0.4rem; }
    .swatch { width: 22px; height: 22px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); }

    /* Button Size Cards */
    .btn-size-option { border: 2px solid var(--border); border-radius: 8px; padding: 0.8rem; text-align: center; cursor: pointer; background: rgba(0,0,0,0.3); transition: all .2s; }
    .btn-size-option:hover { border-color: var(--accent); }
    .btn-size-option.selected { border-color: var(--accent); background: rgba(232,117,22,0.15); }

    /* Upload Box UI */
    .upload-row { display: flex; gap: 0.8rem; align-items: center; margin-top: 0.4rem; }
    .upload-btn { background: rgba(255,255,255,0.1); color: #fff; border: 1px dashed var(--accent); padding: 0.6rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
    .upload-btn:hover { background: rgba(232,117,22,0.2); }
    .thumb-preview { width: 60px; height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border); background: #000; flex-shrink: 0; }

    .item-card { background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .item-card h4 { font-size: 0.9rem; color: #fff; margin-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.4rem; }

    /* Live Preview Controls & Frame Styles */
    .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; flex-wrap: wrap; gap: 0.5rem; }
    .device-switcher { display: flex; gap: 0.4rem; background: rgba(0,0,0,0.4); padding: 0.3rem; border-radius: 8px; border: 1px solid var(--border); }
    .device-btn { background: transparent; color: var(--text-muted); border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.3rem; transition: all .2s; }
    .device-btn.active { background: var(--accent); color: #fff; }

    .preview-wrapper { width: 100%; display: flex; justify-content: center; background: rgba(0,0,0,0.3); border-radius: 12px; padding: 1rem 0; border: 1px solid var(--border); position: sticky; top: 1rem; }
    .preview-iframe { width: 100%; height: 780px; border: 1px solid var(--border); border-radius: 8px; background: #fff; transition: width 0.3s ease, border-radius 0.3s ease; }
    .preview-iframe.device-mobile { width: 375px; height: 750px; border: 8px solid #222; border-radius: 36px; box-shadow: 0 12px 30px rgba(0,0,0,0.8); }
    .preview-iframe.device-tablet { width: 430px; height: 750px; border: 8px solid #222; border-radius: 28px; box-shadow: 0 12px 30px rgba(0,0,0,0.8); }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <a href="../admin.html" class="btn-back"><i class="bi bi-arrow-left"></i> Zpět do Rozcestníku administrace</a>
    </div>

    <h1>Vizuální Editor Landing Pages</h1>
    <p class="subtitle">Upravuj obsah sekcí, 5 barevných schémat, 3-fázový popup formulář, střídavé okraje i pořadí a fonty!</p>

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
          <input type="hidden" name="active_tab" id="active_tab" value="<?= htmlspecialchars($savedActiveTab) ?>" />

          <div class="editor-layout">
            <!-- LEFT: SECTION TABS & FORM FIELDS -->
            <div>
              <div class="section-tab-nav">
                <button type="button" class="tab-btn special-order active" onclick="showTab('tab-order')"><i class="bi bi-arrow-down-up"></i> ⚙️ POŘADÍ SEKCÍ</button>
                <button type="button" class="tab-btn special-design" onclick="showTab('tab-design')"><i class="bi bi-palette"></i> 🎨 DESIGN & BARVY</button>
                <button type="button" class="tab-btn special-design" onclick="showTab('tab-modal-contact')"><i class="bi bi-chat-square-dots"></i> 💬 POPUP FORMULÁŘ (3 FÁZE)</button>
                <button type="button" class="tab-btn special-order" onclick="showTab('tab-typo')"><i class="bi bi-type"></i> 🔤 STUPNĚ FONTŮ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-hero')">1. HERO</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-uvp')">2. UVP</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-master')">3. MISTR</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-outcomes')">4. CO SE NAUČÍŠ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-timeline')">5. POSTUP</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-portfolio')">6. GALERIE FOTEK</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-testimonials')">7. REFERENCE</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-faq')">8. FAQ</button>
                <button type="button" class="tab-btn" onclick="showTab('tab-cta')">9. ZÁVĚREČNÁ SEKCE S TLAČÍTKEM (CTA BOX)</button>
              </div>

              <!-- TAB ORDER: SECTIONS REORDERING -->
              <div id="tab-order" class="tab-content active">
                <h3 style="color:#fff; margin-bottom:1rem;">⚙️ Měnění Pořadí Sekcí na Stránce</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                  Kliknutím na tlačítka <strong>Nahoru ⬆️</strong> nebo <strong>Dolů ⬇️</strong> posuneš sekci na požadované místo. Sekce se automaticky přizpůsobí střídavým okrajům (Lichá / Sudá) v reálném čase.
                </p>
                <div id="order_list_container"></div>
              </div>

              <!-- TAB DESIGN: COLOR THEMES, ODD/EVEN MARGINS & STICKY CTA -->
              <div id="tab-design" class="tab-content">
                <h3 style="color:#fff; margin-bottom:0.5rem;">🎨 Barevná Schémata, Střídavé Okraje & Plovoucí Tlačítko</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                  Vyber si jedno z 5 barevných schémat, nastav střídavé okraje pro liché a sudé sekce a uprav plovoucí CTA tlačítko.
                </p>

                <?php 
                $designData = $editingData['design'] ?? []; 
                $currentTheme = $designData['color_theme'] ?? 'amber';
                $btnSizeVal = $designData['btn_size'] ?? 'm';
                $oddSidePadVal = $designData['odd_side_padding'] ?? '1.2rem';
                /* Extract section CTA data for editor form population */
                $editorSecCtaData = $editingData['section_cta'] ?? [];
                $secCtaSameText = !isset($editorSecCtaData['same_text']) || $editorSecCtaData['same_text'] == true;
                $secCtaVisibility = $editorSecCtaData['visibility'] ?? [];
                $secCtaTexts = $editorSecCtaData['texts'] ?? [];
                $oddVertPadVal = $designData['odd_vert_padding'] ?? '2.8rem';
                $evenSidePadVal = $designData['even_side_padding'] ?? '0.6rem';
                $evenVertPadVal = $designData['even_vert_padding'] ?? '2.2rem';
                $stickyEnabled = !isset($designData['sticky_cta_enabled']) || $designData['sticky_cta_enabled'] == true;
                $stickyHideHero = isset($designData['sticky_cta_hide_in_hero']) && $designData['sticky_cta_hide_in_hero'] == true;
                $stickyHideContact = isset($designData['sticky_cta_hide_in_contact']) && $designData['sticky_cta_hide_in_contact'] == true;
                ?>

                <!-- 1. COLOR THEMES -->
                <div class="item-card">
                  <h4>🎨 Výběr Barevného Schématu (5 Palet)</h4>
                  <div class="theme-grid">
                    <div class="theme-card-option <?= $currentTheme === 'amber' ? 'selected' : '' ?>" onclick="selectTheme('amber')">
                      <strong>🔥 Sklářský Žár</strong> (Default)
                      <div class="theme-swatches">
                        <div class="swatch" style="background:#0B0A08;"></div>
                        <div class="swatch" style="background:#E87516;"></div>
                        <div class="swatch" style="background:#F4EFE7;"></div>
                      </div>
                    </div>

                    <div class="theme-card-option <?= $currentTheme === 'cyan' ? 'selected' : '' ?>" onclick="selectTheme('cyan')">
                      <strong>💎 Křišťálová Noc</strong>
                      <div class="theme-swatches">
                        <div class="swatch" style="background:#050B11;"></div>
                        <div class="swatch" style="background:#00D2C4;"></div>
                        <div class="swatch" style="background:#E0F2FE;"></div>
                      </div>
                    </div>

                    <div class="theme-card-option <?= $currentTheme === 'gold' ? 'selected' : '' ?>" onclick="selectTheme('gold')">
                      <strong>👑 Zlatý Cech</strong>
                      <div class="theme-swatches">
                        <div class="swatch" style="background:#11100E;"></div>
                        <div class="swatch" style="background:#D4AF37;"></div>
                        <div class="swatch" style="background:#FBF7EE;"></div>
                      </div>
                    </div>

                    <div class="theme-card-option <?= $currentTheme === 'ruby' ? 'selected' : '' ?>" onclick="selectTheme('ruby')">
                      <strong>🍷 Královská Dílna</strong>
                      <div class="theme-swatches">
                        <div class="swatch" style="background:#120709;"></div>
                        <div class="swatch" style="background:#E14D4D;"></div>
                        <div class="swatch" style="background:#FFF0F2;"></div>
                      </div>
                    </div>

                    <div class="theme-card-option <?= $currentTheme === 'light' ? 'selected' : '' ?>" onclick="selectTheme('light')">
                      <strong>☀️ Světlé Studio</strong>
                      <div class="theme-swatches">
                        <div class="swatch" style="background:#F8F6F0;"></div>
                        <div class="swatch" style="background:#B85D0D;"></div>
                        <div class="swatch" style="background:#211E1B;"></div>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" id="design_color_theme" value="<?= htmlspecialchars($currentTheme) ?>" />
                </div>

                <!-- 2. BUTTON SIZES -->
                <div class="item-card">
                  <h4>🔘 Velikost Tlačítek (4 Velikosti pro PC i Mobil)</h4>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                    Vyber si velikost tlačítka. Velikosti jsou responzivně vyladěné pro desktop i mobil.
                  </p>
                  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.8rem;">
                    <div class="btn-size-option <?= $btnSizeVal === 's' ? 'selected' : '' ?>" onclick="selectBtnSize('s', this)">
                      <strong>Malé (S)</strong>
                      <div style="margin-top:0.4rem;"><span class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.75rem; min-height:auto;">Tlačítko S</span></div>
                    </div>
                    <div class="btn-size-option <?= $btnSizeVal === 'm' ? 'selected' : '' ?>" onclick="selectBtnSize('m', this)">
                      <strong>Střední (M)</strong>
                      <div style="margin-top:0.4rem;"><span class="btn btn-primary" style="padding:0.6rem 1.1rem; font-size:0.85rem; min-height:auto;">Střední M</span></div>
                    </div>
                    <div class="btn-size-option <?= $btnSizeVal === 'l' ? 'selected' : '' ?>" onclick="selectBtnSize('l', this)">
                      <strong>Velké (L)</strong>
                      <div style="margin-top:0.4rem;"><span class="btn btn-primary" style="padding:0.75rem 1.3rem; font-size:0.95rem; min-height:auto;">Velké L</span></div>
                    </div>
                    <div class="btn-size-option <?= $btnSizeVal === 'xl' ? 'selected' : '' ?>" onclick="selectBtnSize('xl', this)">
                      <strong>Extra (XL)</strong>
                      <div style="margin-top:0.4rem;"><span class="btn btn-primary" style="padding:0.9rem 1.6rem; font-size:1.05rem; min-height:auto;">Extra XL</span></div>
                    </div>
                  </div>
                  <input type="hidden" id="design_btn_size" value="<?= htmlspecialchars($btnSizeVal) ?>" />
                </div>

                <!-- 2. ALTERNATING ODD & EVEN SECTION MARGINS & PADDING -->
                <div class="item-card">
                  <h4>⚖️ Střídavé Okraje Sekcí (Liché vs. Sudé Sekce)</h4>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                    Liché a sudé sekce se automaticky střídají podle pořadí nastaveného v záložce <strong>⚙️ POŘADÍ SEKCÍ</strong>.
                  </p>

                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.2rem;">
                    <!-- ODD SECTIONS -->
                    <div style="background:rgba(232,117,22,0.08); border:1px solid var(--accent); padding:1rem; border-radius:8px;">
                      <h5 style="color:var(--accent); margin-bottom:0.8rem;">🔹 Liché Sekce (1., 3., 5. v pořadí)</h5>
                      <div class="form-group">
                        <label>Boční odsazení lichých sekcí</label>
                        <select class="form-control" id="design_odd_side_padding" onchange="liveUpdateDesign()">
                          <option value="0rem" <?= $oddSidePadVal === '0rem' ? 'selected' : '' ?>>Plná šířka (0px - Full Bleed)</option>
                          <option value="0.6rem" <?= $oddSidePadVal === '0.6rem' ? 'selected' : '' ?>>Jemné odsazení (0.6rem ~ 10px)</option>
                          <option value="1.2rem" <?= $oddSidePadVal === '1.2rem' || $oddSidePadVal === '1.5rem' ? 'selected' : '' ?>>Standardní odsazení (1.2rem ~ 19px - Doporučeno)</option>
                          <option value="2.0rem" <?= $oddSidePadVal === '2.0rem' || $oddSidePadVal === '2.2rem' ? 'selected' : '' ?>>Výrazně odsazené v rámu (2.0rem ~ 32px)</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Svislé výškové mezerování</label>
                        <select class="form-control" id="design_odd_vert_padding" onchange="liveUpdateDesign()">
                          <option value="1.8rem" <?= $oddVertPadVal === '1.8rem' ? 'selected' : '' ?>>Kompaktní výška (1.8rem ~ 29px)</option>
                          <option value="2.8rem" <?= $oddVertPadVal === '2.8rem' ? 'selected' : '' ?>>Vyvážená výška (2.8rem ~ 45px - Doporučeno)</option>
                          <option value="4.0rem" <?= $oddVertPadVal === '4.0rem' ? 'selected' : '' ?>>Prostorná výška (4.0rem ~ 64px)</option>
                        </select>
                      </div>
                    </div>

                    <!-- EVEN SECTIONS -->
                    <div style="background:rgba(59,130,246,0.08); border:1px solid #3b82f6; padding:1rem; border-radius:8px;">
                      <h5 style="color:#60a5fa; margin-bottom:0.8rem;">🔸 Sudé Sekce (2., 4., 6. v pořadí)</h5>
                      <div class="form-group">
                        <label>Boční odsazení sudých sekcí</label>
                        <select class="form-control" id="design_even_side_padding" onchange="liveUpdateDesign()">
                          <option value="0rem" <?= $evenSidePadVal === '0rem' ? 'selected' : '' ?>>Plná šířka (0px - Full Bleed)</option>
                          <option value="0.6rem" <?= $evenSidePadVal === '0.6rem' ? 'selected' : '' ?>>Jemné odsazení (0.6rem ~ 10px - Doporučeno)</option>
                          <option value="1.2rem" <?= $evenSidePadVal === '1.2rem' || $evenSidePadVal === '1.5rem' ? 'selected' : '' ?>>Standardní odsazení (1.2rem ~ 19px)</option>
                          <option value="2.0rem" <?= $evenSidePadVal === '2.0rem' || $evenSidePadVal === '2.2rem' ? 'selected' : '' ?>>Výrazně odsazené v rámu (2.0rem ~ 32px)</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label>Svislé výškové mezerování</label>
                        <select class="form-control" id="design_even_vert_padding" onchange="liveUpdateDesign()">
                          <option value="1.8rem" <?= $evenVertPadVal === '1.8rem' ? 'selected' : '' ?>>Kompaktní výška (1.8rem ~ 29px)</option>
                          <option value="2.2rem" <?= $evenVertPadVal === '2.2rem' ? 'selected' : '' ?>>Vyvážená výška (2.2rem ~ 35px - Doporučeno)</option>
                          <option value="3.5rem" <?= $evenVertPadVal === '3.5rem' || $evenVertPadVal === '4.0rem' ? 'selected' : '' ?>>Prostorná výška (3.5rem ~ 56px)</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 3. FLOATING STICKY CTA BUTTON SWITCH & SECTION FILTERS -->
                <div class="item-card">
                  <h4>📌 Plovoucí Tlačítko (Floating Sticky CTA pro PC, Tablet i Mobil)</h4>
                  <div class="form-group" style="display:flex; align-items:center; gap:0.8rem; margin-bottom:1rem;">
                    <input type="checkbox" id="design_sticky_enabled" style="width:20px; height:20px; accent-color:var(--accent);" <?= $stickyEnabled ? 'checked' : '' ?> onchange="liveUpdateDesign()" />
                    <label for="design_sticky_enabled" style="font-size:1rem; color:#fff; cursor:pointer; margin:0;">Zobrazovat plovoucí tlačítko na dně obrazovky (pro PC, Tablet i Mobil)</label>
                  </div>

                  <div style="background:rgba(0,0,0,0.3); padding:1rem; border-radius:6px; border:1px solid var(--border);">
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">
                      Nastav, ve kterých sekcích má plovoucí tlačítko automaticky mizet, aby nepřekáželo:
                    </p>
                    <div style="display:flex; flex-direction:column; gap:0.6rem;">
                      <label style="color:#fff; font-weight:normal; cursor:pointer;">
                        <input type="checkbox" id="design_sticky_hide_hero" style="accent-color:var(--accent);" <?= $stickyHideHero ? 'checked' : '' ?> onchange="liveUpdateDesign()" />
                        Skrýt tlačítko v úvodní sekci HERO (při nahoře)
                      </label>
                      <label style="color:#fff; font-weight:normal; cursor:pointer;">
                        <input type="checkbox" id="design_sticky_hide_contact" style="accent-color:var(--accent);" <?= $stickyHideContact ? 'checked' : '' ?> onchange="liveUpdateDesign()" />
                        Skrýt tlačítko v sekci KONTAKT (aby nepřekrývalo formulář)
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TAB MODAL CONTACT: 3 PHASES -->
              <div id="tab-modal-contact" class="tab-content">
                <h3 style="color:#fff; margin-bottom:0.5rem;">💬 Nastavení Textů 3-Fázového Popup Formuláře</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                  Tento formulář se zobrazí jako vyskakovací okno po kliknutí na jakékoliv tlačítko s výzvou na stránce.
                </p>

                <?php
                $mc = $editingData['modal_contact'] ?? [];
                $s1_title = $mc['step1_title'] ?? 'Zanechte nám svůj e-mail';
                $s1_text = $mc['step1_text'] ?? 'Zašleme vám podrobné informace o učednictví u mistra a spojíme se s vámi.';
                $s1_btn = $mc['step1_btn'] ?? 'POKRAČOVAT';

                $s2_title = $mc['step2_title'] ?? 'Doplňující údaje (volitelné)';
                $s2_text = $mc['step2_text'] ?? 'Chcete-li, abychom vám přímo zavolali nebo poslali zprávu, vyplňte vaše jméno a telefon.';
                $s2_btn = $mc['step2_btn'] ?? 'ODESLAT VŠECHNO';
                $s2_wa_text = $mc['step2_wa_text'] ?? 'NAPSAT ROVNOU NA WHATSAPP';

                $s3_title = $mc['step3_title'] ?? 'Děkujeme za váš zájem!';
                $s3_text = $mc['step3_text'] ?? 'Vaši zprávu jsme v pořádku přijali. Ozveme se vám do 24 hodin.';
                $s3_btn = $mc['step3_btn'] ?? 'ZAVŘÍT';
                $s3_web_url = $mc['step3_web_url'] ?? '';
                $s3_web_btn_text = $mc['step3_web_btn_text'] ?? 'Přejít na web';
                $s3_web_desc = $mc['step3_web_desc'] ?? '';
                ?>

                <!-- INTERACTIVE PHASE PREVIEW SWITCHER -->
                <div class="item-card" style="background: rgba(232, 117, 22, 0.08); border-left: 4px solid var(--accent); margin-bottom: 1.5rem;">
                  <h4 style="color:#fff; margin:0 0 0.5rem 0;">👁️ Náhled Fáze Popup Formuláře v Živém Náhledu</h4>
                  <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1rem;">
                    Klikněte na tlačítko fáze níže pro otevření popupu a přepnutí náhledu v iframe:
                  </p>
                  <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <button type="button" class="btn-action btn-copy modal-phase-btn active" onclick="previewModalPhase(1, this)" style="flex:1; justify-content:center; font-weight:700;">1️⃣ Fáze 1 (E-mail)</button>
                    <button type="button" class="btn-action btn-copy modal-phase-btn" onclick="previewModalPhase(2, this)" style="flex:1; justify-content:center; font-weight:700;">2️⃣ Fáze 2 (Údaje & WA)</button>
                    <button type="button" class="btn-action btn-copy modal-phase-btn" onclick="previewModalPhase(3, this)" style="flex:1; justify-content:center; font-weight:700;">3️⃣ Fáze 3 (Poděkování)</button>
                    <button type="button" class="btn-action" onclick="closePreviewModal()" style="background:#374151; border-color:#4b5563; color:#fff; padding:0.4rem 0.8rem;" title="Zavřít náhled popupu">❌ Zavřít</button>
                  </div>
                </div>

                <!-- FÁZE 1 -->
                <div class="item-card" style="border-left:4px solid var(--accent);">
                  <h4>Fáze 1: Zachycení E-mailu (Okamžité uložení do DB)</h4>
                  <div class="form-group">
                    <label>Nadpis 1. fáze <span class="badge-typo h2">📌 Nadpis H2</span></label>
                    <input type="text" class="form-control" id="mc_s1_title" value="<?= htmlspecialchars($s1_title) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Podtitul 1. fáze <span class="badge-typo body">📝 Běžný text</span></label>
                    <textarea class="form-control" id="mc_s1_text" oninput="liveUpdateModalContact()"><?= htmlspecialchars($s1_text) ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Text tlačítka 1. fáze <span class="badge-typo body">📝 Běžný text</span></label>
                    <input type="text" class="form-control" id="mc_s1_btn" value="<?= htmlspecialchars($s1_btn) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                </div>

                <!-- FÁZE 2 -->
                <div class="item-card" style="border-left:4px solid #3b82f6;">
                  <h4>Fáze 2: Doplňující údaje (Jméno, Telefon, Zpráva & WhatsApp)</h4>
                  <div class="form-group">
                    <label>Nadpis 2. fáze <span class="badge-typo h2">📌 Nadpis H2</span></label>
                    <input type="text" class="form-control" id="mc_s2_title" value="<?= htmlspecialchars($s2_title) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Podtitul 2. fáze <span class="badge-typo body">📝 Běžný text</span></label>
                    <textarea class="form-control" id="mc_s2_text" oninput="liveUpdateModalContact()"><?= htmlspecialchars($s2_text) ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Text tlačítka pro odeslání 2. fáze <span class="badge-typo body">📝 Běžný text</span></label>
                    <input type="text" class="form-control" id="mc_s2_btn" value="<?= htmlspecialchars($s2_btn) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Text tlačítka "Napsat na WhatsApp" <span class="badge-typo body">📝 Běžný text</span></label>
                    <input type="text" class="form-control" id="mc_s2_wa_text" value="<?= htmlspecialchars($s2_wa_text) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                </div>

                <!-- FÁZE 3 -->
                <div class="item-card" style="border-left:4px solid #25D366;">
                  <h4>Fáze 3: Poděkování po odeslání</h4>
                  <div class="form-group">
                    <label>Nadpis poděkování <span class="badge-typo h2">📌 Nadpis H2</span></label>
                    <input type="text" class="form-control" id="mc_s3_title" value="<?= htmlspecialchars($s3_title) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Text poděkování <span class="badge-typo body">📝 Běžný text</span></label>
                    <textarea class="form-control" id="mc_s3_text" oninput="liveUpdateModalContact()"><?= htmlspecialchars($s3_text) ?></textarea>
                  </div>
                  <div class="form-group" style="margin-top:1rem; padding-top:0.8rem; border-top:1px dashed rgba(255,255,255,0.15);">
                    <label>🌐 Odkaz na web (URL tlačítka v poděkování)</label>
                    <input type="text" class="form-control" id="mc_s3_web_url" placeholder="https://pacinekglass.com" value="<?= htmlspecialchars($s3_web_url) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Text tlačítka pro odkaz na web</label>
                    <input type="text" class="form-control" id="mc_s3_web_btn_text" placeholder="Přejít na web" value="<?= htmlspecialchars($s3_web_btn_text) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                  <div class="form-group">
                    <label>Popis u tlačítka odkazu na web <span class="badge-typo body">📝 Běžný text</span></label>
                    <textarea class="form-control" id="mc_s3_web_desc" placeholder="Chcete se dozvědět více? Navštivte mé webové stránky." oninput="liveUpdateModalContact()"><?= htmlspecialchars($s3_web_desc) ?></textarea>
                  </div>
                  <div class="form-group" style="margin-top:1rem; padding-top:0.8rem; border-top:1px dashed rgba(255,255,255,0.15);">
                    <label>Text tlačítka pro zavření modal okna <span class="badge-typo body">📝 Běžný text</span></label>
                    <input type="text" class="form-control" id="mc_s3_btn" value="<?= htmlspecialchars($s3_btn) ?>" oninput="liveUpdateModalContact()" />
                  </div>
                </div>
              </div>

              <!-- TAB TYPOGRAPHY: STEP-BASED FONT SIZE CONFIGURATION -->
              <div id="tab-typo" class="tab-content">
                <h3 style="color:#fff; margin-bottom:0.5rem;">🎨 Nastavení Stupňů Velikostí Fontů</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">
                  Vyber stupeň velikosti fontu. Každý stupeň má přesně odladěný poměr pro <strong>Mobil i PC</strong>. Změna se okamžitě projevuje v živém náhledu!
                </p>

                <?php 
                $typoData = $editingData['typography'] ?? []; 
                $h1Step = $typoData['hero_h1'] ?? 'step-3';
                $h2Step = $typoData['section_h2'] ?? 'step-3';
                $h3Step = $typoData['card_h3'] ?? 'step-3';
                $bodyStep = $typoData['body_text'] ?? 'step-3';
                $eyebrowStep = $typoData['eyebrow'] ?? 'step-3';
                $subtitle_step = $typoData['subtitle'] ?? 'step-3';
                ?>

                <div class="item-card">
                  <h4>🚀 Hlavní nadpis Hero (H1) <span class="badge-typo h1">🚀 Hero H1</span></h4>
                  <div class="form-group">
                    <label>Stupeň velikosti hlavní tituly (H1) a jména mistra</label>
                    <select class="form-control typo-select" id="typo_hero_h1" onchange="liveUpdateTypography()">
                      <option value="step-1" <?= $h1Step === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Kompaktní</option>
                      <option value="step-2" <?= $h1Step === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Standardní</option>
                      <option value="step-3" <?= $h1Step === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Výrazný (Doporučeno)</option>
                      <option value="step-4" <?= $h1Step === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Velký</option>
                      <option value="step-5" <?= $h1Step === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Extra Velký</option>
                      <option value="step-6" <?= $h1Step === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Gigantický</option>
                    </select>
                  </div>
                </div>

                <div class="item-card">
                  <h4>📌 Nadpisy sekcí (H2) <span class="badge-typo h2">📌 Nadpis H2</span></h4>
                  <div class="form-group">
                    <label>Stupeň velikosti pro hlavních nadpisů sekcí (UVP, Galerie, CTA...)</label>
                    <select class="form-control typo-select" id="typo_section_h2" onchange="liveUpdateTypography()">
                      <option value="step-1" <?= $h2Step === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Kompaktní</option>
                      <option value="step-2" <?= $h2Step === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Standardní</option>
                      <option value="step-3" <?= $h2Step === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Výrazný (Doporučeno)</option>
                      <option value="step-4" <?= $h2Step === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Velký</option>
                      <option value="step-5" <?= $h2Step === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Extra Velký</option>
                      <option value="step-6" <?= $h2Step === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Gigantický</option>
                    </select>
                  </div>
                </div>

                <div class="item-card">
                  <h4>🎴 Nadpisy karet a dovedností (H3 & H4) <span class="badge-typo h3">🎴 Karta H3/H4</span></h4>
                  <div class="form-group">
                    <label>Stupeň velikosti pro podnadpisy v kartách, dovednostech a FAQ</label>
                    <select class="form-control typo-select" id="typo_card_h3" onchange="liveUpdateTypography()">
                      <option value="step-1" <?= $h3Step === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Kompaktní</option>
                      <option value="step-2" <?= $h3Step === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Standardní</option>
                      <option value="step-3" <?= $h3Step === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Výrazný (Doporučeno)</option>
                      <option value="step-4" <?= $h3Step === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Velký</option>
                      <option value="step-5" <?= $h3Step === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Extra Velký</option>
                      <option value="step-6" <?= $h3Step === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Gigantický</option>
                    </select>
                  </div>
                </div>

                <div class="item-card">
                  <h4>📝 Běžný text a odstavce (Body text) <span class="badge-typo body">📝 Běžný text</span></h4>
                  <div class="form-group">
                    <label>Stupeň základní velikosti textu (odstavce, popisy, citace)</label>
                    <select class="form-control typo-select" id="typo_body_text" onchange="liveUpdateTypography()">
                      <option value="step-minus-2" <?= $bodyStep === 'step-minus-2' ? 'selected' : '' ?>>Stupeň -2 – Extrémně drobný text</option>
                      <option value="step-minus-1" <?= $bodyStep === 'step-minus-1' ? 'selected' : '' ?>>Stupeň -1 – Drobný text</option>
                      <option value="step-1" <?= $bodyStep === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Jemný text</option>
                      <option value="step-2" <?= $bodyStep === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Standardní text</option>
                      <option value="step-3" <?= $bodyStep === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Čitelný (Doporučeno)</option>
                      <option value="step-4" <?= $bodyStep === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Větší text</option>
                      <option value="step-5" <?= $bodyStep === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Prostorný text</option>
                      <option value="step-6" <?= $bodyStep === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Maxi text</option>
                    </select>
                  </div>
                </div>

                <div class="item-card">
                  <h4>📄 Podtituly (Dlouhý popis nabídky) <span class="badge-typo body" style="font-size:0.95rem;">📄 Podtitul</span></h4>
                  <div class="form-group">
                    <label>Stupeň velikosti pro dlouhé popisy a podtituly pod hlavními nadpisy</label>
                    <select class="form-control typo-select" id="typo_subtitle" onchange="liveUpdateTypography()">
                      <option value="step-1" <?= $subtitle_step === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Jemný</option>
                      <option value="step-2" <?= $subtitle_step === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Standardní</option>
                      <option value="step-3" <?= $subtitle_step === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Výrazný (Doporučeno)</option>
                      <option value="step-4" <?= $subtitle_step === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Velký</option>
                      <option value="step-5" <?= $subtitle_step === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Extra velký</option>
                      <option value="step-6" <?= $subtitle_step === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Maxi</option>
                    </select>
                  </div>
                </div>

                <div class="item-card">
                  <h4>🏷️ Malé štítky a tituly (Eyebrows) <span class="badge-typo eyebrow">🏷️ Štítek</span></h4>
                  <div class="form-group">
                    <label>Stupeň velikosti pro horní malé štítky (Eyebrow text) a podtituly mistra</label>
                    <select class="form-control typo-select" id="typo_eyebrow" onchange="liveUpdateTypography()">
                      <option value="step-1" <?= $eyebrowStep === 'step-1' ? 'selected' : '' ?>>Stupeň 1 – Mikro (0.7rem ~ 11px)</option>
                      <option value="step-2" <?= $eyebrowStep === 'step-2' ? 'selected' : '' ?>>Stupeň 2 – Kompaktní (0.78rem ~ 12.5px)</option>
                      <option value="step-3" <?= $eyebrowStep === 'step-3' ? 'selected' : '' ?>>Stupeň 3 – Standardní (0.85rem ~ 13.5px - Doporučeno)</option>
                      <option value="step-4" <?= $eyebrowStep === 'step-4' ? 'selected' : '' ?>>Stupeň 4 – Výrazný (0.95rem ~ 15px)</option>
                      <option value="step-5" <?= $eyebrowStep === 'step-5' ? 'selected' : '' ?>>Stupeň 5 – Velký (1.05rem ~ 17px)</option>
                      <option value="step-6" <?= $eyebrowStep === 'step-6' ? 'selected' : '' ?>>Stupeň 6 – Extra Velký (1.15rem ~ 18.5px)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- TAB 1: HERO -->
              <div id="tab-hero" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🚀 Sekce 1: HERO (Úvodní obrazovka)</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text nad nadpisem) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="h_eyebrow" value="<?= htmlspecialchars($editingData['hero']['eyebrow'] ?? 'UČEDNICTVÍ U MISTRA SKLÁŘE') ?>" />
                </div>
                <div class="form-group">
                  <label>Hlavní Nadpis (H1) <span class="badge-typo h1">🚀 Hero H1</span></label>
                  <input type="text" class="form-control" id="h_h1" value="<?= htmlspecialchars($editingData['hero']['h1'] ?? 'NAUČ SE ŘEMESLO PŘÍMO OD MISTRA') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul (Dlouhý popis nabídky) <span class="badge-typo body">📝 Běžný text</span></label>
                  <textarea class="form-control" id="h_sub"><?= htmlspecialchars($editingData['hero']['subtitle'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Text hlavního tlačítka (CTA) <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="h_btn1" value="<?= htmlspecialchars($editingData['hero']['btn_primary'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ') ?>" />
                </div>
                <div class="form-group">
                  <label>Text druhého tlačítka <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="h_btn2" value="<?= htmlspecialchars($editingData['hero']['btn_secondary'] ?? 'PODÍVAT SE, JAK SE PRACUJE SE SKLEM') ?>" />
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:0.6rem; margin-bottom:1.2rem; background:rgba(255,255,255,0.03); padding:0.8rem 1rem; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                  <input type="checkbox" id="h_btn2_show" style="width:20px; height:20px; accent-color:var(--accent); cursor:pointer;" <?= ($editingData['hero']['btn_secondary_show'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateDesign()" />
                  <label for="h_btn2_show" style="margin:0; cursor:pointer; font-weight:600;">Zobrazit druhé tlačítko v HERO sekci</label>
                </div>
                
                <div style="margin-top:1.5rem; background:rgba(232,117,22,0.08); padding:1rem; border-radius:8px; border:1px solid var(--accent);">
                  <h4 style="color:var(--accent); margin-bottom:0.6rem;">📢 Tlačítka s výzvou na konci sekcí (Section CTA)</h4>
                  <div class="form-group" style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.5rem;">
                    <input type="checkbox" id="sec_cta_same_text" style="width:20px; height:20px; accent-color:var(--accent); cursor:pointer;"
                      <?= $secCtaSameText ? 'checked' : '' ?> onchange="onSecCtaSameTextChange()" />
                    <label for="sec_cta_same_text" style="margin:0; cursor:pointer; font-weight:700; color:#fff;">
                      Text tlačítka stejný ve všech sekcích (přebírat z Primárního tlačítka HERO)
                    </label>
                  </div>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">
                    Pokud je zaškrtnuto, všechna tlačítka na konci jednotlivých sekcí automaticky použijí text primárního tlačítka z HERO. Pokud zrušíte zaškrtnutí, můžete u každé sekce nastavit vlastní text.
                  </p>
                </div>

                <div class="form-group" style="margin-top:1.2rem;">
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
                  <label>Eyebrow (malý text nad nadpisem) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="uvp_eyebrow" value="<?= htmlspecialchars($editingData['uvp']['eyebrow'] ?? 'Proč toto učednictví?') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce UVP <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="uvp_title" value="<?= htmlspecialchars($editingData['uvp']['title'] ?? 'SKUTEČNÉ ŘEMESLO. SKUTEČNÝ MISTR. SKUTEČNÁ PRÁCE.') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="uvp_sub" value="<?= htmlspecialchars($editingData['uvp']['subtitle'] ?? '') ?>" />
                </div>
                <h4 style="color:#fff; margin:1rem 0 0.5rem;">Argumentační karty:</h4>
                <div id="uvp_cards_container">
                  <?php foreach (($editingData['uvp']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card uvp-item-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Karta #<?= $idx+1 ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.uvp-item-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.uvp-item-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.uvp-item-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
                      <div class="form-group"><label>Titulek karty <span class="badge-typo h3">🎴 Karta H3</span></label><input type="text" class="form-control uvp-item-title" value="<?= htmlspecialchars($item['title']) ?>" /></div>
                      <div class="form-group"><label>Popis karty <span class="badge-typo body">📝 Běžný text</span></label><textarea class="form-control uvp-item-desc"><?= htmlspecialchars($item['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addUvpItem()">➕ Přidat kartu UVP</button>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_uvp" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['uvp'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce UVP</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_uvp"
                      value="<?= htmlspecialchars($secCtaTexts['uvp'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 3: MISTR -->
              <div id="tab-master" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">👑 Sekce 3: O mistrovi</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text nad jménem) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="m_eyebrow" value="<?= htmlspecialchars($editingData['master']['eyebrow'] ?? 'KDO TĚ BUDE UČIT') ?>" />
                </div>
                <div class="form-group">
                  <label>Jméno mistra <span class="badge-typo h1">🚀 Velký nadpis H1</span></label>
                  <input type="text" class="form-control" id="m_name" value="<?= htmlspecialchars($editingData['master']['name'] ?? 'JIŘÍ PAČINEK') ?>" />
                </div>
                <div class="form-group">
                  <label>Titul mistra & Sklárna <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="m_title" value="<?= htmlspecialchars($editingData['master']['title'] ?? 'MISTR SKLÁŘ & ZAKLADATEL SKLÁRNY PAČINEK GLASS') ?>" />
                </div>
                <div class="form-group">
                  <label>Příběh mistra (Popis 1. část) <span class="badge-typo body">📝 Běžný text</span></label>
                  <textarea class="form-control" id="m_bio"><?= htmlspecialchars($editingData['master']['bio'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Citát mistra <span class="badge-typo h2">📌 Citát / H2</span></label>
                  <textarea class="form-control" id="m_quote"><?= htmlspecialchars($editingData['master']['quote'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Příběh mistra (Popis 2. část) <span class="badge-typo body">📝 Běžný text</span></label>
                  <textarea class="form-control" id="m_bio2"><?= htmlspecialchars($editingData['master']['bio2'] ?? 'V dílně sází na poctivý přístup a předávání sklářského umění další generaci.') ?></textarea>
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
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_master" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['master'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce MISTR</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_master"
                      value="<?= htmlspecialchars($secCtaTexts['master'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 4: CO SE NAUČÍŠ -->
              <div id="tab-outcomes" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">📚 Sekce 4: Co se učedník naučí</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="o_eyebrow" value="<?= htmlspecialchars($editingData['outcomes']['eyebrow'] ?? 'Praktické dovednosti') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="o_title" value="<?= htmlspecialchars($editingData['outcomes']['title'] ?? 'CO SE UČEDNÍK SKUTEČNĚ NAUČÍ') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="o_sub" value="<?= htmlspecialchars($editingData['outcomes']['subtitle'] ?? 'Konkrétní znalosti a návyky, které si z dílny odneseš do života.') ?>" />
                </div>
                <div id="outcomes_container">
                  <?php foreach (($editingData['outcomes']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card outcome-item-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Dovednost #<?= $idx+1 ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.outcome-item-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.outcome-item-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.outcome-item-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
                      <div class="form-group"><label>Ikona (Emoji)</label><input type="text" class="form-control outcome-icon" value="<?= htmlspecialchars($item['icon']) ?>" /></div>
                      <div class="form-group"><label>Název dovednosti <span class="badge-typo h3">🎴 Karta H4</span></label><input type="text" class="form-control outcome-title" value="<?= htmlspecialchars($item['title']) ?>" /></div>
                      <div class="form-group"><label>Popis dovednosti <span class="badge-typo body">📝 Běžný text</span></label><textarea class="form-control outcome-desc"><?= htmlspecialchars($item['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addOutcomeItem()">➕ Přidat dovednost</button>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_outcomes" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['outcomes'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce CO SE NAUČÍŠ</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_outcomes"
                      value="<?= htmlspecialchars($secCtaTexts['outcomes'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 5: POSTUP -->
              <div id="tab-timeline" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🗺️ Sekce 5: Jak probíhá cesta k učednictví</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="t_eyebrow" value="<?= htmlspecialchars($editingData['timeline']['eyebrow'] ?? 'Postup krok za krokem') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="t_title" value="<?= htmlspecialchars($editingData['timeline']['title'] ?? 'JAK PROBÍHÁ CESTA K UČEDNICTVÍ') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="t_sub" value="<?= htmlspecialchars($editingData['timeline']['subtitle'] ?? 'Pět jasných kroků od prvního projevení zájmu až po začátek práce v dílně.') ?>" />
                </div>
                <div id="timeline_container">
                  <?php foreach (($editingData['timeline']['steps'] ?? []) as $idx => $step): ?>
                    <div class="item-card timeline-step-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Krok <?= htmlspecialchars($step['num']) ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.timeline-step-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.timeline-step-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.timeline-step-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
                      <div class="form-group"><label>Číslo kroku</label><input type="text" class="form-control step-num" value="<?= htmlspecialchars($step['num']) ?>" /></div>
                      <div class="form-group"><label>Název kroku <span class="badge-typo h3">🎴 Karta H4</span></label><input type="text" class="form-control step-title" value="<?= htmlspecialchars($step['title']) ?>" /></div>
                      <div class="form-group"><label>Popis kroku <span class="badge-typo body">📝 Běžný text</span></label><textarea class="form-control step-desc"><?= htmlspecialchars($step['desc']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addTimelineStep()">➕ Přidat krok</button>
                <div class="form-group" style="margin-top:1rem;">
                  <label>Poznámka pod postupem (Disclaimer) <span class="badge-typo body">📝 Běžný text</span></label>
                  <textarea class="form-control" id="t_disc"><?= htmlspecialchars($editingData['timeline']['disclaimer'] ?? '💡 Základní cesta vypadá takto. Konkrétní průběh se přizpůsobuje zájemci.') ?></textarea>
                </div>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_timeline" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['timeline'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce POSTUP</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_timeline"
                      value="<?= htmlspecialchars($secCtaTexts['timeline'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 6: GALERIE -->
              <div id="tab-portfolio" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🖼️ Sekce 6: Galerie a Fotky z huti</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="p_eyebrow" value="<?= htmlspecialchars($editingData['portfolio']['eyebrow'] ?? 'Ukázka z prostředí') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="p_title" value="<?= htmlspecialchars($editingData['portfolio']['title'] ?? 'CO VZNIKÁ V DÍLNĚ') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="p_sub" value="<?= htmlspecialchars($editingData['portfolio']['subtitle'] ?? 'Nahlédni do živého procesu sklářského umění.') ?>" />
                </div>
                
                <div id="portfolio_container">
                  <?php foreach (($editingData['portfolio']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card portfolio-item-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Fotka #<?= $idx+1 ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.portfolio-item-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.portfolio-item-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.portfolio-item-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
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
                      <div class="form-group"><label>Popisek pod fotkou <span class="badge-typo body">📝 Běžný text</span></label><input type="text" class="form-control p-cap" value="<?= htmlspecialchars($item['caption']) ?>" oninput="liveUpdatePortfolio()" /></div>
                      <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.8rem; margin-bottom:0;">
                        <div class="form-group" style="margin-bottom:0;">
                          <label>Poměr stran</label>
                          <?php $itemRatio = $item['aspect_ratio'] ?? '4/3'; ?>
                          <select class="form-control p-ratio" onchange="liveUpdatePortfolio()">
                            <option value="4/3" <?= $itemRatio === '4/3' ? 'selected' : '' ?>>🖼️ 4:3</option>
                            <option value="16/9" <?= $itemRatio === '16/9' ? 'selected' : '' ?>>📺 16:9</option>
                            <option value="1/1" <?= $itemRatio === '1/1' ? 'selected' : '' ?>>🔲 1:1</option>
                            <option value="3/2" <?= $itemRatio === '3/2' ? 'selected' : '' ?>>📷 3:2</option>
                            <option value="3/4" <?= $itemRatio === '3/4' ? 'selected' : '' ?>>📱 3:4</option>
                            <option value="auto" <?= $itemRatio === 'auto' ? 'selected' : '' ?>>↔️ Auto (Původní)</option>
                          </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                          <label>Způsob ořezu</label>
                          <?php $itemFit = $item['object_fit'] ?? 'cover'; ?>
                          <select class="form-control p-fit" onchange="liveUpdatePortfolio()">
                            <option value="cover" <?= $itemFit === 'cover' ? 'selected' : '' ?>>✂️ Vyplnit (Cover)</option>
                            <option value="contain" <?= $itemFit === 'contain' ? 'selected' : '' ?>>🔍 Celá (Contain)</option>
                          </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                          <label>Pozice výřezu</label>
                          <?php $itemPos = $item['object_position'] ?? 'center'; ?>
                          <select class="form-control p-pos" onchange="liveUpdatePortfolio()">
                            <option value="center" <?= $itemPos === 'center' ? 'selected' : '' ?>>🎯 Střed</option>
                            <option value="top" <?= $itemPos === 'top' ? 'selected' : '' ?>>⬆️ Horní</option>
                            <option value="bottom" <?= $itemPos === 'bottom' ? 'selected' : '' ?>>⬇️ Spodní</option>
                            <option value="left" <?= $itemPos === 'left' ? 'selected' : '' ?>>⬅️ Levá</option>
                            <option value="right" <?= $itemPos === 'right' ? 'selected' : '' ?>>➡️ Pravá</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addPortfolioItem()">➕ Přidat fotku</button>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_portfolio" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['portfolio'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce GALERIE</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_portfolio"
                      value="<?= htmlspecialchars($secCtaTexts['portfolio'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 7: REFERENCE -->
              <div id="tab-testimonials" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">💬 Sekce 7: Reference</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="ts_eyebrow" value="<?= htmlspecialchars($editingData['testimonials']['eyebrow'] ?? 'Zkušenosti a reference') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="ts_title" value="<?= htmlspecialchars($editingData['testimonials']['title'] ?? 'CO ŘÍKAJÍ LIDÉ, KTEŘÍ U TOHO BYLI') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="ts_sub" value="<?= htmlspecialchars($editingData['testimonials']['subtitle'] ?? 'Reálné pohledy zástupců a rodin.') ?>" />
                </div>
                <div id="testimonials_container">
                  <?php foreach (($editingData['testimonials']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card testimonial-item-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Reference #<?= $idx+1 ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.testimonial-item-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.testimonial-item-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.testimonial-item-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
                      <div class="form-group"><label>Nadpis reference (volitelné) <span class="badge-typo h3">🎴 Karta H4</span></label><input type="text" class="form-control ts-title" value="<?= htmlspecialchars($item['title'] ?? '') ?>" placeholder="Např. Skvělá zkušenost s mistrem" /></div>
                      <div class="form-group"><label>Citace <span class="badge-typo body">📝 Běžný text</span></label><textarea class="form-control ts-quote"><?= htmlspecialchars($item['quote']) ?></textarea></div>
                      <div class="form-group"><label>Jméno autora <span class="badge-typo body">📝 Běžný text</span></label><input type="text" class="form-control ts-name" value="<?= htmlspecialchars($item['name']) ?>" /></div>
                      <div class="form-group"><label>Role / Vztah k dílně <span class="badge-typo eyebrow">🏷️ Štítek</span></label><input type="text" class="form-control ts-role" value="<?= htmlspecialchars($item['role']) ?>" /></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addTestimonialItem()">➕ Přidat referenci</button>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_testimonials" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['testimonials'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce REFERENCE</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_testimonials"
                      value="<?= htmlspecialchars($secCtaTexts['testimonials'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 8: FAQ -->
              <div id="tab-faq" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">❓ Sekce 8: Časté otázky</h3>
                <div class="form-group">
                  <label>Eyebrow (malý text) <span class="badge-typo eyebrow">🏷️ Štítek</span></label>
                  <input type="text" class="form-control" id="f_eyebrow" value="<?= htmlspecialchars($editingData['faq']['eyebrow'] ?? 'Odpovědi na dotazy') ?>" />
                </div>
                <div class="form-group">
                  <label>Nadpis sekce <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="f_title" value="<?= htmlspecialchars($editingData['faq']['title'] ?? 'ČASTÉ OTÁZKY') ?>" />
                </div>
                <div class="form-group">
                  <label>Podtitul sekce <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="f_sub" value="<?= htmlspecialchars($editingData['faq']['subtitle'] ?? 'Vše, co tě může zajímat před prvním kontaktem.') ?>" />
                </div>
                <div id="faq_container">
                  <?php foreach (($editingData['faq']['items'] ?? []) as $idx => $item): ?>
                    <div class="item-card faq-item-box">
                      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <h4 style="margin:0;">Otázka #<?= $idx+1 ?></h4>
                        <div style="display:flex; gap:0.3rem;">
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.faq-item-box', -1)">⬆️</button>
                          <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.faq-item-box', 1)">⬇️</button>
                          <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.faq-item-box')">🗑️ Odebrat</button>
                        </div>
                      </div>
                      <div class="form-group"><label>Otázka <span class="badge-typo h3">🎴 Otázka H4</span></label><input type="text" class="form-control faq-q" value="<?= htmlspecialchars($item['q']) ?>" /></div>
                      <div class="form-group"><label>Odpověď <span class="badge-typo body">📝 Běžný text</span></label><textarea class="form-control faq-a"><?= htmlspecialchars($item['a']) ?></textarea></div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn-action btn-copy" style="margin-bottom:1rem; width:100%;" onclick="addFaqItem()">➕ Přidat otázku</button>
                <div class="item-card" style="border-left: 3px solid var(--accent); margin-top: 1.5rem;">
                  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.6rem; margin:0; cursor:pointer;">
                      <input type="checkbox" class="sec-cta-show-checkbox" id="sec_cta_show_faq" style="width:18px; height:18px; accent-color:var(--accent);"
                        <?= ($secCtaVisibility['faq'] ?? true) ? 'checked' : '' ?> onchange="liveUpdateSecCtaVisibility()" />
                      <strong style="color:#fff;">Zobrazit CTA tlačítko na konci sekce ČASTÉ OTÁZKY</strong>
                    </label>
                  </div>
                  <div class="form-group" style="margin-top:0.8rem; margin-bottom:0;">
                    <label>Text tlačítka pro tuto sekci</label>
                    <input type="text" class="form-control sec-cta-text-input" id="sec_cta_text_faq"
                      value="<?= htmlspecialchars($secCtaTexts['faq'] ?? '') ?>" <?= $secCtaSameText ? 'disabled' : '' ?> oninput="liveUpdateSecCtaTexts()" />
                    <small class="sec-cta-text-note" style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">
                      <?= $secCtaSameText ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.' ?>
                    </small>
                  </div>
                </div>
              </div>

              <!-- TAB 9: CTA BOX -->
              <div id="tab-cta" class="tab-content">
                <h3 style="color:#fff; margin-bottom:1rem;">🎯 Sekce 9: Závěrečná sekce s tlačítkem (CTA Box)</h3>
                <div class="form-group">
                  <label>Nadpis v CTA boxu <span class="badge-typo h2">📌 Nadpis H2</span></label>
                  <input type="text" class="form-control" id="cta_title" value="<?= htmlspecialchars($editingData['cta']['title'] ?? 'ZAJÍMÁ TĚ, JESTLI JE TO PRO TEBE?') ?>" />
                </div>
                <div class="form-group">
                  <label>Text v CTA boxu <span class="badge-typo body">📝 Běžný text</span></label>
                  <textarea class="form-control" id="cta_text"><?= htmlspecialchars($editingData['cta']['text'] ?? 'Nemusíš se hned k ničemu zavazovat. První krok je jednoduchý nezávazný rozhovor.') ?></textarea>
                </div>
                <div class="form-group">
                  <label>Text tlačítka v CTA boxu <span class="badge-typo body">📝 Běžný text</span></label>
                  <input type="text" class="form-control" id="cta_btn" value="<?= htmlspecialchars($editingData['cta']['btn'] ?? 'ZJISTIT, JESTLI JE TO PRO MĚ') ?>" />
                </div>
              </div>

              <div style="margin-top: 2rem;">
                <button type="submit" name="save_sections_form" onclick="prepareJsonData()" class="btn-action btn-view" style="font-size: 1rem; padding: 0.8rem 2rem;">
                  <i class="bi bi-check-circle-fill"></i> Uložit všechny sekce, fotky a pořadí
                </button>
                <a href="landing_pages.php" class="btn-action btn-copy" style="margin-left: 1rem;">Zavřít editor</a>
              </div>
            </div>

            <!-- RIGHT: LIVE PREVIEW IFRAME & DEVICE SWITCHER -->
            <div>
              <div class="preview-header">
                <h3 style="color:#fff; margin:0;"><i class="bi bi-eye"></i> Živý Náhled Stránky</h3>
                <div class="device-switcher">
                  <button type="button" class="device-btn active" id="btn_desktop" onclick="setPreviewDevice('desktop')"><i class="bi bi-display"></i> Desktop</button>
                  <button type="button" class="device-btn" id="btn_tablet" onclick="setPreviewDevice('tablet')"><i class="bi bi-tablet"></i> Tablet (430px)</button>
                  <button type="button" class="device-btn" id="btn_mobile" onclick="setPreviewDevice('mobile')"><i class="bi bi-phone"></i> Mobil (375px)</button>
                  <button type="button" class="device-btn" onclick="refreshPreview()"><i class="bi bi-arrow-clockwise"></i> Obnovit</button>
                </div>
              </div>

              <div class="preview-wrapper">
                <iframe id="livePreviewFrame" class="preview-iframe" src="landing_pages/<?= htmlspecialchars($editingSlug) ?>.html?v=<?= time() ?>"></iframe>
              </div>
            </div>
          </div>
        </form>
      </div>

      <script>
        let currentSectionOrder = <?= json_encode($editingData['section_order'] ?? ['hero', 'uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq', 'cta']) ?>;
        let currentSectionVisibility = <?= json_encode($editingData['section_visibility'] ?? (object)[]) ?>;

        const sectionLabels = {
          'hero': '🚀 HERO (Úvodní obrazovka)',
          'uvp': '💎 UVP (Proč toto učednictví)',
          'master': '👑 MISTR (O mistrovi)',
          'outcomes': '📚 CO SE NAUČÍŠ',
          'timeline': '🗺️ POSTUP (Timeline 5 kroků)',
          'portfolio': '🖼️ GALERIE FOTEK',
          'testimonials': '💬 REFERENCE',
          'faq': '❓ ČASTÉ OTÁZKY (FAQ)',
          'cta': '🎯 ZÁVĚREČNÁ SEKCE S TLAČÍTKEM (CTA BOX)'
        };

        const typoScales = {
          hero_h1: {
            'step-1': 'clamp(1.5rem, 4vw, 2.2rem)',
            'step-2': 'clamp(1.75rem, 4.5vw, 2.8rem)',
            'step-3': 'clamp(2.0rem, 5vw, 3.4rem)',
            'step-4': 'clamp(2.25rem, 5.5vw, 4.0rem)',
            'step-5': 'clamp(2.5rem, 6vw, 4.6rem)',
            'step-6': 'clamp(2.8rem, 6.5vw, 5.2rem)'
          },
          section_h2: {
            'step-1': 'clamp(1.3rem, 3vw, 1.8rem)',
            'step-2': 'clamp(1.5rem, 3.5vw, 2.2rem)',
            'step-3': 'clamp(1.7rem, 4vw, 2.6rem)',
            'step-4': 'clamp(1.9rem, 4.5vw, 3.0rem)',
            'step-5': 'clamp(2.1rem, 5vw, 3.4rem)',
            'step-6': 'clamp(2.4rem, 5.5vw, 3.8rem)'
          },
          card_h3: {
            'step-1': 'clamp(1.05rem, 2vw, 1.2rem)',
            'step-2': 'clamp(1.15rem, 2.2vw, 1.35rem)',
            'step-3': 'clamp(1.25rem, 2.5vw, 1.5rem)',
            'step-4': 'clamp(1.35rem, 2.8vw, 1.7rem)',
            'step-5': 'clamp(1.5rem, 3vw, 1.9rem)',
            'step-6': 'clamp(1.65rem, 3.2vw, 2.1rem)'
          },
          body_text: {
            'step-minus-2': '0.75rem',
            'step-minus-1': '0.85rem',
            'step-1': '0.95rem',
            'step-2': '1rem',
            'step-3': '1.05rem',
            'step-4': '1.15rem',
            'step-5': '1.25rem',
            'step-6': '1.35rem'
          },
          eyebrow: {
            'step-1': '0.7rem',
            'step-2': '0.78rem',
            'step-3': '0.85rem',
            'step-4': '0.95rem',
            'step-5': '1.05rem',
            'step-6': '1.15rem'
          },
          subtitle: {
            'step-1': 'clamp(0.85rem, 1.8vw, 0.95rem)',
            'step-2': 'clamp(0.9rem, 1.9vw, 1.05rem)',
            'step-3': 'clamp(0.95rem, 2vw, 1.1rem)',
            'step-4': 'clamp(1.05rem, 2.2vw, 1.25rem)',
            'step-5': 'clamp(1.15rem, 2.5vw, 1.4rem)',
            'step-6': 'clamp(1.25rem, 2.8vw, 1.55rem)'
          }
        };

        const themeVariables = {
          amber: { primary: '#0B0A08', dark: '#17120E', cream: '#F4EFE7', white: '#FFFDF8', accent: '#E87516', glass: 'rgba(255, 255, 255, 0.05)', glass_border: 'rgba(255, 255, 255, 0.1)', text: '#F4EFE7', text_muted: '#A39B8E', btn_hover: '#d0640d', body_bg: 'linear-gradient(135deg, #0B0A08, #17120E)' },
          cyan: { primary: '#050B11', dark: '#0C1622', cream: '#E0F2FE', white: '#FFFFFF', accent: '#00D2C4', glass: 'rgba(0, 210, 196, 0.06)', glass_border: 'rgba(0, 210, 196, 0.2)', text: '#E0F2FE', text_muted: '#8BAAC4', btn_hover: '#00b3a6', body_bg: 'linear-gradient(135deg, #050B11, #0C1622)' },
          gold: { primary: '#11100E', dark: '#1C1A17', cream: '#FBF7EE', white: '#FFFFFF', accent: '#D4AF37', glass: 'rgba(212, 175, 55, 0.06)', glass_border: 'rgba(212, 175, 55, 0.2)', text: '#FBF7EE', text_muted: '#B5A895', btn_hover: '#b89628', body_bg: 'linear-gradient(135deg, #11100E, #1C1A17)' },
          ruby: { primary: '#120709', dark: '#1E0D11', cream: '#FFF0F2', white: '#FFFFFF', accent: '#E14D4D', glass: 'rgba(225, 77, 77, 0.06)', glass_border: 'rgba(225, 77, 77, 0.2)', text: '#FFF0F2', text_muted: '#C499A0', btn_hover: '#c83b3b', body_bg: 'linear-gradient(135deg, #120709, #1E0D11)' },
          light: { primary: '#F8F6F0', dark: '#EEEBE1', cream: '#211E1B', white: '#12100E', accent: '#B85D0D', glass: 'rgba(0, 0, 0, 0.04)', glass_border: 'rgba(0, 0, 0, 0.12)', text: '#211E1B', text_muted: '#665F57', btn_hover: '#9c4c07', body_bg: 'linear-gradient(135deg, #F8F6F0, #EEEBE1)' }
        };

        const btnSizes = {
          s:  { pv: '0.6rem',  ph: '1.2rem', fs: '0.9rem',  pv_m: '0.45rem', ph_m: '0.9rem',  fs_m: '0.8rem' },
          m:  { pv: '0.9rem',  ph: '1.8rem', fs: '1.05rem', pv_m: '0.6rem',  ph_m: '1.2rem',  fs_m: '0.85rem' },
          l:  { pv: '1.15rem', ph: '2.4rem', fs: '1.2rem',  pv_m: '0.75rem', ph_m: '1.5rem',  fs_m: '0.92rem' },
          xl: { pv: '1.4rem',  ph: '3.0rem', fs: '1.35rem', pv_m: '0.9rem',  ph_m: '1.8rem',  fs_m: '1.0rem' }
        };

        function selectTheme(key, el) {
          document.querySelectorAll('.theme-card-option').forEach(opt => opt.classList.remove('selected'));
          if (el) {
            el.classList.add('selected');
          } else if (window.event && window.event.currentTarget) {
            window.event.currentTarget.classList.add('selected');
          }
          document.getElementById('design_color_theme').value = key;
          liveUpdateDesign();
        }

        function selectBtnSize(key, el) {
          document.querySelectorAll('.btn-size-option').forEach(opt => opt.classList.remove('selected'));
          if (el) {
            el.classList.add('selected');
          } else if (window.event && window.event.currentTarget) {
            window.event.currentTarget.classList.add('selected');
          }
          const hiddenInput = document.getElementById('design_btn_size');
          if (hiddenInput) hiddenInput.value = key;
          liveUpdateDesign();
        }

        // Real-Time Live Design Updates (Themes, Odd/Even Margins, Sticky CTA, Button Sizes)
        function liveUpdateDesign() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentWindow || !iframe.contentDocument) return;
          try {
            const doc = iframe.contentDocument;
            const root = doc.documentElement;
            const body = doc.body;
            if (!root || !body) return;

            const themeKey = document.getElementById('design_color_theme').value || 'amber';
            const tvars = themeVariables[themeKey] || themeVariables.amber;

            root.style.setProperty('--color-primary', tvars.primary);
            root.style.setProperty('--color-dark', tvars.dark);
            root.style.setProperty('--color-cream', tvars.cream);
            root.style.setProperty('--color-white', tvars.white);
            root.style.setProperty('--color-accent', tvars.accent);
            root.style.setProperty('--color-glass', tvars.glass);
            root.style.setProperty('--color-glass-border', tvars.glass_border);
            root.style.setProperty('--text', tvars.text);
            root.style.setProperty('--text-muted', tvars.text_muted);
            root.style.setProperty('--btn-hover', tvars.btn_hover);
            body.style.background = tvars.body_bg;
            body.style.color = tvars.text;

            const bSizeKey = document.getElementById('design_btn_size')?.value || 'm';
            const bSize = btnSizes[bSizeKey] || btnSizes.m;
            // Detect preview device – iframe @media query NEVER fires inside editor (iframe viewport = full editor width)
            // So we manually apply desktop or mobile sizes based on the active device button
            const isMobile = (currentPreviewDevice === 'mobile' || currentPreviewDevice === 'tablet');
            const activePv = isMobile ? bSize.pv_m : bSize.pv;
            const activePh = isMobile ? bSize.ph_m : bSize.ph;
            const activeFs = isMobile ? bSize.fs_m : bSize.fs;
            root.style.setProperty('--btn-pad-v', activePv);
            root.style.setProperty('--btn-pad-h', activePh);
            root.style.setProperty('--btn-font-size', activeFs);
            // Also set mob vars so real device visitors use correct sizes via @media
            root.style.setProperty('--btn-pad-v-mob', bSize.pv_m);
            root.style.setProperty('--btn-pad-h-mob', bSize.ph_m);
            root.style.setProperty('--btn-font-size-mob', bSize.fs_m);
            // PHP bakes sizes as literal values, not var() – so we MUST set inline style on each .btn for live preview
            doc.querySelectorAll('.btn').forEach(btn => {
              btn.style.setProperty('padding', activePv + ' ' + activePh, 'important');
              btn.style.setProperty('font-size', activeFs, 'important');
            });

            const oddSide = document.getElementById('design_odd_side_padding').value;
            const oddVert = document.getElementById('design_odd_vert_padding').value;
            const evenSide = document.getElementById('design_even_side_padding').value;
            const evenVert = document.getElementById('design_even_vert_padding').value;

            root.style.setProperty('--odd-side-padding', oddSide);
            root.style.setProperty('--odd-vert-padding', oddVert);
            root.style.setProperty('--even-side-padding', evenSide);
            root.style.setProperty('--even-vert-padding', evenVert);

            const stickyEnabled = document.getElementById('design_sticky_enabled').checked;
            const cta = doc.querySelector('.mobile-sticky-cta');
            if (cta) {
              cta.style.display = stickyEnabled ? 'block' : 'none';
            }

            const heroBtn2Show = document.getElementById('h_btn2_show')?.checked ?? true;
            const heroBtn2 = doc.querySelector('.hero-buttons-row .btn-secondary');
            if (heroBtn2) {
              heroBtn2.style.display = heroBtn2Show ? 'inline-flex' : 'none';
            }
          } catch(e) { console.log('Design live update error:', e); }
        }

        // Section CTA Text & Visibility live update handlers
        function onSecCtaSameTextChange() {
          const same = document.getElementById('sec_cta_same_text')?.checked;
          const heroBtn1Val = document.getElementById('h_btn1')?.value || '';
          
          document.querySelectorAll('.sec-cta-text-input').forEach(input => {
            input.disabled = same;
            if (same) {
              input.value = heroBtn1Val;
            }
          });
          document.querySelectorAll('.sec-cta-text-note').forEach(note => {
            note.textContent = same ? '🔒 (Přebírá se z HERO tlačítka)' : 'Můžete zadat vlastní text nebo nechat prázdné pro výchozí.';
          });
          liveUpdateSecCtaTexts();
        }

        function liveUpdateSecCtaVisibility() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;
          const sections = ['uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq'];
          const secSelectors = {
            'uvp': '#uvp', 'master': '#mistr', 'outcomes': '#co-se-naucis',
            'timeline': '#jak-to-probiha', 'portfolio': '#realizace',
            'testimonials': '#reference', 'faq': '#faq'
          };
          sections.forEach(key => {
            const chk = document.getElementById('sec_cta_show_' + key);
            const secEl = doc.querySelector(secSelectors[key]);
            if (secEl) {
              const wrap = secEl.querySelector('.section-cta-wrap');
              if (wrap) {
                wrap.style.display = (chk && chk.checked) ? 'block' : 'none';
              }
            }
          });
        }

        function liveUpdateSecCtaTexts() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;
          const same = document.getElementById('sec_cta_same_text')?.checked;
          const heroBtn1Val = document.getElementById('h_btn1')?.value || '';
          const sections = ['uvp', 'master', 'outcomes', 'timeline', 'portfolio', 'testimonials', 'faq'];
          const secSelectors = {
            'uvp': '#uvp', 'master': '#mistr', 'outcomes': '#co-se-naucis',
            'timeline': '#jak-to-probiha', 'portfolio': '#realizace',
            'testimonials': '#reference', 'faq': '#faq'
          };
          sections.forEach(key => {
            const secEl = doc.querySelector(secSelectors[key]);
            if (secEl) {
              const btn = secEl.querySelector('.section-cta-wrap .btn');
              if (btn) {
                let txt = heroBtn1Val;
                if (!same) {
                  const customTxt = document.getElementById('sec_cta_text_' + key)?.value;
                  if (customTxt && customTxt.trim() !== '') txt = customTxt;
                }
                btn.textContent = txt;
              }
            }
          });
        }

        // Real-Time Live Portfolio Updates on iframe (aspect ratio, object fit, position)
        function liveUpdatePortfolio() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;

          const itemBoxes = document.querySelectorAll('.portfolio-item-box');
          const iframeItems = doc.querySelectorAll('#realizace .portfolio-item');

          itemBoxes.forEach((box, idx) => {
            if (iframeItems[idx]) {
              const img = iframeItems[idx].querySelector('img');
              const cap = iframeItems[idx].querySelector('.portfolio-caption');
              const itemRatio = box.querySelector('.p-ratio')?.value || '4/3';
              const itemFit = box.querySelector('.p-fit')?.value || 'cover';
              const itemPos = box.querySelector('.p-pos')?.value || 'center';
              const pImgVal = box.querySelector('.p-img')?.value;
              const pCapVal = box.querySelector('.p-cap')?.value;

              if (img) {
                img.style.height = 'auto';
                img.style.aspectRatio = (itemRatio === 'auto') ? 'auto' : itemRatio;
                img.style.objectFit = itemFit;
                img.style.objectPosition = itemPos;
                if (pImgVal !== undefined && pImgVal !== '') {
                  img.src = pImgVal;
                }
              }
              if (cap && pCapVal !== undefined) {
                cap.textContent = pCapVal;
              }
            }
          });
        }

        // Real-Time Live Reordering of dynamic items in iframe DOM across sections
        function liveUpdateDynamicItems() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;

          // 1. Portfolio live update
          liveUpdatePortfolio();

          // 2. Generic reorder for other dynamic item containers
          const sectionConfig = [
            { containerId: 'uvp_cards_container', boxSel: '.uvp-item-box', iframeParentSel: '#uvp .uvp-grid, #uvp .cards-grid', iframeItemSel: '.uvp-card' },
            { containerId: 'outcomes_container', boxSel: '.outcome-item-box', iframeParentSel: '#co-se-naucis .outcomes-grid', iframeItemSel: '.outcome-item' },
            { containerId: 'timeline_container', boxSel: '.timeline-step-box', iframeParentSel: '#jak-to-probiha .timeline', iframeItemSel: '.timeline-step' },
            { containerId: 'testimonials_container', boxSel: '.testimonial-item-box', iframeParentSel: '#reference .testimonials-grid', iframeItemSel: '.testimonial-card' },
            { containerId: 'faq_container', boxSel: '.faq-item-box', iframeParentSel: '#faq .faq-list', iframeItemSel: 'details' }
          ];

          sectionConfig.forEach(cfg => {
            const editorContainer = document.getElementById(cfg.containerId);
            const iframeParent = doc.querySelector(cfg.iframeParentSel);
            if (!editorContainer || !iframeParent) return;

            const editorBoxes = editorContainer.querySelectorAll(cfg.boxSel);
            const iframeItems = Array.from(iframeParent.querySelectorAll(cfg.iframeItemSel));

            if (editorBoxes.length === 0 || iframeItems.length === 0) return;

            // Re-append items in iframe in the exact order of editorBoxes
            editorBoxes.forEach((box, idx) => {
              if (iframeItems[idx]) {
                iframeParent.appendChild(iframeItems[idx]);
              }
            });
          });
        }

        // Real-Time Live Modal Contact Updates on iframe
        function liveUpdateModalContact() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;

          // Step 1
          const s1Title = document.getElementById('mc_s1_title')?.value;
          const s1Text = document.getElementById('mc_s1_text')?.value;
          const s1Btn = document.getElementById('mc_s1_btn')?.value;

          const step1 = doc.getElementById('modalStep1');
          if (step1) {
            if (s1Title !== undefined && step1.querySelector('h3')) step1.querySelector('h3').textContent = s1Title;
            if (s1Text !== undefined && step1.querySelector('p')) step1.querySelector('p').textContent = s1Text;
            if (s1Btn !== undefined && doc.getElementById('m_s1_submit_btn')) doc.getElementById('m_s1_submit_btn').textContent = s1Btn;
          }

          // Step 2
          const s2Title = document.getElementById('mc_s2_title')?.value;
          const s2Text = document.getElementById('mc_s2_text')?.value;
          const s2Btn = document.getElementById('mc_s2_btn')?.value;
          const s2WaText = document.getElementById('mc_s2_wa_text')?.value;

          const step2 = doc.getElementById('modalStep2');
          if (step2) {
            if (s2Title !== undefined && step2.querySelector('h3')) step2.querySelector('h3').textContent = s2Title;
            if (s2Text !== undefined && step2.querySelector('p')) step2.querySelector('p').textContent = s2Text;
            if (s2Btn !== undefined && doc.getElementById('m_s2_submit_btn')) doc.getElementById('m_s2_submit_btn').textContent = s2Btn;
            if (s2WaText !== undefined && step2.querySelector('.btn-wa')) step2.querySelector('.btn-wa').textContent = s2WaText;
          }

          // Step 3
          const s3Title = document.getElementById('mc_s3_title')?.value;
          const s3Text = document.getElementById('mc_s3_text')?.value;
          const s3Btn = document.getElementById('mc_s3_btn')?.value;
          const s3WebUrl = document.getElementById('mc_s3_web_url')?.value;
          const s3WebBtnText = document.getElementById('mc_s3_web_btn_text')?.value;
          const s3WebDesc = document.getElementById('mc_s3_web_desc')?.value;

          const step3 = doc.getElementById('modalStep3');
          if (step3) {
            if (s3Title !== undefined && step3.querySelector('h3')) step3.querySelector('h3').textContent = s3Title;
            if (s3Text !== undefined && doc.getElementById('m_s3_text_el')) doc.getElementById('m_s3_text_el').textContent = s3Text;
            if (s3Btn !== undefined && doc.getElementById('m_s3_close_btn')) doc.getElementById('m_s3_close_btn').textContent = s3Btn;

            const webBox = doc.getElementById('m_s3_web_box');
            if (webBox) {
              const hasUrl = s3WebUrl && s3WebUrl.trim() !== '';
              webBox.style.display = hasUrl ? 'block' : 'none';
              const webLink = doc.getElementById('m_s3_web_link_el');
              if (webLink) {
                webLink.href = s3WebUrl || '#';
                webLink.textContent = s3WebBtnText || 'Přejít na web';
              }
              const webDesc = doc.getElementById('m_s3_web_desc_el');
              if (webDesc) {
                webDesc.textContent = s3WebDesc || '';
                webDesc.style.display = (s3WebDesc && s3WebDesc.trim() !== '') ? 'block' : 'none';
              }
            }
          }
        }

        function previewModalPhase(stepNum, btn) {
          document.querySelectorAll('.modal-phase-btn').forEach(b => b.classList.remove('active'));
          if (btn) btn.classList.add('active');

          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;
          const modal = doc.getElementById('leadModal');
          if (!modal) return;

          modal.style.display = 'flex';
          const steps = ['modalStep1', 'modalStep2', 'modalStep3'];
          steps.forEach((sId, idx) => {
            const el = doc.getElementById(sId);
            if (el) el.style.display = (idx + 1 === stepNum) ? 'block' : 'none';
          });
          liveUpdateModalContact();
        }

        function closePreviewModal() {
          document.querySelectorAll('.modal-phase-btn').forEach(b => b.classList.remove('active'));
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentDocument) return;
          const doc = iframe.contentDocument;
          const modal = doc.getElementById('leadModal');
          if (modal) modal.style.display = 'none';
        }

        // Real-Time Live Typography Updates on iframe
        function liveUpdateTypography() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentWindow || !iframe.contentDocument) return;
          try {
            const doc = iframe.contentDocument;
            const root = doc.documentElement;
            if (!root) return;

            const h1Step = document.getElementById('typo_hero_h1').value;
            const h2Step = document.getElementById('typo_section_h2').value;
            const h3Step = document.getElementById('typo_card_h3').value;
            const bodyStep = document.getElementById('typo_body_text').value;
            const eyebrowStep = document.getElementById('typo_eyebrow').value;
            const subtitleStep = document.getElementById('typo_subtitle').value;

            root.style.setProperty('--hero-h1-clamp', typoScales.hero_h1[h1Step] || typoScales.hero_h1['step-3']);
            root.style.setProperty('--section-h2-clamp', typoScales.section_h2[h2Step] || typoScales.section_h2['step-3']);
            root.style.setProperty('--card-h3-clamp', typoScales.card_h3[h3Step] || typoScales.card_h3['step-3']);
            root.style.setProperty('--body-text-size', typoScales.body_text[bodyStep] || typoScales.body_text['step-3']);
            root.style.setProperty('--eyebrow-size', typoScales.eyebrow[eyebrowStep] || typoScales.eyebrow['step-3']);
            root.style.setProperty('--subtitle-size', typoScales.subtitle[subtitleStep] || typoScales.subtitle['step-3']);
          } catch(e) { console.log('Live update error:', e); }
        }

        // Real-Time Section Reordering in livePreviewFrame iframe DOM with Odd/Even class re-assignment
        function liveReorderSectionsInIframe() {
          const iframe = document.getElementById('livePreviewFrame');
          if (!iframe || !iframe.contentWindow || !iframe.contentDocument) return;
          try {
            const doc = iframe.contentDocument;
            const main = doc.querySelector('main');
            if (!main) return;

            const sectionElements = {
              'hero': doc.querySelector('.hero-wrapper') || doc.querySelector('.hero'),
              'uvp': doc.querySelector('#uvp'),
              'master': doc.querySelector('#mistr'),
              'outcomes': doc.querySelector('#co-se-naucis'),
              'timeline': doc.querySelector('#jak-to-probiha'),
              'portfolio': doc.querySelector('#realizace'),
              'testimonials': doc.querySelector('#reference'),
              'faq': doc.querySelector('#faq'),
              'cta': doc.querySelector('.primary-cta-box')?.parentElement || doc.querySelector('.primary-cta-box')
            };

            let visibleIdx = 0;
            currentSectionOrder.forEach((key, idx) => {
              const el = sectionElements[key];
              if (!el) return;
              const isVisible = currentSectionVisibility[key] !== false;
              el.style.display = isVisible ? '' : 'none';
              if (isVisible) {
                main.appendChild(el);
                el.classList.remove('section-odd', 'section-even');
                el.classList.add(visibleIdx % 2 === 0 ? 'section-odd' : 'section-even');
                visibleIdx++;
              }
            });
          } catch(e) { console.log('Reorder iframe error:', e); }
        }

        let currentPreviewDevice = 'desktop';

        function setPreviewDevice(device) {
          const frame = document.getElementById('livePreviewFrame');
          document.querySelectorAll('.device-btn').forEach(btn => btn.classList.remove('active'));
          currentPreviewDevice = device;
          if (device === 'mobile') {
            frame.className = 'preview-iframe device-mobile';
            document.getElementById('btn_mobile').classList.add('active');
          } else if (device === 'tablet') {
            frame.className = 'preview-iframe device-tablet';
            document.getElementById('btn_tablet').classList.add('active');
          } else {
            frame.className = 'preview-iframe';
            document.getElementById('btn_desktop').classList.add('active');
          }
          setTimeout(() => {
            liveUpdateTypography();
            liveUpdateDesign();
          }, 100);
        }

        function refreshPreview() {
          const iframe = document.getElementById('livePreviewFrame');
          if (iframe) { 
            iframe.src = iframe.src.split('?')[0] + '?v=' + Date.now(); 
            iframe.onload = () => {
              liveUpdateTypography();
              liveUpdateDesign();
              liveReorderSectionsInIframe();
              liveUpdateSecCtaVisibility();
              liveUpdateSecCtaTexts();
            };
          }
        }

        function renderOrderList() {
          const container = document.getElementById('order_list_container');
          if (!container) return;
          container.innerHTML = '';

          // Count visible sections only (for odd/even badge display)
          let visibleCount = 0;
          const visibleIndexMap = {};
          currentSectionOrder.forEach((key) => {
            const isVisible = currentSectionVisibility[key] !== false;
            if (isVisible) { visibleIndexMap[key] = visibleCount++; }
          });

          currentSectionOrder.forEach((key, idx) => {
            const isVisible = currentSectionVisibility[key] !== false;
            const visIdx = visibleIndexMap[key];
            const isOddText = (isVisible && visIdx % 2 === 0)
              ? '<span style="color:var(--accent); font-size:0.8rem; margin-left:0.5rem;">[🔹 Lichá sekce]</span>'
              : (isVisible ? '<span style="color:#60a5fa; font-size:0.8rem; margin-left:0.5rem;">[🔸 Sudá sekce]</span>' : '<span style="color:#6b7280; font-size:0.8rem; margin-left:0.5rem;">[👁️ Skryta]</span>');

            const div = document.createElement('div');
            div.className = 'item-card';
            div.style.cssText = `display:flex; justify-content:space-between; align-items:center; padding:0.8rem 1.2rem; margin-bottom:0.6rem; opacity:${isVisible ? '1' : '0.45'}; border-color:${isVisible ? '' : '#374151'};`;

            // Toggle switch HTML
            const toggleId = `sec_vis_${key}`;
            const hasCtaCheckbox = document.getElementById('sec_cta_show_' + key);
            const ctaIsVisible = hasCtaCheckbox ? hasCtaCheckbox.checked : false;

            let ctaToggleHtml = '';
            if (hasCtaCheckbox) {
              const ctaToggleId = `sec_cta_order_vis_${key}`;
              ctaToggleHtml = `
                <div style="display:flex; align-items:center; gap:0.4rem; background:rgba(0,0,0,0.2); padding:0.2rem 0.5rem; border-radius:6px; flex-shrink:0;">
                  <span style="font-size:0.75rem; color: ${ctaIsVisible ? 'var(--text)' : 'var(--text-muted)'};"><i class="bi bi-hand-index-thumb"></i> Tlačítko</span>
                  <label class="sec-vis-toggle" title="${ctaIsVisible ? 'Skrýt tlačítko v sekci' : 'Zobrazit tlačítko v sekci'}" style="cursor:pointer; flex-shrink:0;">
                    <input type="checkbox" id="${ctaToggleId}" ${ctaIsVisible ? 'checked' : ''}
                      onchange="toggleSectionCtaFromOrder('${key}')" style="display:none;" />
                    <span style="
                      display:inline-flex; align-items:center; justify-content:center;
                      width:32px; height:18px; border-radius:9px; transition:all .2s;
                      background:${ctaIsVisible ? 'var(--accent)' : '#374151'};
                      position:relative;
                    ">
                      <span style="
                        position:absolute; width:12px; height:12px; border-radius:50%; background:#fff;
                        transition:transform .2s; transform:translateX(${ctaIsVisible ? '7px' : '-7px'});
                        box-shadow:0 1px 3px rgba(0,0,0,0.4);
                      "></span>
                    </span>
                  </label>
                </div>
              `;
            }

            div.innerHTML = `
              <div style="display:flex; align-items:center; gap:0.8rem; flex:1; min-width:0;">
                <label class="sec-vis-toggle" title="${isVisible ? 'Skrýt sekci' : 'Zobrazit sekci'}" style="cursor:pointer; flex-shrink:0;">
                  <input type="checkbox" id="${toggleId}" ${isVisible ? 'checked' : ''}
                    onchange="toggleSectionVisibility('${key}')" style="display:none;" />
                  <span style="
                    display:inline-flex; align-items:center; justify-content:center;
                    width:42px; height:24px; border-radius:12px; transition:all .2s;
                    background:${isVisible ? 'var(--accent)' : '#374151'};
                    position:relative;
                  ">
                    <span style="
                      position:absolute; width:18px; height:18px; border-radius:50%; background:#fff;
                      transition:transform .2s; transform:translateX(${isVisible ? '9px' : '-9px'});
                      box-shadow:0 1px 3px rgba(0,0,0,0.4);
                    "></span>
                  </span>
                </label>
                <span style="font-size:0.95rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;">
                  <strong>${idx + 1}.</strong> ${sectionLabels[key] || key} ${isOddText}
                </span>
                ${ctaToggleHtml}
              </div>
              <div style="display:flex; gap:0.4rem; flex-shrink:0; margin-left:0.6rem;">
                <button type="button" class="btn-action btn-copy" onclick="moveSection(${idx}, -1)" ${idx === 0 ? 'disabled style="opacity:0.4;"' : ''}><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="btn-action btn-copy" onclick="moveSection(${idx}, 1)" ${idx === currentSectionOrder.length - 1 ? 'disabled style="opacity:0.4;"' : ''}><i class="bi bi-arrow-down"></i></button>
              </div>
            `;
            container.appendChild(div);
          });
        }

        function toggleSectionVisibility(key) {
          const isCurrentlyVisible = currentSectionVisibility[key] !== false;
          currentSectionVisibility[key] = !isCurrentlyVisible;
          renderOrderList();
          liveReorderSectionsInIframe();
        }

        function toggleSectionCtaFromOrder(key) {
          const chk = document.getElementById('sec_cta_show_' + key);
          if (chk) {
            chk.checked = !chk.checked;
            liveUpdateSecCtaVisibility(); // Aktualizuje iframe (zavolá iframe code)
            renderOrderList(); // Překreslí samotný order list s novým stavem tlačítka
          }
        }

        // ── ADD/REMOVE/MOVE ITEMS IN SECTIONS ───────────────────────────
        function moveItem(btn, selector, direction) {
          const box = btn.closest(selector);
          if (!box) return;
          if (direction === -1 && box.previousElementSibling) {
            box.parentNode.insertBefore(box, box.previousElementSibling);
          } else if (direction === 1 && box.nextElementSibling) {
            box.parentNode.insertBefore(box.nextElementSibling, box);
          }
          liveUpdateDynamicItems();
        }

        function removeItem(btn, selector) {
          const box = btn.closest(selector);
          if (!box) return;
          const container = box.parentElement;
          if (container.querySelectorAll(selector).length <= 1) {
            alert('Musí zůstat aspoň jedna položka.');
            return;
          }
          box.remove();
          liveUpdateDynamicItems();
        }

        function makeItemBox(cssClass, headerText, innerHtml) {
          const div = document.createElement('div');
          div.className = 'item-card ' + cssClass;
          div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
              <h4 style="margin:0;">${headerText}</h4>
              <div style="display:flex; gap:0.3rem;">
                <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.${cssClass}', -1)">⬆️</button>
                <button type="button" class="btn-action btn-copy" style="padding:0.2rem 0.5rem; font-size:0.75rem;" onclick="moveItem(this, '.${cssClass}', 1)">⬇️</button>
                <button type="button" class="btn-action" style="background:#7f1d1d; border-color:#dc2626; color:#fff; padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="removeItem(this, '.${cssClass}')">🗑️ Odebrat</button>
              </div>
            </div>
            ${innerHtml}
          `;
          return div;
        }

        function addUvpItem() {
          const container = document.getElementById('uvp_cards_container');
          const n = container.querySelectorAll('.uvp-item-box').length + 1;
          container.appendChild(makeItemBox('uvp-item-box', `Karta #${n}`, `
            <div class="form-group"><label>Titulek karty</label><input type="text" class="form-control uvp-item-title" value="" /></div>
            <div class="form-group"><label>Popis karty</label><textarea class="form-control uvp-item-desc"></textarea></div>
          `));
        }

        function addOutcomeItem() {
          const container = document.getElementById('outcomes_container');
          const n = container.querySelectorAll('.outcome-item-box').length + 1;
          container.appendChild(makeItemBox('outcome-item-box', `Dovednost #${n}`, `
            <div class="form-group"><label>Ikona (Emoji)</label><input type="text" class="form-control outcome-icon" value="🔥" /></div>
            <div class="form-group"><label>Název dovednosti</label><input type="text" class="form-control outcome-title" value="" /></div>
            <div class="form-group"><label>Popis dovednosti</label><textarea class="form-control outcome-desc"></textarea></div>
          `));
        }

        function addTimelineStep() {
          const container = document.getElementById('timeline_container');
          const n = container.querySelectorAll('.timeline-step-box').length + 1;
          const numStr = String(n).padStart(2, '0');
          container.appendChild(makeItemBox('timeline-step-box', `Krok ${numStr}`, `
            <div class="form-group"><label>Číslo kroku</label><input type="text" class="form-control step-num" value="${numStr}" /></div>
            <div class="form-group"><label>Název kroku</label><input type="text" class="form-control step-title" value="" /></div>
            <div class="form-group"><label>Popis kroku</label><textarea class="form-control step-desc"></textarea></div>
          `));
        }

        function addPortfolioItem() {
          const container = document.getElementById('portfolio_container');
          const n = container.querySelectorAll('.portfolio-item-box').length + 1;
          const uid = 'new_' + Date.now();
          container.appendChild(makeItemBox('portfolio-item-box', `Fotka #${n}`, `
            <div class="form-group">
              <label>Fotka v galerii</label>
              <div class="upload-row">
                <img src="" id="prev_p_img_${uid}" class="thumb-preview" />
                <input type="text" class="form-control p-img" id="p_img_${uid}" value="" />
                <label class="upload-btn">
                  <i class="bi bi-upload"></i> Nahrát fotku
                  <input type="file" accept="image/*" style="display:none;" onchange="uploadImage(this, 'p_img_${uid}', 'prev_p_img_${uid}')" />
                </label>
              </div>
            </div>
            <div class="form-group"><label>Popisek pod fotkou</label><input type="text" class="form-control p-cap" value="" /></div>
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.8rem; margin-bottom:0;">
              <div class="form-group" style="margin-bottom:0;">
                <label>Poměr stran</label>
                <select class="form-control p-ratio" onchange="liveUpdatePortfolio()">
                  <option value="4/3" selected>🖼️ 4:3</option>
                  <option value="16/9">📺 16:9</option>
                  <option value="1/1">🔲 1:1</option>
                  <option value="3/2">📷 3:2</option>
                  <option value="3/4">📱 3:4</option>
                  <option value="auto">↔️ Auto (Původní)</option>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label>Způsob ořezu</label>
                <select class="form-control p-fit" onchange="liveUpdatePortfolio()">
                  <option value="cover" selected>✂️ Vyplnit (Cover)</option>
                  <option value="contain">🔍 Celá (Contain)</option>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label>Pozice výřezu</label>
                <select class="form-control p-pos" onchange="liveUpdatePortfolio()">
                  <option value="center" selected>🎯 Střed</option>
                  <option value="top">⬆️ Horní</option>
                  <option value="bottom">⬇️ Spodní</option>
                  <option value="left">⬅️ Levá</option>
                  <option value="right">➡️ Pravá</option>
                </select>
              </div>
            </div>
          `));
        }

        function addTestimonialItem() {
          const container = document.getElementById('testimonials_container');
          const n = container.querySelectorAll('.testimonial-item-box').length + 1;
          container.appendChild(makeItemBox('testimonial-item-box', `Reference #${n}`, `
            <div class="form-group"><label>Nadpis reference (volitelné)</label><input type="text" class="form-control ts-title" value="" placeholder="Např. Skvělá zkušenost s mistrem" /></div>
            <div class="form-group"><label>Citace</label><textarea class="form-control ts-quote"></textarea></div>
            <div class="form-group"><label>Jméno autora</label><input type="text" class="form-control ts-name" value="" /></div>
            <div class="form-group"><label>Role / Vztah k dílně</label><input type="text" class="form-control ts-role" value="" /></div>
          `));
        }

        function addFaqItem() {
          const container = document.getElementById('faq_container');
          const n = container.querySelectorAll('.faq-item-box').length + 1;
          container.appendChild(makeItemBox('faq-item-box', `Otázka #${n}`, `
            <div class="form-group"><label>Otázka</label><input type="text" class="form-control faq-q" value="" /></div>
            <div class="form-group"><label>Odpověď</label><textarea class="form-control faq-a"></textarea></div>
          `));
        }
        // ──────────────────────────────────────────────────────────────────

        function moveSection(index, direction) {
          const targetIndex = index + direction;
          if (targetIndex < 0 || targetIndex >= currentSectionOrder.length) return;
          const temp = currentSectionOrder[index];
          currentSectionOrder[index] = currentSectionOrder[targetIndex];
          currentSectionOrder[targetIndex] = temp;
          renderOrderList();
          liveReorderSectionsInIframe();
        }

        document.addEventListener('DOMContentLoaded', () => {
          renderOrderList();

          // Restore saved active tab after save or page reload
          const savedActiveTab = document.getElementById('active_tab')?.value || 'tab-order';
          const activeTabBtn = document.querySelector(`.tab-btn[onclick*="${savedActiveTab}"]`);
          if (savedActiveTab && document.getElementById(savedActiveTab)) {
            showTab(savedActiveTab, activeTabBtn);
          }

          const iframe = document.getElementById('livePreviewFrame');
          if (iframe) {
            iframe.onload = () => {
              liveUpdateTypography();
              liveUpdateDesign();
              liveReorderSectionsInIframe();
              liveUpdateSecCtaVisibility();
              liveUpdateSecCtaTexts();
              liveUpdatePortfolio();
              liveUpdateModalContact();

              // Scroll iframe preview to current active tab section once iframe content is fully loaded
              const currentTabId = document.getElementById('active_tab')?.value || 'tab-order';
              const sectionSelector = tabToSectionMap[currentTabId];
              if (sectionSelector && iframe.contentDocument) {
                const el = iframe.contentDocument.querySelector(sectionSelector);
                if (el) {
                  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
              }
            };
          }

          document.getElementById('h_btn1')?.addEventListener('input', () => {
            if (document.getElementById('sec_cta_same_text')?.checked) {
              const heroBtn1Val = document.getElementById('h_btn1').value;
              document.querySelectorAll('.sec-cta-text-input').forEach(input => {
                input.value = heroBtn1Val;
              });
              liveUpdateSecCtaTexts();
            }
          });
        });

        // Map: editor tab ID -> iframe section element selector
        const tabToSectionMap = {
          'tab-hero': '.hero-wrapper',
          'tab-uvp': '#uvp',
          'tab-master': '#mistr',
          'tab-outcomes': '#co-se-naucis',
          'tab-timeline': '#jak-to-probiha',
          'tab-portfolio': '#realizace',
          'tab-testimonials': '#reference',
          'tab-faq': '#faq',
          'tab-cta': '.primary-cta-box'
        };

        function showTab(tabId, scrollBtn) {
          document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
          document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
          document.getElementById(tabId).classList.add('active');
          // Mark the correct button active
          if (scrollBtn) {
            scrollBtn.classList.add('active');
          } else if (event && event.target) {
            event.target.classList.add('active');
          }
          // Save active tab
          const hiddenTab = document.getElementById('active_tab');
          if (hiddenTab) hiddenTab.value = tabId;

          // Scroll iframe preview to the corresponding section
          const sectionSelector = tabToSectionMap[tabId];
          if (sectionSelector) {
            try {
              const iframe = document.getElementById('livePreviewFrame');
              if (iframe && iframe.contentDocument) {
                const el = iframe.contentDocument.querySelector(sectionSelector);
                if (el) {
                  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
              }
            } catch(e) { /* cross-origin safety */ }
          }
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
              refreshPreview();
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
            section_visibility: currentSectionVisibility,
            section_cta: {
              same_text: document.getElementById('sec_cta_same_text')?.checked ?? true,
              visibility: {
                uvp: document.getElementById('sec_cta_show_uvp')?.checked ?? true,
                master: document.getElementById('sec_cta_show_master')?.checked ?? true,
                outcomes: document.getElementById('sec_cta_show_outcomes')?.checked ?? true,
                timeline: document.getElementById('sec_cta_show_timeline')?.checked ?? true,
                portfolio: document.getElementById('sec_cta_show_portfolio')?.checked ?? true,
                testimonials: document.getElementById('sec_cta_show_testimonials')?.checked ?? true,
                faq: document.getElementById('sec_cta_show_faq')?.checked ?? true
              },
              texts: {
                uvp: document.getElementById('sec_cta_text_uvp')?.value ?? '',
                master: document.getElementById('sec_cta_text_master')?.value ?? '',
                outcomes: document.getElementById('sec_cta_text_outcomes')?.value ?? '',
                timeline: document.getElementById('sec_cta_text_timeline')?.value ?? '',
                portfolio: document.getElementById('sec_cta_text_portfolio')?.value ?? '',
                testimonials: document.getElementById('sec_cta_text_testimonials')?.value ?? '',
                faq: document.getElementById('sec_cta_text_faq')?.value ?? ''
              }
            },
            design: {
              color_theme: document.getElementById('design_color_theme').value,
              btn_size: document.getElementById('design_btn_size').value,
              odd_side_padding: document.getElementById('design_odd_side_padding').value,
              odd_vert_padding: document.getElementById('design_odd_vert_padding').value,
              even_side_padding: document.getElementById('design_even_side_padding').value,
              even_vert_padding: document.getElementById('design_even_vert_padding').value,
              sticky_cta_enabled: document.getElementById('design_sticky_enabled').checked,
              sticky_cta_hide_in_hero: document.getElementById('design_sticky_hide_hero').checked,
              sticky_cta_hide_in_contact: document.getElementById('design_sticky_hide_contact').checked
            },
            modal_contact: {
              step1_title: document.getElementById('mc_s1_title').value,
              step1_text: document.getElementById('mc_s1_text').value,
              step1_btn: document.getElementById('mc_s1_btn').value,
              step2_title: document.getElementById('mc_s2_title').value,
              step2_text: document.getElementById('mc_s2_text').value,
              step2_btn: document.getElementById('mc_s2_btn').value,
              step2_wa_text: document.getElementById('mc_s2_wa_text').value,
              step3_title: document.getElementById('mc_s3_title').value,
              step3_text: document.getElementById('mc_s3_text').value,
              step3_btn: document.getElementById('mc_s3_btn').value,
              step3_web_url: document.getElementById('mc_s3_web_url').value,
              step3_web_btn_text: document.getElementById('mc_s3_web_btn_text').value,
              step3_web_desc: document.getElementById('mc_s3_web_desc').value
            },
            typography: {
              hero_h1: document.getElementById('typo_hero_h1').value,
              section_h2: document.getElementById('typo_section_h2').value,
              card_h3: document.getElementById('typo_card_h3').value,
              body_text: document.getElementById('typo_body_text').value,
              eyebrow: document.getElementById('typo_eyebrow').value,
              subtitle: document.getElementById('typo_subtitle').value
            },
            hero: {
              eyebrow: document.getElementById('h_eyebrow').value,
              h1: document.getElementById('h_h1').value,
              subtitle: document.getElementById('h_sub').value,
              btn_primary: document.getElementById('h_btn1').value,
              btn_secondary: document.getElementById('h_btn2').value,
              btn_secondary_show: document.getElementById('h_btn2_show').checked,
              image: document.getElementById('h_img').value
            },
            uvp: {
              eyebrow: document.getElementById('uvp_eyebrow').value,
              title: document.getElementById('uvp_title').value,
              subtitle: document.getElementById('uvp_sub').value,
              items: Array.from(document.querySelectorAll('.uvp-item-box')).map(box => ({
                title: box.querySelector('.uvp-item-title').value,
                desc: box.querySelector('.uvp-item-desc').value
              }))
            },
            master: {
              eyebrow: document.getElementById('m_eyebrow').value,
              name: document.getElementById('m_name').value,
              title: document.getElementById('m_title').value,
              bio: document.getElementById('m_bio').value,
              quote: document.getElementById('m_quote').value,
              bio2: document.getElementById('m_bio2').value,
              image: document.getElementById('m_img').value
            },
            outcomes: {
              eyebrow: document.getElementById('o_eyebrow').value,
              title: document.getElementById('o_title').value,
              subtitle: document.getElementById('o_sub').value,
              items: Array.from(document.querySelectorAll('.outcome-item-box')).map(box => ({
                icon: box.querySelector('.outcome-icon').value,
                title: box.querySelector('.outcome-title').value,
                desc: box.querySelector('.outcome-desc').value
              }))
            },
            timeline: {
              eyebrow: document.getElementById('t_eyebrow').value,
              title: document.getElementById('t_title').value,
              subtitle: document.getElementById('t_sub').value,
              steps: Array.from(document.querySelectorAll('.timeline-step-box')).map(box => ({
                num: box.querySelector('.step-num').value,
                title: box.querySelector('.step-title').value,
                desc: box.querySelector('.step-desc').value
              })),
              disclaimer: document.getElementById('t_disc').value
            },
            portfolio: {
              eyebrow: document.getElementById('p_eyebrow').value,
              title: document.getElementById('p_title').value,
              subtitle: document.getElementById('p_sub').value,
              items: Array.from(document.querySelectorAll('.portfolio-item-box')).map(box => ({
                image: box.querySelector('.p-img').value,
                caption: box.querySelector('.p-cap').value,
                aspect_ratio: box.querySelector('.p-ratio') ? box.querySelector('.p-ratio').value : '4/3',
                object_fit: box.querySelector('.p-fit') ? box.querySelector('.p-fit').value : 'cover',
                object_position: box.querySelector('.p-pos') ? box.querySelector('.p-pos').value : 'center'
              }))
            },
            testimonials: {
              eyebrow: document.getElementById('ts_eyebrow').value,
              title: document.getElementById('ts_title').value,
              subtitle: document.getElementById('ts_sub').value,
              items: Array.from(document.querySelectorAll('.testimonial-item-box')).map(box => ({
                title: box.querySelector('.ts-title') ? box.querySelector('.ts-title').value : '',
                quote: box.querySelector('.ts-quote').value,
                name: box.querySelector('.ts-name').value,
                role: box.querySelector('.ts-role').value
              }))
            },
            faq: {
              eyebrow: document.getElementById('f_eyebrow').value,
              title: document.getElementById('f_title').value,
              subtitle: document.getElementById('f_sub').value,
              items: Array.from(document.querySelectorAll('.faq-item-box')).map(box => ({
                q: box.querySelector('.faq-q').value,
                a: box.querySelector('.faq-a').value
              }))
            },
            cta: {
              title: document.getElementById('cta_title').value,
              text: document.getElementById('cta_text').value,
              btn: document.getElementById('cta_btn').value
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
            echo "<a class='btn-action btn-edit' href='landing_pages.php?edit=" . urlencode($slug) . "'><i class='bi bi-sliders'></i> Vizuálně upravit sekce, fotky, fonty, témata & pořadí</a>";
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
