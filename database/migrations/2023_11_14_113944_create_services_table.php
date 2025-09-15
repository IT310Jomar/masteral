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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('price', 8, 2);
            $table->enum('status',['Available','Not Available'])->default('Available');
            $table->timestamps();
        });

        $data = array(
            ['name' => 'Chapter 3 Only', 'price' => 300.00],
            ['name' => 'Chapter 4 Only', 'price' => 400.00],
            ['name' => 'Journal', 'price' => 800.0],
);
        
        foreach ($data as $service) {
            DB::table('services')->insert($service);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
