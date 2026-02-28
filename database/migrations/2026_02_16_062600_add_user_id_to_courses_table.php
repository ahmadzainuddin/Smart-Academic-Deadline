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
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
        });

        $ownerId = DB::table('users')
            ->where('email', 'student.email@ug.must.edu.my')
            ->value('id');

        if ($ownerId) {
            DB::table('courses')->whereNull('user_id')->update(['user_id' => $ownerId]);
        }

        if (Schema::hasColumn('courses', 'user_id')) {
            DB::statement('ALTER TABLE courses MODIFY user_id BIGINT UNSIGNED NOT NULL');
            Schema::table('courses', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('courses') || !Schema::hasColumn('courses', 'user_id')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
