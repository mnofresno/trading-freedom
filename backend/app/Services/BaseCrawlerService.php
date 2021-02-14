<?php

namespace App\Services;

use App\Models\User as User;
use App\Models\ExchangeProvider as ExchangeProvider;

abstract class BaseCrawlerService
{
    protected $user;
    protected $exchangeProvider;
    
    public function __construct(User $user, ExchangeProvider $exchangeProvider)
    {
        $this->exchangeProvider = $exchangeProvider;
        $this->user = $user;
    }

    abstract protected function getClientBalances($userId);

    protected function mapBalancesCallback($value, $key)
    {
        return $value;
    }

    abstract protected function getClient($userId);
    abstract protected function GetBitcoinDollarMarket($userId);
    abstract protected function getAmount($item);
    abstract protected function getExchangeProviderCode();
    abstract protected function getUSDSymbols(): array;
    abstract protected function getTickers($client);
    abstract protected function getCurrencyMarket($currency, $tickers, $client);
    abstract protected function GetMarketAverage($market);
    
    protected function getAuthKeys($userId)
    {
        $currentUser = $this->user->find($userId);
        $exchangeProviderId = $this->exchangeProvider->where('code', '=', $this->getExchangeProviderCode())->firstOrFail()->id;
        $userKeys = $currentUser->apiKeys()->where('exchange_provider_id', '=', $exchangeProviderId)->firstOrFail();
        $auth = ['key' => $userKeys->api_key, 'secret' => $userKeys->api_secret];
        return $auth;
    }

    public function GetBalances($userId = null)
    {
        $balances = $this->getClientBalances($userId);
        return collect($balances)
            ->filter(function($value) {
                return $this->getAmount($value) > 0;
            })
            ->map(function($value, $key) {
                return $this->mapBalancesCallback($value, $key);
            });
    }

    public function GetAllBalances($userId)
    {
        $client = $this->getClient($userId);
        $tickers = $this->getTickers($client);
        $btcMean = $this->GetBitcoinDollarMarket($userId);
        $balances = $this->GetBalances($userId);
        $outputBalances = [];
        $saldoTotalMBTC = 0;
        $saldoTotalUSD = 0;
        foreach ($balances as $balance) {
            $detalleBalance = [];
            $saldo = $balance->Balance;
            if ($saldo > 0) {
                $currency = $balance->Currency;
                try {
                    if ($currency == 'BTC') {
                        $mean = 1;
                        $mBtcMean = 1000;
                        $dollarValue = $btcMean;
                    } else if (in_array($currency, $this->getUSDSymbols())) {
                        $mean = 1 / $btcMean;
                        $dollarValue = 1;
                        $mBtcMean = $mean * 1000;
                    } else {
                        $mkt = $this->getCurrencyMarket($currency, $tickers, $client);
                        $mean = $this->GetMarketAverage($mkt);
                        $mBtcMean = $mean * 1000;
                        $dollarValue = $mean * $btcMean;
                    }

                    $mBtcMean = $this->toFixed($mBtcMean);
                    $dollarValue = $this->toFixed($dollarValue);
                    $saldoMBTC = $this->toFixed($mBtcMean * $saldo);
                    $saldoUSD = $this->toFixed($dollarValue * $saldo);
                    $saldo = $this->toFixed($saldo);

                    $detalleBalance['MONEDA'] = $currency;
                    $detalleBalance['VALOR_MBTC'] = $mBtcMean;
                    $detalleBalance['VALOR_USDT'] = $dollarValue;
                    $detalleBalance['SALDO'] = $saldo;
                    $detalleBalance['SALDO_MBTC'] = $saldoMBTC;
                    $detalleBalance['SALDO_USDT'] = $saldoUSD;

                    $saldoTotalMBTC += $saldoMBTC;
                    $saldoTotalUSD += $saldoUSD;
                } catch (\Exception $e) {
                    $detalleBalance['ERROR'] = "Error getting data for $currency. ".$e->getMessage();
                }
                $outputBalances[] = $detalleBalance;
            }
        }

        return [
            'TOTAL_USD' => $saldoTotalUSD,
            'TOTAL_MBTC' => $saldoTotalMBTC,
            'VALOR_BTC_USD' => $btcMean,
            'assets' => $outputBalances
        ];
    }
        
    protected function toFixed($number)
    {
        return number_format($number, 4, ".", "");
    }
}
