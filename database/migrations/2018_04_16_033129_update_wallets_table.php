<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::table('wallets', function (Blueprint $table) {
			$table->text('xpub')->after('token_id')->nullable();
			$table->text('xpriv')->after('token_id')->nullable();
			$table->text('cold_key')->after('token_id')->nullable();
			$table->string('token_type')->after('token_id')->default('App\\\Models\\\Token');
			$table->unsignedInteger('user_id')->change();
			$table->unsignedInteger('token_id')->change();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
			$table->unique(['user_id', 'token_id','token_type']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('wallets', function (Blueprint $table) {
			$table->dropColumn('xpub') ;
			$table->dropColumn('xpriv');
			$table->dropColumn('cold_key');
			$table->dropColumn('token_type');
			$table->dropForeign('wallets_user_id_foreign');
			$table->dropForeign('wallets_token_id_foreign');
			$table->dropUnique(['user_id', 'token_id','token_type']);
		});
    }
}
