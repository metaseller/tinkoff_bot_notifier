<?php

namespace app\commands;

use yii\console\Controller;
use app\controllers\TelegramController;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BotController extends Controller
{
    public function actionStart()
    {
        echo "Bot started.";
        while (true) {
            TelegramController::checkCommands();
        }
    }
}
