<?php

use App\Enums\LinkStatus;
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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('url');
            $table->string('title')->nullable();
            $table->text('descripion')->nullable();
            $table->string('site_name')->nullable();
            $table->string('image')->nullable();
            $table->string('favicon')->nullable();

            $table->string('status')->default(LinkStatus::SAVED->value);
            $table->boolean('is_favourite')->default(false);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_favourite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
