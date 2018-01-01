<?php

namespace App\Services;

use Messerli90\Bittrex\Bittrex;

class BittrexCrawlerService
{
    public function GetAllBalances($user_id = null)
    {    
        $bittrexClient = new Bittrex(config('services.bittrex.key'), config('services.bittrex.secret'));
        
        $btcMkt = $bittrexClient->getMarketSummary('USDT-BTC');
        $btcMkt = $btcMkt->result[0];
        
        $btcLast = $btcMkt->Last;
        $btcBid  = $btcMkt->Bid;
        $btcAsk  = $btcMkt->Ask;
        
        $btcMean = ( $btcLast + $btcBid + $btcAsk ) / 3;
        $balances = $bittrexClient->getBalances()->result;
        
        $outputBalances = [];
        
        $saldoTotalMBTC = 0;
        $saldoTotalUSD  = 0;
        
        foreach($balances as $balance)
        {
            $detalleBalance = [];
            $saldo = $balance->Balance;
            if($saldo > 0)
            {
                $currency = $balance->Currency;
                try{
                    
                    if($currency != 'BTC') 
                    {
                        $mkt = $bittrexClient->getMarketSummary('BTC-'.$currency)->result;

                        $last        = $mkt[0]->Last;
                        $bid         = $mkt[0]->Bid;
                        $ask         = $mkt[0]->Ask;
                        $mean        = ( $last + $bid + $ask ) / 3;
                        $mBtcMean    = $mean * 1000;
                        $dollarValue = $mean * $btcMean;
                    }
                    else
                    {
                        $mean        = 1;
                        $mBtcMean    = 1000;
                        $dollarValue = $btcMean;
                    }
                    
                    $mBtcMean    = $this->toFixed($mBtcMean, 3);
                    $dollarValue = $this->toFixed($dollarValue, 3);
                    $saldoMBTC   = $this->toFixed($mBtcMean * $saldo, 3);
                    $saldoUSD    = $this->toFixed($dollarValue * $saldo, 3);
                    $saldo       = $this->toFixed($saldo, 3);
                    //echo "Moneda: $currency,\t Valor mBTC: $mBtcMean,\t Valor USD: $dollarValue,\t Saldo: $saldo,\t Saldo mBTC: $saldoMBTC,\t Saldo USD: $saldoUSD\r\n";
                    $detalleBalance['MONEDA'    ] = $currency;
                    $detalleBalance['VALOR_MBTC'] = $mBtcMean;
                    $detalleBalance['VALOR_USDT'] = $dollarValue;
                    $detalleBalance['SALDO'     ] = $saldo;
                    $detalleBalance['SALDO_MBTC'] = $saldoMBTC;
                    $detalleBalance['SALDO_USDT'] = $saldoUSD;
                    
                    $saldoTotalMBTC += $saldoMBTC;
                    $saldoTotalUSD  += $saldoUSD;
                }
                catch(\Exception $e){
                    $detalleBalance['ERROR'] = "Error obteniendo datos para $currency";
                }
                $outputBalances[] = $detalleBalance;
            }
            
            
        }
        
        return [ 'TOTAL_USD'     => $saldoTotalUSD,
                 'TOTAL_MBTC'    => $saldoTotalMBTC,
                 'VALOR_BTC_USD' => $btcMean,
                 'assets'        => $outputBalances ];
    }

    public function GetAssets()
    {
        $bittrex = new Bittrex();
        return $bittrex->getMarkets();
    }
    
    private function toFixed($number)
    {
        return number_format($number, 3, ".", "");
    }
}
