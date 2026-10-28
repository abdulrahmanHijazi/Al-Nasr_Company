<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_d_models', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');                         // {en, ar}
            $table->string('brand')->nullable();          // Voltronic, Felicity, Solis, Tobac …
            $table->string('type')->index();              // App\Enums\ThreeDModelType
            $table->string('file');                       // .glb on the public disk
            $table->string('preview')->nullable();        // picture shown in the pickers
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_d_models');
    }
};
