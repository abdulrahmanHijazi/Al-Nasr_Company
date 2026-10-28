<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_page_sections', function (Blueprint $table) {
            // App\Enums\Background3dModel value, or null for "no 3D model in this section"
            $table->string('model_3d')->nullable()->after('background_color');
            // uploaded .glb / .gltf (public disk) when model_3d = 'custom'
            $table->string('model_3d_file')->nullable()->after('model_3d');
            // which side of the section the model sits on: start | end
            $table->string('model_3d_side', 8)->default('end')->after('model_3d_file');
        });
    }

    public function down(): void
    {
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->dropColumn(['model_3d', 'model_3d_file', 'model_3d_side']);
        });
    }
};
