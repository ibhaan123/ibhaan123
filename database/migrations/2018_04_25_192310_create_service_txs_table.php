<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServiceTxsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::disableForeignKeyConstraints();
        Schema::create('service_txs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->morphs('token');
            $table->integer('service_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('amount')->nullable();
			$table->string('margin_id')->nullable();
			$table->string('margin')->default(0)->nullable();
			$table->string('leverage')->default(0)->nullable();
			$table->string('collateral')->nullable();
            $table->string('type')->nullable();
			$table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
			$table->string('active')->default(1);
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			//$table->foreign('margin_id')->references('id')->on('margins')->onDelete('cascade');
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
			$table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
			
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
        Schema::drop('service_txs');
    }
}
