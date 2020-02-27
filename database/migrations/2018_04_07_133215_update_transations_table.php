<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTransationsTable extends Migration
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
		//
		Schema::table('transactions', function (Blueprint $table) {
			$table->string('token_type')->after('token_id')->default('App\\\Models\\\Token');
			$table->string('tx_hash_link',100)->after('tx_hash')->nullable();
			$table->tinyInteger('processed')->after('tx_hash')->default(0);
			$table->string('blockheight')->after('confirmations')->nullable();
			$table->unsignedInteger('user_id')->change();
			$table->unsignedInteger('token_id')->change();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
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
		Schema::table('transactions', function (Blueprint $table) {
			$table->dropForeign('transactions_user_id_foreign');
			$table->dropForeign('transactions_token_id_foreign');
			$table->dropColumn('token_type');
			$table->dropColumn('tx_hash_link',100);
		});
			
    }
}
