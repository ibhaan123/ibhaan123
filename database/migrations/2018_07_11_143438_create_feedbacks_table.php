<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('rater_id')->unsigned();
            $table->integer('trade_id')->unsigned();
			 $table->integer('feedback_id')->unsigned()->nullable();
            $table->string('message')->nullable();
            $table->string('rating')->nullable();
            $table->string('feedback')->nullable()->default('neutral');;
            $table->boolean('active')->default(true);
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('rater_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('trade_id')->references('id')->on('trades')->onDelete('cascade')->onUpdate('cascade');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('feedbacks');
    }
}
