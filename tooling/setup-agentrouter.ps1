#Requires -Version 5.1
<#
.SYNOPSIS
    Stores the AgentRouter API key for CLI clients, with an explicit warning about
    how Windows User-scope environment variables are actually stored.

.DESCRIPTION
    SECURITY DISCLOSURE — please read.

    SetEnvironmentVariable(name, value, "User") writes the value as PLAINTEXT into
    HKEY_CURRENT_USER\Environment. Any process running as the same user can read it.
    It is NOT encrypted, despite commonly being described as "secure registry storage".

    Options offered:
      1. Session only        - key lives in this PowerShell process, then vanishes.
                               Nothing is written to disk. Safest short-lived option.
      2. Windows User scope  - key is written to HKCU\Environment as PLAINTEXT and
                               persists across reboots. Convenient but unencrypted.

    This script never writes the key to any file in the repository, and it clears
    the key from local memory after use.

    NOTE ON KEY ROTATION
    If you revoke a token in the AgentRouter console, the value stored here becomes
    stale. You MUST re-run this script with the replacement token. Roo Code keeps its
    own copy in SecretStorage and will keep working, which masks the staleness for
    every CLI or scripted client. This exact failure has already occurred once —
    see docs/AGENTROUTER_MODEL_VERIFICATION.md.

.PARAMETER NonInteractive
    Skip the menu and assume option 1 (session only).

.EXAMPLE
    .\tooling\setup-agentrouter.ps1
#>

param(
    [switch]$NonInteractive
)

$ErrorActionPreference = 'Stop'

Write-Host ''
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host ' AgentRouter Secure Setup' -ForegroundColor Cyan
Write-Host '====================================================' -ForegroundColor Cyan
Write-Host ''

if ($NonInteractive) {
    $choice = '1'
} else {
    Write-Host 'Pilih metode penyimpanan key:' -ForegroundColor Yellow
    Write-Host '  1. Sesi PowerShell saat ini saja  (tidak ditulis ke disk)'
    Write-Host '  2. Windows USER environment variable (PLAINTEXT di registry)'
    Write-Host ''
    $choice = Read-Host 'Pilihan (1/2)'
}

$secureKey = Read-Host -AsSecureString 'Masukkan API Key AgentRouter'
$bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureKey)
$apiKey = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)
[System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)

if ([string]::IsNullOrWhiteSpace($apiKey)) {
    Write-Host 'API key kosong. Batal.' -ForegroundColor Red
    exit 1
}

# Endpoints and defaults
$anthropicBaseUrl = 'https://agentrouter.org'
$openaiBaseUrl    = 'https://agentrouter.org/v1'
$defaultModel     = 'deepseek-v4-flash'

# Report key identity without revealing it
$sha      = [Security.Cryptography.SHA256]::Create()
$hashBytes = $sha.ComputeHash([Text.Encoding]::UTF8.GetBytes($apiKey.Trim()))
$fingerprint = -join ($hashBytes[0..7] | ForEach-Object { $_.ToString('x2') })
$visible  = [Math]::Min(6, $apiKey.Trim().Length)
$prefix   = $apiKey.Trim().Substring(0, $visible) + '...'

if ($choice -eq '2') {
    [Environment]::SetEnvironmentVariable('AGENTROUTER_API_KEY',  $apiKey.Trim(), 'User')
    [Environment]::SetEnvironmentVariable('ANTHROPIC_AUTH_TOKEN',  $apiKey.Trim(), 'User')
    [Environment]::SetEnvironmentVariable('ANTHROPIC_BASE_URL',    $anthropicBaseUrl, 'User')
    [Environment]::SetEnvironmentVariable('AGENTROUTER_BASE_URL',  $openaiBaseUrl, 'User')
    [Environment]::SetEnvironmentVariable('ANTHROPIC_MODEL',       $defaultModel, 'User')
    [Environment]::SetEnvironmentVariable('AGENTROUTER_MODEL',     $defaultModel, 'User')

    Write-Host ''
    Write-Host 'Tersimpan untuk Windows User.' -ForegroundColor Green
    Write-Host 'PERINGATAN KEAMANAN:' -ForegroundColor Yellow
    Write-Host '  Nilai ini ditulis PLAINTEXT ke HKCU\Environment.' -ForegroundColor Yellow
    Write-Host '  Proses lain milik user yang sama dapat membacanya.' -ForegroundColor Yellow
    Write-Host '  Untuk secret produksi, gunakan Windows Credential Manager.' -ForegroundColor Yellow
} else {
    $env:AGENTROUTER_API_KEY  = $apiKey.Trim()
    $env:ANTHROPIC_AUTH_TOKEN = $apiKey.Trim()
    $env:ANTHROPIC_BASE_URL   = $anthropicBaseUrl
    $env:AGENTROUTER_BASE_URL = $openaiBaseUrl
    $env:ANTHROPIC_MODEL      = $defaultModel
    $env:AGENTROUTER_MODEL    = $defaultModel

    Write-Host ''
    Write-Host 'Tersimpan untuk sesi PowerShell ini saja.' -ForegroundColor Green
    Write-Host 'Tidak ada yang ditulis ke disk.' -ForegroundColor Green
}

Write-Host ''
Write-Host ("Key fingerprint : {0}" -f $fingerprint) -ForegroundColor Cyan
Write-Host ("Masked prefix   : {0}" -f $prefix) -ForegroundColor Cyan
Write-Host 'Bandingkan prefix dengan daftar token di https://agentrouter.org/console/token' -ForegroundColor DarkGray
Write-Host 'Jika tidak cocok, Anda memakai token yang salah atau sudah dicabut.' -ForegroundColor DarkGray
Write-Host ''
Write-Host 'LANGKAH VERIFIKASI WAJIB:' -ForegroundColor Yellow
Write-Host '  .\tooling\diagnose-agentrouter-auth.ps1' -ForegroundColor Yellow
Write-Host '  Harus menampilkan status 200. Jika masih 401, key belum valid.' -ForegroundColor Yellow
Write-Host ''

# Clear the key from local memory
$apiKey    = $null
$secureKey = $null
Write-Host 'Setup selesai.' -ForegroundColor Green
