<?php

namespace app\commands;

use app\controllers\TinkoffController;
use yii\console\Controller;
use app\helpers\TelegramHelper;

class BotController extends Controller
{
    const CONST_TIME_DELAY_REQUEST = 0.1;
    public function actionStart()
    {
        echo "Bot started.";

        $telegram = new TelegramHelper();

        while (true) {
            $telegram->checkCommands();
            TinkoffController::checkStocks();
            sleep(self::CONST_TIME_DELAY_REQUEST);
        }
    }
}
