<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExchangeProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_providers', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('code');
            $table->string('description');
        });
        
        Schema::table('users_keys', function(Blueprint $table)
        {
            $table->integer('exchange_provider_id')->unsigned()->nullable();
            $table->foreign('exchange_provider_id')->references('id')->on('exchange_providers'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_keys', function(Blueprint $table)
        {
            $table->dropForeign(['exchange_provider_id']);
            $table->dropColumn('exchange_provider_id');
        });
        
        Schema::drop('exchange_providers');
    }
}
