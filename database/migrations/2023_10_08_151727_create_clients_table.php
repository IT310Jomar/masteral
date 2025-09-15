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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('contact')->nullable();
            $table->string('course')->nullable();
            $table->string('school')->nullable();
            $table->bigInteger('school_type_id')->unsigned()->nullable();
            $table->string('image')->nullable();
            $table->enum('status',['Active','Disabled'])->default('Active');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('school_type_id')->references('id')->on('school_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
