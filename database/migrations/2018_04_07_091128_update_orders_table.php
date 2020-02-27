<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrdersTable extends Migration
{
	public function __construct()
	{
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
	}
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::table('orders', function (Blueprint $table) {
			$table->string('counter_id')->nullable()->after('active');
			$table->string('rate_id')->nullable()->after('active');
			$table->string('pair_id')->nullable()->after('active');
			$table->string('filled')->nullable()->after('active');
			$table->text('logg')->nullable()->after('counter_id');
			$table->string('gateway')->nullable()->after('logg');
			$table->string('unfilled')->nullable()->after('active');
			$table->string('token_type')->after('token_id');
			$table->dateTime('expires_at')->nullable()->after('counter_id');
			$table->string('idx',20)->nullable()->change();
			$table->string('address')->nullable()->change();
			$table->string('description')->nullable()->after('address');
			$table->unsignedInteger('user_id')->nullable()->change();
			$table->unsignedInteger('token_id')->nullable()->change();
			$table->unsignedInteger('wallet_id')->nullable()->change();
			$table->unsignedInteger('account_id')->nullable()->change();
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
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
        //
		Schema::table('orders', function (Blueprint $table) {
			$table->drop('counter_id');
			$table->drop('rate_id');
			$table->drop('pair_id');
			$table->drop('filled');
			$table->drop('unfilled');
			$table->drop('token_type');
			$table->drop('expires_at');
			$table->drop('logg');
   			$table->dropForeign('orders_account_id_foreign');
			$table->dropForeign('orders_user_id_foreign');
			$table->dropForeign('orders_token_id_foreign');
			$table->dropForeign('orders_wallet_id_foreign');
		});
		
    }
	
}
