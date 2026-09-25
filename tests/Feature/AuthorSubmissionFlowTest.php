<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\BookSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_author()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/author/register', [
            'pen_name' => 'Pen Name',
            'id_card_number' => '1234567890123456',
            'id_card_file' => UploadedFile::fake()->create('ktp.jpg', 100, 'image/jpeg'),
            'bank_name' => 'Bank A',
            'bank_account' => '123456789',
            'bank_holder_name' => 'John Doe',
        ]);

        $response->assertRedirect(route('author.kyc-status'));
        $author = Author::where('user_id', $user->id)->first();
        
        $this->assertNotNull($author);
        $this->assertEquals('pending', $author->kyc_status);
        Storage::disk('local')->assertExists($author->id_card_path);
    }

    public function test_pending_author_cannot_access_submissions()
    {
        $user = User::factory()->create();
        Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John',
            'id_card_number' => '1234567890123456',
            'kyc_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/author/submissions');
        $response->assertRedirect(route('author.kyc-status'));
    }

    public function test_verified_author_can_upload_valid_pdf()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John',
            'id_card_number' => '1234567890123456',
            'kyc_status' => 'verified',
        ]);

        $category = \App\Models\Category::create(['name' => 'Fiction', 'slug' => 'fiction']);

        // Create a fake PDF file with correct magic bytes
        $file = UploadedFile::fake()->createWithContent('document.pdf', "%PDF-1.4\n...");

        $response = $this->actingAs($user)->post('/author/submissions', [
            'title' => 'My Book',
            'category_id' => $category->id,
            'synopsis' => str_repeat('A', 150),
            'proposed_price' => 50000,
            'manuscript_file' => $file,
            'action' => 'submit'
        ]);

        $response->assertRedirect(route('author.submissions.index'));
        $this->assertDatabaseHas('book_submissions', [
            'title' => 'My Book',
            'status' => 'submitted'
        ]);
    }

    public function test_verified_author_cannot_upload_invalid_pdf()
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John',
            'id_card_number' => '1234567890123456',
            'kyc_status' => 'verified',
        ]);

        $category = \App\Models\Category::create(['name' => 'Fiction', 'slug' => 'fiction']);

        // Create a fake PDF file with INVALID magic bytes
        $file = UploadedFile::fake()->createWithContent('document.pdf', "PK\x03\x04\n...");

        $response = $this->actingAs($user)->post('/author/submissions', [
            'title' => 'My Book',
            'category_id' => $category->id,
            'synopsis' => str_repeat('A', 150),
            'proposed_price' => 50000,
            'manuscript_file' => $file,
            'action' => 'submit'
        ]);

        $response->assertSessionHasErrors('manuscript_file');
        $this->assertDatabaseMissing('book_submissions', ['title' => 'My Book']);
    }

    public function test_author_cannot_edit_submission_in_review()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John',
            'id_card_number' => '1234567890123456',
            'kyc_status' => 'verified',
        ]);

        $submission = BookSubmission::create([
            'author_id' => $author->id,
            'title' => 'My Book',
            'synopsis' => 'Test',
            'manuscript_path' => 'test.pdf',
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($user)->get("/author/submissions/{$submission->id}/edit");
        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('error');
        
        $responseUpdate = $this->actingAs($user)->put("/author/submissions/{$submission->id}", [
            'title' => 'Updated Title',
            'category_id' => 1,
            'synopsis' => str_repeat('A', 150),
            'proposed_price' => 50000,
        ]);
        
        $responseUpdate->assertStatus(403);
    }
}
