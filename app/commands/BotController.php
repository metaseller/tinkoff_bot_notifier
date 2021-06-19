<?php

namespace app\commands;

use app\controllers\TinkoffController;
use yii\console\Controller;
use app\controllers\TelegramController;

class BotController extends Controller
{
    public function actionStart()
    {
        echo "Bot started.";
        while (true) {
            TelegramController::checkCommands();
            TinkoffController::checkStocks();
        }
    }
}
