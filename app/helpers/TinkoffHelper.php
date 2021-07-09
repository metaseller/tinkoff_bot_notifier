<?php
namespace app\helpers;

use Exception;
use \jamesRUS52\TinkoffInvest\TIClient;
use jamesRUS52\TinkoffInvest\TIException;
use \jamesRUS52\TinkoffInvest\TISiteEnum;
use \jamesRUS52\TinkoffInvest\TIIntervalEnum;

use \app\models\Candle;
use \app\models\User;
use \app\models\Stock;


class TinkoffHelper
{
    public function isTokenLegit($tinkoff_token) {
        try {
            $client = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);
            $client->getStocks();
        }
        catch (TIException $e) {
            return false;
        }
        return true;
    }
    public function isFigiExist($tinkoff_token=TINKOFF_TOKEN, $figi ="SBER")
    {
        $client = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);
        $all_stocks = $client->getStocks(); //Получаем массив со всеми тикерами.

        foreach ($all_stocks as $stock) {
            if ($stock->getFigi() == $figi) return true;
        }
        return false;
    }

    public function isPriceShift($candle, $percent=5){
        if ($candle instanceof Candle)
        {
            $shift = $candle->prcopen * $percent / 100;
            if (abs($candle->prcopen - $candle->prcclose) > $shift)  return true;
            return false;
        }
    }

    public function addCandle($stock_id, $interval=TIIntervalEnum::MIN10,$tinkoff_token=TINKOFF_TOKEN){
        $TIclient = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);

        $TIcandle = $TIclient->getCandle($stock_id->figi, $interval);

        $candle = new Candle();
        $candle->prcopen = $TIcandle->getOpen();
        $candle->prcclose = $TIcandle->getClose();
        $candle->prcmin = $TIcandle->getLow();
        $candle->prcmax = $TIcandle->getHigh();
        $candle->tradevolume = $TIcandle->getVolume();
        $candle->timeq = $TIcandle->getTime();
        $candle->stock_id = $stock_id;
        $candle->save();
    }

    public function checkStocks()
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

