<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->json('name'); $table->json('slug')->nullable(); $table->json('description')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->json('name'); $table->json('slug')->nullable(); $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name'); $table->json('slug')->nullable();
            $table->json('short_description')->nullable(); $table->json('description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2); $table->decimal('compare_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->integer('stock_qty')->default(0);
            $table->boolean('is_active')->default(true); $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->json('title'); $table->json('slug')->nullable(); $table->json('description')->nullable();
            $table->json('client_name')->nullable();
            $table->string('project_url')->nullable();
            $table->date('completed_at')->nullable();
            $table->boolean('is_active')->default(true); $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->json('name'); $table->json('slug')->nullable(); $table->json('description')->nullable();
            $table->decimal('bundle_price', 12, 2);
            $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('bundle_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->decimal('price_override', 12, 2)->nullable();
        });
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); $table->string('email')->nullable(); $table->string('phone')->nullable();
            $table->string('subject')->nullable(); $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('bundle_product');
        Schema::dropIfExists('bundles');
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
