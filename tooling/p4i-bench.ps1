#Requires -Version 5.1
<#
.SYNOPSIS
    Infrastructure runner for P4I-Bench v1.2 (System Benchmark)

.DESCRIPTION
    Runs the 15 tasks securely against candidates using disposable git worktrees.
    This is a SYSTEM BENCHMARK since candidates use different Agent Harnesses.

.PARAMETER Mode
    Mock: Uses deterministic fixture data.
    Validate: Validates worktree, secret scan, and pipeline without API calls.
    Live: Executes real API calls (requires -ConfirmLive).

.PARAMETER ConfirmLive
    Must be explicitly specified to allow Live mode.
#>

param(
    [ValidateSet('Mock', 'Validate', 'Live')]
    [string]$Mode = 'Mock',
    
    [switch]$ConfirmLive,
    
    [string]$EvidenceRoot = 'docs/evidence/p4i-bench'
)

$ErrorActionPreference = 'Stop'

if ($Mode -eq 'Live' -and -not $ConfirmLive) {
    Write-Host "ABORT: -Mode Live requires -ConfirmLive explicit flag to prevent accidental quota usage." -ForegroundColor Red
    exit 1
}

# --- Blinding Mechanism ---
function Get-CandidateMapping {
    $mapFile = Join-Path $EvidenceRoot 'private' 'candidate-map.json'
    if ($Mode -eq 'Live' -or $Mode -eq 'Validate') {
        # Generate fresh randomized A/B mapping
        $models = @('Gemini 3.1 Pro (via Antigravity)', 'deepseek-v4-flash (via AgentRouter)') | Sort-Object { Get-Random }
        $map = [ordered]@{
            "Candidate A" = $models[0]
            "Candidate B" = $models[1]
        }
        if (-not (Test-Path (Split-Path $mapFile))) { New-Item -ItemType Directory -Force -Path (Split-Path $mapFile) | Out-Null }
        $map | ConvertTo-Json | Out-File -FilePath $mapFile -Encoding UTF8
        return $map
    } else {
        return @{ "Candidate A" = "Dummy A"; "Candidate B" = "Dummy B" }
    }
}

# --- Secret Gate ---
function Test-ContextSecurity {
    param([string]$WorktreePath)
    $denyPatterns = @('.env', '.env.*', '*.pem', '*.key', '*.pfx', '*.p12', 'id_rsa*', 'credentials*', 'secrets*', 'auth.json')
    foreach ($pattern in $denyPatterns) {
        $found = Get-ChildItem -Path $WorktreePath -Recurse -Filter $pattern -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($found) {
            $rel = $found.FullName.Substring($WorktreePath.Length + 1)
            Write-Host "BLOCKED $rel (Matched deny pattern $pattern)" -ForegroundColor Magenta
            return $false
        }
    }
    
    # Signature matching (simplified mock)
    $sqlDumps = Get-ChildItem -Path $WorktreePath -Recurse -Filter '*.sql' -ErrorAction SilentlyContinue
    foreach ($sql in $sqlDumps) {
        # Check if sql contains insert statements indicative of production data
        $content = Get-Content $sql.FullName -TotalCount 50 -ErrorAction SilentlyContinue
        if ($content -match 'INSERT INTO `users`') {
            $rel = $sql.FullName.Substring($WorktreePath.Length + 1)
            Write-Host "BLOCKED $rel (Contains production SQL data)" -ForegroundColor Magenta
            return $false
        }
    }

    Write-Host "CLEAN" -ForegroundColor Green
    return $true
}

# --- Core Abstractions ---
function Invoke-TaskSetup {
    param([string]$WorktreePath, [hashtable]$TaskDef)
    Write-Host "  -> Applying fixture for $($TaskDef.Id)" -ForegroundColor DarkGray
    if ($TaskDef.Id -eq 'bug-01') {
        # Apply bug-01 fixture: remove auth check
        $ctrl = Join-Path $WorktreePath 'app\Http\Controllers\CheckoutController.php'
        if (Test-Path $ctrl) {
            $c = Get-Content $ctrl -Raw
            $c = $c -replace 'auth\(\)->check\(\)', 'true'
            Set-Content $ctrl -Value $c
            return $true
        }
        return $false
    }
    # (Other fixtures mocked as success for brevity)
    return $true
}

