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
        Schema::create('countries', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('capital')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('country_code', 3)->default('');
            $table->string('currency')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_sub_unit')->nullable();
            $table->string('currency_symbol', 5)->nullable();
            $table->unsignedTinyInteger('currency_decimals')->nullable();
            $table->string('full_name')->nullable();
            $table->string('iso_3166_2', 2);
            $table->string('iso_3166_3', 3);
            $table->string('name');
            $table->string('region_code', 3)->default('');
            $table->string('sub_region_code', 3)->default('');
            $table->boolean('eea')->default(false);
            $table->string('calling_code', 3)->nullable();
            $table->string('flag', 6)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('eu')->default(false);

            $table->index('iso_3166_2');
            $table->index('iso_3166_3');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
