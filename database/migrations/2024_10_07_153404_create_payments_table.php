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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->unsigned();
            $table->bigInteger('service_id')->unsigned();
            $table->bigInteger('mode_of_payment_id')->unsigned();
            $table->string('reference_number')->nullable();
            $table->string('or_number')->nullable();
            $table->string('amount');
            $table->string('account')->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->date('date_of_payment')->nullable();
            $table->enum('payment_status',['Pending','Approved','Rejected'])->default('Pending');
            $table->enum('upload_status',['Yes','No'])->default('No');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('mode_of_payment_id')->references('id')->on('mode_of_payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
