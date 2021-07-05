<?php
namespace app\controllers;
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


class TinkoffController extends Controller
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
        if ($candle instanceof TICandle)
        {
            $shift = $candle->getOpen() * $percent / 100;
            if (abs($candle->getOpen() - $candle->getClose()) > $shift)  return true;
                return false;
        }

    }


    public static function checkStocks($ticker = "SBER",$interval = TIIntervalEnum::WEEK)
    {

    }
}
