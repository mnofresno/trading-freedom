<?php

use App\Models\ExchangeProvider;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            ['code' => 'BITTREX', 'description' => 'Bittrex'],
            ['code' => 'BITFINEX', 'description' => 'Bitfinex'],
            ['code' => 'POLONIEX', 'description' => 'Poloniex']
        ];
        foreach ($providers as $provider) {
            $exchange = new ExchangeProvider($provider);
            $exchange->save();
        }
    }
}
