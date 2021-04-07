<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 19.07.2019
 * Time: 12:02
 */
// сценарий показа объектов рядом, для прямых продаж.
class Objects_near extends Scenarios
{
    /**
     * @param $in_arr
     */
    public function __construct($in_arr)
    {
        parent::__construct($in_arr);
    }

    public function script_by_steps(){
        $data=array_key_exists('data',$_SESSION)?$_SESSION['data']:[];
        switch (is_array($data)){
            case !array_key_exists('location',$data):$this->location();
                break;
            case !array_key_exists('objects_nearby',$data):$this->objects_nearby();
                break;
            case array_key_exists('objects_nearby',$data):$this->get_the_objects_by_distance();
                break;
            default: break;
        }
    }
    // координаты пользователя
    public function location(){
        if(array_key_exists('message',$this->in_arr) && array_key_exists('location',$this->in_arr['message'])){
            $_SESSION['data']['location']=$this->in_arr['message']['location'];
            $this->script_by_steps();
        }else{
            $message="\n<i> Пришлите мне координаты </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_clear');
            die();
        }
    }
    // выбрать расстояние
    public function objects_nearby(){
        if(is_array($this->data) && array_key_exists('objects_nearby',$this->data)){
            $_SESSION['data']['objects_nearby']=$this->data['objects_nearby'];
            $this->script_by_steps();
        }else{
            $message="\n<i> Выберите удалённость объектов </i>";
            TelegrammApi::sendMessage($this->chat_id,$message,'key_objects_nearby');
            die();
        }
    }

    // Объекты рядом - список объевтов из Кб
    public function get_the_objects_by_distance(){
        if(array_key_exists('data',$_SESSION)){
            if (array_key_exists('objects_nearby',$_SESSION['data'])) {
                $distanse = $_SESSION['data']['objects_nearby'];
            }
            if(array_key_exists('location',$_SESSION['data'])) {
                $latitude2 = $_SESSION['data']['location']['latitude'];
                $longitude2 = $_SESSION['data']['location']['longitude'];
            }
        }

        $komand=new KbApiComand('read',970,['id','f18200','f18290','f18300']);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            foreach ($result->output->data as $data) {
                if (isset($data->row->f18290) && isset($data->row->f18300) && isset($latitude2) && isset($longitude2)) {
                    $latitude = $data->row->f18290;
                    $longitude = $data->row->f18300;
                    if($distanse=='all') {
                        TelegrammApi::sendMessage($this->chat_id, "<b>Объект №</b>" . $data->row->id . "\n<b>Адрес: </b>" . $data->row->f18200,'key_objects',$data->row->id);
                    }elseif ($distanse > $calculated_distance=self::getDistance($latitude2, $longitude2, $latitude, $longitude)) {
                        TelegrammApi::sendMessage($this->chat_id, "<b>Объект №</b>" . $data->row->id . "\n<b>Адрес: </b>" . $data->row->f18200."\n<b>Расстояние до объекта: </b> ".round($calculated_distance)." м.",'key_objects',$data->row->id);
                    }
                } else {
                    TelegrammApi::sendMessage($this->chat_id, 'Ошибки в координатах', null, null, null, $this->message_id);
                }
            }
            unset($_SESSION['Script_name']);
            unset($_SESSION['data']);
            TelegrammApi::sendMessage($this->chat_id,"Вот, пожалуйста.",'key_direct_sale_menu');
            die();
        }else {
            TelegrammApi::sendMessage($this->chat_id, 'Координаты не удалось получить. \n'.$result->status_message, null, null, null, $this->message_id);
            die();
        }

    }
    // Расстояние в метрах между двумя точками
    public function getDistance($lat1, $lon1, $lat2, $lon2) {
        $lat1 *= M_PI / 180;
        $lat2 *= M_PI / 180;
        $lon1 *= M_PI / 180;
        $lon2 *= M_PI / 180;

        $d_lon = $lon1 - $lon2;

        $slat1 = sin($lat1);
        $slat2 = sin($lat2);
        $clat1 = cos($lat1);
        $clat2 = cos($lat2);
        $sdelt = sin($d_lon);
        $cdelt = cos($d_lon);

        $y = pow($clat2 * $sdelt, 2) + pow($clat1 * $slat2 - $slat1 * $clat2 * $cdelt, 2);
        $x = $slat1 * $slat2 + $clat1 * $clat2 * $cdelt;

        return atan2(sqrt($y), $x) * 6372795;
    }

    public function out()
    {
        unset($_SESSION['Script_name']);
        unset($_SESSION['data']);
        $message = 'Выберите пункт меню.';
        TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
    }
}