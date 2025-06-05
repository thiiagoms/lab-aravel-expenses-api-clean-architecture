<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table): ColumnDefinition => $table->enum('role', ['admin', 'user'])
            ->default('user')
            ->after('password')
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table): Fluent => $table->dropColumn('role'));
    }
};
