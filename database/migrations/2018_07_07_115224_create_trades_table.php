<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
			$table->uuid('uuid');
            $table->integer('ad_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('ad_user_id')->unsigned();
            $table->integer('chat_id')->unsigned()->nullable();
			$table->integer('country_id')->unsigned();
            $table->string('price');
            $table->string('qty');
            $table->string('total');
            $table->morphs('token');
            $table->string('type')->nullable();
            $table->enum('status',['open','closed','locked','pending','rejected','success','disputed','ignored','paid','cancelled'])->default('open');
            $table->boolean('active')->default(true);
            $table->string('account_name')->nullable();
            $table->string('account')->nullable();
			$table->boolean('verified_phone')->default(false);
            $table->boolean('verified_id')->default(false);
            $table->string('min_vol')->nullable();
            $table->string('min_count')->nullable();
			$table->string('escrow')->nullable();
			$table->integer('escrow_user')->unsigned()->nullable();
			$table->text('trader_instruction')->nullable();
            $table->text('user_instruction')->nullable();
			$table->dateTime('expires_at')->nullable();
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ad_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('trades');
    }
}