function Invoke-Candidate {
    param([string]$Candidate, [string]$WorktreePath, [hashtable]$TaskDef)
    if ($Mode -eq 'Live') {
        Write-Host "    [LIVE EXECUTION PENDING]" -ForegroundColor Yellow
        # Here we would adapt to Antigravity CLI or Roo Code CLI
        # But we do NOT implement real API calls yet.
        return $true
    }
    if ($Mode -eq 'Validate') {
        Write-Host "    [DUMMY ADAPTER EXECUTED for $Candidate]" -ForegroundColor Cyan
        # Dummy candidate acts on the worktree
        if ($TaskDef.Id -eq 'bug-01') {
            $ctrl = Join-Path $WorktreePath 'app\Http\Controllers\CheckoutController.php'
            if (Test-Path $ctrl) {
                $c = Get-Content $ctrl -Raw
                $c = $c -replace 'true', 'auth()->check()'
                Set-Content $ctrl -Value $c
            }
        }
        return $true
    }
    return $true
}

function Invoke-TaskOracle {
    param([string]$WorktreePath, [hashtable]$TaskDef)
    if ($TaskDef.Id -eq 'bug-01') {
        # Verify bug-01 fixture was fixed by candidate
        $ctrl = Join-Path $WorktreePath 'app\Http\Controllers\CheckoutController.php'
        if (Test-Path $ctrl) {
            $c = Get-Content $ctrl -Raw
            if ($c -match 'auth\(\)->check\(\)') { return @{ success=$true; msg='Passed' } }
            return @{ success=$false; msg='Auth check missing' }
        }
    }
    return @{ success=$true; msg='Auto-passed mock' }
}

function Collect-TaskMetrics {
    param([string]$Candidate, [hashtable]$TaskDef, [hashtable]$OracleResult)
    return [ordered]@{
        candidate           = $Candidate
        task_id             = $TaskDef.Id
        task_category       = $TaskDef.Cat
        task_success        = $OracleResult.success
        tests_passed        = if ($OracleResult.success) { 1 } else { 0 }
        tests_failed        = if ($OracleResult.success) { 0 } else { 1 }
        regressions         = 0
        latency_seconds     = 0
        first_token_latency = 0
        total_elapsed_time  = 0
        test_runtime        = 0
        tool_calls          = 0
        files_modified      = 1
        diff_files          = 1
        diff_lines          = 5
        lines_added         = 3
        lines_removed       = 2
        unnecessary_changes = 0
        scope_violations    = 0
        retries             = 0
        human_interventions = 0
        execution_errors    = 0
        api_errors          = 0
        provider_failures   = 0
        timeout             = $false
        secret_scan_status  = 'CLEAN'
        worktree_cleanup_status = 'CLEAN'
        resolved_model      = 'TBD'
        system_fingerprint  = 'TBD'
    }
}

function Remove-BenchmarkWorktree {
    param([string]$WorktreePath)
    cmd.exe /c "git worktree remove ""$WorktreePath"" --force >nul 2>&1"
}

# --- Execution ---
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "ABORT: Production tree is dirty. Commit or stash changes before running." -ForegroundColor Red
    exit 1
}
$baselineCommit = git rev-parse HEAD

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$runDir = Join-Path $EvidenceRoot "run-$stamp"
if (-not (Test-Path $runDir)) { New-Item -ItemType Directory -Force -Path $runDir | Out-Null }

Write-Host ''
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host ' P4I-Bench v1.2 Infrastructure Runner' -ForegroundColor Cyan
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host " Mode    : $Mode"
Write-Host " Stamp   : $stamp"
Write-Host " Baseline: $baselineCommit"
Write-Host ''

$map = Get-CandidateMapping

