<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('contact-approvable.table_names.contacts', 'contacts');
        //check if table exists
        if (Schema::hasTable($tableName)) {
            return;
        }


        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        $tableName = config('contact-approvable.table_names.contacts', 'contacts');

        Schema::dropIfExists($tableName);
    }
};

