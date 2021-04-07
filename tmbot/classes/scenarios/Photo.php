<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 19.07.2019
 * Time: 12:02
 */
// Загрузка любых фоток, передача фото через бота
class Photo extends Scenarios
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
            case !array_key_exists('foto_object_uploaded',$data):$this->photo();
                break;
            case !array_key_exists('bind',$data):$this->bind();
                break;
            case !array_key_exists('id',$data):$this->id();
                break;
            case array_key_exists('id',$data):$this->save_photo();
                break;
            default: break;
        }
    }
    // Получение фоток пачками
    public function photo(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('photo',$this->in_arr['message'])){
            $photo=array_pop($this->in_arr['message']['photo']);
            $_SESSION['data']['photo'][]=$photo['file_id'];
            if(array_key_exists('media_group_id',$this->in_arr['message'])){
                $id_mediagrop=(string)$this->in_arr['message']['media_group_id'];
                $_SESSION[$id_mediagrop][]=(string)$this->message_id;
                if(array_key_exists($id_mediagrop,$_SESSION) && count($_SESSION[$id_mediagrop])>1){
                    TelegrammApi::editMessageText($this->chat_id,array_shift($_SESSION[$id_mediagrop]),"получено..");
                }
            }
            $message="Загружено - ".count($_SESSION['data']['photo'])." фото\n<i> Пришлите мне еще Фото или нажмите продолжить </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_foto_object');
            die();
        }else{
            if(is_array($this->data) && array_key_exists('photo_uploaded',$this->data)){
                $_SESSION['data']['foto_object_uploaded']=$this->data['photo_uploaded'];
                if(!array_key_exists('photo',$_SESSION['data'])){
                    $_SESSION['data']['photo']=null;
                }
                $this->script_by_steps();
            }else {
                $message="Загружено - ".count($_SESSION['data']['photo'])." фото\n<i> Пришлите мне еще Фото или нажмите продолжить </i>";
                TelegrammApi::sendMessage($this->chat_id, $message, 'key_foto_object');
            }
            die();
        }
    }
    // выбрать что за файл
    public function bind(){
        if(is_array($this->data) && array_key_exists('bind',$this->data)){
            $_SESSION['data']['bind']=$this->data['bind'];
            $this->script_by_steps();
        }else{
            $message="\n<i> Что это за фото? </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_files');
            die();
        }
    }
    // выбрать что за файл
    public function id(){
        if(is_array($this->data) && array_key_exists('id',$this->data)){
            $_SESSION['data']['id']=$this->data['id'];
            $this->script_by_steps();
        }else{
             TelegrammApi::editMessageText($this->chat_id,$this->message_id,"Выберите замер к которому прикрепить файл/ы",'file_zamer_list',$_SESSION['data']['sotrud_id']);
            die();
        }
    }
    public function save_photo(){
        TelegrammApi::sendMessage($this->chat_id," Сохраняю.... ",'key_clear');
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

        $bind=$_SESSION['data']['bind'];
        $zamer_id=$_SESSION['data']['id'];
        $photoname=$_SESSION['data']['photoname'];
        if($bind && $zamer_id>1 && strlen($photoname)>1){
            if($bind=='zamerlist'){
               $result= KbApi::file_zamer_list_add($zamer_id,$photoname);
            }elseif ($bind=='zamerphoto'){
                $result=KbApi::set_object_foto($zamer_id,$photoname);
            }
        }
        if($result->status==true){
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            $message = "Успешно. Фото отправлены в Кб к замеру №$zamer_id";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
        }elseif ($result->status==false){
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            $message = "Ошибка.$result->status_message \r\n Перешлите сообщение администратору";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
        }
        die();
    }
    public function out()
    {
        unset($_SESSION['Script_name']);
        unset($_SESSION['data']);
        $message = 'Выберите пункт меню.';
        TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
    }
}