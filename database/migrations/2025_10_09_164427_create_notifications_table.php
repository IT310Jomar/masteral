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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->bigInteger('request_id')->unsigned()->nullable();
            $table->bigInteger('client_id')->unsigned();
            $table->dateTime('time');
            $table->boolean('is_read')->default(1);
            $table->boolean('published')->default(1);
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
