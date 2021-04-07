<?PHP

/**
 * Class KbApi
 */
class KbApi{
	//запись всех запросов к боту
    /**
     * @param $user_id
     * @param $chat_id
     * @param $message_id
     * @param $text
     * @param $data
     * @param $in_arr
     * @param $sotrud_id
     * @param $from
     * @return KbApiCurl
     */
    public static function log_add_kb($user_id,$chat_id,$message_id,$text,$data,$in_arr,$sotrud_id,$from){
		$fields = new stdClass();
		 $fields->f14631=$user_id;
		 $fields->f14641=$chat_id;
		 $fields->f14651=$message_id;
		 $fields->f14661=$text;
		 $fields->f14671=$data;
		 $fields->f14701=$in_arr;	
		 $fields->f14691=$sotrud_id;
		 $fields->f14711=$from;
 
		$komand=new KbApiComand('create',821,$fields);
		$result=new KbApiCurl($komand);
		return $result;
	}
	//получить юзера по ТелеграммID
    /**
     * @param $telgram_user_id
     * @return KbApiCurl
     */
    public static function get_user(){
		$komand=new KbApiComand('read',46,['id','f483','f484','f14300','f13940','f1400'],[['status','=','0']]);
		$result=new KbApiCurl($komand);
		return $result;
	}
	//получаем список замеров по юзеру из КБ на сегодня
    /**
     * @param $sotrud_id
     * @param $is_admin
     * @return array|null
     */
    public static function get_zamer_today($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
        $komand=new KbApiComand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['f5061','<>','Обработан','and'],['status','=','0']],[['f5041','ASC']]);		//['f14731','=','Замер просрочен!','and'],
        $result=new KbApiCurl($komand);
		$date1=date('Y-m-d');
		if($result->status==true && isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
                Logger::getLogger('result_cb')->log($date_zam==$date1);
                Logger::getLogger('result_cb')->log($date_zam.'=='.$date1);
				if($date_zam==$date1 or $data->row->f14731=='Замер просрочен!'){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));	
					$lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;
					$return[]=$lin;
				}						
			}
			if(!isset($return)){return $return=null;}
		}else {$return=null; }
        return $return;
	}
	//получаем список замеров по юзеру из КБ на завтра
    /**
     * @param $sotrud_id
     * @param $is_admin
     * @return array|null
     */
    public static function get_zamer_tomorrow($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new KbApiComand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['f5061','<>','Обработан','and'],['status','=','0']],[['f5041','ASC']]);
		$result=new KbApiCurl($komand);
        $date1=date("Y-m-d",  (strtotime(date("Y-m-d"))+86400));
		if(isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
                Logger::getLogger('result_cb')->log($date_zam==$date1);
                Logger::getLogger('result_cb')->log($date_zam.'=='.$date1);
				if($date_zam==$date1 or $data->row->f14731=='Замер просрочен!'){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));
					$lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;
					$return[]=$lin;		
				}							
			}
			if(isset($return)){return $return;}else {return $return=null;}
		}else {return $return=null;}
	}
	//получаем список замеров по юзеру из КБ все будущие
    /**
     * @param $sotrud_id
     * @param $is_admin
     * @return array|null
     */
    public static function get_zamer_next($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new KbApiComand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['f5061','<>','Обработан','and'],['status','=','0']],[['f5041','ASC']]);
		$result=new KbApiCurl($komand);
		$date1=date('Y-m-d');
		if(isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
				if($date_zam>=$date1 or $data->row->f14731=='Замер просрочен!'){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));	
					$lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;					
					$return[]=$lin;
				}						
			}
			if(isset($return)){return $return;}else {return $return=null;}
		}else {return $return=null;}
	}
	//получаем список замеров по юзеру из КБ за 40 дней
    /**
     * @param $sotrud_id
     * @param $is_admin
     * @return array|null
     */
    public static function get_zamer_previous($sotrud_id,$is_admin){
		$date1=date("Y-m-d H:i",strtotime(date("Y-m-d H:i"))-40*86400);
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new KbApiComand('read',291,['id','f5041','f14741','f14751'],[$field_filtr,['f5041','>',$date1,'and'],['f5061','<>','Отказ','and'],['status','=','0']],[['f14731','ASC'],['f5041','ASC']]);
		$result=new KbApiCurl($komand);
		$date2=date('Y-m-d');
		if(isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
				if($date_zam<$date2){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));
                    $lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;
					$return[]=$lin;
				}						
			}
			if(isset($return)){return $return;}else {return $return=null;}
		}else {return $return=null;}
	}
	// детализация замера
    /**
     * @param $zamer_id
     * @return string
     */
    public static function get_zamer_detal($zamer_id){
		$komand=new KbApiComand('read',291,['id','f5041','f14571','f14561','f5031','f5591','f14721','f16980','user_id'],[['id','=',$zamer_id]]);
		$result=new KbApiCurl($komand);
		$data=$result->output->data->$zamer_id->row;
		if(isset($data->id)    && $data->id!=null)$nomer=$data->id; else $nomer='eror';
		if(isset($data->f5041) && $data->f5041!=null)$date=$data->f5041; else $date='1900-01-01 00:00:00';
		if(isset($data->f14571)&& $data->f14571!=null)$fio=$data->f14571; else $fio='не указано';
		if(isset($data->f14561)&& $data->f14561!=null)$tell=$data->f14561; else $tell='не указан';
		if(isset($data->f5031) && $data->f5031!=null)$addres=$data->f5031; else $addres='не указан';
		if(isset($data->f5591) && $data->f5591!=null)$coment=$data->f5591; else $coment='-';
		if(isset($data->f14721) && $data->f14721!=null)$zamershik=$data->f14721; else $zamershik='не согласован';
        if(isset($data->user_id) && $data->user_id!=null)$user=$_SESSION['users_name'][$data->user_id]; else $user='нет';
        $dopinfo=$data->f16980;
		$text=KbApi::textmessege($nomer,$date,$fio,$tell,$addres,$coment,$zamershik,$dopinfo,$user);
		return $text;
	}
	// скрыть подробности
    /**
     * @param $zamer_id
     * @return array|null
     */
    public static function get_zamer_hide($zamer_id){
        $komand=new KbApiComand('read',291,['id','f5041','f14741','f14751'],[['id','=',$zamer_id]]);
        $result=new KbApiCurl($komand);
        if(isset($result->output->data)){
            foreach($result->output->data as $data){
                $lin['id']=$data->row->id;
                $lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));
                $lin['icon']=$data->row->f14741;
                $lin['accepted']=$data->row->f14751;
                $return[]=$lin;
            }
            if(isset($return)){return $return;}else {return $return=null;}
        }else {return $return=null;}
	}
	// принять в работу
    /**
     * @param $zamer_id
     * @return array|null
     */
    public static function accepted($zamer_id){
		$komands=new KbApiComand('update',291,array('f14751'=>'1'),[['id','=',$zamer_id]]);
		$results=new KbApiCurl($komands);
		$komand=new KbApiComand('read',291,['id','f5041','f14741','f14751'],[['id','=',$zamer_id]]);
		$result=new KbApiCurl($komand);
		if(isset($result->output->data)){
			foreach($result->output->data as $data){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));
                    $lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;
					$return[]=$lin;
			}
			if(isset($return)){return $return;}else {return $return=null;}
		}else {return $return=null;}
	}
	//передать замерный лист
    /**
     * @param $zamer_id
     * @param $name_file
     * @param null $coments
     * @return KbApiCurl
     */
    public static function file_zamer_list_add($zamer_id,$name_file,$coments=null)
    {
        if(is_null($coments) || $coments=='-'){
            $array_fields=array('f14781' => $name_file,'f14831'=>date("Y-m-d H:i"));
        }else{
            $komand=new KbApiComand('read',291,['f5591'],[['id','=',$zamer_id]]);
            $result=new KbApiCurl($komand);
            if(isset($result->output->data)){
                foreach($result->output->data as $data){
                    $coment=$data->row->f5591;
                }
                $array_fields=[
                    'f14781' => $name_file,
                    'f5591' => $coment.' '.$coments,
                    'f14831'=>date("Y-m-d H:i")
                ];
            }
        }

        $komands = new KbApiComand('update', 291,$array_fields, [['id', '=', $zamer_id]]);
        $results = new KbApiCurl($komands);
        return $results;
    }
    //передать фото объектов
    /**
     * @param $zamer_id
     * @param $name_file
     * @param null $coments
     * @return KbApiCurl
     */
    public static function set_object_foto($zamer_id,$name_file,$coments=null)
    {
        if (is_null($coments) || $coments == '-') {
            $array_fields = array('f14791' => $name_file);
        } else {
            $komand = new KbApiComand('read', 291, ['f5591'], [['id', '=', $zamer_id]]);
            $result = new KbApiCurl($komand);
            if (isset($result->output->data)) {
                foreach ($result->output->data as $data) {
                    $coment = $data->row->f5591;
                }
                $array_fields =[
                    'f14791' => $name_file,
                    'f5591' => $coment . ' ' . $coments
                ];
            }
        }
        //$name_file='/wdimg/zamerlist/'.$name_file; f14781
        $komands = new KbApiComand('update', 291, $array_fields, [['id', '=', $zamer_id]]);
        $results = new KbApiCurl($komands);
        return $results;
    }
    // получить замерный лист
    /**
     * @param $zakaz_id
     * @return array|null
     */
    public static function file_zamer_list_get($zakaz_id)
    {
        $return=null;
        $komand=new KbApiComand('read',311,['id','f7141'],[['id','=',$zakaz_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                foreach ($result->output->data as $data) {
                    $komand2 = new KbApiComand('read', 291, ['id', 'f5001'], [['id', '=', $data->row->f7141]]);
                    $result2 = new KbApiCurl($komand2);
                    if ($result2->status == true && isset($result2->output->data)) {
                        if ($result2->output->count > 0) {
                            foreach ($result2->output->data as $data2) {
                                $lin['id'] = $data2->row->id;
                                $lin['f5001'] = $data2->row->f5001;
                                $return[] = $lin;
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }
    // получить фото объекта с замера
    /**
     * @param $zakaz_id
     * @return array|null
     */
    public static function file_zamer_foto_object_get($zakaz_id)
    {
        $return=null;
        $komand=new KbApiComand('read',311,['id','f7141'],[['id','=',$zakaz_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                foreach ($result->output->data as $data) {
                    $komand2 = new KbApiComand('read', 291, ['id', 'f5841'], [['id', '=', $data->row->f7141]]);
                    $result2 = new KbApiCurl($komand2);
                    if ($result2->status == true && isset($result2->output->data)) {
                        if ($result2->output->count > 0) {
                            foreach ($result2->output->data as $data2) {
                                $lin['id'] = $data2->row->id;
                                $lin['f5841'] = $data2->row->f5841;
                                $return[] = $lin;
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }
	//разбор параметров
    /**
     * @param $obj
     * @param $arr_par
     * @return array
     */
    public static function get_param($obj,$arr_par){
        $get_result='';
        $params='';
        if(is_object($obj)){
            foreach($obj as $obj1){
                if(is_object($obj1)){
                    foreach($obj1 as $id=>$obj2){
                        for($i=0;$i<count($arr_par);$i++){
                            $params[$arr_par[$i]]=$obj2->row->$arr_par[$i];
                        }
                        $get_result[]=$params;
                    }
                }
            }
        }
		return $get_result;
	}
	// монтажи на сегодня
    /**
     * @param $chat_id
     */
    public static function get_montaj_today($chat_id)
    {
        $komand = new KbApiComand('read', 311, ['id', 'f12370'], [['f12370', '<>', '0000-00-00 00:00:00', 'and'],['f5571','<>','10. Выполнен','and'], ['status', '=', '0']], [['f12370', 'ASC']]);
        $result = new KbApiCurl($komand);
        $date1 = date("Y-m-d");
        $count = 0;
        if ($result->status == true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12370));
                    if ($date_montaj == $date1) {
                        $id_montaj = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12370));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x88\x9A <b>Монтаж № $id_montaj</b> на $date2", 'key_montaj', $id_montaj);
                        $count++;
                    }
                }
                if ($count == 0) TelegrammApi::sendMessage($chat_id, "на сегодня нет монтажей", 'key_montaj_menu');
            } else {
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montaj_menu');
            }

        } else {
            TelegrammApi::sendMessage($chat_id, $result->status_message, 'key_montaj_menu');
        }
    }
    // монтажи на завтра
    /**
     * @param $chat_id
     */
    public static function get_montaj_tomorrow($chat_id){
        $komand=new KbApiComand('read',311,['id','f12370'],[['f12370','<>','0000-00-00 00:00:00','and'],['f5571','<>','10. Выполнен','and'],['status','=','0']],[['f12370','ASC']]);
        $result=new KbApiCurl($komand);
        $date1=date("Y-m-d", strtotime('+1 day', strtotime(date("Y-m-d"))));
        /*$dey=date('d')+1;
        $dey=$dey<10?'0'.$dey:$dey;
        $date1=date('Y-m-').$dey;*/
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12370));
                    if ($date_montaj == $date1) {
                        $id_montaj = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12370));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x88\x9A <b>Монтаж № $id_montaj</b> на $date2", 'key_montaj', $id_montaj);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "на завтра нет монтажей", 'key_montaj_menu');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montaj_menu');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_montaj_menu');}
    }
    // монтажи все предстоящие
    /**
     * @param $chat_id
     */
    public static function get_montaj_next($chat_id){
        $komand=new KbApiComand('read',311,['id','f12370'],[['f12370','<>','0000-00-00 00:00:00','and'],['f5571','<>','10. Выполнен','and'],['status','=','0']],[['f12370','ASC']]);
        $result=new KbApiCurl($komand);
        $date1=date('Y-m-d');
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12370));
                    if ($date1<$date_montaj) {
                        $id_montaj = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12370));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x88\x9A <b>Монтаж № $id_montaj</b> на $date2", 'key_montaj', $id_montaj);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "нет будущих монтажей", 'key_montaj_menu');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montaj_menu');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_montaj_menu');}
    }
    // детализация монтажа
    /**
     * @param $chat_id
     * @param $message_id
     * @param $montaj_id
     */
    public static function get_montaj_detal($chat_id,$message_id,$montaj_id){
        $komand=new KbApiComand('read',311,['id','f12370','f17040','f17050','f12870','f5521','f16020','f17070','f12890'],[['id','=',$montaj_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                if ($data = $result->output->data->$montaj_id->row) {
                    //Получим ФИО монтажника
                    if (isset($data->f17070) && $data->f17070 != null) {
                        $montajnik = $data->f17070;
                        $komand2 = new KbApiComand('read', 46, ['id', 'f483'], [['id', '=', $montajnik, 'and'], ['status', '=', '0']]);
                        $result2 = new KbApiCurl($komand2);
                        if ($result2->status == true && isset($result2->output->data)) {
                            if ($result2->output->count > 0) {
                                if ($data2 = $result2->output->data->$montajnik->row) {
                                    $montajnik = $data2->f483;
                                }
                            }else $montajnik = '-';
                        }else $montajnik = '-';

                    } else $montajnik = '-';

                    if (isset($data->id) && $data->id != null) $nomer = $data->id; else $nomer = 'eror';
                    if (isset($data->f12370) && $data->f12370 != null) $date = $data->f12370; else $date = '1900-01-01 00:00:00';
                    if (isset($data->f17040) && $data->f17040 != null) $fio = $data->f17040; else $fio = 'не указано';
                    if (isset($data->f17050) && $data->f17050 != null) $tell = $data->f17050; else $tell = 'не указан';
                    if (isset($data->f12870) && $data->f12870 != null) $tel2 = $data->f12870; else $tel2 = 'не указан';
                    if (isset($data->f5521) && $data->f5521 != null) $addres = $data->f5521; else $addres = 'не указан';
                    if (isset($data->f16020) && $data->f16020 != null) $coment = $data->f16020; else $coment = '-';
                    if (isset($data->f12890) && $data->f12890 != null) $soglasovan = $data->f12890; else $soglasovan = '-';
                    $text = KbApi::textmessegemontaj($nomer, $date, $fio, $tell, $tel2, $addres, $coment, $montajnik, $soglasovan);
                    TelegrammApi::editMessageText($chat_id, $message_id, $text, 'key_montaj_hide', $montaj_id);
                }
            }
        }elseif($result->status==false)TelegrammApi::editMessageText($chat_id, $message_id, 'ER-'.$result->status_message, 'key_montaj', $montaj_id);
    }
    // минимизация монтажа
    /**
     * @param $chat_id
     * @param $message_id
     * @param $montaj_id
     */
    public static function get_montaj_hide($chat_id,$message_id,$montaj_id){
        $komand=new KbApiComand('read',311,['id','f12370'],[['id','=',$montaj_id]]);
        $result=new KbApiCurl($komand);
        if($data=$result->output->data->$montaj_id->row) {
            if (isset($data->id) && $data->id != null) $nomer = $data->id; else $nomer = 'eror';
            if (isset($data->f12370) && $data->f12370 != null) $date = $data->f12370; else $date = '1900-01-01 00:00:00';
            $time=date("H:i",strtotime($date));
            $date = date("d-m-Y", strtotime($date));
            $text = "\xF0\x9F\x88\x9A <b>Монтаж № $nomer</b> на $date в $time";
            TelegrammApi::editMessageText($chat_id, $message_id, $text, 'key_montaj', $montaj_id);
        }else TelegrammApi::editMessageText($chat_id, $message_id, "\xF0\x9F\x88\x9A <b>Монтаж № ".$montaj_id."</b>", 'key_montaj', $montaj_id);
    }

    // Доставки на сегодня
    /**
     * @param $chat_id
     */
    public static function get_delivery_today($chat_id){
        $komand=new KbApiComand('read',311,['id','f12360'],[['f12360','<>','0000-00-00 00:00:00','and'],['f5531','=','Доставка','and'],['status','=','0']],[['f12360','ASC']]);
        $result=new KbApiCurl($komand);
        $date1=date('Y-m-d');
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12360));
                    if ($date_montaj == $date1) {
                        $delivery_id = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12360));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x9A\x9B <b>Доставка № $delivery_id</b> на $date2", 'key_delivery', $delivery_id);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "на сегодня нет доставок", 'key_delivery_menu');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых доставок", 'key_delivery_menu');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_delivery_menu');}
    }
    // Доставки на завтра
    /**
     * @param $chat_id
     */
    public static function get_delivery_tomorrow($chat_id){
        $komand=new KbApiComand('read',311,['id','f12360'],[['f12360','<>','0000-00-00 00:00:00','and'],['f5531','=','Доставка','and'],['status','=','0']],[['f12360','ASC']]);
        $result=new KbApiCurl($komand);
        $date1=date("Y-m-d", strtotime('+1 day', strtotime(date("Y-m-d"))));
        /*$dey=date('d')+1;
        $dey=$dey<10?'0'.$dey:$dey;
        $date1=date('Y-m-').$dey;*/
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12360));
                    if ($date_montaj == $date1) {
                        $delivery_id = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12360));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x9A\x9B <b>Доставка № $delivery_id</b> на $date2", 'key_delivery', $delivery_id);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "на завтра нет доставок", 'key_delivery_menu');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых доставок", 'key_delivery_menu');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_delivery_menu');}
    }
    // Доставки все предстоящие
    /**
     * @param $chat_id
     */
    public static function get_delivery_next($chat_id){
        $komand=new KbApiComand('read',311,['id','f12360'],[['f12360','<>','0000-00-00 00:00:00','and'],['f5531','=','Доставка','and'],['status','=','0']],[['f12360','ASC']]);
        $result=new KbApiCurl($komand);
        $date1=date('Y-m-d');
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12360));
                    if ($date1<$date_montaj) {
                        $delivery_id = $data->row->id;
                        $date2 = date("d-m-Y в H:i", strtotime($data->row->f12360));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x9A\x9B <b>Доставка № $delivery_id</b> на $date2", 'key_delivery', $delivery_id);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "нет будущих доставок", 'key_delivery_menu');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых доставок", 'key_delivery_menu');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_delivery_menu');}
    }
    // Детализация доставки
    /**
     * @param $chat_id
     * @param $message_id
     * @param $delivery_id
     */
    public static function get_delivery_detal($chat_id,$message_id,$delivery_id){
        $komand=new KbApiComand('read',311,['id','f12360','f17040','f17050','f12870','f5521','f16020','f17070','f12890'],[['id','=',$delivery_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                if ($data = $result->output->data->$delivery_id->row) {
                    if (isset($data->id) && $data->id != null) $nomer = $data->id; else $nomer = 'eror';
                    if (isset($data->f12360) && $data->f12360 != null) $date = $data->f12360; else $date = '1900-01-01 00:00:00';
                    if (isset($data->f17040) && $data->f17040 != null) $fio = $data->f17040; else $fio = 'не указано';
                    if (isset($data->f17050) && $data->f17050 != null) $tell = $data->f17050; else $tell = 'не указан';
                    if (isset($data->f12870) && $data->f12870 != null) $tel2 = $data->f12870; else $tel2 = 'не указан';
                    if (isset($data->f5521) && $data->f5521 != null) $addres = $data->f5521; else $addres = 'не указан';
                    if (isset($data->f16020) && $data->f16020 != null) $coment = $data->f16020; else $coment = '-';
                    $text = KbApi::textmessegedelivery($nomer, $date, $fio, $tell, $tel2, $addres, $coment);
                    TelegrammApi::editMessageText($chat_id, $message_id, $text, 'key_delivery_hide', $delivery_id);
                }
            }
        }elseif($result->status==false)TelegrammApi::editMessageText($chat_id, $message_id, 'ER-'.$result->status_message, 'key_delivery', $delivery_id);
    }
    // минимизация доставки
    /**
     * @param $chat_id
     * @param $message_id
     * @param $delivery_id
     */
    public static function get_delivery_hide($chat_id,$message_id,$delivery_id){
        $komand=new KbApiComand('read',311,['id','f12360'],[['id','=',$delivery_id]]);
        $result=new KbApiCurl($komand);
        if($data=$result->output->data->$delivery_id->row) {
            if (isset($data->id) && $data->id != null) $nomer = $data->id; else $nomer = 'eror';
            if (isset($data->f12360) && $data->f12360 != null) $date = $data->f12360; else $date = '1900-01-01 00:00:00';
            $time=date("H:i",strtotime($date));
            $date = date("d-m-Y", strtotime($date));
            $text = "\xF0\x9F\x9A\x9B <b>Доставка № $nomer</b> на $date в $time";
            TelegrammApi::editMessageText($chat_id, $message_id, $text, 'key_delivery', $delivery_id);
        }else TelegrammApi::editMessageText($chat_id, $message_id, "\xF0\x9F\x88\x9A <b>Доставка № ".$delivery_id."</b>", 'key_delivery', $delivery_id);
    }

    // координаты замера
    /**
     * @param $chat_id
     * @param $message_id
     * @param $zamer_id
     */
    public static function get_Location_zamer($chat_id,$message_id,$zamer_id){
        $komand=new KbApiComand('read',291,['id','f17080','f17090'],[['id','=',$zamer_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            $data = $result->output->data->$zamer_id->row;
            if (isset($data->f17080) && isset($data->f17090) && strlen($data->f17080)>1 && strlen($data->f17090)>1) {
                $latitude = $data->f17080;
                $longitude = $data->f17090;
                TelegrammApi::sendLocation($chat_id, $latitude, $longitude, $message_id);
            } else {
                TelegrammApi::sendMessage($chat_id, 'Координаты отсутствуют', null, null, null, $message_id);
            }
        }else {
            TelegrammApi::sendMessage($chat_id, 'Координаты не удалось получить. \n'.$result->status_message, null, null, null, $message_id);
        }
    }
    // координаты монтажа
    /**
     * @param $chat_id
     * @param $message_id
     * @param $montaj_id
     */
    /**
     * @param $chat_id
     * @param $message_id
     * @param $montaj_id
     */
    public static function get_Location_montaj($chat_id,$message_id,$montaj_id){
        $komand=new KbApiComand('read',311,['id','f17100','f17110'],[['id','=',$montaj_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            $data = $result->output->data->$montaj_id->row;
            if (isset($data->f17100) && isset($data->f17110) && strlen($data->f17100)>1 && strlen($data->f17110)>1) {
                $latitude = $data->f17100;
                $longitude = $data->f17110;
                TelegrammApi::sendLocation($chat_id, $latitude, $longitude, $message_id);
            } else {
                TelegrammApi::sendMessage($chat_id, 'Координаты отсутствуют', null, null, null, $message_id);
            }
        }else {
            TelegrammApi::sendMessage($chat_id, 'Координаты не удалось получить. \n'.$result->status_message, null, null, null, $message_id);
        }
    }
    // координаты доставки
    /**
     * @param $chat_id
     * @param $message_id
     * @param $delivery_id
     */
    public static function get_Location_delivery($chat_id,$message_id,$delivery_id){
        $komand=new KbApiComand('read',311,['id','f17100','f17110'],[['id','=',$delivery_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            $data = $result->output->data->$delivery_id->row;
            if (isset($data->f17100) && isset($data->f17110) && strlen($data->f17100)>1 && strlen($data->f17110)>1) {
                $latitude = $data->f17100;
                $longitude = $data->f17110;
                TelegrammApi::sendLocation($chat_id, $latitude, $longitude, $message_id);
            } else {
                TelegrammApi::sendMessage($chat_id, 'Координаты отсутствуют', null, null, null, $message_id);
            }
        }else {
            TelegrammApi::sendMessage($chat_id, 'Координаты не удалось получить. \n'.$result->status_message, null, null, null, $message_id);
        }
    }

    //Сохранить фото монтажа в закапз покупателя
    /**
     * @param $array
     * @return bool
     */
    public static function save_photo_montaj ($array)
    {
        $data=$array['data'];
        $komands = new KbApiComand('update', 311, ['f19240' => $data['photoname']],[['id','=',$data['montaj_id']]]);
        $results = new KbApiCurl($komands);
        return $results->status;
    }
    //Сохранить новый объект в КБ - Прямые продажи
    /**
     * @param $array
     * @return bool
     */
    public static function new_direct_sale($array)
    {
        $data=$array['data'];
        $komands = new KbApiComand('create', 970, [
            'f18200' => $data['address'],
            'f18320' => $data['photoname'],
            'f18220' => $data['stage_of_construction'],
            'f18230' => $data['activity'],
            'f18240' => $data['marketing'],
            'f18250' => date("Y-m-d H:i",strtotime($data['last_visit'])),
            'f18260' => $data['comments'],
            'f18290' => $data['location']['latitude'],
            'f18300' => $data['location']['longitude'],
        ]);
        $results = new KbApiCurl($komands);
        return $results->status;
    }
    //Сохранить новый объект в КБ - Прямые продажи
    /**
     * @param $array
     * @return bool
     */
    public static function update_direct_sale($array)
    {
        $data=$array['data'];
        $komands = new KbApiComand('update', 970, [
            'f18320' => $data['photoname'],
            'f18220' => $data['stage_of_construction'],
            'f18230' => $data['activity'],
            'f18240' => $data['marketing'],
            'f18250' => date("Y-m-d H:i",strtotime($data['last_visit'])),
            'f18260' => $data['comments']
        ],[['id','=',$data['object_id']]]);
        $results = new KbApiCurl($komands);
        return $results->status;
    }
    // получить кооринаты объекта
    /**
     * @param $chat_id
     * @param $message_id
     * @param $object_id
     */
    public static function get_Location_objects($chat_id,$message_id,$object_id){
        $komand=new KbApiComand('read',970,['id','f18290','f18300'],[['id','=',$object_id]]);
        $result=new KbApiCurl($komand);
        if($result->status==true && isset($result->output->data)) {
            $data = $result->output->data->$object_id->row;
            if (isset($data->f18290) && isset($data->f18300) && strlen($data->f18290)>1 && strlen($data->f18300)>1) {
                $latitude = $data->f18290;
                $longitude = $data->f18300;
                TelegrammApi::sendLocation($chat_id, $latitude, $longitude, $message_id);
            } else {
                TelegrammApi::sendMessage($chat_id, 'Координаты не известны', null, null, null, $message_id);
            }
        }else {
            TelegrammApi::sendMessage($chat_id, 'Координаты не удалось получить. \n'.$result->status_message, null, null, null, $message_id);
        }
    }

    // упаковка ответа для замеров
    /**
     * @param $nomer
     * @param $date
     * @param $fio
     * @param $tell
     * @param $addres
     * @param $coment
     * @param null $zamershik
     * @param $dopinfo
     * @return string
     */
    public static function textmessege ($nomer,$date,$fio,$tell,$addres,$coment,$zamershik=null,$dopinfo,$user){
		$time=date("H:i",strtotime($date));
		$date=date("d-m-Y",strtotime($date));
        $tell=htmlentities ($tell);
		$textmessege="<b>Замер №$nomer</b>\n<b>Дата:</b> $date <b>Время:</b> $time\n<b>Клиент:</b> <i>$fio</i>\n<b>Телефон:</b> $tell\n<b>Адрес:</b> $addres\n<b>Комментарий:</b> $coment\n<b>Замерщик:</b> <i>$zamershik</i>\n<b>Менеджер:</b> <i>$user</i> \n<i>$dopinfo</i>";
		return $textmessege;
	}
	// упаковка ответа для монтажей
    /**
     * @param $nomer
     * @param $date
     * @param $fio
     * @param $tell
     * @param $tel2
     * @param $addres
     * @param $coment
     * @param $montajnik
     * @param $soglasovan
     * @return string
     */
    public static function textmessegemontaj ($nomer,$date,$fio,$tell,$tel2,$addres,$coment,$montajnik,$soglasovan){
        $time=date("H:i",strtotime($date));
        $date=date("d-m-Y",strtotime($date));
        $tell=htmlentities ($tell);
        $tel2=htmlentities ($tel2);
        $textmessege="<b>Монтаж №$nomer</b>\n<b>Дата:</b> $date <b>Время:</b> $time\n<b>Клиент:</b> <i>$fio</i>\n<b>Телефон:</b> $tell\n<b>Доп.Телефон:</b> $tel2\n<b>Адрес:</b> $addres\n<b>Монтажник:</b> <i>$montajnik</i>\n<b>Монтаж согласован:</b> <i>$soglasovan</i>\n<b>Комментарий:</b> $coment\n";
        return $textmessege;
    }
    // упаковка ответа для монтажей
    /**
     * @param $nomer
     * @param $date
     * @param $fio
     * @param $tell
     * @param $tel2
     * @param $addres
     * @param $coment
     * @return string
     */
    public static function textmessegedelivery ($nomer,$date,$fio,$tell,$tel2,$addres,$coment){
        $time=date("H:i",strtotime($date));
        $date=date("d-m-Y",strtotime($date));
        $tell=htmlentities ($tell);
        $tel2=htmlentities ($tel2);
        $textmessege="<b>Доставка №$nomer</b>\n<b>Дата:</b> $date <b>Время:</b> $time\n<b>Клиент:</b> <i>$fio</i>\n<b>Телефон:</b> $tell\n<b>Доп.Телефон:</b> $tel2\n<b>Адрес:</b> $addres\n<b>Комментарий:</b> $coment\n";
        return $textmessege;
    }
}