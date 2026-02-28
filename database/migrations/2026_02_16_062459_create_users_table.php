<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('student_id', 50)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('api_token', 80)->nullable()->unique();
                $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'student_id')) {
                    $table->string('student_id', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 30)->nullable()->after('student_id');
                }
                if (!Schema::hasColumn('users', 'api_token')) {
                    $table->string('api_token', 80)->nullable()->unique()->after('password');
                }
            });
        }

        // Default account to own existing data.
        DB::table('users')->updateOrInsert(
            ['email' => 'student.email@ug.must.edu.my'],
            [
                'name' => 'Ahmad Zainuddin',
                'student_id' => 'EXISTING-DATA',
                'phone' => null,
                'password' => Hash::make('Must@12345'),
                'api_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
