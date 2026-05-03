# deploy.ps1
# Použití: .\deploy.ps1 "vzkaz k verzi"

param (
    [string]$Message = "Auto-update $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
)

Write-Host "🚀 Generuji novou verzi pro cache busting..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMddHHmm"
$htmlFiles = Get-ChildItem -Filter *.html -Recurse

foreach ($file in $htmlFiles) {
    $content = Get-Content $file.FullName -Raw
    # Najde ?v=... a nahradí ho aktuálním timestampem
    $newContent = $content -replace '\?v=[a-zA-Z0-9_-]+', "?v=$timestamp"
    if ($content -ne $newContent) {
        $newContent | Set-Content $file.FullName -NoNewline
        Write-Host "✅ Aktualizována verze v: $($file.Name)" -ForegroundColor Gray
    }
}

Write-Host "🚀 Startuji odesílání na GitHub..." -ForegroundColor Cyan

git add .
git commit -m "$Message"
git push

Write-Host "✅ Hotovo! Změny jsou na GitHubu." -ForegroundColor Green
