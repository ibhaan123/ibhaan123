<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('idx');
			$table->string('start',60);
			$table->string('address',42);
			$table->integer('user_id');
			$table->integer('token_id')->nullable();
			$table->integer('wallet_id')->nullable();
			$table->unsignedInteger('service_id')->nullable();
			$table->integer('account_id');
			$table->string('symbol',16);
			$table->string('type',16)->default('credit');
			$table->string('amount',32);
			$table->string('fees',32)->default(0);
			$table->string('item',60);
			$table->string('reference',32);
			$table->string('txid',300)->nullable();
			$table->string('item_url',200)->nullable();
			$table->text('item_data')->nullable();
			$table->tinyInteger('active')->default(1);
			$table->enum('status', ['UNPAID','CONFIRMING','COMPLETE','PARTIAL','CANCELLED','EXPIRED','COLD','FAILED'])->default('UNPAID');
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
        Schema::dropIfExists('orders');
    }
}
