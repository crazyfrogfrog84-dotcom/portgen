<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('location', 120)->nullable();
            $table->string('availability', 80)->nullable();
            $table->string('contact_email', 190)->nullable();
            $table->string('motto', 60)->nullable();
            $table->jsonb('interests')->nullable();
        });

        Schema::create('portfolio_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('portfolio_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16);
            $table->unsignedSmallInteger('project_index')->nullable();
            $table->string('mime', 50);
            $table->binary('bytes');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('alt', 200);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX portfolio_images_one_profile ON portfolio_images (portfolio_id) WHERE kind = 'profile'");
            DB::statement("CREATE UNIQUE INDEX portfolio_images_one_project_cover ON portfolio_images (portfolio_id, project_index) WHERE kind = 'project'");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE UNIQUE INDEX portfolio_images_one_profile ON portfolio_images (portfolio_id) WHERE kind = 'profile'");
            DB::statement("CREATE UNIQUE INDEX portfolio_images_one_project_cover ON portfolio_images (portfolio_id, project_index) WHERE kind = 'project'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_images');

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn(['location', 'availability', 'contact_email', 'motto', 'interests']);
        });
    }
};
