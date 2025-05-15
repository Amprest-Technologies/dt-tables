<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('laravel-dt')->create('data_tables', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('identifier')->unique();
            $table->json('settings');
            $table->timestamps();
        });
    }
};
