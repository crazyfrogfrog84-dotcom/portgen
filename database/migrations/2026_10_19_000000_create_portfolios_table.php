<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 10)->unique();
            $table->string('full_name', 120);
            $table->string('headline', 180);
            $table->text('about_me');
            $table->jsonb('education');
            $table->jsonb('work_experience');
            $table->jsonb('skills');
            $table->jsonb('projects');
            $table->jsonb('social_links');
            $table->string('template_choice')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE portfolios ADD CONSTRAINT portfolios_template_choice_check CHECK (template_choice IS NULL OR template_choice IN ('Simple', 'Modern', 'Creative'))");

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER portfolios_template_choice_insert_check BEFORE INSERT ON portfolios FOR EACH ROW WHEN NEW.template_choice IS NOT NULL AND NEW.template_choice NOT IN ('Simple', 'Modern', 'Creative') BEGIN SELECT RAISE(ABORT, 'Invalid template_choice'); END");
            DB::unprepared("CREATE TRIGGER portfolios_template_choice_update_check BEFORE UPDATE OF template_choice ON portfolios FOR EACH ROW WHEN NEW.template_choice IS NOT NULL AND NEW.template_choice NOT IN ('Simple', 'Modern', 'Creative') BEGIN SELECT RAISE(ABORT, 'Invalid template_choice'); END");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS portfolios_template_choice_insert_check');
            DB::unprepared('DROP TRIGGER IF EXISTS portfolios_template_choice_update_check');
        }

        Schema::dropIfExists('portfolios');
    }
};
