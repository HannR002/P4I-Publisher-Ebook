#Requires -Version 5.1
<#
.SYNOPSIS
    Switches the active AgentRouter model for Anthropic-compatible clients
    (Claude Code CLI) and records the selection for OpenAI-compatible clients.

.DESCRIPTION
    Previous behaviour: the "deepseek" branch only printed a message and set NOTHING,
    and the "astra" branch printed an instruction only. That made the switcher a
    no-op for two of its four advertised options, while AI_AGENTS_SETUP.md claimed
    it worked. This version actually sets the variables.

    Anthropic-compatible clients read ANTHROPIC_MODEL.
    OpenAI-compatible clients are not switchable via environment variable for Roo Code,
    so AGENTROUTER_MODEL is exported as a convenience record and the exact value to
    paste into Roo Code settings is shown.

.PARAMETER Model
    One of: claude48 | claude5 | astra | deepseek

.NOTES
    "deepseek41" is REJECTED by design. Verification of
    https://agentrouter.org/api/pricing proved that exactly ONE DeepSeek route
    exists (deepseek-v4-flash). No deepseek-v4.1-flash route is exposed, so the
    previous deepseek41 option would have produced a guaranteed 404/failure.
    See docs/AGENTROUTER_MODEL_VERIFICATION.md section 4.5.

.EXAMPLE
    .\tooling\ai-model.ps1 deepseek
#>

param(
    [Parameter(Mandatory = $true)]
    [string]$Model,

    # Apply to the Windows User scope permanently. Omit for session-only.
    [switch]$Persist
)

$ErrorActionPreference = 'Stop'

# Routes verified present on the gateway (see docs/evidence/agentrouter-public-models.json).
$registry = @{
    'claude48' = @{ Id = 'claude-opus-4-8';   Label = 'Claude Opus 4.8';   Protocol = 'both' }
    'claude5'  = @{ Id = 'claude-opus-5';     Label = 'Claude Opus 5';     Protocol = 'both' }
    'astra'    = @{ Id = 'gpt-6-astra';       Label = 'GPT-6 Astra';       Protocol = 'openai' }
    'deepseek' = @{ Id = 'deepseek-v4-flash'; Label = 'DeepSeek V4 Flash'; Protocol = 'both' }
}

# Routes proven NOT to exist. Rejected explicitly instead of silently failing later.
$unavailable = @{
    'deepseek41' = 'deepseek-v4.1-flash'
    'deepseek41flash' = 'deepseek-v4.1-flash'
}

$key = $Model.ToLower()

if ($unavailable.ContainsKey($key)) {
    $badId = $unavailable[$key]
    Write-Host ''
    Write-Host ("Route '{0}' TIDAK ADA di AgentRouter." -f $badId) -ForegroundColor Red
    Write-Host 'Terbukti dari /api/pricing: hanya satu route DeepSeek yang diekspos.' -ForegroundColor Yellow
    Write-Host 'Yaitu: deepseek-v4-flash (model_ratio 2, completion_ratio 3).' -ForegroundColor Yellow
    Write-Host 'Lihat docs/AGENTROUTER_MODEL_VERIFICATION.md bagian 4.5.' -ForegroundColor DarkGray
    Write-Host ''
    Write-Host 'Gunakan: .\tooling\ai-model.ps1 deepseek' -ForegroundColor Cyan
    exit 2
}

if (-not $registry.ContainsKey($key)) {
    $options = ($registry.Keys | Sort-Object) -join ', '
    Write-Host "Model tidak dikenal: $Model" -ForegroundColor Red
    Write-Host "Pilihan yang valid: $options" -ForegroundColor Yellow
    Write-Host ''
    Write-Host 'Catatan: "deepseek41" sengaja ditolak — route itu tidak ada di AgentRouter.' -ForegroundColor DarkGray
    exit 1
}

$entry = $registry[$key]
$id    = $entry.Id

# --- Apply the change -------------------------------------------------------
function Set-ModelVariable {
    param([string]$Name, [string]$Value, [bool]$PersistScope)

    Set-Item -Path ("Env:{0}" -f $Name) -Value $Value
    if ($PersistScope) {
        [Environment]::SetEnvironmentVariable($Name, $Value, 'User')
    }
}

Set-ModelVariable -Name 'ANTHROPIC_MODEL'   -Value $id -PersistScope ([bool]$Persist)
Set-ModelVariable -Name 'AGENTROUTER_MODEL' -Value $id -PersistScope ([bool]$Persist)

if ($entry.Protocol -eq 'openai') {
    # Codex CLI is configured statically via ~/.codex/config.toml
    Write-Host ''
    Write-Host 'Model ini memakai protokol OpenAI.' -ForegroundColor Yellow
    Write-Host 'Codex CLI dikonfigurasi statis di ~\.codex\config.toml — ubah "model" di sana.' -ForegroundColor Yellow
}

$scopeLabel = if ($Persist) { 'sesi ini + Windows User (permanen)' } else { 'sesi ini saja' }
Write-Host ''
Write-Host ("Model aktif : {0}" -f $entry.Label) -ForegroundColor Green
Write-Host ("Model ID    : {0}" -f $id) -ForegroundColor Green
Write-Host ("Scope       : {0}" -f $scopeLabel) -ForegroundColor Green
Write-Host ''
Write-Host 'Untuk Roo Code (tidak terbaca dari environment variable):' -ForegroundColor Cyan
Write-Host ("  Buka Settings > Provider > Model, isi: {0}" -f $id) -ForegroundColor Cyan

