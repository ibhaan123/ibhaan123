<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('token_id')->unsigned();
            $table->string('token_type')->nullable();
            $table->integer('account_id')->unsigned();
			$table->integer('wallet_id')->unsigned()->nullable();
            $table->string('balance')->default('0.00000000');
			$table->string('number');
			$table->integer('margin_days')->default(0);
            $table->string('collateral')->default('0.00000000');
            $table->string('leverage')->default('0.00000000');
            $table->string('credit')->default('0.00000000');
            $table->string('status')->default(1);;
			$table->boolean('active')->default(true);
			$table->timestamps();
			$table->softDeletes();
           });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('services');
    }
}
