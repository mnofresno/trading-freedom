<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssetsValuesSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_values_summaries', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('TOTAL_USD', 10, 4);
            $table->decimal('TOTAL_MBTC', 10, 4);
            $table->decimal('VALOR_BTC_USD', 10, 4);
        });
        
        Schema::table('assets_values', function(Blueprint $table)
        {
            $table->integer('asset_value_summary_id')->unsigned()->nullable();
            $table->foreign('asset_value_summary_id')->references('id')->on('assets_values_summaries'); 
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
            $table->dropForeign(['asset_value_summary_id']);
            $table->dropColumn('asset_value_summary_id');
        });
        
        Schema::drop('assets_values_summaries');
    }
}
