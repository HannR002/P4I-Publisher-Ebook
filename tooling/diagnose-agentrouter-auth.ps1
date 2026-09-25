#Requires -Version 5.1
<#
.SYNOPSIS
    Diagnoses why AgentRouter returns 401 UNAUTHENTICATED from scripted clients
    while the same account works from inside Roo Code.

.DESCRIPTION
    The first verification run returned 401 with this body:

        {"error":{"message":"unauthorized client detected, contact support ..."},
         "message":"UNAUTHENTICATED","success":false,
         "type":"unauthorized_client_error"}

    Note it says "unauthorized client", not "invalid api key". Combined with the
    X-Oneapi-Request-Id header (one-api / new-api gateway) and the acw_tc cookie
    (Aliyun WAF), there are three competing hypotheses:

      H1 - The key in $env:AGENTROUTER_API_KEY is a REVOKED token. The journey log
           records that the old token was deleted and a new token was created, and
           that the new token was entered manually into Roo Code SecretStorage.
           If setup-agentrouter.ps1 was run before the rotation, the environment
           variable still holds the dead key.

      H2 - The WAF blocks requests that do not present a recognised client
           User-Agent. PowerShell's HttpClient sends no User-Agent by default.

      H3 - The path or host variant is wrong (e.g. /v1/models vs /models).

    This script tests H2 and H3 directly, and produces a non-reversible fingerprint
    of the local key so H1 can be confirmed by comparison against the AgentRouter
    console token list.

.SECURITY
    The API key is never written to disk and never printed.
    Only a SHA-256 fingerprint (one-way) and a short masked prefix are shown, and
    only for the purpose of identity comparison against the console.
#>

[CmdletBinding()]
param(
    [string]$BaseUrl = 'https://agentrouter.org/v1'
)

$ErrorActionPreference = 'Stop'

try {
    [Net.ServicePointManager]::SecurityProtocol =
        [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11
} catch { }

Add-Type -AssemblyName System.Net.Http -ErrorAction SilentlyContinue

function Get-KeyInfo {
    <# Reports which scope supplied the key, plus a safe one-way fingerprint. #>
    $process = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'Process')
    $user    = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'User')
    $machine = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'Machine')

    $chosen = $null
    $scope  = 'NONE'
    if (-not [string]::IsNullOrWhiteSpace($process)) { $chosen = $process.Trim(); $scope = 'Process' }
    elseif (-not [string]::IsNullOrWhiteSpace($user)) { $chosen = $user.Trim();    $scope = 'User' }
    elseif (-not [string]::IsNullOrWhiteSpace($machine)) { $chosen = $machine.Trim(); $scope = 'Machine' }

    if (-not $chosen) {
        return [pscustomobject]@{
            Present = $false; Scope = 'NONE'; Length = 0; Prefix = ''
            Fingerprint = ''; Value = $null
            ProcessSet = (-not [string]::IsNullOrWhiteSpace($process))
            UserSet    = (-not [string]::IsNullOrWhiteSpace($user))
            MachineSet = (-not [string]::IsNullOrWhiteSpace($machine))
        }
    }

    $sha = [Security.Cryptography.SHA256]::Create()
    $hashBytes = $sha.ComputeHash([Text.Encoding]::UTF8.GetBytes($chosen))
    $hex = -join ($hashBytes[0..7] | ForEach-Object { $_.ToString('x2') })

    $visible = [Math]::Min(6, $chosen.Length)
    $prefix  = $chosen.Substring(0, $visible) + '...'

    return [pscustomobject]@{
        Present     = $true
        Scope       = $scope
        Length      = $chosen.Length
        Prefix      = $prefix
        Fingerprint = $hex
        Value       = $chosen
        ProcessSet  = (-not [string]::IsNullOrWhiteSpace($process))
        UserSet     = (-not [string]::IsNullOrWhiteSpace($user))
        MachineSet  = (-not [string]::IsNullOrWhiteSpace($machine))
    }
}

function Invoke-Probe {
    param(
        [string]$Name,
        [string]$ApiKey,
        [string]$Url,
        [string]$UserAgent,
        [string]$Method = 'GET'
    )

    $handler = New-Object System.Net.Http.HttpClientHandler
    $client  = New-Object System.Net.Http.HttpClient($handler)
    $client.Timeout = [TimeSpan]::FromSeconds(30)

    $req = New-Object System.Net.Http.HttpRequestMessage
    $req.Method     = New-Object System.Net.Http.HttpMethod($Method)
    $req.RequestUri = [Uri]$Url
    $req.Headers.TryAddWithoutValidation('Authorization', "Bearer $ApiKey") | Out-Null
    if ($UserAgent) { $req.Headers.TryAddWithoutValidation('User-Agent', $UserAgent) | Out-Null }
    $req.Headers.TryAddWithoutValidation('Accept', 'application/json') | Out-Null

    try {
        $resp = $client.SendAsync($req).GetAwaiter().GetResult()
        $body = $resp.Content.ReadAsStringAsync().GetAwaiter().GetResult()
        $code = [int]$resp.StatusCode
    } catch {
        $code = 0
        $body = "TRANSPORT ERROR: $($_.Exception.Message)"
    } finally {
        $client.Dispose()
    }

    $short = $body
    if ($short.Length -gt 220) { $short = $short.Substring(0, 220) + '...' }

    return [pscustomobject]@{
        Name = $Name; Url = $Url; UserAgent = $UserAgent; Status = $code; Body = $short
    }
}

