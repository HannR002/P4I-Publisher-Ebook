#Requires -Version 5.1
<#
.SYNOPSIS
    Verifies which upstream DeepSeek model AgentRouter actually serves for the
    route alias "deepseek-v4-flash".

.DESCRIPTION
    The alias "deepseek-v4-flash" is a gateway label, not a version guarantee.
    This script captures the observable evidence AgentRouter exposes so the
    resolved upstream model can be identified, or explicitly declared unknown.

    Evidence captured:
      A. GET  {BaseUrl}/models            -> full route catalogue
      B. POST {BaseUrl}/chat/completions   -> full raw body for the target alias
      C. Every HTTP response header from both calls
      D. Resolved identifiers: model, id, created, system_fingerprint, owned_by
      F. A behavioural probe, recorded as LOW-CONFIDENCE corroboration only

.SECURITY
    * The API key is read ONLY from $env:AGENTROUTER_API_KEY.
    * The key is never written to disk, never echoed, never logged.
    * The key is never accepted via file or command-line argument.
    * Evidence files contain response data only, never credentials.

.EXAMPLE
    .\tooling\check-deepseek-version.ps1

.EXAMPLE
    .\tooling\check-deepseek-version.ps1 -Model "deepseek-v4.1-flash"
#>

[CmdletBinding()]
param(
    [string]$BaseUrl      = 'https://agentrouter.org/v1',
    [string]$Model        = 'deepseek-v4-flash',
    [string]$EvidenceRoot = 'docs/evidence'
)

$ErrorActionPreference = 'Stop'

# ---- TLS for Windows PowerShell 5.1 ----------------------------------------
try {
    [Net.ServicePointManager]::SecurityProtocol =
        [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11
} catch { }

function Get-AgentRouterKey {
    <# Reads the key from the environment only. Never from disk, never from args. #>
    $k = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'Process')
    if ([string]::IsNullOrWhiteSpace($k)) {
        $k = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'User')
    }
    if ([string]::IsNullOrWhiteSpace($k)) {
        throw ('AGENTROUTER_API_KEY is not set. Run .\tooling\setup-agentrouter.ps1 with option 2 first. ' +
               'The key is intentionally never accepted via file or argument.')
    }
    return $k.Trim()
}

function New-AgentRouterClient {
    param([string]$ApiKey)
    Add-Type -AssemblyName System.Net.Http -ErrorAction SilentlyContinue
    $handler = New-Object System.Net.Http.HttpClientHandler
    $client  = New-Object System.Net.Http.HttpClient($handler)
    $client.Timeout = [TimeSpan]::FromSeconds(120)
    $client.DefaultRequestHeaders.Add('Authorization', "Bearer $ApiKey")
    $client.DefaultRequestHeaders.Add('Accept', 'application/json')
    return $client
}

function Invoke-Captured {
    <# Sends a request and returns status, every header, and the untouched body. #>
    param(
        [System.Net.Http.HttpClient]$Client,
        [string]$Method,
        [string]$Url,
        [string]$JsonBody
    )

    $req = New-Object System.Net.Http.HttpRequestMessage
    $req.Method     = New-Object System.Net.Http.HttpMethod($Method)
    $req.RequestUri = [Uri]$Url
    if ($JsonBody) {
        $req.Content = New-Object System.Net.Http.StringContent(
            $JsonBody, [Text.Encoding]::UTF8, 'application/json')
    }

    $sw   = [Diagnostics.Stopwatch]::StartNew()
    $resp = $Client.SendAsync($req).GetAwaiter().GetResult()
    $body = $resp.Content.ReadAsStringAsync().GetAwaiter().GetResult()
    $sw.Stop()

    $headers = [ordered]@{}
    foreach ($h in $resp.Headers)         { $headers[$h.Key] = ($h.Value -join '; ') }
    foreach ($h in $resp.Content.Headers) { $headers[$h.Key] = ($h.Value -join '; ') }

    return [pscustomobject]@{
        Method     = $Method
        Url        = $Url
        StatusCode = [int]$resp.StatusCode
        Reason     = $resp.ReasonPhrase
        ElapsedMs  = [int]$sw.ElapsedMilliseconds
        Headers    = $headers
        Body       = $body
    }
}

