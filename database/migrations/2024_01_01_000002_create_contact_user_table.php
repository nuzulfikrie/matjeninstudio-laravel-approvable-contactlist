<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('contact-approvable.table_names.contact_user', 'contact_user');
        $contactsTable = config('contact-approvable.table_names.contacts', 'contacts');
        $userModel = config('contact-approvable.user_model', 'App\\Models\\User');
        $usersTable = (new $userModel)->getTable();

        //check if table exists
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($contactsTable, $usersTable) {
            $table->id();
            $table->foreignId('contact_id')->constrained($contactsTable)->cascadeOnDelete();
            $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
            $table->boolean('is_approver')->default(false);
            $table->timestamps();

            $table->unique(['contact_id', 'user_id']);
            $table->index('is_approver');
        });
    }

    public function down(): void
    {
        $tableName = config('contact-approvable.table_names.contact_user', 'contact_user');

        Schema::dropIfExists($tableName);
    }
};