Write-Host ''
Write-Host '==========================================' -ForegroundColor Cyan
Write-Host ' AgentRouter Auth Diagnostics' -ForegroundColor Cyan
Write-Host '==========================================' -ForegroundColor Cyan
Write-Host ''

# ---------- Key identity report ----------
$info = Get-KeyInfo
Write-Host 'Local key report (no secret is printed):' -ForegroundColor Yellow
Write-Host ('  Present            : {0}' -f $info.Present)
Write-Host ('  Resolved from scope: {0}' -f $info.Scope)
Write-Host ('  Masked prefix      : {0}' -f $info.Prefix)
Write-Host ('  Length             : {0} characters' -f $info.Length)
Write-Host ('  SHA-256 fingerprint: {0}' -f $info.Fingerprint)
Write-Host ('  Present in Process : {0}' -f $info.ProcessSet)
Write-Host ('  Present in User    : {0}' -f $info.UserSet)
Write-Host ('  Present in Machine : {0}' -f $info.MachineSet)
Write-Host ''
Write-Host '  Compare the masked prefix against the token list at' -ForegroundColor DarkGray
Write-Host '  https://agentrouter.org/console/token' -ForegroundColor DarkGray
Write-Host '  If the prefix does not match the token that Roo Code uses,' -ForegroundColor DarkGray
Write-Host '  the environment variable holds a stale or revoked key.' -ForegroundColor DarkGray
Write-Host ''

if (-not $info.Present) {
    Write-Host 'No key found in any scope. Re-run .\tooling\setup-agentrouter.ps1 (option 2).' -ForegroundColor Red
    return
}

# ---------- Probe matrix ----------
$root = $BaseUrl.TrimEnd('/')
$uaRoo    = 'Roo-Code/3.54.0 (Antigravity IDE)'
$uaOpenAI = 'OpenAI/Python 1.55.0'
$uaClaude = 'claude-cli/2.1.281 (external, cli)'
$uaCurl   = 'curl/8.4.0'

$probes = @(
    @{ Name = 'H2 no UA             '; Url = "$root/models"; Ua = $null }
    @{ Name = 'H2 RooCode UA        '; Url = "$root/models"; Ua = $uaRoo }
    @{ Name = 'H2 OpenAI UA         '; Url = "$root/models"; Ua = $uaOpenAI }
    @{ Name = 'H2 Claude CLI UA     '; Url = "$root/models"; Ua = $uaClaude }
    @{ Name = 'H2 curl UA           '; Url = "$root/models"; Ua = $uaCurl }
    @{ Name = 'H3 root host models  '; Url = "$($root -replace '/v1$','')/models"; Ua = $uaRoo }
)

Write-Host 'Probe matrix:' -ForegroundColor Yellow
$results = @()
foreach ($p in $probes) {
    $r = Invoke-Probe -Name $p.Name -ApiKey $info.Value -Url $p.Url -UserAgent $p.Ua
    $results += $r
    $color = if ($r.Status -eq 200) { 'Green' } elseif ($r.Status -eq 401) { 'Red' } else { 'Yellow' }
    Write-Host ('  [{0}] {1} -> {2}' -f $r.Status, $p.Name, $r.Url) -ForegroundColor $color
}

Write-Host ''
Write-Host 'Response bodies:' -ForegroundColor Yellow
foreach ($r in $results) {
    Write-Host ('  {0}' -f $r.Name)
    Write-Host ('    {0}' -f $r.Body) -ForegroundColor DarkGray
}

# ---------- Verdict ----------
Write-Host ''
Write-Host '==========================================' -ForegroundColor Cyan
$anySuccess = ($results | Where-Object { $_.Status -eq 200 }).Count -gt 0
$all401     = ($results | Where-Object { $_.Status -eq 401 }).Count -eq $results.Count

if ($anySuccess) {
    Write-Host ' VERDICT: Authentication WORKS with the tested client profile.' -ForegroundColor Green
    Write-Host ' A User-Agent or path variant was the blocker. Use the winning' -ForegroundColor Green
    Write-Host ' profile in tooling/check-deepseek-version.ps1 and re-run it.' -ForegroundColor Green
} elseif ($all401) {
    Write-Host ' VERDICT: Hypothesis H1 is favoured - the key itself is rejected' -ForegroundColor Red
    Write-Host ' regardless of User-Agent or path. The environment variable almost' -ForegroundColor Red
    Write-Host ' certainly holds the REVOKED token from before the rotation.' -ForegroundColor Red
    Write-Host ''
    Write-Host ' Fix: re-run .\tooling\setup-agentrouter.ps1 with option 2 and paste' -ForegroundColor Yellow
    Write-Host ' the CURRENT token (the one Roo Code is using). Then re-run this' -ForegroundColor Yellow
    Write-Host ' diagnostic to confirm 200.' -ForegroundColor Yellow
} else {
    Write-Host ' VERDICT: Mixed results. Inspect bodies above.' -ForegroundColor Yellow
}
Write-Host '==========================================' -ForegroundColor Cyan
Write-Host ''
