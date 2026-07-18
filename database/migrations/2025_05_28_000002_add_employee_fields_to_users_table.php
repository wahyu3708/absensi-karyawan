<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->nullable()->after('id');
            $table->string('department')->nullable()->after('name');
            $table->string('position')->nullable()->after('department');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete()->after('position');
            $table->enum('role', ['admin', 'employee'])->default('employee')->after('shift_id');
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
            $table->text('fcm_token')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('fcm_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'employee_id', 'department', 'position', 'shift_id',
                'role', 'phone', 'avatar', 'fcm_token', 'is_active'
            ]);
        });
    }
};
