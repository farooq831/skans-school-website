# docs/build-release.ps1 : makes the upload zip of the website (code only).
# Run in PowerShell:  powershell -ExecutionPolicy Bypass -File docs\build-release.ps1
# Result: _release\skans-website-v<version>-<date>.zip
#
# NOT in the zip (they belong to each server / campus and must never be overwritten):
#   campus-data\ (database logins, campus pictures, backups), adminpanel\upload\ (pictures),
#   uploads\ contents (student photos), backups\, _old\, docs\, _release\, test files.
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem
$root = Split-Path -Parent $PSScriptRoot
$ver = ([regex]::Match((Get-Content "$root\includes\campus.php" -Raw), "SK_VERSION = '([^']+)'")).Groups[1].Value
$zipPath = "$root\_release\skans-website-v$ver-$(Get-Date -Format yyyy-MM-dd).zip"
if (Test-Path $zipPath) { Remove-Item $zipPath }

$skipDirs = @('_old', 'docs', 'backups', 'campus-data', '_release', 'adminpanel/upload', 'uploads', '.git', 'node_modules')
$keep = @('uploads/.htaccess', 'campus-data/.htaccess', 'campus-data/index.html')   # safety rules only, no secrets
$files = Get-ChildItem $root -Recurse -File -Force | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1).Replace('\', '/')
    $skip = $false
    foreach ($d in $skipDirs) { if ($rel -eq $d -or $rel.StartsWith("$d/")) { $skip = $true } }
    if ($rel -like '*/zz_*' -or $rel -like 'zz_*' -or $rel -match '\.(log|bak|zip)$' -or $rel -eq 'error_log') { $skip = $true }
    if ($keep -contains $rel) { $skip = $false }
    if (-not $skip) { [pscustomobject]@{ Full = $_.FullName; Rel = $rel } }
}
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')
foreach ($f in $files) { [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $f.Full, $f.Rel, 'Optimal') }
$zip.Dispose()
$mb = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Output "Made $zipPath : $($files.Count) files, $mb MB (version $ver)"
