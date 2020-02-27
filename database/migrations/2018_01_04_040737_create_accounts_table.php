<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id');
			$table->string('account',42);
			$table->string('api_key',1410)->nullable();
			$table->string('balance',32)->default('0.00000000');
			$table->string('xpriv',600);
			$table->string('xpub',600);
			$table->string('mnemonic',1200);
			$table->string('cypher',1200);
			$table->string('path',32)->default("m/44\'/60\'/0\'/0/0");
			$table->tinyInteger('active')->default(1);
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
        Schema::dropIfExists('accounts');
    }
}
