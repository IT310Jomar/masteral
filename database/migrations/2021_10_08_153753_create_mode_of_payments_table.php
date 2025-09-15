<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mode_of_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('qr_code_image')->nullable();
            $table->enum('status', ['Available', 'Not Available'])->default('Available');
            $table->string('account')->nullable();
            $table->timestamps();
        });

        $data = array('Cash','GCash');
       
        foreach ($data as $val) {
            DB::table('mode_of_payments')->insert(
                array('name' => $val)
            );
        }
   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mode_of_payments');
    }
};
