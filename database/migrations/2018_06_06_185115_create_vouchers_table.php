<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::disableForeignKeyConstraints();
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('token_id')->unsigned();
            $table->string('token_type')->nullable();
            $table->string('value')->nullable();
            $table->string('code');
			$table->string('serial');
            $table->dateTime('used')->nullable();
            $table->tinyInteger('status')->nullable();
			$table->boolean('active')->default(true);
			$table->string('reference')->nullable();
			$table->timestamps();
            $table->softDeletes();
        });
		
		Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vouchers');
    }
}
