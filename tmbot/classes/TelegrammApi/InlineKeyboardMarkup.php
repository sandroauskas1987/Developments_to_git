<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 28.05.2018
 * Time: 14:17
 */

class InlineKeyboardMarkup
{
    public static function key_foto_object(){

        $button1 = new stdClass();
        $button1->text=" Продолжить 	\xE2\x8F\xA9";
        $button1->callback_data="photo_uploaded=true";
        $buttons=[[$button1]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function key_foto_zamer_list(){

        $button1 = new stdClass();
        $button1->text=" Продолжить 	\xE2\x8F\xA9";
        $button1->callback_data="photo_zamer_list_uploaded=true";
        $buttons=[[$button1]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function stage_of_construction(){

        $button1 = new stdClass();
        $button1->text=" Фундамент ";
        $button1->callback_data="stage_of_construction=Фундамент";
        $button2 = new stdClass();
        $button2->text=" Стены ";
        $button2->callback_data="stage_of_construction=Стены";
        $button3 = new stdClass();
        $button3->text=" Крыша ";
        $button3->callback_data="stage_of_construction=Крыша";
        $button4 = new stdClass();
        $button4->text=" Отделка ";
        $button4->callback_data="stage_of_construction=Отделка";
        $buttons=[[$button1],[$button2],[$button3],[$button4]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function activity(){

        $button1 = new stdClass();
        $button1->text=" Отсутствует ";
        $button1->callback_data="activity=Отсутствует";
        $button2 = new stdClass();
        $button2->text=" Низкая ";
        $button2->callback_data="activity=Низкая";
        $button3 = new stdClass();
        $button3->text=" Высокая ";
        $button3->callback_data="activity=Высокая";
        $buttons=[[$button1],[$button2],[$button3]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function marketing(){

        $button1 = new stdClass();
        $button1->text=" Вручил лично ";
        $button1->callback_data="marketing=Вручил лично";
        $button2 = new stdClass();
        $button2->text=" Оставил ";
        $button2->callback_data="marketing=Оставил";
        $button3 = new stdClass();
        $button3->text=" Забрали ";
        $button3->callback_data="marketing=Забрали";
        $button4 = new stdClass();
        $button4->text=" Не забрали ";
        $button4->callback_data="marketing=Не забрали";
        $buttons=[[$button1],[$button2],[$button3],[$button4]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function consent_to_add(){

        $button1 = new stdClass();
        $button1->text=" Сохранить в КБ ";
        $button1->callback_data="consent_to_add=true";
        $buttons=[[$button1]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function key_zamer($id=null,$accepted){
        if($accepted==0){
            $button1 = new stdClass();
            $button1->text="\xE2\x84\xB9 Подробнее ";
            $button1->callback_data='get_zamer_detal='.$id;
            $button3 = new stdClass();
            $button3->text="\xE2\x9C\x85 Принять в работу";
            $button3->callback_data='accepted='.$id;
            $button4 = new stdClass();
            $button4->text="\xF0\x9F\x93\x8D Точка на карте";
            $button4->callback_data='get_Location_zamer='.$id;
            $buttons=[[$button1,$button4],[$button3]];
        }else{
            $button1 = new stdClass();
            $button1->text="\xE2\x84\xB9 Подробнее ";
            $button1->callback_data='get_zamer_detal='.$id;
            $button2 = new stdClass();
            $button2->text="\xF0\x9F\x93\x8B Отправить замер";
            $button2->callback_data='send_metering='.$id;
            $button3 = new stdClass();
            $button3->text="\xF0\x9F\x93\x8D Точка на карте";
            $button3->callback_data='get_Location_zamer='.$id;
            $buttons=[[$button1,$button2],[$button3]];
        }

        return self::Inline_KeyboardMarkup($buttons);
    }

    //выбор замера для закрепления
    public static function file_zamer_list($sotrud_id){

        $zamer_list=KbApi::get_zamer_today($sotrud_id,true);
        if(isset($zamer_list)){
            foreach ($zamer_list as $val){
                $button = new stdClass();
                $button->text=$val['icon'].' Замер №'.$val['id'].' от '.$val['date'];
                $button->callback_data='id='.$val['id'];
                $buttons[]=array($button);
            }
        }else{
            $button = new stdClass();
            $button->text='Нет доступных замеров';
            $button->callback_data='0';
            $buttons[]=array($button);
        }
        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function key_inform($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x84\xB9 Подробнее ";
        $button1->callback_data='get_zamer_detal='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8B Отправить замер";
        $button2->callback_data='send_metering='.$id;
        $button3 = new stdClass();
        $button3->text="\xF0\x9F\x93\x8D Точка на карте";
        $button3->callback_data='get_Location_zamer='.$id;
        $buttons=[[$button1,$button2],[$button3]];

        return self::Inline_KeyboardMarkup($buttons);
    }
    public static function key_hide($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x86\xA9 Скрыть ";
        $button1->callback_data='get_zamer_hide='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8B Отправить замер";
        $button2->callback_data='send_metering='.$id;
        $button3 = new stdClass();
        $button3->text="\xF0\x9F\x93\x8D Точка на карте";
        $button3->callback_data='get_Location_zamer='.$id;
        $buttons=[[$button1,$button2],[$button3]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    //определение типа файла
    public static function key_files(){
        $button1 = new stdClass();
        $button1->text="Замерный лист";
        $button1->callback_data='bind=zamerlist';
        $button2 = new stdClass();
        $button2->text="Фото по замеру";
        $button2->callback_data='bind=zamerphoto';
        /*
        $button3 = new stdClass();
        $button3->text="Акт выполненых работ";
        $button3->callback_data='file_akt_list='.$file_id;
        $button4 = new stdClass();
        $button4->text="Фото выполненых работ";
        $button4->callback_data='file_akt_foto='.$file_id;
        */
        $buttons=[[$button1,$button2]];

        return self::Inline_KeyboardMarkup($buttons);
    }

    //дополнительные кнопки для монтажа
    public static function key_montaj($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x84\xB9 подробнее ";
        $button1->callback_data='get_montaj_detal='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8D Точка на карте";
        $button2->callback_data='get_Location_montaj='.$id;
        $button3 = new stdClass();
        $button3->text="Замерный лист";
        $button3->callback_data='file_zamer_list_get='.$id;
        $button4 = new stdClass();
        $button4->text="Фото объекта";
        $button4->callback_data='file_zamer_foto_object_get='.$id;
        $button5 = new stdClass();
        $button5->text="Отправить фото монтажа";
        $button5->callback_data='to_send_installation='.$id;
        $buttons=[[$button1,$button2],[$button3,$button4],[$button5]];
            return self::Inline_KeyboardMarkup($buttons);
    }
    public static function key_montaj_hide($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x86\xA9 Скрыть ";
        $button1->callback_data='get_montaj_hide='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8D Точка на карте";
        $button2->callback_data='get_Location_montaj='.$id;
        $button3 = new stdClass();
        $button3->text="Замерный лист";
        $button3->callback_data='file_zamer_list_get='.$id;
        $button4 = new stdClass();
        $button4->text="Фото объекта";
        $button4->callback_data='file_zamer_foto_object_get='.$id;
        $button5 = new stdClass();
        $button5->text="Отправить фото монтажа";
        $button5->callback_data='to_send_installation='.$id;
        $buttons=[[$button1,$button2],[$button3,$button4],[$button5]];
        return self::Inline_KeyboardMarkup($buttons);
    }

    //дополнительные кнопки для доставки
    public static function key_delivery($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x84\xB9 подробнее ";
        $button1->callback_data='get_delivery_detal='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8D Точка на карте";
        $button2->callback_data='get_Location_delivery='.$id;
        $buttons=[[$button1,$button2]];
        return self::Inline_KeyboardMarkup($buttons);
    }
    public static function key_delivery_hide($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x86\xA9 Скрыть ";
        $button1->callback_data='get_delivery_hide='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8D Точка на карте";
        $button2->callback_data='get_Location_delivery='.$id;
        $buttons=[[$button1,$button2]];
        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function key_objects_nearby(){
        $button1 = new stdClass();
        $button1->text="В радиусе 1 км";
        $button1->callback_data='objects_nearby=1000';
        $button2 = new stdClass();
        $button2->text="В радиусе 5 км";
        $button2->callback_data='objects_nearby=5000';
        $button3 = new stdClass();
        $button3->text="В радиусе 10 км";
        $button3->callback_data='objects_nearby=10000';
        $button4 = new stdClass();
        $button4->text="Все объекты";
        $button4->callback_data='objects_nearby=all';
        $buttons=[[$button1],[$button2],[$button3],[$button4]];
        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function key_objects($id=null){
        $button1 = new stdClass();
        $button1->text="\xE2\x86\xA9 Посетить объект";
        $button1->callback_data='visit_to_object='.$id;
        $button2 = new stdClass();
        $button2->text="\xF0\x9F\x93\x8D Точка на карте";
        $button2->callback_data='get_Location_objects='.$id;
        $buttons=[[$button1,$button2]];
        return self::Inline_KeyboardMarkup($buttons);
    }

    public static function Inline_KeyboardMarkup($buttons){
        $keyboard =json_encode($keyboard =['inline_keyboard' => $buttons]);
        return $keyboard;
    }
}