function Write-EvidenceBlock {
    param(
        [string]$Path,
        [string]$Title,
        [object]$Result
    )
    $lines = @()
    $lines += "=== $Title ==="
    $lines += "Method       : $($Result.Method)"
    $lines += "Url          : $($Result.Url)"
    $lines += "Status       : $($Result.StatusCode) $($Result.Reason)"
    $lines += "ElapsedMs    : $($Result.ElapsedMs)"
    $lines += ''
    $lines += '--- Response Headers ---'
    if ($Result.Headers.Keys.Count -eq 0) {
        $lines += '(none)'
    } else {
        foreach ($k in $Result.Headers.Keys) { $lines += ("{0}: {1}" -f $k, $Result.Headers[$k]) }
    }
    $lines += ''
    $lines += '--- Raw Body ---'
    $lines += $Result.Body
    $lines += ''
    $lines | Set-Content -Path $Path -Encoding UTF8
}

# ---- Setup -----------------------------------------------------------------
$apiKey = Get-AgentRouterKey
$stamp  = Get-Date -Format 'yyyyMMdd-HHmmss'
$dir    = Join-Path $EvidenceRoot ("agentrouter-verification-$stamp")
$null   = New-Item -ItemType Directory -Path $dir -Force

Write-Host 'AgentRouter DeepSeek Version Verification' -ForegroundColor Cyan
Write-Host ('Evidence directory: {0}' -f $dir) -ForegroundColor Cyan
Write-Host ('Target alias      : {0}' -f $Model) -ForegroundColor Cyan
Write-Host ''

$client = New-AgentRouterClient -ApiKey $apiKey

# ---- Step A + C: route catalogue -------------------------------------------
Write-Host '[A/C] GET /models ...' -ForegroundColor Yellow
$modelsUrl = ($BaseUrl.TrimEnd('/') + '/models')
$models    = Invoke-Captured -Client $client -Method 'GET' -Url $modelsUrl -JsonBody $null
Write-EvidenceBlock -Path (Join-Path $dir 'A-models.txt') -Title 'Route Catalogue' -Result $models
Write-Host ('      status {0}' -f $models.StatusCode)

# ---- Step B + C: chat completion -------------------------------------------
$payloadObj = [ordered]@{
    model      = $Model
    messages   = @(@{ role = 'user'; content = 'Reply with the single word: ping' })
    max_tokens = 1
    temperature = 0
}
$payload = ($payloadObj | ConvertTo-Json -Depth 10 -Compress)
$chatUrl = ($BaseUrl.TrimEnd('/') + '/chat/completions')

Write-Host '[B/C] POST /chat/completions ...' -ForegroundColor Yellow
$chat = Invoke-Captured -Client $client -Method 'POST' -Url $chatUrl -JsonBody $payload
Write-EvidenceBlock -Path (Join-Path $dir 'B-chat.txt') -Title 'Chat Completion Raw' -Result $chat
Write-Host ('      status {0}' -f $chat.StatusCode)

