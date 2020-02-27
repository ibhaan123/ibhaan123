<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTokensTable extends Migration
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
		Schema::table('tokens', function (Blueprint $table) {
			$table->string('family')->after('technology')->default('ethereum');
			$table->unsignedInteger('user_id')->nullable()->change();
			$table->string('price',60)->nullable()->change();
			$table->string('minimum',60)->nullable()->after('net');
			$table->string('maximum',60)->nullable()->after('net');
			$table->string('withdraw_fees',60)->nullable()->after('net');
			$table->string('desposit_fees',60)->nullable()->after('net');
			$table->string('image')->nullable()->after('net');
			$table->string('sweepthreshold')->nullable()->after('net');
			$table->string('sweeptoaddress')->nullable()->after('net');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			
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
		Schema::table('tokens', function (Blueprint $table) {
			 $table->dropColumn([
				 'family', 
				 'minimum', 
				 'maximum',
				 'withdraw_fees', 
				 'desposit_fees', 
				 'image',
				 'sweepthreshold', 
				 'avatar', 
				 'sweeptoaddress',
			 ]);
			$table->dropForeign('token_user_id_foreign');
			$table->dropForeign('token_contract_id_foreign');
		});
    }
}
