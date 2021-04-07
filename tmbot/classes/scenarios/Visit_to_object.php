<?php
/**
 * Created by PhpStorm.
 * User: Админ
 * Date: 24.07.2019
 * Time: 13:56
 */

class Visit_to_object extends Scenarios
{
    public function __construct($in_arr)
    {
        parent::__construct($in_arr);
    }

    public function script_by_steps(){
        $data=array_key_exists('data',$_SESSION)?$_SESSION['data']:array();
        switch (is_array($data)){
            case !array_key_exists('photo',$data):$this->photo();
                break;
            case !array_key_exists('photo_uploaded',$data):$this->photo();
                break;
            case !array_key_exists('stage_of_construction',$data):$this->stage_of_construction();
                break;
            case !array_key_exists('activity',$data):$this->activity();
                break;
            case !array_key_exists('marketing',$data):$this->marketing();
                break;
            case !array_key_exists('last_visit',$data):$this->last_visit();
                break;
            case !array_key_exists('comments',$data):$this->comments();
                break;
            case !array_key_exists('consent_to_add',$data):$this->consent_to_add();
                break;
            case array_key_exists('consent_to_add',$data):$this->new_direct_sale_write();
                break;
            default: break;
        }
    }
    public function photo(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('photo',$this->in_arr['message'])){
            $photo=array_pop($this->in_arr['message']['photo']);
            $_SESSION['data']['photo'][]=$photo['file_id'];
            $id_mediagrop='';
            if(array_key_exists('media_group_id',$this->in_arr['message'])){
                $id_mediagrop.=(string)$this->in_arr['message']['media_group_id'];
                $_SESSION[$id_mediagrop][]=(string)$this->message_id;
                if(array_key_exists($id_mediagrop,$_SESSION) && count($_SESSION[$id_mediagrop])>1){
                    TelegrammApi::editMessageText($this->chat_id,array_shift($_SESSION[$id_mediagrop]),"получено..");
                }
            }
            $message="Загружено - ".count($_SESSION['data']['photo'])." фото\n<i> Пришлите мне еще Фото или нажмите продолжить </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_foto_object',null,null,$this->message_id);
            die();
        }else{
            if(is_array($this->data) && array_key_exists('photo_uploaded',$this->data)){
                $_SESSION['data']['photo_uploaded']=$this->data['photo_uploaded'];
                if(!array_key_exists('photo',$_SESSION['data'])){
                    $_SESSION['data']['photo']=null;
                }
                $this->script_by_steps();
            }else {
                $message=$this->textmessegeobject()."\n<i> Пришлите мне Фото или нажмите Продолжить </i>";
                TelegrammApi::sendMessage($this->chat_id, $message, 'key_foto_object',null,null,$this->message_id);
            }
            die();
        }
    }
    public function stage_of_construction(){
        if(is_array($this->data) && array_key_exists('stage_of_construction',$this->data)){
            $_SESSION['data']['stage_of_construction']=$this->data['stage_of_construction'];
            $this->script_by_steps();
        }else{
            $message=$this->textmessegeobject()."\n<i> Выберите этап строительства </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'stage_of_construction',null,null,$this->message_id);
            die();
        }
    }
    public function activity(){
        if(is_array($this->data) && array_key_exists('activity',$this->data)){
            $_SESSION['data']['activity']=$this->data['activity'];
            $this->script_by_steps();
        }else{
            $message=$this->textmessegeobject()."\n<i> Выберите активность на объекте </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'activity',null,null,$this->message_id);
            die();
        }
    }
    public function marketing(){
        if(is_array($this->data) && array_key_exists('marketing',$this->data)){
            $_SESSION['data']['marketing']=$this->data['marketing'];
            $this->script_by_steps();
        }else{
            $message=$this->textmessegeobject()."\n<i> Выберите действие с рекламой </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'marketing',null,null,$this->message_id);
            die();
        }
    }
    public function last_visit(){
        $_SESSION['data']['last_visit']=(string)date("d-m-Y H:i");
        $this->script_by_steps();
    }
    public function comments(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('text',$this->in_arr['message'])){
            $_SESSION['data']['comments']=$this->in_arr['message']['text'];
            $this->script_by_steps();
        }else{
            $message=$this->textmessegeobject()."\n<i> Напишите коментарий </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_clear',null,null,$this->message_id);
            die();
        }
    }
    public function consent_to_add(){
        if(is_array($this->data) && array_key_exists('consent_to_add',$this->data)){
            if ($this->data['consent_to_add']=='true') {
                $_SESSION['data']['consent_to_add'] = $this->data['consent_to_add'];
                $this->script_by_steps();
            }
        }else{
            $message=$this->textmessegeobject()."\n<i> Все поля заполнены!\n нажмите - Сохранить в Кб</i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'consent_to_add',null,null,$this->message_id);
            die();
        }
    }
    public function new_direct_sale_write(){
        TelegrammApi::sendMessage($this->chat_id," Обновляю.... ",'key_clear',null,null,$this->message_id);
        try {
            if (count($_SESSION['data']['photo'])>0) {
                foreach ($_SESSION['data']['photo'] as $fileid) {
                    $file_name = TelegrammApi::getFile($fileid);
                    if ($file_name != null) {
                        if (array_key_exists('photoname', $_SESSION['data'])) {
                            $_SESSION['data']['photoname'] .= "\r\n" . $file_name;
                        } else $_SESSION['data']['photoname'] = $file_name;
                    }
                }
            }
        }catch (Exception $e){
            $err="error: ".$e;
            TelegrammApi::sendMessage($this->chat_id,$err);
        }

        $status=KbApi::update_direct_sale($_SESSION);
        if($status==true){
            TelegrammApi::sendMessage($this->chat_id," Объект успешно обновлён\n переключаюсь в меню ",'key_clear',null,null,$this->message_id);
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            $message = 'Выберите пункт меню.';
            TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
        }else{
            TelegrammApi::sendMessage($this->chat_id,"Неудача ..",'key_clear');
        }

        die();
    }
    public function textmessegeobject(){
        $data=array_key_exists('data',$_SESSION)?$_SESSION['data']:array();
        $textmessege = "|<b>Объект (обновление)</b>";
        if(is_array($data)) {
            $textmessege .= array_key_exists('object_id', $data) ? "|<b> №: </b>" . $data['object_id'] . "\n" : "\n";
            $textmessege .= array_key_exists('photo', $data) ? "|<b>Фото: </b> Загружено -" . count($data['photo']) . " шт. \n" : "";
            $textmessege .= array_key_exists('stage_of_construction', $data) ? "|<b>Этап строительства: </b>" . $data['stage_of_construction'] . "\n" : "";
            $textmessege .= array_key_exists('activity', $data) ? "|<b>Активность: </b>" . $data['activity'] . "\n" :  "";
            $textmessege .= array_key_exists('marketing', $data) ? "|<b>Реклама: </b>" . $data['marketing'] . "\n" :  "";
            $textmessege .= array_key_exists('last_visit', $data) ? "|<b>Последний визит: </b>" . $data['last_visit'] . "\n" :  "";
            $textmessege .= array_key_exists('comments', $data) ? "|<b>Комментарии: </b>" . $data['comments'] . "\n" :  "";
        }
        return $textmessege;
    }
    public function out()
    {
        unset($_SESSION['Script_name']);
        unset($_SESSION['data']);
        $message = 'Выберите пункт меню.';
        TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
    }

}