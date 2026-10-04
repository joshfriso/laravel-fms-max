$ErrorActionPreference = 'Stop'

$collectionPath = Join-Path (Split-Path -Parent $PSScriptRoot) 'docs\postman\laravel-fms.postman_collection.json'
$environmentPath = Join-Path (Split-Path -Parent $PSScriptRoot) 'docs\postman\laravel-fms.postman_environment.json'

$collection = Get-Content -Raw $collectionPath | ConvertFrom-Json
$environment = Get-Content -Raw $environmentPath | ConvertFrom-Json

$requestNames = [System.Collections.Generic.List[string]]::new()
function Add-RequestNames($items) {
    foreach ($item in $items) {
        if ($item.request) { $requestNames.Add([string] $item.name) }
        if ($item.item) { Add-RequestNames $item.item }
    }
}
Add-RequestNames $collection.item

$expected = @(
    'Login',
    'Login admin',
    'Logout',
    'Dashboard',
    'Daftar folder',
    'Daftar dokumen',
    'Daftar departemen',
    'Daftar aktivitas',
    'Daftar sampah'
)

$missing = $expected | Where-Object { $_ -notin $requestNames }
$unexpected = $requestNames | Where-Object { $_ -notin $expected }
if ($missing) { throw "Request wajib belum ada: $($missing -join ', ')" }
if ($unexpected) { throw "Request di luar smoke test standar masih ada: $($unexpected -join ', ')" }

$auth = $collection.item | Where-Object name -eq 'Auth' | Select-Object -First 1
$login = $auth.item | Where-Object name -eq 'Login' | Select-Object -First 1
$loginAdmin = $auth.item | Where-Object name -eq 'Login admin' | Select-Object -First 1
$logout = $auth.item | Where-Object name -eq 'Logout' | Select-Object -First 1

if ($login.request.method -ne 'GET') { throw 'Login harus GET /login.' }
if ($loginAdmin.request.method -ne 'POST') { throw 'Login admin harus POST /login.' }
if ($logout.request.method -ne 'POST') { throw 'Logout harus POST /logout.' }

$loginHeader = $loginAdmin.request.header | Where-Object key -eq 'X-XSRF-TOKEN' | Select-Object -First 1
$logoutHeader = $logout.request.header | Where-Object key -eq 'X-XSRF-TOKEN' | Select-Object -First 1
if (-not $loginHeader -or $loginHeader.value -ne '{{csrf_token}}') { throw 'Login admin harus memakai header X-XSRF-TOKEN dari csrf_token.' }
if (-not $logoutHeader -or $logoutHeader.value -ne '{{csrf_token}}') { throw 'Logout harus memakai header X-XSRF-TOKEN dari csrf_token.' }

$envKeys = $environment.values | ForEach-Object key
foreach ($key in @('base_url', 'csrf_token', 'admin_email', 'admin_password')) {
    if ($key -notin $envKeys) { throw "Environment belum punya variabel $key." }
}

Write-Output "Koleksi Postman valid: $($requestNames.Count) request smoke test standar."
