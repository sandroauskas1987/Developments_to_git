<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 05.08.2019
 * Time: 10:21
 */

class Send_metering extends Scenarios
{
    /**
     * @param $in_arr
     */
    public function __construct($in_arr)
    {
        parent::__construct($in_arr);
    }
    public function script_by_steps()
    {
        $data=array_key_exists('data',$_SESSION)?$_SESSION['data']:[];
        switch (is_array($data)){
            case !array_key_exists('photo_object',$data):$this->photo_object();
                break;
            case !array_key_exists('photo_object_uploaded',$data):$this->photo_object();
                break;
            case !array_key_exists('photo_zamer_list',$data):$this->photo_zamer_list();
                break;
            case !array_key_exists('photo_zamer_list_uploaded',$data):$this->photo_zamer_list();
                break;
            case !array_key_exists('comments',$data):$this->comments();
                break;
            case !array_key_exists('consent_to_add',$data):$this->consent_to_add();
                break;
            case array_key_exists('consent_to_add',$data):$this->save_photo();
                break;
            default: break;
        }
    }
    public function photo_object(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('photo',$this->in_arr['message'])){
            $photo=array_pop($this->in_arr['message']['photo']);
            $_SESSION['data']['photo_object'][]=$photo['file_id'];
            $id_mediagrop='';
            if(array_key_exists('media_group_id',$this->in_arr['message'])){
                $id_mediagrop.=(string)$this->in_arr['message']['media_group_id'];
                $_SESSION[$id_mediagrop][]=(string)$this->message_id;
                if(array_key_exists($id_mediagrop,$_SESSION) && count($_SESSION[$id_mediagrop])>1){
                    TelegrammApi::editMessageText($this->chat_id,array_shift($_SESSION[$id_mediagrop]),"получено..");
                }
            }
            $message="Загружено - ".count($_SESSION['data']['photo_object'])." фото объекта\n<i> Пришлите мне еще или нажмите продолжить </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_foto_object');
            die();
        }else{
            if(is_array($this->data) && array_key_exists('photo_uploaded',$this->data)){
                $_SESSION['data']['photo_object_uploaded']=$this->data['photo_uploaded'];
                if(!array_key_exists('photo_object',$_SESSION['data'])){
                    $_SESSION['data']['photo_object']=null;
                }
                $this->script_by_steps();
            }else {
                $message="\n<i> Пришлите мне Фото объекта или нажмите Продолжить </i>";
                TelegrammApi::sendMessage($this->chat_id, $message, 'key_foto_object');
            }
            die();
        }
    }
    public function photo_zamer_list(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('photo',$this->in_arr['message'])){
            $photo=array_pop($this->in_arr['message']['photo']);
            $_SESSION['data']['photo_zamer_list'][]=$photo['file_id'];
            $id_mediagrop='';
            if(array_key_exists('media_group_id',$this->in_arr['message'])){
                $id_mediagrop.=(string)$this->in_arr['message']['media_group_id'];
                $_SESSION[$id_mediagrop][]=(string)$this->message_id;
                if(array_key_exists($id_mediagrop,$_SESSION) && count($_SESSION[$id_mediagrop])>1){
                    TelegrammApi::editMessageText($this->chat_id,array_shift($_SESSION[$id_mediagrop]),"получено..");
                }
            }
            $message="Загружено - ".count($_SESSION['data']['photo_zamer_list'])." Замерных лист/ов\n<i> Пришлите мне еще или нажмите продолжить </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_foto_zamer_list');
            die();
        }else{
            if(is_array($this->data) && array_key_exists('photo_zamer_list_uploaded',$this->data)){
                $_SESSION['data']['photo_zamer_list_uploaded']=$this->data['photo_zamer_list_uploaded'];
                if(!array_key_exists('photo_zamer_list',$_SESSION['data'])){
                    $_SESSION['data']['photo_zamer_list']=null;
                }
                $this->script_by_steps();
            }else {
                $message="\n<i> Пришлите мне Фото замерного листа или нажмите Продолжить </i>";
                TelegrammApi::sendMessage($this->chat_id, $message, 'key_foto_zamer_list');
            }
            die();
        }
    }
    public function comments(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('text',$this->in_arr['message'])){
            $_SESSION['data']['comments']=$this->in_arr['message']['text'];
            $this->script_by_steps();
        }else{
            $message="\n<i> Напишите коментарий </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_clear');
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
            $message="\n<i> Все поля заполнены!\n нажмите - Сохранить в Кб</i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'consent_to_add');
            die();
        }
    }
    public function save_photo(){
        TelegrammApi::sendMessage($this->chat_id," Сохраняю.... ",'key_clear');
        $zamer_id=$_SESSION['data']['zamer_id'];
        $comment=$_SESSION['data']['comments'];
        $errormsg='';
        try {
            if (count($_SESSION['data']['photo_object'])>0) {
                $photo_obj='';
                foreach ($_SESSION['data']['photo_object'] as $fileid) {
                    $file_name = TelegrammApi::getFile($fileid);
                    if ($file_name != null) {
                        if (strlen($photo_obj)>1) {
                            $photo_obj .= "\r\n" . $file_name;
                        } else $photo_obj = $file_name;
                    }
                }
                $result=KbApi::set_object_foto($zamer_id,$photo_obj,$comment);
                if($result->status==true) $comment='';
                if($result->status==false) $errormsg.="\r\n" .$result->status_message;
            }
            if (count($_SESSION['data']['photo_zamer_list'])>0) {
                $zamer_list='';
                foreach ($_SESSION['data']['photo_zamer_list'] as $fileid) {
                    $file_name = TelegrammApi::getFile($fileid);
                    if ($file_name != null) {
                        if (strlen($zamer_list)>1) {
                            $zamer_list .= "\r\n" . $file_name;
                        } else $zamer_list = $file_name;
                    }
                }

                $result2= KbApi::file_zamer_list_add($zamer_id,$zamer_list,$comment);
                if($result2->status==false) $errormsg.="\r\n" .$result2->status_message;
            }
        }catch (Exception $e){
            $err="error: ".$e;
            TelegrammApi::sendMessage('451029189',json_encode($err));
        }

        if(strlen($errormsg)>1){
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            TelegrammApi::sendMessage('451029189',"Ошибки:" .json_encode($errormsg)." \r\n Перешлите сообщение администратору",'key_default_menu');
        }else{
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            $message = "Успешно. Фото отправлены в Кб к замеру №$zamer_id";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
        }
        die();
    }
    public function out()
    {
        unset($_SESSION['Script_name']);
        unset($_SESSION['data']);
        $message = 'Выберите пункт меню.';
        TelegrammApi::sendMessage($this->chat_id,$message,'key_sotrud_menu');
    }

}