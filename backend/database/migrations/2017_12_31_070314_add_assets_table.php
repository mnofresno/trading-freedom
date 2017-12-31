<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('code');
            $table->string('description');
        });
        
        Schema::table('assets_values', function(Blueprint $table)
        {
            $table->integer('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_values', function(Blueprint $table)
        {
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });
        
        Schema::drop('assets');
    }
}
