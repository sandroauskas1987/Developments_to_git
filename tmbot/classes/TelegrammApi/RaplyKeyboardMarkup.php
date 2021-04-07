<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 28.05.2018
 * Time: 14:16
 */

class RaplyKeyboardMarkup
{

    /**
     * @return array|string
     */
    public static function key_clear()
    {
        $buttons = [[['text'=>'Отмена']]];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @return array|string
     */
    public static function key_default_menu()
    {
        $buttons = [[['text'=>'Замеры'],['text'=>'Монтажи'],['text'=>'Доставки']],[['text'=>'Прямые продажи'],['text'=>'Отметиться','request_location'=>true]]];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @return array|string
     */
    public static function key_sotrud_menu()
    {
        $buttons = [['На сегодня','На завтра'],['Все предстоящие','Все за 40 дней'],['Назад']];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @return array|string
     */
    public static function key_montaj_menu(){
        $buttons = [['Монтажи сегодня','Монтажи завтра'],['Все предстоящие монтажи'],['Назад']];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @return array|string
     */
    public static function key_delivery_menu(){
        $buttons = [['Доставки сегодня','Доставки завтра'],['Все предстоящие доставки'],['Назад']];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @return array|string
     */
    public static function key_direct_sale_menu(){
        $buttons = [[['text'=>'Новый объект']],[['text'=>'Объекты рядом']],[['text'=>'Назад']]];
        return self::Reply_KeyboardMarkup($buttons);
    }

    /**
     * @param $buttons
     * @return array|string
     */
    public static function Reply_KeyboardMarkup($buttons)
    {
        $keyboard =json_encode($keyboard =['keyboard' => $buttons,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
        return $keyboard;
    }

}