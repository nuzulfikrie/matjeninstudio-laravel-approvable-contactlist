<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('contact-approvable.table_names.approval_records', 'approval_records');
        $approvalsTable = config('contact-approvable.table_names.approvals', 'approvals');
        $userModel = config('contact-approvable.user_model', 'App\\Models\\User');
        $usersTable = (new $userModel)->getTable();

        // check if table exists
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($approvalsTable, $usersTable) {
            $table->id();
            $table->foreignId('approval_id')->constrained($approvalsTable)->cascadeOnDelete();
            $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
            $table->boolean('is_approved');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('approval_id');
            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        $tableName = config('contact-approvable.table_names.approval_records', 'approval_records');

        Schema::dropIfExists($tableName);
    }
};
