<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50)->index();
            $table->foreignId('library_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('anonymous_id', 64)->nullable()->index();
            $table->string('search_term')->nullable()->index();
            $table->unsignedInteger('result_count')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['event_type', 'occurred_at']);
            $table->index(['library_item_id', 'event_type', 'occurred_at'], 'analytics_item_event_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
