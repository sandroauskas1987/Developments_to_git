<?php
class bot
{
    public $peer_id;   //ID пользователя в ВК
    public $sotrud_id; //ID сотрудника в Кб

    public function __construct($event = array())
    {
        $this->peer_id = $event['object']['peer_id'];
        $this->sotrud_id =$this->get_sotrud_id($this->peer_id);
        if ($payload = $event['object']['payload']) {// если объект сообщения содержит объект кнопки - передаём к разбору
            if($this->sotrud_id!=null){
                $result_arr = $this->button_controller($payload, $this->sotrud_id);
                $this->bot_sendMessage($this->peer_id, $result_arr['message'], $result_arr['buttons'],$result_arr['coordinate']);
            }else{
                $result_arr['message']='Простите, но информация не доступна для незарегистрированных пользователей';
                $result_arr['buttons']=buttons_default();
                $result_arr['coordinate']='';
                $this->bot_sendMessage($this->peer_id, $result_arr['message'], $result_arr['buttons'],$result_arr['coordinate']);
            }
        }/* else {// разбор текста сообщения ..  при необходимости.
            //$result_arr = $this->text_controller($event['object']['text']);
            //$this->bot_sendMessage($this->peer_id, $result_arr['message'], $result_arr['buttons'],$result_arr['coordinate']);
        }*/
    }

    function button_controller($json, $user_id)
    {
        $payload_arr = json_decode($json, true);
        try {
            $result=$payload_arr['command']($payload_arr['id'], $user_id);
        } catch (Exception $e) {
            log_error($e);
        }
        return $result;
    }

    public function text_controller($text)
    {
        switch ($text) {
            case  'Привет':
                $message = 'О-о-о, Здравсствуй хозяин, Добби - готов служить Вам. Добби, очень приятно что хозяин с ним поздоровался. ';
                break;
            case  'привет':
                $message = 'О-о-о, Здравсствуй хозяин, Добби - готов служить Вам. Добби, очень приятно что хозяин с ним поздоровался. ';
                break;
            case  'Ghbdtn':
                $message = 'О-о-о, Добби знает эльфийский, это значит - Привет.';
                break;
            case  'пока':
                $message = 'О-о-о, Хозяин подарил Добби носок, теперь добби свободен';
                break;
            case  'Пока':
                $message = 'О-о-о, Хозяин подарил Добби носок, теперь добби свободен';
                break;
            default:
                $message = 'Простите хозяин :(, но Добби не обучен читать.';
                break;
        }
        return ['message' => $message, 'buttons' => buttons_default(),'coordinate'=>''];
    }

    public function bot_sendMessage($user_id, $msg, $but,$coordinate)
    {
        vkApi_messagesSend($user_id, $msg, $but,$coordinate);
    }

//получаем ID сотрудника с КБ по ID телеграмма
    public function get_sotrud_id($peer_id)
    {
        $obj = Api::get_user($peer_id);
        if ($obj->status == true && isset($obj->output->data)) {
            $data = $obj->output->data;
            foreach ($data as $val) {
                if (isset($val->row->id)) {
                    return $val->row->id;
                } else {
                    return null;
                }
            }
        }return null;
    }
}