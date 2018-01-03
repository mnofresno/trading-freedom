<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsersAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_assets', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('SALDO', 10, 4);
            $table->integer('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets'); 
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users'); 
        });
        
        Schema::table('assets_values_summaries', function(Blueprint $table)
        {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        
        Schema::table('assets_values', function(Blueprint $table)
        {
            $table->dropColumn('SALDO');
            $table->dropColumn('SALDO_USDT');
            $table->dropColumn('SALDO_MBTC');
            $table->dropColumn('MONEDA');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_assets');
        
        Schema::table('assets_values', function(Blueprint $table)
        {
            $table->decimal('SALDO', 10, 4);
            $table->decimal('SALDO_USDT', 10, 4);
            $table->decimal('SALDO_MBTC', 10, 4);
            $table->string('MONEDA');
        });
        
        Schema::table('assets_values_summaries', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users'); 
        });
    }
}
