<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 05.08.2019
 * Time: 10:26
 */
// Сценарий загрузки изображений по замеру, замерный лист и фото объектов
class Scenarios
{
    public $in_arr;
    public $chat_id;
    public $user_id;
    public $text;
    public $data=null;
    public $message_id;

    /**
     * @param $in_arr
     */
    public function __construct($in_arr)
    {
        //TelegrammApi::chat_clear();
        if (array_key_exists('message', $in_arr)) {
            $this->in_arr=$in_arr;
            $this->chat_id = $in_arr['message']['chat']['id'];
            $this->user_id = $in_arr['message']['from']['id'];
            $this->message_id = $in_arr['message']['message_id'];
            $this->text =array_key_exists('text', $in_arr['message'])? $in_arr['message']['text']:'';

        } elseif (array_key_exists('callback_query', $in_arr)) {
            $this->in_arr=$in_arr;
            $this->chat_id = $in_arr['callback_query']['message']['chat']['id'];
            $this->user_id = $in_arr['callback_query']['from']['id'];

            $params=explode("=",$in_arr['callback_query']['data']);
            $this->data =[$params[0]=>$params[1]];

            $this->message_id = $in_arr['callback_query']['message']['message_id'];
            if($in_arr['callback_query']['message']['text']){
                TelegrammApi::editMessageText($this->chat_id,$this->message_id,$in_arr['callback_query']['message']['text']);
            }
            //TelegrammApi::editMessageText($this->chat_id,$this->message_id,'Выбран №'.$params[1]);
            //TelegrammApi::deleteMessage($this->chat_id,$this->message_id);
        }
        switch($this->text){
            case 'отмена':
                $this->out();
                die();
                break;
            case 'Отмена':
                $this->out();
                die();
                break;
            case 'Отмена 	\xE2\x86\xA9':
                $this->out();
                die();
                break;
        }


        if(is_array($this->in_arr) && array_key_exists('Script_name',$_SESSION)){
            $this->script_by_steps();
        }
    }
    // пошаговый сценарий взаимодействия
    public function script_by_steps(){ }
    public function out(){}


}