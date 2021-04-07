<?php
define('SECRET_KEY', '************************');            // Секретный ключь
define('CALLBACK_API_EVENT_CONFIRMATION', 'confirmation');   // Подтверждение сервера
define('CALLBACK_API_EVENT_MESSAGE_NEW', 'message_new');     // входящее сообщение
define('CALLBACK_API_EVENT_MESSAGE_REPLY', 'message_reply'); // новое исходящее сообщение
define('CALLBACK_API_EVENT_MESSAGE_EDIT', 'message_edit');   // редактирование сообщения
define('CALLBACK_API_EVENT_MESSAGE_ALLOW', 'message_allow'); // подписка на сообщения от сообщества
define('CALLBACK_API_EVENT_MESSAGE_DENY', 'message_deny');   // новый запрет сообщений от сообщества
define('CALLBACK_API_EVENT_PHOTO_NEW', 'photo_new');         // добавление фотографии

require_once 'config.php';
require_once 'global.php';

require_once 'api/vk_api.php';
require_once 'api/yandex_api.php';

require_once 'bot/bot.php';
require_once 'bot/bot_buttons.php';
require_once 'bot/bot_command.php';

require_once 'KbApi/KbApi.php';
require_once 'KbApi/KbApiComand.php';
require_once 'KbApi/KbApiComandfilter.php';
require_once 'KbApi/KbApiCurl.php';


if (!isset($_REQUEST)) {
  exit;
}

callback_handleEvent();


function callback_handleEvent() {
  $event = _callback_getEvent();
    if($event['secret']==SECRET_KEY) {
        try {
            switch ($event['type']) {

                case CALLBACK_API_EVENT_CONFIRMATION:
                    _callback_handleConfirmation();
                    break;

                case CALLBACK_API_EVENT_MESSAGE_NEW:
                    $Bot=new bot($event);
                    break;

                default:
                    _callback_response('Unsupported event');
                    break;
            }
        } catch (Exception $e) {
            log_error($e);
        }
    }
    _callback_okResponse();
}

function _callback_getEvent() {
  return json_decode(file_get_contents('php://input'), true);
}

function _callback_handleConfirmation() {
  _callback_response(CALLBACK_API_CONFIRMATION_TOKEN);
}

function _callback_okResponse() {
  _callback_response('ok');
}

function _callback_response($data) {
  echo $data;
  exit();
}


