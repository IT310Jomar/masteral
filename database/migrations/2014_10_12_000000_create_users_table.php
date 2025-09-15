<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token', 60)->nullable();
            $table->boolean('verified')->default(false);
            $table->string('password')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->rememberToken();
            $table->string('reset_token')->nullable();
            $table->timestamps();
        
            $table->foreign('role_id')->references('id')->on('user_roles');
        });
        
        DB::table('users')->insert([
            [
                'username' => 'God Mode',
                'email' => 'superAdmin@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'verified' => true,
            ],
            [
                'username' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'verified' => true,
            ],
        ]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
