<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::disableForeignKeyConstraints();
        Schema::create('ios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->integer('service_tx_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned();
            $table->morphs('token');
            $table->integer('order_id')->unsigned()->nullable();
            $table->enum('status',['pending','complete','failed','rejected'])->default('pending');
            $table->string('type')->nullable();
            $table->string('message')->nullable();
            $table->string('amount')->nullable();
			$table->string('fees')->nullable();
			$table->string('fees_value')->nullable();
			$table->string('fees_percent')->nullable();
            $table->string('symbol')->nullable();
			$table->string('reference')->nullable();
			$table->string('txid')->nullable();
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('ios');
    }
}
