<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 05.08.2019
 * Time: 10:21
 */

class To_send_installation extends Scenarios
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
            case !array_key_exists('consent_to_add',$data):$this->save_photo();
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
            $message="Загружено - ".count($_SESSION['data']['photo_object'])." фото монтажа\n<i> Пришлите еще или нажмите \"Продолжить\" </i>";
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
                $message="\n<i>ЗП №{$_SESSION['data']['montaj_id']} Пришлите Фото монтажа или нажмите \"Продолжить\" </i>";
                TelegrammApi::sendMessage($this->chat_id, $message, 'key_foto_object');
            }
            die();
        }
    }
    public function save_photo(){
        TelegrammApi::sendMessage($this->chat_id," Сохраняю.... ",'key_clear');
        try {
            if (count($_SESSION['data']['photo_object'])>0) {
                foreach ($_SESSION['data']['photo_object'] as $fileid) {
                    $file_name = TelegrammApi::getFile($fileid);
                    if ($file_name != null) {
                        if (array_key_exists('photoname', $_SESSION['data'])) {
                            $_SESSION['data']['photoname'] .= "\r\n" . $file_name;
                        } else $_SESSION['data']['photoname'] = $file_name;
                    }
                }
            }else{
                TelegrammApi::sendMessage($this->chat_id," Нет изображений для сохранения ",'key_clear');
                self::out();
                exit;
            }
        }catch (Exception $e){
            $err="error: ".$e;
            TelegrammApi::sendMessage($this->chat_id,$err);
        }

        $status=KbApi::save_photo_montaj($_SESSION);
        if($status==true){
            TelegrammApi::sendMessage($this->chat_id," Успещно схоранено.",'key_clear');
            self::out();
        }else{
            TelegrammApi::sendMessage($this->chat_id,"Неудача ..",'key_clear');
        }

        die();
    }
    public function out()
    {
        unset($_SESSION['Script_name']);
        unset($_SESSION['data']);
        $message = 'Выберите пункт меню.';
        TelegrammApi::sendMessage($this->chat_id,$message,'key_montaj_menu');
    }

}