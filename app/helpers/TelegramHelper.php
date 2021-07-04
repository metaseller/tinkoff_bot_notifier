<?php

namespace app\helpers;

class TelegramHelper
{
    private $lastMessageId = 0;
    private $botToken = TELEGRAM_TOKEN;


    public function sendMessage($textMessage = "This is my message !!!", $chatId = 442912517){
        $website = "https://api.telegram.org/bot" . $this->botToken;
        $params = [
            'chat_id' => $chatId,
            'text' => $textMessage,
        ];
        $ch = curl_init($website . '/sendMessage');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
        $result = curl_exec($ch);
        curl_close($ch);

    }

    public function getUpdates(){
        echo "\ngetUpdates\n";

        $website = "https://api.telegram.org/bot" . $this->botToken;

        $ch = curl_init($website . '/getUpdates?offset=-1');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);

        $result = $result->result[0];

        var_dump($result);
        if($this->isAlreadyViewed($result)){
            echo $result->message->text;
        }else{

            $this->sendMessage("Сообщение получино и обработано", $result->message->from->id);
            $this->lastMessageId = $result->update_id;
            //$this->foundCommands($result->message->text);
            echo $result->message->text;

            return [
                    "id_telegram" => $result->message->from->id,
                    "text" => $result->message->text
                   ];
        }
    }

    public function isAlreadyViewed($result){
        // todo вытащить из базы данных инфу об этом сообщении, обработали ли мы его когда то?
        // и проверка человека, новый ли он?

        return $result->update_id == $this->lastMessageId;
    }



}


