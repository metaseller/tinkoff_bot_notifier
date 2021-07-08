<?php
namespace app\helpers;

use http\Client;

use yii\console\Controller;
use \jamesRUS52\TinkoffInvest\TIClient;
use \jamesRUS52\TinkoffInvest\TISiteEnum;
use \jamesRUS52\TinkoffInvest\TICurrencyEnum;
use \jamesRUS52\TinkoffInvest\TIInstrument;
use \jamesRUS52\TinkoffInvest\TIPortfolio;
use \jamesRUS52\TinkoffInvest\TIOperationEnum;
use \jamesRUS52\TinkoffInvest\TIIntervalEnum;
use \jamesRUS52\TinkoffInvest\TICandleIntervalEnum;
use \jamesRUS52\TinkoffInvest\TICandle;
use \jamesRUS52\TinkoffInvest\TIOrderBook;
use \jamesRUS52\TinkoffInvest\TIInstrumentInfo;

use \app\models\Candle;
use \app\models\User;
use \app\models\Stock;



class TinkoffHelper
{
    public static function isTickerExist($ticker ="SBER", $tinkoff_token)
    {
        $client = new TIClient(TINKOFF_TOKEN, TISiteEnum::SANDBOX);
        $all_stocks = $client->getStocks(); //Получаем массив со всеми тикерами.

        foreach ($all_stocks as $stock) {
            if ($stock->getTicker() == $ticker) return true;
            else return false;
        }
    }

    public static function isPriceShift($candle, $percent=5){
        if ($candle instanceof Candle)
        {
            $shift = $candle->prcopen * $percent / 100;
            if (abs($candle->prcopen - $candle->prcclose) > $shift)  return true;
            return false;
        }
    }

    public static function addCandle($stock_id, $interval=TIIntervalEnum::MIN10,$tinkoff_token=TINKOFF_TOKEN){
        $TIclient = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);

        $TIcandle = $TIclient->getCandle($stock_id->figi, $interval);

        $candle = new Candle;
        $candle->prcopen = $TIcandle->getOpen();
        $candle->prcclose = $TIcandle->getClose();
        $candle->prcmin = $TIcandle->getLow();
        $candle->prcmax = $TIcandle->getHigh();
        $candle->tradevolume = $TIcandle->getVolume();
        $candle->timeq = $TIcandle->getTime();
        $candle->stock_id = $stock_id;
        $candle->save();
    }

    public static function checkStocks()
    {
        $users = User::findAll() // Получаем массив всех юзеров, сортируя по id.
        ->orderBy('id');

        foreach ($users as $user)
        {
            $stocks = $user->getStock();
            foreach ($stocks as $stock)
            {
                $candles = $stock->getCandle();
                $latest_candle = end($candles);
                if (time() - $latest_candle->timeq > $stock->interval)
                {
                    self::addCandle($latest_candle->stock_id, $stock->interval, $user->token);
                    $candles = $stock->getCandle();
                    $latest_candle = end($candles);
                    if(self::isPriceShift($latest_candle, $stock->change))
                    {
                        $shift_percent = ($latest_candle->prcclose / $latest_candle->prcopen - 1) *100;
                    }
                }
            }
        }
    }
}

