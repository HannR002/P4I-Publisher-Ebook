#Requires -Version 5.1
<#
.SYNOPSIS
    Enumerates the authoritative AgentRouter model roster WITHOUT an API key.

.DESCRIPTION
    Discovered during the DeepSeek version investigation: the endpoint

        https://agentrouter.org/api/pricing

    is public. It returns the complete server-published model roster with pricing
    ratios, enabled groups, and supported endpoint types. No authentication needed.

    Why this matters: the /v1/models endpoint requires a valid key and returned 401,
    so route enumeration was blocked. This endpoint bypasses the auth blocker entirely
    and answers the question "does a separate deepseek-v4.1-flash route exist?"

    This is server-published data, so it ranks as strong evidence. It does NOT,
    however, disclose the upstream model version. AgentRouter leaves the owner_by
    (channel attribution) field empty for every model.

.PARAMETER Base
    API origin. Default https://agentrouter.org

.PARAMETER Filter
    Optional substring filter applied to model_name. Default 'deepseek'.

.PARAMETER Json
    Emit raw JSON instead of a formatted table.

.EXAMPLE
    .\tooling\check-agentrouter-public-models.ps1
    .\tooling\check-agentrouter-public-models.ps1 -Filter '' -Json
#>

[CmdletBinding()]
param(
    [string]$Base = 'https://agentrouter.org',
    [string]$Filter = 'deepseek',
    [switch]$Json,
    [string]$EvidenceRoot = 'docs/evidence'
)

$ErrorActionPreference = 'Stop'

try {
    [Net.ServicePointManager]::SecurityProtocol =
        [Net.SecurityProtocolType]::Tls12 -bor [Net.SecurityProtocolType]::Tls11
} catch { }

Add-Type -AssemblyName System.Net.Http -ErrorAction SilentlyContinue

$url = ($Base.TrimEnd('/') + '/api/pricing')

$handler = New-Object System.Net.Http.HttpClientHandler
$client  = New-Object System.Net.Http.HttpClient($handler)
$client.Timeout = [TimeSpan]::FromSeconds(45)
$client.DefaultRequestHeaders.TryAddWithoutValidation(
    'User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)') | Out-Null

$req = New-Object System.Net.Http.HttpRequestMessage
$req.Method     = New-Object System.Net.Http.HttpMethod('GET')
$req.RequestUri = [Uri]$url

Write-Host ''
Write-Host '=================================================' -ForegroundColor Cyan
Write-Host ' AgentRouter Public Model Roster (NO AUTH)' -ForegroundColor Cyan
Write-Host '=================================================' -ForegroundColor Cyan
Write-Host (" GET {0}" -f $url)
Write-Host ''

try {
    $resp = $client.SendAsync($req).GetAwaiter().GetResult()
    $body = $resp.Content.ReadAsStringAsync().GetAwaiter().GetResult()
    $code = [int]$resp.StatusCode
} catch {
    Write-Host (" REQUEST FAILED: {0}" -f $_.Exception.Message) -ForegroundColor Red
    $client.Dispose()
    exit 1
} finally {
    $client.Dispose()
}

if ($code -ne 200) {
    Write-Host (" HTTP {0} : endpoint not reachable or changed." -f $code) -ForegroundColor Red
    exit 1
}

# PowerShell 5.1 ConvertFrom-Json throws
#   'Cannot process argument because the value of argument "name" is not valid'
# when an object has an empty-string key. AgentRouter returns:
#   "usable_group":{"":"用户分组","default":"默认分组"}
# Rename that one empty key before parsing. The field is only a UI label map
# and is irrelevant to version analysis.
$cleanBody = $body -replace '\{"":', '{"_blank":'

try {
    $parsed = $cleanBody | ConvertFrom-Json
} catch {
    $diagDir = Join-Path $EvidenceRoot ("agentrouter-public-models-FAILED-" + (Get-Date -Format 'yyyyMMdd-HHmmss'))
    $null = New-Item -ItemType Directory -Path $diagDir -Force
    $rawPath = Join-Path $diagDir 'raw-response.txt'
    $body | Set-Content -Path $rawPath -Encoding UTF8

    Write-Host ' Response was not parseable as JSON.' -ForegroundColor Red
    Write-Host (" HTTP status : {0}" -f $code) -ForegroundColor Red
    Write-Host (" Body length : {0} bytes" -f $body.Length) -ForegroundColor Red
    Write-Host (" Parse error : {0}" -f $_.Exception.Message) -ForegroundColor Red
    Write-Host ''
    Write-Host ' First 300 characters of the actual response:' -ForegroundColor Yellow
    $preview = if ($body.Length -gt 300) { $body.Substring(0, 300) } else { $body }
    Write-Host (' ' + $preview) -ForegroundColor DarkGray
    Write-Host ''
    Write-Host (" Raw response saved to: {0}" -f $rawPath) -ForegroundColor Green
    exit 1
}

