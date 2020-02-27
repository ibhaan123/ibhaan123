<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::disableForeignKeyConstraints();
        Schema::create('commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('icosale_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('token_id')->unsigned();
            $table->string('status')->nullable();
            $table->string('message')->nullable();
            $table->string('amount')->nullable();
            $table->string('symbol')->nullable();
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('icosale_id')->references('id')->on('icosales')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('commissions');
    }
}