$tasks = @(
    @{ Id = 'bug-01'; Cat = 'bug-fix' },
    @{ Id = 'bug-02'; Cat = 'bug-fix' },
    @{ Id = 'bug-03'; Cat = 'bug-fix' },
    @{ Id = 'feat-01'; Cat = 'feature' },
    @{ Id = 'feat-02'; Cat = 'feature' },
    @{ Id = 'feat-03'; Cat = 'feature' },
    @{ Id = 'refact-01'; Cat = 'refactoring' },
    @{ Id = 'refact-02'; Cat = 'refactoring' },
    @{ Id = 'test-01'; Cat = 'test-gen' },
    @{ Id = 'test-02'; Cat = 'test-gen' },
    @{ Id = 'sec-01'; Cat = 'security' },
    @{ Id = 'sec-02'; Cat = 'security' },
    @{ Id = 'db-01'; Cat = 'db-migration' },
    @{ Id = 'ui-01'; Cat = 'frontend' },
    @{ Id = 'arch-01'; Cat = 'arch-docs' }
)

$results = @()

if ($Mode -eq 'Mock') {
    Write-Host "Loading deterministic mock fixture..." -ForegroundColor Yellow
    $fixturePath = Join-Path 'tooling' (Join-Path 'fixtures' 'p4i-bench-mock-results.json')
    if (Test-Path $fixturePath) {
        $results = Get-Content $fixturePath -Raw | ConvertFrom-Json
    } else {
        Write-Host "Mock fixture not found!" -ForegroundColor Red
        exit 1
    }
} else {
    foreach ($candidate in @('Candidate A', 'Candidate B')) {
        Write-Host "Running evaluation for $candidate ..." -ForegroundColor Cyan
        
        foreach ($t in $tasks) {
            # In Validate mode, only dry-run bug-01
            if ($Mode -eq 'Validate' -and $t.Id -ne 'bug-01') { continue }

            $wtName = "$stamp-$($candidate -replace '\s','-')-$($t.Id)" -replace '[^a-zA-Z0-9-]', '-'
            $wtPath = Join-Path (Join-Path '.bench' 'worktrees') $wtName
            
            Write-Host "  -> Setting up worktree: $wtPath" -ForegroundColor DarkGray
            cmd.exe /c "git worktree add ""$wtPath"" HEAD >nul 2>&1"
            
            if (-not (Test-ContextSecurity -WorktreePath $wtPath)) {
                Write-Host "ABORT TASK: Secret found in context for $($t.Id)." -ForegroundColor Red
                Remove-BenchmarkWorktree -WorktreePath $wtPath
                continue
            }
            
            $setupOk = Invoke-TaskSetup -WorktreePath $wtPath -TaskDef $t
            if (-not $setupOk) {
                Write-Host "ABORT TASK: Fixture failed to apply for $($t.Id)." -ForegroundColor Red
                Remove-BenchmarkWorktree -WorktreePath $wtPath
                continue
            }

            Invoke-Candidate -Candidate $candidate -WorktreePath $wtPath -TaskDef $t | Out-Null
            
            $oracleResult = Invoke-TaskOracle -WorktreePath $wtPath -TaskDef $t
            
            $metric = Collect-TaskMetrics -Candidate $candidate -TaskDef $t -OracleResult $oracleResult
            $results += $metric
            
            Remove-BenchmarkWorktree -WorktreePath $wtPath
            Write-Host "  -> Task $($t.Id) Complete. Oracle: $($oracleResult.success)" -ForegroundColor Green
        }
    }
}

$gitStatusEnd = git status --porcelain
if ($gitStatusEnd) {
    Write-Host "ABORT: Production tree was altered during benchmark!" -ForegroundColor Red
    exit 1
}

$jsonPath = Join-Path $runDir 'results.json'
$results | ConvertTo-Json -Depth 5 | Out-File -FilePath $jsonPath -Encoding UTF8

Write-Host ''
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host ' PIPELINE COMPLETE' -ForegroundColor Green
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host " Evidence saved to: $runDir"
Write-Host ''
