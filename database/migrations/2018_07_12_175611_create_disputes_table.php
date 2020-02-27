<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('trade_id')->unsigned();
            $table->integer('ad_id')->unsigned();
            $table->integer('won')->unsigned()->nullable();
            $table->integer('won1')->unsigned()->nullable();
            $table->integer('settled')->unsigned()->nullable();
			$table->integer('loser')->unsigned()->nullable();
            $table->text('message')->nullable();
            $table->enum('status',['open','closed'])->default('open');
            $table->boolean('active')->default(true);
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('trade_id')->references('id')->on('trades')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade')->onUpdate('cascade');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('disputes');
    }
}
