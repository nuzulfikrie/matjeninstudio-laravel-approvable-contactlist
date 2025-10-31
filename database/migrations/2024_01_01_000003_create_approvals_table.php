<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('contact-approvable.table_names.approvals', 'approvals');
        $contactsTable = config('contact-approvable.table_names.contacts', 'contacts');

        Schema::create($tableName, function (Blueprint $table) use ($contactsTable) {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('contact_id')->constrained($contactsTable)->cascadeOnDelete();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        $tableName = config('contact-approvable.table_names.approvals', 'approvals');

        Schema::dropIfExists($tableName);
    }
};

