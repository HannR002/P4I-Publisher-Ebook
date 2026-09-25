#Requires -Version 5.1
<#
.SYNOPSIS
    Measures the harness-style metrics against the LIVE model.

.DESCRIPTION
    Fixes applied (v1.1):
    - pass@1 strictly uses only the first attempt per task.
    - HTTP/API errors (401/429/etc) are marked INVALID CALL and excluded from scoring.
    - SE is N/A when valid_calls = 0.
    - Code execution is used for coding tasks instead of regex.
    - Partial scores (0.5) are labeled as LOCAL RUBRIC.
    - Gateway metadata is recorded but explicitly documented as non-authoritative for upstream identity.

.PARAMETER Model
    Model id passed as the JSON "model" field. Default: deepseek-v4-flash

.PARAMETER Runs
    Repetitions per task. 1 => pass@1, 3 => repeated evaluation. Default: 3

.PARAMETER Base
    OpenAI-compatible base URL. Default: https://agentrouter.org/v1

.PARAMETER EvidenceRoot
    Directory for the raw transcript. Default: docs/evidence

.PARAMETER MaxTokens
    max_tokens per call. Kept high enough that truncation does not fake a failure.
#>

param(
    [string]$Model = 'deepseek-v4-flash',
    [ValidateRange(1, 10)]
    [int]$Runs = 3,
    [string]$Base = 'https://agentrouter.org/v1',
    [string]$EvidenceRoot = 'docs/evidence',
    [int]$MaxTokens = 512
)

$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

# --- Credential (env only) --------------------------------------------------
$key = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'Process')
if ([string]::IsNullOrWhiteSpace($key)) {
    $key = [Environment]::GetEnvironmentVariable('AGENTROUTER_API_KEY', 'User')
}
if ([string]::IsNullOrWhiteSpace($key)) {
    Write-Host 'AGENTROUTER_API_KEY tidak ditemukan (Process/User).' -ForegroundColor Red
    Write-Host 'Jalankan .\tooling\setup-agentrouter.ps1 lalu ulangi.' -ForegroundColor Yellow
    exit 1
}

# --- Task set ---------------------------------------------------------------
$tasks = @(
    @{
        Id       = 'arith-exact'
        Category = 'Accuracy'
        Type     = 'Text'
        Prompt   = 'What is 47 * 89? Reply with the number only, no words.'
        Strict   = '^\s*4183\s*$'
        Partial  = @('4183')
    },
    @{
        Id       = 'reasoning-step'
        Category = 'Reasoning'
        Type     = 'Text'
        Prompt   = 'A tank fills at 12 L/min and drains at 5 L/min. Starting empty, how many minutes until it holds 42 L? Answer with the number of minutes only.'
        Strict   = '^\s*6\s*$'
        Partial  = @('6')
    },
    @{
        Id       = 'instruction-fmt'
        Category = 'Instruction-following'
        Type     = 'Text'
        Prompt   = 'Return exactly this JSON and nothing else: {"status":"ok","count":3}'
        Strict   = '^\s*\{\s*"status"\s*:\s*"ok"\s*,\s*"count"\s*:\s*3\s*\}\s*$'
        Partial  = @('"status"', '"count"')
    },
    @{
        Id       = 'code-correct'
        Category = 'Code'
        Type     = 'Python'
        Prompt   = 'In Python, write a single expression that returns the sum of squares of 1..5. Output only the expression.'
        # We will sandbox test this if Python is available.
    },
    @{
        Id       = 'retrieval-inline'
        Category = 'Context-use'
        Type     = 'Text'
        Prompt   = 'Context: "The launch code is ORBIT-7734." Question: what is the launch code? Answer with only the code.'
        Strict   = '^\s*ORBIT-7734\s*$'
        Partial  = @('ORBIT-7734')
    }
)

# --- Helpers ----------------------------------------------------------------
function Get-Percentile {
    param([double[]]$Values, [double]$P)
    if ($Values.Count -eq 0) { return 0 }
    $sorted = $Values | Sort-Object
    $idx = [Math]::Ceiling($P * $sorted.Count) - 1
    if ($idx -lt 0) { $idx = 0 }
    if ($idx -ge $sorted.Count) { $idx = $sorted.Count - 1 }
    return $sorted[$idx]
}

