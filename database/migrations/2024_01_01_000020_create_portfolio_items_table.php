<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['project', 'achievement', 'milestone', 'skill', 'experience', 'education']);
            $table->string('title', 255);
            $table->text('description');
            $table->date('item_date')->nullable();
            $table->json('tags')->nullable();
            $table->json('links')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
    }
};