$all = @($parsed.data)
$matched = if ([string]::IsNullOrEmpty($Filter)) {
    $all
} else {
    @($all | Where-Object { $_.model_name -match [regex]::Escape($Filter) })
}

# ---- Persist raw evidence ---------------------------------------------------
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$null  = New-Item -ItemType Directory -Path $EvidenceRoot -Force
$outPath = Join-Path $EvidenceRoot ("agentrouter-public-models-$stamp.json")
$body | Set-Content -Path $outPath -Encoding UTF8

if ($Json) {
    $body
    Write-Host ''
    Write-Host (" Saved: {0}" -f $outPath) -ForegroundColor Green
    exit 0
}

function Format-Row {
    param($Name, $Ratio, $Completion, $Price, $Endpoints)
    return '  ' + `
        ([string]$Name).PadRight(22) + ' ' + `
        ([string]$Ratio).PadLeft(7) + ' ' + `
        ([string]$Completion).PadLeft(11) + ' ' + `
        ([string]$Price).PadLeft(10) + '  ' + `
        ([string]$Endpoints)
}

Write-Host (" Total routes published : {0}" -f @($all).Count) -ForegroundColor White
Write-Host (" Matching '{0}' : {1}" -f $Filter, @($matched).Count) -ForegroundColor White
Write-Host ''
Write-Host ' All published routes:' -ForegroundColor Yellow
Write-Host ''
Write-Host (Format-Row 'MODEL_NAME' 'RATIO' 'COMPLETION' 'PRICE' 'ENDPOINTS')
Write-Host ('  ' + ('-' * 78))

foreach ($m in $all) {
    $eps = @($m.supported_endpoint_types) -join ','
    $price = if ($m.model_price -eq 0) { 'per-token' } else { $m.model_price }
    Write-Host (Format-Row $m.model_name $m.model_ratio $m.completion_ratio $price $eps)
}

# ---- Version-route analysis ------------------------------------------------
Write-Host ''
Write-Host '=================================================' -ForegroundColor Cyan
Write-Host ' DEEPSEEK VERSION ROUTE ANALYSIS' -ForegroundColor Cyan
Write-Host '=================================================' -ForegroundColor Cyan
Write-Host ''

$dsRoutes = @($all | Where-Object { $_.model_name -match '(?i)deepseek' })
$has41    = @($dsRoutes | Where-Object { $_.model_name -match '(?i)v4\.1|4\.1|v5|next|latest|preview' })

Write-Host (" DeepSeek routes exposed: {0}" -f $dsRoutes.Count) -ForegroundColor White
foreach ($r in $dsRoutes) {
    Write-Host ("   - {0}" -f $r.model_name) -ForegroundColor Green
}
Write-Host ''

if ($dsRoutes.Count -eq 1) {
    Write-Host ' FINDING: exactly ONE DeepSeek route exists.' -ForegroundColor Yellow
    Write-Host ("   '{0}' is the only DeepSeek alias AgentRouter sells." -f $dsRoutes[0].model_name) -ForegroundColor Yellow
    Write-Host '   There is no separate V4.1 alias, so version cannot be' -ForegroundColor Yellow
    Write-Host '   distinguished by route name alone.' -ForegroundColor Yellow
} elseif ($has41.Count -gt 0) {
    Write-Host ' FINDING: a distinct newer-version route EXISTS.' -ForegroundColor Green
    Write-Host '   The two aliases are different products, which means the' -ForegroundColor Green
    Write-Host '   original alias is NOT that newer version.' -ForegroundColor Green
} else {
    Write-Host ' FINDING: multiple DeepSeek routes, but no explicit version marker.' -ForegroundColor Yellow
}

# owner_by check: this is the field that would name the upstream version
$ownerEmpty = (@($all | Where-Object { [string]::IsNullOrWhiteSpace($_.owner_by) }).Count -eq $all.Count)
Write-Host ''
if ($ownerEmpty) {
    Write-Host ' LIMITATION: the owner_by (channel/provider attribution) field is' -ForegroundColor DarkYellow
    Write-Host ' EMPTY for every model. AgentRouter does not publish which upstream' -ForegroundColor DarkYellow
    Write-Host ' provider or model build serves each alias.' -ForegroundColor DarkYellow
    Write-Host ' => Route enumeration alone CANNOT resolve V4 vs V4.1 Flash.' -ForegroundColor DarkYellow
} else {
    Write-Host ' owner_by field is populated. Check it for upstream attribution.' -ForegroundColor Green
}

Write-Host ''
Write-Host (" Saved: {0}" -f $outPath) -ForegroundColor Green
Write-Host ''