function Get-StdDev {
    param([double[]]$Values)
    if ($Values.Count -lt 2) { return 0 }
    $mean = ($Values | Measure-Object -Average).Average
    $sumSq = 0.0
    foreach ($v in $Values) { $sumSq += [Math]::Pow($v - $mean, 2) }
    return [Math]::Sqrt($sumSq / ($Values.Count - 1))
}

function Test-PythonCode {
    param([string]$Expression)
    $clean = $Expression -replace '```python', '' -replace '```', ''
    $clean = $clean.Trim()
    
    $tempFile = [IO.Path]::GetTempFileName() + ".py"
    $script = "result = eval('$clean')`nif result == 55:`n    exit(0)`nelse:`n    exit(1)"
    Set-Content -Path $tempFile -Value $script -Encoding UTF8
    
    $proc = Start-Process -FilePath "python" -ArgumentList $tempFile -Wait -NoNewWindow -PassThru -ErrorAction SilentlyContinue
    $passed = ($proc -and $proc.ExitCode -eq 0)
    
    if (Test-Path $tempFile) { Remove-Item $tempFile }
    return $passed
}

function Invoke-ModelCall {
    param([string]$ModelId, [string]$PromptText, [int]$TokenCap)

    $payload = @{
        model       = $ModelId
        messages    = @(@{ role = 'user'; content = $PromptText })
        max_tokens  = $TokenCap
        temperature = 0
    } | ConvertTo-Json -Depth 6 -Compress

    $headers = @{
        'Authorization' = "Bearer $key"
        'Content-Type'  = 'application/json'
        'User-Agent'    = 'Roo-Code/3.54.0 (Antigravity IDE)'
    }

    $sw = [Diagnostics.Stopwatch]::StartNew()
    $status = 0
    $body = $null
    $err = $null
    try {
        $resp = Invoke-WebRequest -Uri ("{0}/chat/completions" -f $Base) `
                                  -Method Post -Headers $headers -Body $payload `
                                  -TimeoutSec 120 -UseBasicParsing
        $status = [int]$resp.StatusCode
        $body = $resp.Content
    } catch {
        $err = $_.Exception.Message
        if ($_.Exception.Response) {
            try { $status = [int]$_.Exception.Response.StatusCode } catch { $status = -1 }
            try {
                $stream = $_.Exception.Response.GetResponseStream()
                $reader = New-Object IO.StreamReader($stream)
                $body = $reader.ReadToEnd()
                $reader.Close()
            } catch { }
        }
    }
    $sw.Stop()

    $content = $null
    $toolCalls = 0
    $resolvedId = $null
    $fingerprint = $null
    if ($body) {
        try {
            $raw = $body
            $clean = $raw -replace '\{"":', '{"_blank":'
            $obj = $clean | ConvertFrom-Json
            if ($obj.choices -and $obj.choices.Count -gt 0) {
                $msg = $obj.choices[0].message
                if ($msg) {
                    if ($msg.content) { $content = [string]$msg.content }
                    if ($msg.tool_calls) { $toolCalls = @($msg.tool_calls).Count }
                }
            }
            if ($obj.model) { $resolvedId = [string]$obj.model }
            if ($obj.system_fingerprint) { $fingerprint = [string]$obj.system_fingerprint }
        } catch { }
    }

    return [pscustomobject]@{
        Status          = $status
        ElapsedMs       = [int]$sw.ElapsedMilliseconds
        Content         = $content
        ToolCalls       = $toolCalls
        ResolvedModelId = $resolvedId
        SystemFingerprint = $fingerprint
        Body            = $body
        Error           = $err
    }
}

# --- Run --------------------------------------------------------------------
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$evDir = Join-Path $EvidenceRoot ("agentrouter-capabilities-$stamp")
New-Item -ItemType Directory -Force -Path $evDir | Out-Null

Write-Host ''
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host ' AgentRouter Capability Harness (Fixed v1.1)' -ForegroundColor Cyan
Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host (" Model  : {0}" -f $Model)
Write-Host (" Runs   : {0} per task" -f $Runs)
Write-Host (" Tasks  : {0}" -f $tasks.Count)
Write-Host (" Calls  : {0}" -f ($tasks.Count * $Runs))
Write-Host ''