# ---- Step F: behavioural probe (low confidence) ----------------------------
$probeObj = [ordered]@{
    model    = $Model
    messages = @(@{
        role    = 'user'
        content = 'Answer strictly as data, no preamble. Provide: (1) your training knowledge cutoff as YYYY-MM, (2) your exact DeepSeek model name and version string, (3) the previous minor release of your model family. If unknown, write UNKNOWN.'
    })
    max_tokens  = 300
    temperature = 0
}
$probe = Invoke-Captured -Client $client -Method 'POST' -Url $chatUrl `
         -JsonBody ($probeObj | ConvertTo-Json -Depth 10 -Compress)
Write-EvidenceBlock -Path (Join-Path $dir 'F-probe.txt') -Title 'Behavioural Probe (LOW CONFIDENCE)' -Result $probe
Write-Host ('[F]   probe status {0}' -f $probe.StatusCode)

# ---- Step D: resolved identifier extraction --------------------------------
Write-Host '[D] Extracting resolved identifiers ...' -ForegroundColor Yellow

function Get-JsonSafe {
    param([string]$Text)
    if ([string]::IsNullOrWhiteSpace($Text)) { return $null }
    try { return $Text | ConvertFrom-Json } catch { return $null }
}

$modelsJson = Get-JsonSafe $models.Body
$chatJson   = Get-JsonSafe $chat.Body

$deepseekRoutes = @()
if ($modelsJson -and $modelsJson.data) {
    $deepseekRoutes = @($modelsJson.data | Where-Object { $_.id -match 'deepseek' } | ForEach-Object { $_.id })
}

$chatModel  = $null
$chatId     = $null
$chatCreated = $null
$chatFinger = $null
$chatOwner  = $null
$chatSystemFingerprint = $null
if ($chatJson) {
    $chatModel             = $chatJson.model
    $chatId                = $chatJson.id
    $chatCreated           = $chatJson.created
    $chatSystemFingerprint = $chatJson.system_fingerprint
    $chatFinger            = $chatJson.system_fingerprint
    $chatOwner             = $chatJson.owned_by
}

# Version-disclosure headers
$versionHeaders = [ordered]@{}
foreach ($k in $chat.Headers.Keys) {
    if ($k -match '(?i)version|model|provider|upstream|router|fingerprint|x-') {
        $versionHeaders[$k] = $chat.Headers[$k]
    }
}

$summary = [ordered]@{
    capturedAtUtc        = (Get-Date).ToUniversalTime().ToString('o')
    baseUrl              = $BaseUrl
    requestedAlias       = $Model
    modelsStatus         = $models.StatusCode
    chatStatus           = $chat.StatusCode
    probeStatus          = $probe.StatusCode
    chatEchoedModel      = $chatModel
    chatResponseId       = $chatId
    chatCreated          = $chatCreated
    systemFingerprint    = $chatSystemFingerprint
    ownedBy              = $chatOwner
    deepseekRoutesOffered = $deepseekRoutes
    aliasPublishedInCatalogue = ($deepseekRoutes -contains $Model)
    versionRelatedHeaders = $versionHeaders
}

$summaryPath = Join-Path $dir 'D-summary.json'
($summary | ConvertTo-Json -Depth 10) | Set-Content -Path $summaryPath -Encoding UTF8

# ---- Console report (no secrets) -------------------------------------------
Write-Host ''
Write-Host '================ RESULT ================' -ForegroundColor Cyan
Write-Host ('Requested alias       : {0}' -f $Model)
Write-Host ('Alias in /models      : {0}' -f $summary.aliasPublishedInCatalogue)
Write-Host ('Chat status           : {0}' -f $chat.StatusCode)
Write-Host ('Echoed model field    : {0}' -f $chatModel)
Write-Host ('Response id           : {0}' -f $chatId)
Write-Host ('system_fingerprint    : {0}' -f $chatSystemFingerprint)
Write-Host ('owned_by              : {0}' -f $chatOwner)
Write-Host ''
Write-Host 'DeepSeek routes offered by the gateway:' -ForegroundColor Cyan
if ($deepseekRoutes.Count -eq 0) {
    Write-Host '  (none matched the pattern "deepseek")'
} else {
    foreach ($r in $deepseekRoutes) { Write-Host ('  - {0}' -f $r) }
}
Write-Host ''
Write-Host 'Version-related response headers:' -ForegroundColor Cyan
if ($versionHeaders.Keys.Count -eq 0) {
    Write-Host '  (none - gateway discloses no version info in headers)'
} else {
    foreach ($k in $versionHeaders.Keys) { Write-Host ('  {0}: {1}' -f $k, $versionHeaders[$k]) }
}
Write-Host ''
Write-Host ('Evidence written to: {0}' -f $dir) -ForegroundColor Green
Write-Host 'Send the contents of D-summary.json and the console output above to analyse the verdict.' -ForegroundColor Green

$client.Dispose()
