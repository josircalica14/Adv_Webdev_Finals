<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme', 50)->default('default');
            $table->string('layout', 50)->default('grid');
            $table->string('primary_color', 7)->default('#3498db');
            $table->string('accent_color', 7)->default('#e74c3c');
            $table->string('heading_font', 100)->default('Roboto');
            $table->string('body_font', 100)->default('Open Sans');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_settings');
    }
};
