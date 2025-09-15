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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('editor_id')->unsigned()->nullable();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->dateTime('date_time_requests');
            $table->enum('request_status',['Pending','Approved','Rejected'])->default('Pending');
            $table->enum('editor_status',['Pending','Approved','Rejected'])->default('Pending');
            $table->text('notes')->nullable();
            $table->text('reasons')->nullable();
            $table->enum('progress_status',[0,1,2,3])->default(0);
            $table->boolean('release_status')->default(1);
            $table->string('uploaded_file')->nullable();
            $table->string('editor_uploaded_file')->nullable();
            $table->boolean('edited_upload')->default(1);
            $table->dateTime('date_of_published')->nullable();
            $table->timestamps();

            // $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('editor_id')->references('id')->on('editors');
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requests');
    }
};
