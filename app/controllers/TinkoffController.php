<?php
namespace app\controllers;
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

define("TOKEN",     "t.XtUKvAmIkeXWviB7brsoJ-2VAQEg4FAcKmDumDltzrnGZJbPFgBsZjYxYiokXYojR7haUBCjUwjWRHew6EWZnw");

class TinkoffController extends Controller
{
    //Инициализация юзера, добавление в БД;
    public static function ActionInitUser(){
    }

    public static function ActionAddTicker(){

    }
    public static function  ActionCheckStocks(){
    }
}
