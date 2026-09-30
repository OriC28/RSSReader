<?php

use App\Enums\StatusArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('newsletter_id')->nullable()->constrained();
            $table->string('title', 500);
            $table->string('url', 2048)->unique();
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default(StatusArticle::PENDING);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
