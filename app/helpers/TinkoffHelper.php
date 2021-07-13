<?php
namespace app\helpers;

use DateInterval;
use DateTime;
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

    public function isTickerExist($tinkoff_token=TINKOFF_TOKEN, $ticker ="SBER")
    {
        $client = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);
        $all_stocks = $client->getStocks(); //Получаем массив со всеми тикерами.

        foreach ($all_stocks as $stock) {
            if ($stock->getTicker() == $ticker) return true;
        }
        return false;
    }

    public function getFigiByTicker($tinkoff_token=TINKOFF_TOKEN, $ticker ="SBER")
    {
        $client = new TIClient($tinkoff_token, TISiteEnum::SANDBOX);
        $stock = $client->getInstrumentByTicker($ticker);
        return $stock->getFigi();
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
            $date_interval = new DateInterval('PT' . strval($this->toSeconds($interval)) . 'S');
            $new_date = date_sub(new DateTime('now'), $date_interval);
            $now = new DateTime('now');
            $TIcandles = $TIclient->getHistoryCandles($stock->figi, $new_date, $now, $interval);
            $TIcandle = end($TIcandles);
            if ($TIcandle) {
                $candle = new Candle();
                $candle->prcopen = $TIcandle->getOpen();
                $candle->prcclose = $TIcandle->getClose();
                $candle->prcmin = $TIcandle->getLow();
                $candle->prcmax = $TIcandle->getHigh();
                $candle->tradevolume = $TIcandle->getVolume();
                $candle->timeq = $TIcandle->getTime()->format('Y-m-d H:i:s');
                $candle->stock_id = $stock->id;
                $candle->save();
            }
            else {
                $candle = new Candle();
                $candle->timeq = $now->format('Y-m-d H:i:s');
                $candle->stock_id = $stock->id;
                $candle->save();
            }
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

    public function getLatestCandle($stock) {
        $candles = Candle::find()->where(['stock_id' => $stock->id])->all();
        return end($candles);
    }

    public function checkStocks()
    {
        $users = User::find()->all(); // Получаем массив всех юзеров
        $result = [];
        foreach ($users as $user)
        {
            $stocks = Stock::find()->where(['user_id' => $user->id])->all();
            foreach ($stocks as $stock) {
                $sma = 'none';
                if ($stock) {
                    $latest_candle = $this->getLatestCandle($stock);

                    if (!$latest_candle) {
                        self::addCandle($stock, $stock->interval, $user->token);
                        $latest_candle = $this->getLatestCandle($stock);
                        if (self::isPriceShift($latest_candle, $stock->change)) {
                            $shift_percent = ($latest_candle->prcclose / $latest_candle->prcopen - 1) * 100;
                            array_push($result, ['user' => $user, 'stock' => $stock, 'percent' => $shift_percent]);
                        }
                    }
                    $date_now = new DateTime();
                    $date_now->sub($latest_candle->timeq);
                    if ($date_now > date_interval_create_from_date_string($stock->interval)) {
                        self::addCandle($stock, $stock->interval, $user->token);
                        $latest_candle = $this->getLatestCandle($stock);
                        if (self::isPriceShift($latest_candle, $stock->change)) {
                            $shift_percent = ($latest_candle->prcclose / $latest_candle->prcopen - 1) * 100;
                            array_push($result, ['user' => $user, 'stock' => $stock, 'percent' => $shift_percent]);
                        }
                    }
                    if ($candles = Candle::find()->where(['stock_id' => $stock->id])->count() >= $stock->period)
                    {
                        $candles = Candle::find()->where(['stock_id' => $stock->id])->all();
                        $latest_candle = $this->getLatestCandle($stock);
                        $last_n_candles = array_slice(-$stock->period);
                        $average = array_sum($last_n_candles)/count($last_n_candles);
                        if ($latest_candle->prcclose - $average > 0) $sma = 'up';
                        if ($latest_candle->prcclose - $average < 0) $sma = 'down';
                        array_push($result, ['sma' => $sma]);
                    }
                }
            }
        }
        return $result;
    }
}

