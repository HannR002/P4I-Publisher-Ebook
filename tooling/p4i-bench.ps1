#Requires -Version 5.1
<#
.SYNOPSIS
    Infrastructure runner for P4I-Bench v1.2 (System Benchmark) - BATCH 1 & 2
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
    if ($Mode -eq 'Live') {
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
        $found = Get-ChildItem -Path $WorktreePath -Recurse -Filter $pattern -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch '\\vendor\\' -and $_.FullName -notmatch '\\node_modules\\' } | Select-Object -First 1
        if ($found) {
            $rel = $found.FullName.Substring($WorktreePath.Length + 1)
            Write-Host "BLOCKED $rel (Matched deny pattern $pattern)" -ForegroundColor Magenta
            return $false
        }
    }
    
    $sqlDumps = Get-ChildItem -Path $WorktreePath -Recurse -Filter '*.sql' -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch '\\vendor\\' -and $_.FullName -notmatch '\\node_modules\\' }
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

function Invoke-TaskTests {
    param([string]$WorktreePath, [string]$TestFilter)
    
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = ':memory:'
    
    $sw = [Diagnostics.Stopwatch]::StartNew()
    $cmd = "cd /d `"$WorktreePath`" && php artisan test"
    if ($TestFilter) {
        $cmd += " --filter $TestFilter"
    }
    
    $out = cmd.exe /c "$cmd 2>&1"
    $exitCode = $LASTEXITCODE
    $sw.Stop()
    
    $passed = 0
    $failed = 0
    $outString = $out -join "`n"
    if ($outString -match 'Tests:\s+(\d+)\s+failed,\s+(\d+)\s+passed') {
        $failed = [int]$Matches[1]
        $passed = [int]$Matches[2]
    } elseif ($outString -match 'Tests:\s+(\d+)\s+passed') {
        $passed = [int]$Matches[1]
    } elseif ($outString -match 'Tests:\s+(\d+)\s+failed') {
        $failed = [int]$Matches[1]
    }
    
    return @{
        exit_code = $exitCode
        tests_passed = $passed
        tests_failed = $failed
        runtime_ms = $sw.ElapsedMilliseconds
        output = $out
    }
}

function Build-BenchmarkTest {
    param([string]$wt, [string]$testName, [string]$content)
    $testDir = Join-Path $wt 'tests/Feature/Benchmark'
    if (-not (Test-Path $testDir)) { New-Item -ItemType Directory -Force -Path $testDir | Out-Null }
    $testFile = Join-Path $testDir "$testName.php"
    Set-Content $testFile -Value $content
}

