param([int]$Port = 8088)

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

if (-not (php -m | Select-String -Quiet '^pdo_pgsql$')) {
    $iniDirectory = Join-Path $projectRoot '.local-php'
    New-Item -ItemType Directory -Force $iniDirectory | Out-Null
    Set-Content -Path (Join-Path $iniDirectory 'pgsql.ini') -Value 'extension=pdo_pgsql'
    $env:PHP_INI_SCAN_DIR = $iniDirectory
}

php artisan serve --host=127.0.0.1 --port=$Port
