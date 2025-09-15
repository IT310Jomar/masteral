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
        Schema::create('admin_notification', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->bigInteger('request_id')->unsigned()->nullable();
            $table->bigInteger('editor_id')->unsigned()->nullable();
            $table->bigInteger('client_id')->unsigned();
            $table->dateTime('time');
            $table->boolean('is_read')->default(1);
            $table->boolean('published')->default(1);
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('editor_id')->references('id')->on('editors');
        
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_notification');
    }
};
