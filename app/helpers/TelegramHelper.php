<?php

namespace app\helpers;

class TelegramHelper
{
    private $lastMessageId = 0;
    private $botToken = TELEGRAM_TOKEN;

    public function getCommand() {

        $message = $this->getUpdates();
        if ($message != null) {
            $matches = explode(' ', $message['text']);
            $command_name = substr($matches[0], 1);
            $parameters = array_slice($matches, 1);
            return ['id_telegram' => $message['id_telegram'], 'command_name' => $command_name, 'parameters' => $parameters];
        }

    }

    public function sendMessage($textMessage = "default message", $chatId) {

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

    public function getUpdates() {

        $website = "https://api.telegram.org/bot" . $this->botToken;
        $ch = curl_init($website . '/getUpdates?offset=-1');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        if (!empty($result->result)) {
            $result = $result->result[0];
            if (!$this->isAlreadyViewed($result)) {
                $this->lastMessageId = $result->update_id;
                if (isset($result->message)) {
                    return [
                        'id_telegram' => $result->message->from->id,
                        'text' => $result->message->text
                    ];
                }
                elseif (isset($result->my_chat_member)) {
                    return [
                        'id_telegram' => $result->my_chat_member->from->id,
                        'text' => '/stop',
                    ];
                }
            }
        }
    }

    public function isAlreadyViewed($result) {
        return $result->update_id == $this->lastMessageId;
    }

}


