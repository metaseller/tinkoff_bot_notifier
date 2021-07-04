<?php

namespace app\commands;

use app\controllers\TinkoffController;
use yii\console\Controller;
use app\helpers\TelegramHelper;

include '../env.php';

class BotController extends Controller
{
    const CONST_TIME_DELAY_REQUEST = 0.1;

    public function interpretCommand($telegram, $command) {
        # todo сделать проверку на нового пользователя
        # todo сделать комманды addstock (добавление новой акции для отслеживания), stocks (вывод отслеживаемых акций),
        # interval (изменение интервала для отслеживаемой акции), priceshift (изменение "критического" сдвига цены акции)
        if ($command != null) {
            if ($command['command_name'] == 'start') {
                $telegram->sendMessage('Вы зарегистрированы.', $command['id_telegram']);
            }
            if ($command['command_name'] == 'token' && $command['parameters'] != null) {
                $token = $command['parameters'][0];
                $telegram->sendMessage('Токен получен.', $command['id_telegram']);
            }
        }
    }

    public function actionStart()
    {
        echo "Bot started.";

        $telegram = new TelegramHelper();

        while (true) {
            $command = $telegram->getCommand();
            $this->interpretCommand($telegram, $command);

            TinkoffController::checkStocks();
            sleep(self::CONST_TIME_DELAY_REQUEST);
        }
    }
}
