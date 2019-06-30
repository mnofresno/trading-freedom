<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRulesForNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->string('condition');
            $table->string('threshold');
            $table->timestamp("last_executed");
            $table->integer('interval');
        });

        Schema::table('users', function(Blueprint $table)
		{
			$table->string('fcm_token')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules');
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn('fcm_token');
        });
    }
}