$transcript = New-Object System.Text.StringBuilder
[void]$transcript.AppendLine("model=$Model runs=$Runs base=$Base max_tokens=$MaxTokens")
[void]$transcript.AppendLine("started_utc=$(Get-Date -Format o)")
[void]$transcript.AppendLine('')

$perTask = @()
$allRunScores = New-Object System.Collections.Generic.List[double]
$allLatencies = New-Object System.Collections.Generic.List[double]
$passAt1Count = 0
$validCalls = 0
$totalCalls = 0
$authBlocked = $false

foreach ($t in $tasks) {
    $taskLatencies = New-Object System.Collections.Generic.List[double]
    $taskScores = New-Object System.Collections.Generic.List[double]
    $passedFirstTry = $false

    Write-Host ("[{0}] {1}" -f $t.Category, $t.Id) -ForegroundColor White

    for ($r = 1; $r -le $Runs; $r++) {
        $totalCalls++
        $res = Invoke-ModelCall -ModelId $Model -PromptText $t.Prompt -TokenCap $MaxTokens

        [void]$transcript.AppendLine("--- task=$($t.Id) run=$r status=$($res.Status) ms=$($res.ElapsedMs) ---")
        [void]$transcript.AppendLine("resolved_model=$($res.ResolvedModelId) fingerprint=$($res.SystemFingerprint) tool_calls=$($res.ToolCalls)")
        [void]$transcript.AppendLine("content<<<")
        [void]$transcript.AppendLine($res.Content)
        [void]$transcript.AppendLine(">>>")
        if ($res.Status -ne 200) {
            [void]$transcript.AppendLine("error=$($res.Error)")
            [void]$transcript.AppendLine("body=$($res.Body)")
        }
        [void]$transcript.AppendLine('')

        if ($res.Status -eq 401) { $authBlocked = $true }

        if ($res.Status -eq 200 -and $res.Content) {
            $validCalls++
            $score = 0.0
            $text = $res.Content.Trim()

            if ($t.Type -eq 'Text') {
                if ($text -match $t.Strict) {
                    $score = 1.0
                } else {
                    $hit = $true
                    foreach ($token in $t.Partial) {
                        if ($text -notmatch [regex]::Escape($token)) { $hit = $false; break }
                    }
                    if ($hit) { $score = 0.5 }
                }
            } elseif ($t.Type -eq 'Python') {
                if (Get-Command python -ErrorAction SilentlyContinue) {
                    if (Test-PythonCode -Expression $text) {
                        $score = 1.0
                    }
                } else {
                    Write-Host "Python not found. Skipping code execution." -ForegroundColor Yellow
                    $score = 0.0
                }
            }

            if ($r -eq 1 -and $score -eq 1.0) {
                $passedFirstTry = $true
                $passAt1Count++
            }

            $allRunScores.Add($score)
            $taskScores.Add($score)
            $allLatencies.Add($res.ElapsedMs)
            $taskLatencies.Add($res.ElapsedMs)

            $mark = if ($score -eq 1.0) { 'STRICT PASS' } elseif ($score -eq 0.5) { 'LOCAL RUBRIC' } else { 'FAIL       ' }
            $color = if ($score -eq 1.0) { 'Green' } elseif ($score -eq 0.5) { 'Yellow' } else { 'Red' }
            Write-Host ("   run {0}/{1}  {2}  {3} ms" -f $r, $Runs, $mark, $res.ElapsedMs) -ForegroundColor $color
        } else {
            $color = if ($res.Status -match "^(401|402|429|500)$") { 'Magenta' } else { 'Red' }
            Write-Host ("   run {0}/{1}  INVALID CALL [{2}]" -f $r, $Runs, $res.Status) -ForegroundColor $color
        }
        Start-Sleep -Milliseconds 300
    }

    $meanScoreTask = if ($taskScores.Count -gt 0) { ($taskScores | Measure-Object -Average).Average } else { 0 }
    $perTask += [pscustomobject]@{
        Id              = $t.Id
        Category        = $t.Category
        PassAt1         = $passedFirstTry
        MeanScore       = [Math]::Round($meanScoreTask, 3)
        ValidRuns       = $taskScores.Count
        MedMs           = [int](Get-Percentile -Values ([double[]]$taskLatencies) -P 0.5)
    }
    Write-Host ''
}

# --- Aggregate --------------------------------------------------------------
$scoresArr = [double[]]$allRunScores.ToArray()
$latArr = [double[]]$allLatencies.ToArray()

