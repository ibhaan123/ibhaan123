<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLaravelLoggerActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::table('laravel_logger_activity', function (Blueprint $table) {
			$table->nullableMorphs('subject');
			
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
		Schema::table('laravel_logger_activity', function (Blueprint $table) {
			$table->dropColumn('subject_type') ;
			$table->dropColumn('subject_id');
		});
		
    }
}
