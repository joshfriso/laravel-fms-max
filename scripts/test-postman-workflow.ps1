$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Net.Http

$baseUri = [Uri] 'http://127.0.0.1:8088'
$cookies = [System.Net.CookieContainer]::new()
$handler = [System.Net.Http.HttpClientHandler]::new()
$handler.CookieContainer = $cookies
$handler.AllowAutoRedirect = $false
$client = [System.Net.Http.HttpClient]::new($handler)

function Send-FmsRequest(
    [string] $Method,
    [string] $Path,
    [hashtable] $Form,
    [int[]] $Expected = @(200)
) {
    $request = [System.Net.Http.HttpRequestMessage]::new(
        [System.Net.Http.HttpMethod]::new($Method),
        [Uri]::new($baseUri, $Path)
    )

    if ($Method -in @('POST', 'PUT', 'PATCH', 'DELETE')) {
        $xsrf = $cookies.GetCookies($baseUri)['XSRF-TOKEN']
        if (-not $xsrf) { throw "Cookie XSRF-TOKEN belum tersedia sebelum $Method $Path." }
        $request.Headers.Add('X-XSRF-TOKEN', [Uri]::UnescapeDataString($xsrf.Value))
    }

    if ($Form) {
        $pairs = [System.Collections.Generic.List[System.Collections.Generic.KeyValuePair[string,string]]]::new()
        foreach ($entry in $Form.GetEnumerator()) {
            $pairs.Add([System.Collections.Generic.KeyValuePair[string,string]]::new([string] $entry.Key, [string] $entry.Value))
        }
        $request.Content = [System.Net.Http.FormUrlEncodedContent]::new($pairs)
    }

    $response = $client.SendAsync($request).Result
    $status = [int] $response.StatusCode
    Write-Output "$Method $Path -> $status"
    if ($status -notin $Expected) {
        $body = $response.Content.ReadAsStringAsync().Result
        if ($body.Length -gt 600) { $body = $body.Substring(0, 600) }
        throw "Status $status untuk $Method $Path.`n$body"
    }
    return $response
}

try {
    Send-FmsRequest -Method GET -Path '/login' -Expected @(200) | Out-Null
    Send-FmsRequest -Method POST -Path '/login' -Form @{
        email = 'admin@example.com'
        password = 'password'
        remember = 'on'
    } -Expected @(302) | Out-Null
    Send-FmsRequest -Method GET -Path '/dashboard' -Expected @(200) | Out-Null
    Send-FmsRequest -Method GET -Path '/folders' -Expected @(200) | Out-Null
    Send-FmsRequest -Method GET -Path '/documents' -Expected @(200) | Out-Null
    Send-FmsRequest -Method GET -Path '/departments' -Expected @(200) | Out-Null
    Send-FmsRequest -Method GET -Path '/activity' -Expected @(200) | Out-Null
    Send-FmsRequest -Method GET -Path '/trash' -Expected @(200) | Out-Null
    Send-FmsRequest -Method POST -Path '/logout' -Expected @(302) | Out-Null
    Write-Output 'Smoke test Postman standar: PASS'
} finally {
    $client.Dispose()
    $handler.Dispose()
}
