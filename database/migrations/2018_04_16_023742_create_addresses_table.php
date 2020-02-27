<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
			$table->string('idx');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('token_id');
			$table->unsignedInteger('account_id');
			$table->unsignedInteger('wallet_id');
			$table->string('balance', 32)->default('0.00000000');
			$table->string('address');
			$table->string('address_link',900);
			$table->string('symbol');
			$table->enum('type',['external','change','order'])->default('external');
			$table->tinyInteger('active')->default(1);;
            $table->timestamps();
			$table->softDeletes();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
			$table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
