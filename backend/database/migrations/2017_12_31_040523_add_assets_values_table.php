<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssetsValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_values', function(Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string ('MONEDA');
            $table->decimal('VALOR_MBTC', 10, 4);
            $table->decimal('VALOR_USDT', 10, 4);
            $table->decimal('SALDO'     , 10, 4);
            $table->decimal('SALDO_MBTC', 10, 4);
            $table->decimal('SALDO_USDT', 10, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assets_values');
    }
}
