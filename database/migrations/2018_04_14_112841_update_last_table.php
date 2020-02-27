<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLastTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		//
		Schema::table('last', function (Blueprint $table) {
			$table->renameColumn('last_block', 'end_block');
			$table->string('start_block')->after('rid')->default(NULL);
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
		Schema::table('last', function (Blueprint $table) {
			$table->renameColumn('end_block', 'last_block');
			$table->dropColumn('start_block');
		});
    }
}
