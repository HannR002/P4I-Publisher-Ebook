<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;
use App\Models\BookSubmission;

class AuthorRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_surfaces_render()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'Penulis',
            'id_card_number' => '',
            'kyc_status' => 'verified'
        ]);
        
        $category = Category::create(['name' => 'Fiksi', 'slug' => 'fiksi']);
        
        $submission = BookSubmission::create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Test',
            'synopsis' => str_repeat('A', 100),
            'proposed_price' => 50000,
            'status' => 'revision_requested',
            'manuscript_path' => 'test.pdf'
        ]);

        $this->actingAs($user);

        // Dashboard
        $this->get(route('author.dashboard'))->assertOk();

        // Submissions Index
        $this->get(route('author.submissions.index'))->assertOk();

        // Submission Create
        $this->get(route('author.submissions.create'))->assertOk();

        // Submission Detail
        $this->get(route('author.submissions.show', $submission->id))->assertOk();

        // Submission Edit (Revision)
        $this->get(route('author.submissions.edit', $submission->id))->assertOk();

        // Books (Buku Saya)
        $this->get(route('books.index'))->assertOk();

        // Profile
        $this->get(route('profile.edit'))->assertOk();
    }
}
