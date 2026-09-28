#Requires -Version 5.1
<#
.SYNOPSIS
    Infrastructure runner for P4I-Bench v1.2 (System Benchmark)

.DESCRIPTION
    Data-driven benchmark harness for P4I tasks.
#>

param(
    [ValidateSet('Mock', 'Validate', 'Live')]
    [string]$Mode = 'Mock',
    [switch]$ConfirmLive,
    [string]$EvidenceRoot = 'docs/evidence/p4i-bench'
)

$ErrorActionPreference = 'Stop'

if ($Mode -eq 'Live' -and -not $ConfirmLive) {
    Write-Host "ABORT: -Mode Live requires -ConfirmLive" -ForegroundColor Red
    exit 1
}

function Get-CandidateMapping {
    $mapFile = Join-Path (Join-Path $EvidenceRoot 'private') 'candidate-map.json'
    if ($Mode -eq 'Live' -or $Mode -eq 'Validate') {
        $models = @('Gemini 3.1 Pro (via Antigravity)', 'deepseek-v4-flash (via AgentRouter)') | Sort-Object { Get-Random }
        $map = [ordered]@{ "Candidate A" = $models[0]; "Candidate B" = $models[1] }
        if (-not (Test-Path (Split-Path $mapFile))) { New-Item -ItemType Directory -Force -Path (Split-Path $mapFile) | Out-Null }
        $map | ConvertTo-Json | Out-File -FilePath $mapFile -Encoding UTF8
        return $map
    }
    return @{ "Candidate A" = "Dummy A"; "Candidate B" = "Dummy B" }
}

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
    
    $sqlDumps = Get-ChildItem -Path $WorktreePath -Recurse -Filter '*.sql' -ErrorAction SilentlyContinue
    foreach ($sql in $sqlDumps) {
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

$TaskDefinitions = @{
    'bug-01' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/CheckoutController.php'
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/CheckoutController.php'
            $c = Get-Content $f -Raw
            $target = "if (!auth()->check())" # example
            # Actual logic for bug-01 in CheckoutController.php (lines 16-17 etc.)
            # Wait, the controller has "abort_unless(config('features.midtrans')"
            # Wait, in the actual file, there is NO auth()->check() in CheckoutController@store!
            # The route is likely protected by middleware 'auth'.
            # If the bug was "removed auth check", maybe I need to remove middleware from routes?
            # Or add a manual auth()->check() bypass.
            # Let's target the exact string "$user  = $request->user();" and replace it.
            $search = '        $user  = $request->user();'
            $replace = '        $user  = \App\Models\User::find(1); // MOCKED FOR BENCHMARK'
            if ($c.Contains($search)) {
                $c = $c.Replace($search, $replace)
                Set-Content $f -Value $c
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/CheckoutController.php'
            $c = Get-Content $f -Raw
            return $c.Contains('\App\Models\User::find(1); // MOCKED FOR BENCHMARK')
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/CheckoutController.php'
            $c = Get-Content $f -Raw
            $c = $c.Replace('        $user  = \App\Models\User::find(1); // MOCKED FOR BENCHMARK', '        $user  = $request->user();')
            Set-Content $f -Value $c
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/CheckoutController.php'
            $c = Get-Content $f -Raw
            if ($c.Contains('$user  = $request->user();')) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'bug-02' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/LibraryController.php'
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            $c = Get-Content $f -Raw
            $search = "BookLicense::with('book')"
            if ($c.Contains($search)) {
                Set-Content $f -Value $c.Replace($search, "BookLicense::query()")
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            return (Get-Content $f -Raw).Contains("BookLicense::query()")
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace("BookLicense::query()", "BookLicense::with('book')")
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            if ((Get-Content $f -Raw).Contains("BookLicense::with('book')")) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'bug-03' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/DrmController.php'
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            $c = Get-Content $f -Raw
            $search = "if (!`$license) {`n            abort(403, 'No active license found for this book.');`n        }"
            if ($c.Contains($search)) {
                Set-Content $f -Value $c.Replace($search, "// if (!`$license) abort removed")
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            return (Get-Content $f -Raw).Contains("// if (!`$license) abort removed")
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace("// if (!`$license) abort removed", "if (!`$license) {`n            abort(403, 'No active license found for this book.');`n        }")
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            if ((Get-Content $f -Raw).Contains("abort(403,")) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'sec-01' = @{
        Cat = 'security'
        Source = 'app/Http/Controllers/ProfileController.php'
        Setup = {
            param($wt)
            # Inject vulnerability in blade so controller MUST sanitize it, OR controller explicitly passes unsanitized.
            # We'll make the blade vulnerable:
            $blade = Join-Path $wt 'resources/views/layouts/navigation.blade.php'
            $c = Get-Content $blade -Raw
            if ($c.Contains('{{ Auth::user()->name }}')) {
                Set-Content $blade -Value $c.Replace('{{ Auth::user()->name }}', '{!! Auth::user()->name !!}')
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $blade = Join-Path $wt 'resources/views/layouts/navigation.blade.php'
            return (Get-Content $blade -Raw).Contains('{!! Auth::user()->name !!}')
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/ProfileController.php'
            $c = Get-Content $f -Raw
            $search = "`$request->user()->fill(`$request->validated());"
            $replace = "`$val = `$request->validated();`n        `$val['name'] = strip_tags(`$val['name']);`n        `$request->user()->fill(`$val);"
            Set-Content $f -Value $c.Replace($search, $replace)
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/ProfileController.php'
            if ((Get-Content $f -Raw).Contains("strip_tags(")) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'sec-02' = @{
        Cat = 'security'
        Source = 'app/Models/User.php'
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            # Replace fillable array with $guarded = []
            $search = "    protected `$fillable = [`n        'name',`n        'email',`n        'password',`n        'is_admin',`n        'is_active',`n    ];"
            if ($c.Contains($search)) {
                Set-Content $f -Value $c.Replace($search, "    protected `$guarded = [];")
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            return (Get-Content $f -Raw).Contains('protected $guarded = [];')
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace("    protected `$guarded = [];", "    protected `$fillable = ['name', 'email', 'password'];")
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            # Must not allow mass assignment of is_admin
            if ($c.Contains('$guarded = []') -or $c.Contains("'is_admin'")) {
                return 'CANDIDATE_FAILED'
            }
            return 'PASS'
        }
    }
}

function Invoke-BenchmarkTask {
    param([string]$Candidate, [string]$TaskId, [string]$WorktreePath)
    
    $def = $TaskDefinitions[$TaskId]
    if (-not $def) {
        return @{ status = 'NOT_IMPLEMENTED' }
    }
    
    # 1. Setup
    $sw = [Diagnostics.Stopwatch]::StartNew()
    $setupOk = & $def.Setup $WorktreePath
    if (-not $setupOk) { return @{ status = 'FIXTURE_FAILED' } }
    
    # 2. Verify
    $verifyOk = & $def.VerifyFixture $WorktreePath
    if (-not $verifyOk) { return @{ status = 'FIXTURE_FAILED' } }
    $sw.Stop()
    $fixMs = $sw.ElapsedMilliseconds
    
    # Commit fixture so diff metrics are relative to fixture
    cmd.exe /c "cd /d ""$WorktreePath"" && git add . && git commit -m ""Fixture"" >nul 2>&1"
    
    # 3. Candidate
    $sw.Restart()
    if ($Mode -eq 'Validate') {
        & $def.DummyCandidate $WorktreePath
    } elseif ($Mode -eq 'Live') {
        # TBD implementation
    }
    $sw.Stop()
    $candMs = $sw.ElapsedMilliseconds
    
    # Metrics Collection via Git
    $filesMod = 0; $add = 0; $del = 0
    $diffOut = cmd.exe /c "cd /d ""$WorktreePath"" && git diff --numstat HEAD"
    if ($diffOut) {
        foreach ($line in $diffOut) {
            if ($line -match '^(\d+|-)\s+(\d+|-)\s+(.+)$') {
                $a = $Matches[1]; $d = $Matches[2]; $f = $Matches[3]
                $filesMod++
                if ($a -ne '-') { $add += [int]$a }
                if ($d -ne '-') { $del += [int]$d }
            }
        }
    }
    
    # Scope validation
    $scopeViolations = 0
    $modFiles = cmd.exe /c "cd /d ""$WorktreePath"" && git diff --name-only HEAD"
    if ($modFiles) {
        foreach ($f in $modFiles) {
            # simple check: did they modify something outside expected source?
            # in real harness, allowed files would be an array.
            if ($f -ne $def.Source -and $f -notmatch 'blade\.php') {
                $scopeViolations++
            }
        }
    }
    if ($scopeViolations -gt 0) {
        return @{ status = 'SCOPE_VIOLATION' }
    }
    
    # 4. Oracle
    $sw.Restart()
    $oracleRes = & $def.Oracle $WorktreePath
    $sw.Stop()
    
    return [ordered]@{
        status = $oracleRes
        latency_fixture = $fixMs
        latency_cand = $candMs
        latency_oracle = $sw.ElapsedMilliseconds
        files_modified = $filesMod
        lines_added = $add
        lines_removed = $del
        scope_violations = $scopeViolations
    }
}

# --- Execution ---
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "ABORT: Production tree is dirty." -ForegroundColor Red; exit 1
}

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$runDir = Join-Path $EvidenceRoot "run-$stamp"
if (-not (Test-Path $runDir)) { New-Item -ItemType Directory -Force -Path $runDir | Out-Null }

$map = Get-CandidateMapping

$tasksToRun = @('bug-01', 'bug-02', 'bug-03', 'sec-01', 'sec-02')

$results = @()

foreach ($candidate in @('Candidate A', 'Candidate B')) {
    Write-Host "Running evaluation for $candidate ..." -ForegroundColor Cyan
    foreach ($t in $tasksToRun) {
        $wtName = "$stamp-$($candidate -replace '\s','-')-$t" -replace '[^a-zA-Z0-9-]', '-'
        $wtPath = Join-Path (Join-Path '.bench' 'worktrees') $wtName
        
        Write-Host "  -> Setting up worktree: $wtPath" -ForegroundColor DarkGray
        cmd.exe /c "git worktree add ""$wtPath"" HEAD >nul 2>&1"
        
        if (-not (Test-ContextSecurity -WorktreePath $wtPath)) {
            Write-Host "ABORT TASK: Secret found in context for $t." -ForegroundColor Red
            cmd.exe /c "git worktree remove ""$wtPath"" --force >nul 2>&1"
            continue
        }
        
        $res = Invoke-BenchmarkTask -Candidate $candidate -TaskId $t -WorktreePath $wtPath
        
        $metric = [ordered]@{
            candidate = $candidate
            task_id = $t
            status = $res.status
            files_modified = if ($null -ne $res.files_modified) { $res.files_modified } else { 0 }
            lines_added = if ($null -ne $res.lines_added) { $res.lines_added } else { 0 }
            lines_removed = if ($null -ne $res.lines_removed) { $res.lines_removed } else { 0 }
            scope_violations = if ($null -ne $res.scope_violations) { $res.scope_violations } else { 0 }
            lat_fix = if ($null -ne $res.latency_fixture) { $res.latency_fixture } else { 0 }
            lat_cand = if ($null -ne $res.latency_cand) { $res.latency_cand } else { 0 }
            lat_oracle = if ($null -ne $res.latency_oracle) { $res.latency_oracle } else { 0 }
        }
        $results += $metric
        
        cmd.exe /c "git worktree remove ""$wtPath"" --force >nul 2>&1"
        Write-Host "  -> Task $t Complete. Status: $($res.status)" -ForegroundColor Green
    }
}

$gitStatusEnd = git status --porcelain
if ($gitStatusEnd) {
    Write-Host "ABORT: Production tree altered!" -ForegroundColor Red; exit 1
}

$results | ConvertTo-Json -Depth 5 | Out-File -FilePath (Join-Path $runDir 'results.json') -Encoding UTF8

Write-Host "PIPELINE COMPLETE. Evidence saved to: $runDir" -ForegroundColor Green
