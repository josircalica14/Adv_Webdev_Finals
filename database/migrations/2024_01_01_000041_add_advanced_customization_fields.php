<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customization_settings', function (Blueprint $table) {
            $table->string('font_size', 20)->default('medium')->after('body_font');
            $table->string('spacing', 20)->default('normal')->after('font_size');
            $table->json('visible_sections')->nullable()->after('spacing');
            $table->json('section_order')->nullable()->after('visible_sections');
            $table->boolean('show_email')->default(true)->after('section_order');
            $table->boolean('show_username')->default(true)->after('show_email');
            $table->boolean('show_bio')->default(true)->after('show_username');
            $table->string('header_style', 20)->default('dark')->after('show_bio');
        });
    }

    public function down(): void
    {
        Schema::table('customization_settings', function (Blueprint $table) {
            $table->dropColumn(['font_size','spacing','visible_sections','section_order','show_email','show_username','show_bio','header_style']);
        });
    }
};
