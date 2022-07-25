<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staging_records', function (Blueprint $table) {
            $table->id();
            $table->text('email')->nullable();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('prefix')->nullable();
            $table->text('active')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staging_records');
    }
};
