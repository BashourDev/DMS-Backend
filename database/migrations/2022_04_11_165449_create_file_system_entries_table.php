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
        Schema::create('file_system_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_approval_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('creator');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name');
            $table->boolean('is_directory');
            $table->date('due_date')->nullable();
            $table->boolean('is_approved')->nullable();
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
        Schema::dropIfExists('file_system_entries');
    }
};
