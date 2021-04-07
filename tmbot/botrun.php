<?php
// Модули работы с Клиентской базой
require_once(dirname(__FILE__).'/classes/KbApi/KbApi.php');
require_once(dirname(__FILE__).'/classes/KbApi/KbApiComand.php');
require_once(dirname(__FILE__).'/classes/KbApi/KbApiComandfilter.php');
require_once(dirname(__FILE__).'/classes/KbApi/KbApiCurl.php');
// Логер
require_once(dirname(__FILE__).'/classes/Logger.php');
// Модули работы с Телеграммом
require_once(dirname(__FILE__).'/classes/TelegrammApi/TelegrammApi.php');
require_once(dirname(__FILE__).'/classes/TelegrammApi/InlineKeyboardMarkup.php');
require_once(dirname(__FILE__).'/classes/TelegrammApi/RaplyKeyboardMarkup.php');
require_once(dirname(__FILE__).'/classes/TelegrammApi/TelegrammBot.php');
// Сценарии работы бота.
require_once(dirname(__FILE__).'/classes/scenarios/Scenarios.php');
require_once(dirname(__FILE__).'/classes/scenarios/Send_metering.php');
require_once(dirname(__FILE__).'/classes/scenarios/To_send_installation.php');
require_once(dirname(__FILE__).'/classes/scenarios/NewDirectSale.php');
require_once(dirname(__FILE__).'/classes/scenarios/Objects_near.php');
require_once(dirname(__FILE__).'/classes/scenarios/Visit_to_object.php');
require_once(dirname(__FILE__).'/classes/scenarios/Photo.php');

$incoming_message_object= file_get_contents('php://input');
$in_arr=json_decode($incoming_message_object,true);

// запустим сессию с именем id_пользователя который обращается к боту
if(isset($in_arr['message'])) {
    $user = isset($in_arr['message']['from']['id']) ? $in_arr['message']['from']['id'] : false;
}elseif (isset($in_arr['callback_query'])){
    $user = isset($in_arr['callback_query']['from']['id'])?$in_arr['callback_query']['from']['id']:false;
}

if($user) {
    startSession($user);
}

/**
 * @param $user
 * @return bool
 */
function startSession($user) {
    if ( session_id()==$user ) return true;
    else {
        session_id($user);
        return session_start();
    }
}

Logger::$PATH=(__DIR__).'/Logs';
Logger::getLogger('info')->log($in_arr);
// Точка входа.. запуск бота
$Bot = new TelegrammBot($in_arr,$incoming_message_object);
echo "Bot is run! Big happy!";