<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('tokens', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id');
			$table->integer('account_id')->nullable();
            $table->string('name', 75);
			$table->string('slug');
			$table->integer('contract_id')->nullable();
            $table->string('contract_address',42)->unique();
			$table->string('mainsale_address',42)->nullable();
            $table->text('contract_ABI_array')->nullable();
			$table->text('contract_Bin')->nullable();
            $table->datetime('ico_start')->nullable();
			$table->datetime('ico_ends')->nullable();
            $table->string('token_price',32)->nullable();
            $table->boolean('active')->default(false);
            $table->string('symbol',32)->unique();
			$table->integer('decimals')->default(18);
            $table->string('logo',200)->nullable();
			$table->decimal('price', 20, 10)->nullable();
			$table->decimal('change', 16, 6)->nullable();
			$table->decimal('change_pct', 10, 2)->nullable();
			$table->decimal('open', 20, 10)->nullable();
			$table->decimal('low', 20, 10)->nullable();
			$table->decimal('high', 20, 10)->nullable();
			$table->string('supply')->nullable();
			$table->string('template')->nullable();
			$table->string('total_supply',64)->nullable();
			$table->string('market_cap',64)->nullable();
			$table->string('volume',64)->nullable();
			$table->string('volume_ccy',45)->nullable();
			$table->datetime('last_updated')->nullable();
			$table->tinyInteger('sale_active')->default(0);
			$table->tinyInteger('ico_active')->default(0);
			$table->tinyInteger('wallet_active')->default(0);
			$table->string('mining_proof',50)->nullable()->default('proof Of Work');
			$table->string('website',200)->nullable();
			$table->string('twitter',100)->nullable();
			$table->string('facebook',100)->nullable();
			$table->string('whitepaper',100)->nullable();
			$table->text('description')->nullable();
			$table->text('features')->nullable();
			$table->text('technology')->nullable();
			$table->string('ico_address',42)->nullable();
			$table->text('ico_pass')->nullable();
			$table->enum('net',['olympic','frontier','mainnet','homestead','metropolis','classic','expanse','morden','ropsten','rinkeby','kovan'])->default('mainnet');
			$table->text('bonus')->nullable();
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
        Schema::dropIfExists('tokens');
    }
}
