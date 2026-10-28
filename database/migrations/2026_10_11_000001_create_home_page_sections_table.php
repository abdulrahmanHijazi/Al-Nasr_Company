<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('design')->nullable();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->string('show_type')->default('latest');
            $table->unsignedSmallInteger('limit')->default(8);
            $table->string('background_color', 32)->nullable();
            $table->json('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Hand-picked items: one polymorphic pivot shared by products, categories, brands, bundles, portfolio.
        Schema::create('home_page_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_page_section_id')->constrained()->cascadeOnDelete();
            $table->morphs('itemable');
            $table->integer('sort_order')->default(0);
            $table->unique(['home_page_section_id', 'itemable_type', 'itemable_id'], 'section_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_page_section_items');
        Schema::dropIfExists('home_page_sections');
    }
};
