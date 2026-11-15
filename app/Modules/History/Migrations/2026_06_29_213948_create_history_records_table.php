<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Aplica la migración. */
    public function up(): void
    {
        Schema::create('history_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('module_name');
            $table->string('action');
            $table->json('payload');
            $table->json('result');
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    /** Revierte la migración. */
    public function down(): void
    {
        Schema::dropIfExists('history_records');
    }
};