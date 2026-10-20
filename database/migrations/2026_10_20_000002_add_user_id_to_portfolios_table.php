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
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });

        $this->restoreSqliteTemplateChoiceGuards();
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        $this->restoreSqliteTemplateChoiceGuards();
    }

    private function restoreSqliteTemplateChoiceGuards(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::unprepared("CREATE TRIGGER IF NOT EXISTS portfolios_template_choice_insert_check BEFORE INSERT ON portfolios FOR EACH ROW WHEN NEW.template_choice IS NOT NULL AND NEW.template_choice NOT IN ('Simple', 'Modern', 'Creative') BEGIN SELECT RAISE(ABORT, 'Invalid template_choice'); END");
        DB::unprepared("CREATE TRIGGER IF NOT EXISTS portfolios_template_choice_update_check BEFORE UPDATE OF template_choice ON portfolios FOR EACH ROW WHEN NEW.template_choice IS NOT NULL AND NEW.template_choice NOT IN ('Simple', 'Modern', 'Creative') BEGIN SELECT RAISE(ABORT, 'Invalid template_choice'); END");
    }
};
