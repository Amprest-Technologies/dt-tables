<?php

use Amprest\LaravelDT\Models\DataTable;
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
        Schema::connection('laravel-dt')->create('data_table_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DataTable::class)->constrained();
            $table->string('key');
            $table->string('search_type')->nullable();
            $table->string('data_type');
            $table->timestamps();
        });
    }
};
