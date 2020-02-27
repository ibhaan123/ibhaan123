<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRatesTable extends Migration
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
        Schema::create('rates', function (Blueprint $table) {
            $table->increments('id');
          
            $table->integer('src_id')->unsigned();
            $table->integer('dst_id')->unsigned();
            $table->string('pair_id')->nullable();
			$table->string('fromsym',10);
            $table->string('tosym',10);
			$table->string('src_type',100);
            $table->string('dst_type',100);
			$table->string('src_gateway',100);
            $table->string('dst_gateway',100);
            $table->string('rate')->default(1);
            $table->string('fees')->default(0);
            $table->string('minimum')->nullable();
            $table->string('maximum')->nullable();
			$table->tinyInteger('active')->default(1);
			$table->tinyInteger('autoupdate')->default(1);
			$table->tinyInteger('autocomplete')->default(1);
            $table->text('message')->nullable();
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
        Schema::drop('rates');
    }
}
