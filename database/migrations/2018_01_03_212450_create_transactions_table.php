<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id');
			$table->integer('token_id')->nullable();
			$table->integer('wallet_id')->nullable();
			$table->integer('nonce')->nullable();
			$table->integer('account_id');
			$table->integer('order_id')->nullable();
			$table->integer('confirmations')->default(0);
			$table->string('from_address',42)->nullable();
			$table->string('to_address',42);
			$table->string('type',16)->default('credit');
			$table->string('amount',32);
			$table->string('tx_hash',96);
			$table->text('description')->nullable();
			$table->string('gas_limit',32)->nullable();
			$table->string('gas_price',32)->nullable();;
			$table->integer('active')->default(1);
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
        Schema::dropIfExists('transactions');
    }
}
