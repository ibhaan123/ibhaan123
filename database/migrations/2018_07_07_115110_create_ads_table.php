<?php

use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     * Create the Ads Table
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
	
            $table->increments('id');
			$table->uuid('uuid');
			$table->string('slug',300);
            $table->integer('user_id')->unsigned();
            $table->integer('country_id')->unsigned();
            $table->string('city')->nullable();
			$table->string('from_symbol')->nullable();
            $table->string('to_symbol');
            $table->string('rate')->nullable();
			$table->string('market_rate',32)->nullable();
            $table->morphs('token');
            $table->point('location')->nullable();
            $table->polygon('area')->nullable();
            $table->string('overhead');
            $table->string('min');
            $table->string('max');
            $table->string('method');
			$table->text('custom_method')->nullable();
			$table->enum('custom_type',['online','offline'])->nullable();
			$table->enum('type',['buy','sell']);
            $table->text('instructions');
			$table->string('min_vol',32)->nullable();
            $table->integer('min_count')->nullable();
			$table->boolean('verified_phone')->default(false);
            $table->boolean('verified_id')->default(false);
            $table->string('account')->nullable();
			$table->integer('window')->unsigned()->default(10);
            $table->enum('status',['approved','rejected','blocked','pending'])->default('pending');
            $table->boolean('active')->default(true);
			$table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('ads');
    }
}
