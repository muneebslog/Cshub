<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->date('lesson_date')->nullable()->after('original_filename');
        });

        DB::table('slides')->orderBy('id')->get()->each(function (object $slide): void {
            DB::table('slides')->where('id', $slide->id)->update([
                'lesson_date' => $slide->created_at
                    ? substr((string) $slide->created_at, 0, 10)
                    : now()->toDateString(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropColumn('lesson_date');
        });
    }
};
