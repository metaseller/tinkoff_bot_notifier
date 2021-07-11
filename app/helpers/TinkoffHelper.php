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

    public function addCandle($stock, $interval=TIIntervalEnum::MIN10,$tinkoff_token=TINKOFF_TOKEN){
        $TIclient = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);
        try {
            $TIcandle = $TIclient->getCandle($stock->figi, $interval);

            $candle = new Candle();
            $candle->prcopen = $TIcandle->getOpen();
            $candle->prcclose = $TIcandle->getClose();
            $candle->prcmin = $TIcandle->getLow();
            $candle->prcmax = $TIcandle->getHigh();
            $candle->tradevolume = $TIcandle->getVolume();
            $candle->timeq = $TIcandle->getTime();
            $candle->stock_id = $stock->id;
            $candle->save();
            print_r($candle);
        }
        catch (TIException $e) {
            echo($e->getMessage());
        }
    }

    public function toSeconds($interval) {
        $change = ['1min' => 60, '2min' => 120, '3min' => 180, '5min' => 300, '10min' => 600, '15min' => 900, '30min' =>
        1800, 'hour' => 3600, 'day' => 86400, 'week' => 604800, 'month' => 2592000];
        return $change[$interval];
    }

    public function checkStocks()
    {
        $users = User::find()->all(); // Получаем массив всех юзеров
        $result = [];
        foreach ($users as $user)
        {
            $stocks = Stock::find()->where(['user_id' => $user->id])->all();
            foreach ($stocks as $stock) {
                if ($stock) {
                    $candles = Candle::find()->where(['stock_id' => $stock->id])->all();
                    $latest_candle = end($candles);
                    if ($latest_candle) {
                        if (time() - $latest_candle->timeq > $this->toSeconds($stock->interval)) {
                            self::addCandle($latest_candle->stock_id, $this->$stock->interval, $user->token);
                            $candles = Candle::find()->where(['stock_id' => $stock->id])->all();
                            $latest_candle = end($candles);
                            if (self::isPriceShift($latest_candle, $stock->change)) {
                                $shift_percent = ($latest_candle->prcclose / $latest_candle->prcopen - 1) * 100;
                                array_push($result, ['user' => $user, 'stock' => $stock, 'percent' => $shift_percent]);
                            }
                        }
                    }
                    else {
                        self::addCandle($stock, $stock->interval, $user->token);
                    }
                }
            }
        }
        return $result;
    }
}

