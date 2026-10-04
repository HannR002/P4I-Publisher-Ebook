<?php

namespace Tests\Feature;

use App\Models\LibraryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_issue_article_hierarchy_is_valid()
    {
        $journal = LibraryItem::create([
            'type' => 'journal',
            'title' => 'Test Journal',
            'access_policy' => 'public_read_download',
        ]);

        $issue = LibraryItem::create([
            'type' => 'journal_issue',
            'title' => 'Vol 1 No 1',
            'parent_id' => $journal->id,
            'access_policy' => 'public_read_download',
        ]);

        $article = LibraryItem::create([
            'type' => 'journal_article',
            'title' => 'Article 1',
            'parent_id' => $issue->id,
            'access_policy' => 'public_read_download',
        ]);

        $articleDirect = LibraryItem::create([
            'type' => 'journal_article',
            'title' => 'Article 2 (Direct to Journal)',
            'parent_id' => $journal->id,
            'access_policy' => 'public_read_download',
        ]);

        $this->assertEquals($journal->id, $issue->parent_id);
        $this->assertEquals($issue->id, $article->parent_id);
        $this->assertEquals($journal->id, $articleDirect->parent_id);
    }

    public function test_self_parent_is_prevented()
    {
        $journal = LibraryItem::create([
            'type' => 'journal',
            'title' => 'Test Journal',
            'access_policy' => 'public_read_download',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A library item cannot be its own parent.');

        $journal->parent_id = $journal->id;
        $journal->save();
    }

    public function test_circular_parent_is_prevented()
    {
        $journal = LibraryItem::create([
            'type' => 'journal',
            'title' => 'Test Journal',
            'access_policy' => 'public_read_download',
        ]);

        $issue = LibraryItem::create([
            'type' => 'journal_issue',
            'title' => 'Vol 1 No 1',
            'parent_id' => $journal->id,
            'access_policy' => 'public_read_download',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Circular parent relationship detected.');

        $journal->parent_id = $issue->id;
        $journal->save();
    }

    public function test_invalid_hierarchy_is_prevented()
    {
        $article = LibraryItem::create([
            'type' => 'journal_article',
            'title' => 'Article 1',
            'access_policy' => 'public_read_download',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A journal cannot have a parent.');

        LibraryItem::create([
            'type' => 'journal',
            'title' => 'Test Journal 2',
            'parent_id' => $article->id,
            'access_policy' => 'public_read_download',
        ]);
    }

    public function test_journal_issue_cannot_belong_to_book()
    {
        $book = LibraryItem::create([
            'type' => 'book',
            'title' => 'A Book',
            'access_policy' => 'public_read_download',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A journal_issue can only belong to a journal.');

        LibraryItem::create([
            'type' => 'journal_issue',
            'title' => 'Vol 1 No 1',
            'parent_id' => $book->id,
            'access_policy' => 'public_read_download',
        ]);
    }
}