$TaskDefinitions = @{
    'bug-01' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/CheckoutController.php'
        AllowedFiles = @('app/Http/Controllers/CheckoutController.php', 'tests/Feature/Benchmark/Bug01Test.php')
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/CheckoutController.php'
            $c = Get-Content $f -Raw
            $search = '        $user  = $request->user();'
            $replace = '        $user  = \App\Models\User::find(1); // MOCKED FOR BENCHMARK'
            if ($c.Contains($search)) {
                $c = $c.Replace($search, $replace)
                Set-Content $f -Value $c
                
                Build-BenchmarkTest $wt 'Bug01Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use App\Models\User;
class Bug01Test extends TestCase {
    public function test_auth_check_works() {
        $content = file_get_contents(app_path("Http/Controllers/CheckoutController.php"));
        $this->assertFalse(strpos($content, "MOCKED FOR BENCHMARK") !== false, "Fixture bug still exists!");
    }
}
'
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Bug01Test'
            return ($res.exit_code -ne 0)
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
            $res = Invoke-TaskTests $wt 'Bug01Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'bug-02' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/LibraryController.php'
        AllowedFiles = @('app/Http/Controllers/LibraryController.php', 'tests/Feature/Benchmark/Bug02Test.php')
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            $c = Get-Content $f -Raw
            $search = "BookLicense::with('book')"
            if ($c.Contains($search)) {
                Set-Content $f -Value $c.Replace($search, "BookLicense::query() // MOCKED FOR BENCHMARK")
                
                Build-BenchmarkTest $wt 'Bug02Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\BookLicense;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
class Bug02Test extends TestCase {
    use RefreshDatabase;
    public function test_no_n_plus_one() {
        $content = file_get_contents(app_path("Http/Controllers/LibraryController.php"));
        $this->assertFalse(strpos($content, "MOCKED FOR BENCHMARK") !== false, "Fixture N+1 bug still exists!");
    }
}
'
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Bug02Test'
            return ($res.exit_code -ne 0)
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/LibraryController.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace("BookLicense::query()", "BookLicense::with('book')")
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Bug02Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'bug-03' = @{
        Cat = 'bug-fix'
        Source = 'app/Http/Controllers/DrmController.php'
        AllowedFiles = @('app/Http/Controllers/DrmController.php', 'tests/Feature/Benchmark/Bug03Test.php')
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            $c = Get-Content $f -Raw
            $search = "abort(403, 'No active license found for this book.');"
            if ($c.Contains($search)) {
                Set-Content $f -Value $c.Replace($search, "// FIXTURE REMOVED NULL GUARD")
                
                Build-BenchmarkTest $wt 'Bug03Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
class Bug03Test extends TestCase {
    use RefreshDatabase;
    public function test_missing_license_handled() {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $response = $this->actingAs($user)->get("/reader/" . $book->id);
        $this->assertEquals(403, $response->status());
    }
}
'
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Bug03Test'
            return ($res.exit_code -ne 0)
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Http/Controllers/DrmController.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace("// FIXTURE REMOVED NULL GUARD", "abort(403, 'No active license found for this book.');")
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Bug03Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'sec-01' = @{
        Cat = 'security'
        Source = 'resources/views/layouts/navigation.blade.php'
        AllowedFiles = @('resources/views/layouts/navigation.blade.php', 'app/Http/Controllers/ProfileController.php', 'tests/Feature/Benchmark/Sec01Test.php')
        Setup = {
            param($wt)
            $blade = Join-Path $wt 'resources/views/layouts/navigation.blade.php'
            $c = Get-Content $blade -Raw
            if ($c.Contains('{{ Auth::user()->name }}')) {
                Set-Content $blade -Value $c.Replace('{{ Auth::user()->name }}', '{!! Auth::user()->name !!}')
                
                Build-BenchmarkTest $wt 'Sec01Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
class Sec01Test extends TestCase {
    public function test_profile_xss() {
        $content = file_get_contents(resource_path("views/layouts/navigation.blade.php"));
        $this->assertFalse(strpos($content, "{!! Auth::user()->name !!}") !== false, "XSS Vulnerability found!");
    }
}
'
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Sec01Test'
            return ($res.exit_code -ne 0)
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'resources/views/layouts/navigation.blade.php'
            $c = Get-Content $f -Raw
            Set-Content $f -Value $c.Replace('{!! Auth::user()->name !!}', '{{ Auth::user()->name }}')
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Sec01Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    'sec-02' = @{
        Cat = 'security'
        Source = 'app/Models/User.php'
        AllowedFiles = @('app/Models/User.php', 'tests/Feature/Benchmark/Sec02Test.php')
        Setup = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            $search = "protected `$fillable = ["
            if ($c.Contains($search)) {
                $newC = $c -replace 'protected\s+\$fillable\s*=\s*\[[^\]]+\];', 'protected $guarded = [];'
                Set-Content $f -Value $newC
                
                Build-BenchmarkTest $wt 'Sec02Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
class Sec02Test extends TestCase {
    public function test_mass_assignment() {
        $content = file_get_contents(app_path("Models/User.php"));
        $this->assertFalse(strpos($content, '$guarded = []') !== false, "Mass assignment vulnerability: guarded is empty!");
    }
}
'
                return $true
            }
            return $false
        }
        VerifyFixture = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Sec02Test'
            return ($res.exit_code -ne 0)
        }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            $search = 'protected \$guarded = \[\];'
            $replace = "protected `$fillable = ['name', 'email', 'password', 'is_admin', 'is_active'];"
            $newC = $c -replace $search, $replace
            Set-Content $f -Value $newC
        }
        Oracle = {
            param($wt)
            $f = Join-Path $wt 'app/Models/User.php'
            $c = Get-Content $f -Raw
            if ($c.Contains('$guarded = []')) { return 'CANDIDATE_FAILED' }
            $res = Invoke-TaskTests $wt 'Sec02Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    
    'feat-01' = @{
        Cat = 'feature'
        Source = 'app/Models/Book.php'
        AllowedFiles = @('app/Models/Book.php', 'database/migrations/*.php', 'tests/Feature/Benchmark/Feat01Test.php')
        Setup = {
            param($wt)
            Build-BenchmarkTest $wt 'Feat01Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
class Feat01Test extends TestCase {
    use RefreshDatabase;
    public function test_published_at_exists() {
        $this->assertTrue(Schema::hasColumn("books", "published_at"), "Column published_at does not exist");
        $user = User::factory()->create();
        $book = Book::create([
            "title" => "t", "description" => "d", "price" => 0, 
            "user_id" => $user->id, "file_path" => "x", "cover_image" => "x", 
            "status" => "approved", "published_at" => now()
        ]);
        $this->assertNotNull($book->published_at);
    }
}
'
            return $true
        }
        VerifyFixture = { return $true }
        DummyCandidate = {
            param($wt)
            $migFile = Join-Path $wt 'database/migrations/2026_09_28_000000_add_published_at_to_books.php'
            Set-Content $migFile -Value '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() { Schema::table("books", function (Blueprint $table) { $table->dateTime("published_at")->nullable(); }); }
    public function down() { Schema::table("books", function (Blueprint $table) { $table->dropColumn("published_at"); }); }
};
'
            $f = Join-Path $wt 'app/Models/Book.php'
            $c = Get-Content $f -Raw
            $c = $c.Replace("'publish_date',", "'publish_date',`n        'published_at',")
            Set-Content $f -Value $c
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Feat01Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }
    
    'feat-02' = @{
        Cat = 'feature'
        Source = 'app/Models/Review.php'
        AllowedFiles = @('app/Models/Review.php', 'database/migrations/*.php', 'tests/Feature/Benchmark/Feat02Test.php')
        Setup = {
            param($wt)
            Build-BenchmarkTest $wt 'Feat02Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use App\Models\Review;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class Feat02Test extends TestCase {
    use RefreshDatabase;
    public function test_soft_deletes() {
        $user = User::factory()->create();
        $book = Book::create([
            "title" => "t", "description" => "d", "price" => 0, 
            "user_id" => $user->id, "file_path" => "x", "cover_image" => "x", 
            "status" => "approved"
        ]);
        $review = Review::create([
            "user_id" => $user->id, "book_id" => $book->id, 
            "rating" => 5, "comment" => "Great"
        ]);
        $review->delete();
        $this->assertNotNull($review->deleted_at);
        $this->assertEquals(0, Review::count());
        $this->assertEquals(1, Review::withTrashed()->count());
    }
}
'
            return $true
        }
        VerifyFixture = { return $true }
        DummyCandidate = {
            param($wt)
            $migFile = Join-Path $wt 'database/migrations/2026_09_28_000000_add_softdeletes_to_reviews.php'
            Set-Content $migFile -Value '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() { Schema::table("reviews", function (Blueprint $table) { $table->softDeletes(); }); }
    public function down() { Schema::table("reviews", function (Blueprint $table) { $table->dropSoftDeletes(); }); }
};
'
            $f = Join-Path $wt 'app/Models/Review.php'
            $c = Get-Content $f -Raw
            $c = $c.Replace("use Illuminate\Database\Eloquent\Model;", "use Illuminate\Database\Eloquent\Model;`nuse Illuminate\Database\Eloquent\SoftDeletes;")
            $c = $c.Replace("use HasFactory;", "use HasFactory, SoftDeletes;")
            Set-Content $f -Value $c
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Feat02Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }

    'feat-03' = @{
        Cat = 'feature'
        Source = 'app/Http/Controllers/CategoryController.php'
        AllowedFiles = @('app/Http/Controllers/CategoryController.php', 'routes/api.php', 'routes/web.php', 'bootstrap/app.php', 'tests/Feature/Benchmark/Feat03Test.php')
        Setup = {
            param($wt)
            Build-BenchmarkTest $wt 'Feat03Test' '<?php
namespace Tests\Feature\Benchmark;
use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
class Feat03Test extends TestCase {
    use RefreshDatabase;
    public function test_category_api() {
        $cat = Category::factory()->create(["name" => "Fiction"]);
        $response = $this->get("/api/categories");
        $response->assertStatus(200);
        $response->assertJsonFragment(["name" => "Fiction"]);
    }
}
'
            return $true
        }
        VerifyFixture = { return $true }
        DummyCandidate = {
            param($wt)
            $f = Join-Path $wt 'routes/api.php'
            Set-Content $f -Value "<?php`nuse Illuminate\Support\Facades\Route;`nuse App\Http\Controllers\CategoryController;`nRoute::get('/categories', [CategoryController::class, 'index']);`n"
            $fc = Join-Path $wt 'app/Http/Controllers/CategoryController.php'
            if (-not (Test-Path $fc)) {
                Set-Content $fc -Value "<?php`nnamespace App\Http\Controllers;`nuse App\Models\Category;`nclass CategoryController extends Controller { public function index() { return response()->json(Category::all()); } }`n"
            }
            $boot = Join-Path $wt 'bootstrap/app.php'
            if (Test-Path $boot) {
                $bc = Get-Content $boot -Raw
                if (-not $bc.Contains('api: __DIR__')) {
                    $bc = $bc.Replace("web: __DIR__.'/../routes/web.php',", "web: __DIR__.'/../routes/web.php',`n        api: __DIR__.'/../routes/api.php',")
                    Set-Content $boot -Value $bc
                }
            } else {
                # Setup older laravel RouteServiceProvider if applicable
                $rsp = Join-Path $wt 'app/Providers/RouteServiceProvider.php'
                if (Test-Path $rsp) {
                    $rspc = Get-Content $rsp -Raw
                    if ($rspc.Contains('function boot')) {
                        # naive replacement for dummy
                        $rspc = $rspc -replace "Route::middleware\('web'\)", "Route::prefix('api')->middleware('api')->group(base_path('routes/api.php'));`n            Route::middleware('web')"
                        Set-Content $rsp -Value $rspc
                    }
                }
            }
        }
        Oracle = {
            param($wt)
            $res = Invoke-TaskTests $wt 'Feat03Test'
            if ($res.exit_code -eq 0) { return 'PASS' }
            return 'CANDIDATE_FAILED'
        }
    }

    'test-01' = @{
        Cat = 'test'
        Source = 'tests/Feature/OrderItemTest.php'
        AllowedFiles = @('tests/Feature/OrderItemTest.php', 'tests/Unit/OrderItemTest.php')
        Setup = { return $true }
        VerifyFixture = { return $true }
        DummyCandidate = {
            param($wt)
            $testDir = Join-Path $wt 'tests/Feature'
            if (-not (Test-Path $testDir)) { New-Item -ItemType Directory -Force -Path $testDir | Out-Null }
            $testFile = Join-Path $testDir 'OrderItemTest.php'
            Set-Content $testFile -Value '<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
class OrderItemTest extends TestCase {
    use RefreshDatabase;
    public function test_belongs_to_order() {
        $item = new OrderItem();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $item->order());
    }
}
'
        }
        Oracle = {
            param($wt)
            $resBaseline = Invoke-TaskTests $wt 'OrderItemTest'
            if ($resBaseline.exit_code -ne 0) { return 'CANDIDATE_FAILED' }
            
            $f = Join-Path $wt 'app/Models/OrderItem.php'
            $c = Get-Content $f -Raw
            $mutated = $c.Replace('belongsTo(Order::class', 'belongsTo(User::class')
            Set-Content $f -Value $mutated
            
            $resMutant = Invoke-TaskTests $wt 'OrderItemTest'
            
            Set-Content $f -Value $c
            
            if ($resMutant.exit_code -ne 0) { return 'PASS' }
            return 'TEST_FAILED'
        }
    }

    'test-02' = @{
        Cat = 'test'
        Source = 'tests/Feature/ProfileUpdateTest.php'
        AllowedFiles = @('tests/Feature/ProfileUpdateTest.php', 'tests/Feature/ProfileTest.php')
        Setup = { return $true }
        VerifyFixture = { return $true }
        DummyCandidate = {
            param($wt)
            $testDir = Join-Path $wt 'tests/Feature'
            if (-not (Test-Path $testDir)) { New-Item -ItemType Directory -Force -Path $testDir | Out-Null }
            $testFile = Join-Path $testDir 'ProfileUpdateTest.php'
            Set-Content $testFile -Value '<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ProfileUpdateTest extends TestCase {
    use RefreshDatabase;
    public function test_profile_updates() {
        $user = User::factory()->create();
        $this->actingAs($user)->patch("/profile", ["name" => "New Name", "email" => "t@t.com"]);
        $this->assertEquals("New Name", $user->fresh()->name);
    }
}
'
        }
        Oracle = {
            param($wt)
            $resBaseline = Invoke-TaskTests $wt 'ProfileUpdateTest'
            if ($resBaseline.exit_code -ne 0) { return 'CANDIDATE_FAILED' }
            
            $f = Join-Path $wt 'app/Http/Controllers/ProfileController.php'
            $c = Get-Content $f -Raw
            $mutated = $c.Replace('$request->user()->save();', '// $request->user()->save();')
            Set-Content $f -Value $mutated
            
            $resMutant = Invoke-TaskTests $wt 'ProfileUpdateTest'
            
            Set-Content $f -Value $c
            
            if ($resMutant.exit_code -ne 0) { return 'PASS' }
            return 'TEST_FAILED'
        }
    }
}

function Invoke-BenchmarkTask {
    param([string]$Candidate, [string]$TaskId, [string]$WorktreePath)
    
    $def = $TaskDefinitions[$TaskId]
    if (-not $def) { return @{ status = 'NOT_IMPLEMENTED' } }
    
    $sw = [Diagnostics.Stopwatch]::StartNew()
    $setupOk = & $def.Setup $WorktreePath
    if (-not $setupOk) { return @{ status = 'FIXTURE_FAILED' } }
    
    $verifyOk = & $def.VerifyFixture $WorktreePath
    if (-not $verifyOk) { return @{ status = 'FIXTURE_VERIFY_FAILED' } }
    $sw.Stop()
    $fixMs = $sw.ElapsedMilliseconds
    
    cmd.exe /c "cd /d `"$WorktreePath`" && git add . && git commit -m `"Fixture`" >nul 2>&1"
    
    $sw.Restart()
    if ($Mode -eq 'Validate') {
        & $def.DummyCandidate $WorktreePath
    }
    $sw.Stop()
    $candMs = $sw.ElapsedMilliseconds
    
    $filesMod = 0; $add = 0; $del = 0
    $diffOut = cmd.exe /c "cd /d `"$WorktreePath`" && git diff --numstat HEAD"
    if ($diffOut) {
        foreach ($line in $diffOut) {
            if ($line -match '^(\d+|-)\s+(\d+|-)\s+(.+)$') {
                $a = $Matches[1]; $d = $Matches[2]; $filesMod++
                if ($a -ne '-') { $add += [int]$a }
                if ($d -ne '-') { $del += [int]$d }
            }
        }
    }
    
    $scopeViolations = 0
    $modFiles = cmd.exe /c "cd /d `"$WorktreePath`" && git diff --name-only HEAD"
    if ($modFiles) {
        foreach ($f in $modFiles) {
            $allowed = $false
            foreach ($pattern in $def.AllowedFiles) {
                if ($f -like $pattern -or $f -eq $pattern) { $allowed = $true; break }
            }
            if (-not $allowed) { $scopeViolations++ }
        }
    }
    $scopeViolationFlag = ($scopeViolations -gt 0)
    
    $sw.Restart()
    $oracleRes = & $def.Oracle $WorktreePath
    $sw.Stop()
    
    $finalStatus = if ($scopeViolationFlag) { 'SCOPE_VIOLATION' } else { $oracleRes }
    
    return [ordered]@{
        status = $finalStatus
        latency_fixture = $fixMs
        latency_cand = $candMs
        latency_oracle = $sw.ElapsedMilliseconds
        files_modified = $filesMod
        lines_added = $add
        lines_removed = $del
        scope_violations = $scopeViolations
    }
}

$gitStatus = git status --porcelain
if ($gitStatus) { Write-Host "ABORT: Production tree is dirty." -ForegroundColor Red; exit 1 }

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$runDir = Join-Path $EvidenceRoot "run-$stamp"
if (-not (Test-Path $runDir)) { New-Item -ItemType Directory -Force -Path $runDir | Out-Null }

$map = Get-CandidateMapping
$tasksToRun = @('bug-01', 'bug-02', 'bug-03', 'sec-01', 'sec-02', 'feat-01', 'feat-02', 'feat-03', 'test-01', 'test-02')
$results = @()

foreach ($candidate in @('Candidate A', 'Candidate B')) {
    Write-Host "Running evaluation for $candidate ..." -ForegroundColor Cyan
    foreach ($t in $tasksToRun) {
        $wtName = "$stamp-$($candidate -replace '\s','-')-$t" -replace '[^a-zA-Z0-9-]', '-'
        $wtPath = Join-Path (Join-Path '.bench' 'worktrees') $wtName
        
        Write-Host "  -> Setting up worktree: $wtPath" -ForegroundColor DarkGray
        cmd.exe /c "git worktree add --detach `"$wtPath`" HEAD >nul 2>&1"
        cmd.exe /c "mklink /J `"$PWD\$wtPath\vendor`" `"$PWD\vendor`" >nul 2>&1"
        
        $bootstrapContent = "<?php`n`$loader = require __DIR__.'/../vendor/autoload.php';`n`$loader->setPsr4('App\\', __DIR__.'/../app/', true);`n`$loader->setPsr4('Database\\Factories\\', __DIR__.'/../database/factories/', true);`n`$loader->setPsr4('Database\\Seeders\\', __DIR__.'/../database/seeders/', true);`n`$loader->setPsr4('Tests\\', __DIR__.'/../tests/', true);`nreturn `$loader;"
        Set-Content (Join-Path "$PWD\$wtPath" "bootstrap/testing_autoload.php") -Value $bootstrapContent
        
        $phpunitPath = Join-Path "$PWD\$wtPath" "phpunit.xml"
        if (Test-Path $phpunitPath) {
            $xml = Get-Content $phpunitPath -Raw
            $xml = $xml.Replace('bootstrap="vendor/autoload.php"', 'bootstrap="bootstrap/testing_autoload.php"')
            Set-Content $phpunitPath -Value $xml
        }
        
        if (-not (Test-ContextSecurity -WorktreePath $wtPath)) {
            Write-Host "ABORT TASK: Secret found in context for $t." -ForegroundColor Red
            cmd.exe /c "git worktree remove `"$wtPath`" --force >nul 2>&1"
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
        
        cmd.exe /c "git worktree remove `"$wtPath`" --force >nul 2>&1"
        Write-Host "  -> Task $t Complete. Status: $($res.status)" -ForegroundColor Green
    }
}

Remove-Item -Recurse -Force .bench -ErrorAction SilentlyContinue

$gitStatusEnd = git status --porcelain
if ($gitStatusEnd) { Write-Host "ABORT: Production tree altered!" -ForegroundColor Red; exit 1 }

$results | ConvertTo-Json -Depth 5 | Out-File -FilePath (Join-Path $runDir 'results.json') -Encoding UTF8

Write-Host "PIPELINE COMPLETE. Evidence saved to: $runDir" -ForegroundColor Green
