<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKycsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kycs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('image')->nullable();
            $table->string('id_card')->nullable();
            $table->string('passport')->nullable();
            $table->string('pdf')->nullable();
			$table->string('image_message',300)->nullable();
            $table->string('id_card_message',300)->nullable();
            $table->string('passport_message',300)->nullable();
            $table->string('pdf_message',300)->nullable();
			$table->tinyInteger('status')->default(0);
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('kycs');
    }
}
