<?php
/**
 * Created by PhpStorm.
 * User: Alexandr
 * Date: 06.03.2019
 * Time: 9:41
 */

function start($id,$sotrud_id){
    return ['message'=>'Выберите меню','buttons'=>buttons_default(),'coordinate'=>''];
}
function zamer($id,$sotrud_id){
    $ar=[
        ['На сегодня','zamer_today','0'],
        ['На завтра','zamer_tomorrow','0'],
        ['Все предстоящие','zamer_next','0'],
        ['Прошедшие','zamer_previous','0'],
        ['Вернуться','start','0'],
    ];
    return ['message'=>'Хозяин, какие замеры показать?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zakaz($id,$sotrud_id){
    $ar=[
        ['На сегодня','zakaz_today','0'],
        ['На завтра','zakaz_tomorrow','0'],
        ['Все предстоящие','zakaz_next','0'],
        ['Вернуться','start','0'],
    ];
    return ['message'=>'Хозяин, какие заказы показать?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}

function zamer_today($id,$sotrud_id){
    $zamer_today=Api::get_zamer_today($sotrud_id,true);
    if($zamer_today!=null){
        foreach ($zamer_today as $zamer){
            $button_name="№".$zamer['id']." ".$zamer['date']."".$zamer['icon'];
            $ar[]=[$button_name,'zamer_detal',$zamer['id']];
        }
    }else{
        $ar[]=['Не найдено! -> Вернуться','zamer','0'];
        return ['message'=>'Хозяин, нет ничего на сегодня','buttons'=>_buttons_get($ar),'coordinate'=>''];
    }
    $ar[]=['Вернуться','zamer','0'];
    return ['message'=>'Хозяин, какой замер показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zamer_tomorrow($id,$sotrud_id){
    $zamer_tomorrow=Api::get_zamer_tomorrow($sotrud_id,true);
    if($zamer_tomorrow!=null){
        foreach ($zamer_tomorrow as $zamer){
            $button_name="№".$zamer['id']." ".$zamer['date']."".$zamer['icon'];
            $ar[]=[$button_name,'zamer_detal',$zamer['id']];
        }
    }else{
        $ar[]=['Не найдено! -> Вернуться','zamer','0'];
        return ['message'=>'Хозяин, нет ничего на завтра','buttons'=>_buttons_get($ar),'coordinate'=>''];
    }
    $ar[]=['Вернуться','zamer','0'];
    return ['message'=>'Хозяин, какой замер показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zamer_next($id,$sotrud_id){
    $zamer_next=Api::get_zamer_next($sotrud_id,true);
    if($zamer_next!=null){
        foreach ($zamer_next as $zamer){
            $button_name="№".$zamer['id']." ".$zamer['date']."".$zamer['icon'];
            $ar[]=[$button_name,'zamer_detal',$zamer['id']];
        }
    }else{
        $ar[]=['Не найдено! -> Вернуться','zamer','0'];
        return ['message'=>'Хозяин, нет ничего в будущем :)','buttons'=>_buttons_get($ar),'coordinate'=>''];
    }
    $ar[]=['Вернуться','zamer','0'];
    return ['message'=>'Хозяин, какой замер показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zamer_previous($id,$sotrud_id){
    $zamer_previous=Api::get_zamer_previous($sotrud_id,true);
    if($zamer_previous!=null){
        foreach ($zamer_previous as $zamer){
            $button_name="№".$zamer['id']." ".$zamer['date']."".$zamer['icon'];
            $ar[]=[$button_name,'zamer_detal',$zamer['id']];
        }
    }else{
        $ar[]=['Не найдено! -> Вернуться','zamer','0'];
        return ['message'=>'Хозяин, нет прошечших','buttons'=>_buttons_get($ar),'coordinate'=>''];
    }
    $ar[]=['Вернуться','zamer','0'];
    return ['message'=>'Хозяин, какой замер показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zamer_detal($id,$sotrud_id){
    $zamer_detal=Api::get_zamer_detal($id);
    if($zamer_detal!=null){
        $ar=[['Вернуться','zamer','0'],];
        return ['message'=>$zamer_detal['message'],'buttons'=>_buttons_get($ar),'coordinate'=>$zamer_detal['coordinate']];
    }
}

function zakaz_today($id,$sotrud_id){
    $zamer_today=Api::get_zamer_today($sotrud_id,true);
    if($zamer_today!=null){
        foreach ($zamer_today as $zamer){
            $button_name="№".$zamer['id']." ".$zamer['date']."".$zamer['icon'];
            $ar[]=[$button_name,'zamer_detal',$zamer['id']];
        }
    }else{
        $ar[]=['Не найдено! -> Вернуться','zamer','0'];
        return ['message'=>'Хозяин, нет ничего на сегодня','buttons'=>_buttons_get($ar),'coordinate'=>''];
    }
    $ar[]=['Вернуться','zamer','0'];
    return ['message'=>'Хозяин, какой заказ показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zakaz_tomorrow($id,$sotrud_id){
    $ar=[['Вернуться','zakaz','0'],];
    return ['message'=>'Хозяин, какой заказ показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zakaz_next($id,$sotrud_id){
    $ar=[['Вернуться','zakaz','0'],];
    return ['message'=>'Хозяин, какой заказ показать детальней?','buttons'=>_buttons_get($ar),'coordinate'=>''];
}
function zakaz_detal($id,$sotrud_id){
    $zakaz_detal=Api::get_montaj_detal($id);
    if($zakaz_detal!=null){
        $ar=[['Вернуться','zakaz','0'],];
        return ['message'=>$zakaz_detal['message'],'buttons'=>_buttons_get($ar),'coordinate'=>$zakaz_detal['coordinate']];
    }
}

function _buttons_get($ar=array())
{
    $keyboard=new bot_buttons();
    return $result=$keyboard->button_constructor($ar);
}
function buttons_default(){
    $arrr=[
        ['Меню Замеры','zamer','0'],
        ['Меню Заказы','zakaz','0'],
        ['Вернуться','start','0'],
    ];
    $keyboard=new bot_buttons();
    return $result=$keyboard->button_constructor($arrr);
}