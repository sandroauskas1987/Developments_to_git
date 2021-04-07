<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 05.03.2019
 * Time: 14:27
*/
class bot_buttons
{
    public function button_constructor($arr=array()){
        $keyboard = new stdClass();
        $keyboard->one_time=false;
        $i=0;
        foreach ($arr  as  $key => $ar) {
            $object=self::button(self::button_action($ar[0], $ar[1], $ar[2]));
            if($key<10){$keyboard->buttons[]=[$object]; continue;}
            if($key<39){
                array_push($keyboard->buttons[$i],$object); $i++;
                if($i==10) $i=0;
            }
        }
        return $keyboard;
    }

    public static function button_action($button_name,$command,$id){
        $action = new stdClass();
        $action->type="text";
        $action->payload=json_encode(array('command'=>$command,'id'=>$id));
        $action->label=$button_name;
        return $action;
    }

    public static function button($action){
        $button=new stdClass();
        $button->action=$action;
        $button->color='primary';
        return $button;
    }

}