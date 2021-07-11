<?php

namespace app\commands;

use app\helpers\TinkoffHelper;
use app\models\Stock;
use app\models\User;
use yii\console\Controller;
use app\helpers\TelegramHelper;

include '../env.php';

//todo: переработать метод interpretCommand (слишком громоздкий, здесь надо применить ООП ¯\_(ツ)_/¯)

class BotController extends Controller
{
    const CONST_TIME_DELAY_REQUEST = 1;
    const INTERVALS = ['1min', '2min', '3min', '5min', '10min', '15min', '30min', 'hour', 'day', 'week', 'month'];

    public function interpretCommand($telegram, $tinkoff, $command) {
        if ($command != null) {
            if (!User::find()->where(['iduser' => $command['id_telegram']])->exists()) {
                if ($command['command_name'] == 'start') {
                    $user = new User();
                    $user->iduser = $command['id_telegram'];
                    $user->save();
                    $telegram->sendMessage('Вы зарегистрированы. Напишите /help, чтобы получить список команд.', $command['id_telegram']);
                }
                else {
                    $telegram->sendMessage('Вы не зарегистрированы. Напишите /start, чтобы зарегистрироваться.', $command['id_telegram']);
                }
            }
            else {
                $user = User::findOne(['iduser' => $command['id_telegram']]);
                if ($command['command_name'] == 'start') {
                    $telegram->sendMessage('Вы уже зарегистрированы. Напишите /help, чтобы получить список команд.', $command['id_telegram']);
                }
                elseif ($command['command_name'] == 'stop') {
                    $user->delete();
                }
                elseif ($command['command_name'] == 'token' && count($command['parameters']) == 1) {
                    $token = $command['parameters'][0];
                    $tinkoff->isTokenLegit($token);
                    if ($tinkoff->isTokenLegit($token)) {
                        $user->token = $token;
                        $user->save();
                        $telegram->sendMessage('Токен получен.', $command['id_telegram']);
                    }
                    else {
                        $telegram->sendMessage('Некорректный токен.', $command['id_telegram']);
                    }
                }
                elseif ($command['command_name'] == 'help') {
                    $telegram->sendMessage('/token [Tinkoff Invest токен] - передать свой токен Tinkoff Invest API. Идущие далее команды работают только в том случае, если вы передали токен.' .PHP_EOL.
                        '/addstock [ticker акции] - отслеживать акцию' .PHP_EOL.
                        '/removestock [ticker акции] - перестать отслеживать акцию' .PHP_EOL.
                        '/stocks - получить список отслеживаемых акций'.PHP_EOL.
                        '/interval [ticker акции] [интервал] - поменять интервал получения новых свечей (допустимые интервалы:  1min, 2min, 3min, 5min, 10min, 15min, 30min, hour, day, week, month)'.PHP_EOL.
                        '/priceshift [ticker акции] [процент] - поменять процент "критического" сдвига'.PHP_EOL.
                        '/period [ticker акции] [период] - поменять период средней скользящей', $command['id_telegram']);
                }
                elseif ($command['command_name'] == 'addstock' && count($command['parameters']) == 1 && !$user->token == null) {
                        $ticker = $command['parameters'][0];
                        if ($tinkoff->isTickerExist($user->token, $ticker)) {
                            $stock = new Stock();
                            $stock->ticker = $ticker;
                            $stock->figi = $tinkoff->getFigiByTicker($user->token, $ticker);
                            $stock->user_id = $user->id;
                            $stock->save();
                            $telegram->sendMessage('Акция теперь отслеживается.', $command['id_telegram']);
                        }
                        else{
                            $telegram->sendMessage('Такой акции не существует.', $command['id_telegram']);
                        }
                    }
                elseif ($command['command_name'] == 'removestock' && count($command['parameters']) == 1 && !$user->token == null) {
                        $ticker= $command['parameters'][0];
                        $stock = Stock::findOne(['user_id' => $user->id, 'ticker' => $ticker]);
                        if ($stock) {
                            $stock->delete();
                            $telegram->sendMessage('Акция больше не отслеживается.', $command['id_telegram']);
                        }
                        else{
                            $telegram->sendMessage('Вы не отслеживаете такую акцию.', $command['id_telegram']);
                        }
                    }
                elseif ($command['command_name'] == 'stocks' && count($command['parameters']) == 0 && !$user->token == null) {
                        $message = 'Отслеживаемые вами акции: ';
                        $stocks = Stock::find()->where(['user_id' => $user->id])->asArray()->all();
                        if (!empty($stocks)) {
                            foreach ($stocks as $stock) {
                                $message = $message . PHP_EOL . $stock['ticker'] . ' интервал: ' . $stock['interval'] .
                                ' сдвиг цены: '. $stock['change']. '% период скользящей средней: ' . $stock['period'];
                            }
                            $telegram->sendMessage($message, $command['id_telegram']);
                        }
                        else {
                            $telegram->sendMessage('Вы не отслеживаете акции.', $command['id_telegram']);
                        }
                    }
                elseif ($command['command_name'] == 'priceshift' && count($command['parameters']) == 2 && !$user->token == null) {
                        $ticker = $command['parameters'][0];
                        $change = $command['parameters'][1];
                        $stock = Stock::findOne(['user_id' => $user->id, 'ticker' => $ticker]);
                        if ($stock) {
                            $stock->change = $change;
                            $stock->save();
                            $telegram->sendMessage('Присвоен новый сдвиг цены.', $command['id_telegram']);
                        }
                        else{
                            $telegram->sendMessage('Вы не отслеживаете такую акцию.', $command['id_telegram']);
                        }
                    }
                elseif ($command['command_name'] == 'period' && count($command['parameters']) == 2 && !$user->token == null) {
                        $ticker = $command['parameters'][0];
                        $period = $command['parameters'][1];
                        $stock = Stock::findOne(['user_id' => $user->id, 'ticker' => $ticker]);
                        if ($stock) {
                            if ($period >= 2) {
                                $stock->period = $period;
                                $stock->save();
                                $telegram->sendMessage('Присвоен новый период.', $command['id_telegram']);
                            }
                            else {
                                $telegram->sendMessage('Период должен быть больше или равен 2.', $command['id_telegram']);
                            }
                        }
                        else{
                            $telegram->sendMessage('Вы не отслеживаете такую акцию.', $command['id_telegram'] && !$user->token == null);
                        }
                    }
                elseif ($command['command_name'] == 'interval' && count($command['parameters']) == 2 && !$user->token == null) {
                        $ticker = $command['parameters'][0];
                        $interval = $command['parameters'][1];
                        $stock = Stock::findOne(['user_id' => $user->id, 'ticker' => $ticker]);
                        if ($stock) {
                            if (in_array($interval, self::INTERVALS)) {
                                $stock->interval = $interval;
                                $stock->save();
                                $telegram->sendMessage('Присвоен новый интервал.', $command['id_telegram']);
                            }
                            else {
                                $telegram->sendMessage('Недопустимый интервал.', $command['id_telegram']);
                            }
                        }
                        else{
                            $telegram->sendMessage('Вы не отслеживаете такую акцию.', $command['id_telegram']);
                        }
                    }
                else {
                    $telegram->sendMessage('Команда не распознана, или вы не передали токен.', $command['id_telegram']);
                }
            }
        }
    }

    public function actionStart()
    {
        echo "Bot started.";

        $telegram = new TelegramHelper();
        $tinkoff = new TinkoffHelper();

        while (true) {
            $command = $telegram->getCommand();
            $this->interpretCommand($telegram, $tinkoff, $command);
            $results = $tinkoff->checkStocks();
            foreach ($results as $result) {
                $message = 'Цена акции '. $result['stock']->ticker . ' изменилась на '
                    . round($result['percent'], 2) . '% за интервал ' . $result['stock']->interval .'!';
                $telegram->sendMessage($message, $result['user']->iduser);
            }
            sleep(self::CONST_TIME_DELAY_REQUEST);
        }
    }
}