$meanScore = if ($scoresArr.Count -gt 0) { ($scoresArr | Measure-Object -Average).Average } else { 0 }
$sd = if ($scoresArr.Count -gt 1) { Get-StdDev -Values $scoresArr } else { 0 }
$se = if ($scoresArr.Count -gt 0) { $sd / [Math]::Sqrt($scoresArr.Count) } else { 0 }

Write-Host '=========================================================' -ForegroundColor Cyan
Write-Host ' RESULT' -ForegroundColor Cyan
Write-Host '=========================================================' -ForegroundColor Cyan

if ($authBlocked) {
    Write-Host ''
    Write-Host '  API ERRORS DETECTED (e.g. 401).' -ForegroundColor Magenta
    Write-Host '  Please verify the credential in the environment variable.' -ForegroundColor Yellow
}

Write-Host ''
Write-Host (" Valid calls        : {0}/{1}" -f $validCalls, $totalCalls)

if ($validCalls -gt 0) {
    # pass@1 is based on the first run of each task
    $passAt1Rate = $passAt1Count / $tasks.Count
    Write-Host (" pass@1 (strict)    : {0}/{1} = {2}" -f $passAt1Count, $tasks.Count, [Math]::Round($passAt1Rate, 3))
    
    if ($Runs -gt 1) {
        $strictOverallCount = ($scoresArr | Where-Object { $_ -eq 1.0 }).Count
        $strictOverallRate = $strictOverallCount / $validCalls
        $partialMean = ($scoresArr | Measure-Object -Average).Average
        Write-Host (" strict success rate (all runs) : {0}" -f [Math]::Round($strictOverallRate, 3))
        Write-Host (" mean partial score (all runs)  : {0} (LOCAL RUBRIC)" -f [Math]::Round($partialMean, 3))
        Write-Host (" consistency rate   : {0}" -f [Math]::Round($strictOverallRate, 3))
    }

    Write-Host (" Standard error     : {0}" -f [Math]::Round($se, 4))
    Write-Host (" Latency median     : {0} ms" -f [int](Get-Percentile -Values $latArr -P 0.5))
    Write-Host (" Latency p95        : {0} ms" -f [int](Get-Percentile -Values $latArr -P 0.95))
} else {
    Write-Host " pass@1             : N/A"
    Write-Host " mean score         : N/A"
    Write-Host " Standard error     : N/A"
    Write-Host " latency            : N/A"
}
Write-Host ''
Write-Host ' Note on Identity: Gateway metadata (resolved_model, system_fingerprint) does NOT prove upstream model identity.' -ForegroundColor DarkGray

# --- Persist ----------------------------------------------------------------
$summary = [ordered]@{
    model                = $Model
    runs_per_task        = $Runs
    total_calls          = $totalCalls
    valid_calls          = $validCalls
    authenticated        = -not $authBlocked
    pass_at_1_strict     = if ($validCalls -gt 0) { [Math]::Round($passAt1Rate, 4) } else { "N/A" }
    mean_run_score       = if ($validCalls -gt 0) { [Math]::Round($meanScore, 4) } else { "N/A" }
    standard_error       = if ($validCalls -gt 0) { [Math]::Round($se, 4) } else { "N/A" }
    latency_median_ms    = if ($validCalls -gt 0) { [int](Get-Percentile -Values $latArr -P 0.5) } else { "N/A" }
    latency_p95_ms       = if ($validCalls -gt 0) { [int](Get-Percentile -Values $latArr -P 0.95) } else { "N/A" }
    per_task             = $perTask
    captured_utc         = (Get-Date -Format o)
    caveat               = 'Tiny 5-task set. Qualitative signal only.'
}
$jsonPath = Join-Path $evDir 'capabilities-summary.json'
$summary | ConvertTo-Json -Depth 6 | Out-File -FilePath $jsonPath -Encoding UTF8
$txPath = Join-Path $evDir 'capabilities-transcript.txt'
$transcript.ToString() | Out-File -FilePath $txPath -Encoding UTF8

Write-Host ''
Write-Host (" Evidence: {0}" -f $evDir) -ForegroundColor DarkGray
Write-Host (" Summary : {0}" -f $jsonPath) -ForegroundColor DarkGray
Write-Host ''
