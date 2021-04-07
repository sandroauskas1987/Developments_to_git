<?PHP
class KbApi{
	public function __construct(){}
		
	//запись всех запросов к боту
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
 
		$komand=new Comand('create',821,$fields);
		$result=new Curl($komand);
		return $result;
	}
	//получить ID пользователя КБ
	public static function get_user($peer_id){
		$komand=new Comand('read',46,['id','f483'],[['f17560','=',$peer_id,'and'],['status','=','0']]);
		$result=new Curl($komand);
		return $result;
	}

	//получаем список замеров по юзеру из КБ на сегодня
	public static function get_zamer_today($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
        $komand=new Comand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['status','=','0']],[['f5041','ASC']]);		//['f14731','=','Замер просрочен!','and'],
        $result=new Curl($komand);
		$date1=date('Y-m-d');
		if($result->status==true && isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
				if($date_zam==$date1 or $data->row->f14731=='Замер просрочен!'){
					$lin['id']=$data->row->id;
					$lin['date']=date("d-m-Y в H:i",strtotime($data->row->f5041));	
					$lin['icon']=$data->row->f14741;
					$lin['accepted']=$data->row->f14751;
					$return[]=$lin;
				}						
			}
			if(isset($return)){return $return;}else {return $return=null;}
		}else {$return=null; }
	}
	//получаем список замеров по юзеру из КБ на завтра
	public static function get_zamer_tomorrow($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new Comand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['status','=','0']],[['f5041','ASC']]);
		$result=new Curl($komand);
		if(date('Y-m-d')==date('Y-m-t')){
            $m=date('m')+1;
            $m=$m<10?'0'.$m:$m;
            $date1=date('Y-'.$m.'-01');
        }else{
            $dey=date('d')+1;
            $dey=$dey<10?'0'.$dey:$dey;
            $date1=date('Y-m-').$dey;
        }


		if(isset($result->output->data)){
			foreach($result->output->data as $data){
				$date_zam=date("Y-m-d",strtotime($data->row->f5041));
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
	public static function get_zamer_next($sotrud_id,$is_admin){
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new Comand('read',291,['id','f5041','f14741','f14751','f14731'],[$field_filtr,['f5061','<>','Выполнен','and'],['status','=','0']],[['f5041','ASC']]);
		$result=new Curl($komand);
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
	public static function get_zamer_previous($sotrud_id,$is_admin){
		$date1=date("Y-m-d H:i",strtotime(date("Y-m-d H:i"))-40*86400);
		if($is_admin){$field_filtr=['f5051','<>','0','and'];}else{$field_filtr=['f5051','=',$sotrud_id,'and'];}
	    $komand=new Comand('read',291,['id','f5041','f14741','f14751'],[$field_filtr,['f5041','>',$date1,'and'],['status','=','0']],[['f14731','ASC'],['f5041','ASC']]);
		$result=new Curl($komand);
		$date2=date('Y-m-d');
        $return=[];
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
	
	public static function get_zamer_detal($zamer_id){
		$komand=new Comand('read',291,['id','f5041','f14571','f14561','f5031','f5591','f14721','f16980','f17080','f17090'],[['id','=',$zamer_id]]);
		$result=new Curl($komand);
		$data=$result->output->data->$zamer_id->row;		
		if(isset($data->id)    && $data->id!=null)$nomer=$data->id; else $nomer='eror';
		if(isset($data->f5041) && $data->f5041!=null)$date=$data->f5041; else $date='1900-01-01 00:00:00';
		if(isset($data->f14571)&& $data->f14571!=null)$fio=$data->f14571; else $fio='не указано';
		if(isset($data->f14561)&& $data->f14561!=null)$tell=$data->f14561; else $tell='не указан';
		if(isset($data->f5031) && $data->f5031!=null)$addres=$data->f5031; else $addres='не указан';
		if(isset($data->f5591) && $data->f5591!=null)$coment=$data->f5591; else $coment='-';
		if(isset($data->f14721) && $data->f14721!=null)$zamershik=$data->f14721; else $zamershik='не согласован';
        $dopinfo=$data->f16980;
        $ar['message']=Api::textmessege($nomer,$date,$fio,$tell,$addres,$coment,$zamershik,$dopinfo);
        $ar['coordinate']['lat']=$data->f17080;
        $ar['coordinate']['long']=$data->f17090;
		return $ar;
	}
	public static function get_zamer_telefon($zamer_id){
		$komand=new Comand('read',291,['f14561'],[['id','=',$zamer_id]]);
		$result=new Curl($komand);
		if(isset($result->output->data->$zamer_id->row->f14561) && $result->output->data->$zamer_id->row->f14561!=null)$return=$result->output->data->$zamer_id->row->f14561;
		else $return='Не указан телефон';
		return $return;
	}
	public static function accepted($zamer_id){
		$komands=new Comand('update',291,array('f14751'=>'1'),[['id','=',$zamer_id]]);
		$results=new Curl($komands);
		$komand=new Comand('read',291,['id','f5041','f14741','f14751'],[['id','=',$zamer_id]]);
		$result=new Curl($komand);
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
    public static function file_zamer_list_add($zamer_id,$name_file)
    {
        //$name_file='/wdimg/zamerlist/'.$name_file; f14781
        $komands = new Comand('update', 291, array('f14781' => $name_file), [['id', '=', $zamer_id]]);
        $results = new Curl($komands);
        return $results->status;
    }
	public static function get_param($obj,$arr_par){		
		foreach($obj as $obj1){
			if(is_object($obj1)){			
				foreach($obj1 as $id=>$obj2){
					for($i=0;$i<count($arr_par);$i++){
						$param[$arr_par[$i]]=$obj2->row->$arr_par[$i];		
					}
					$result[]=$param;
				}
			}
		}
		return $result;
	}
	public static function get_montaj_today($chat_id){
        $komand=new Comand('read',311,['id','f12370'],[['f12370','<>','0000-00-00 00:00:00','and'],['status','=','0']],[['f12370','ASC']]);
        $result=new Curl($komand);
        $date1=date('Y-m-d');
        $count=0;
        if($result->status==true && isset($result->output->data)) {
            if ($result->output->count > 0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12370));
                    if ($date_montaj == $date1) {
                        $id_montaj = $data->row->id;
                        $date1 = date("d-m-Y в H:i", strtotime($data->row->f12370));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x88\x9A <b>Монтаж № $id_montaj</b> на $date1", 'key_montaj', $id_montaj);
                        $count++;
                    }
                }
                if ($count == 0) TelegrammApi::sendMessage($chat_id, "на сегодня нет монтажей", 'key_montajnik');
            } else {
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montajnik');
            }
        } else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_montaj');}
    }
    public static function get_montaj_tomorrow($chat_id){
        $komand=new Comand('read',311,['id','f12370'],[['f12370','<>','0000-00-00 00:00:00','and'],['status','=','0']],[['f12370','ASC']]);
        $result=new Curl($komand);
        $dey=date('d')+1;
        $dey=$dey<10?'0'.$dey:$dey;
        $date1=date('Y-m-').$dey;
        $count=0;
        if($result->status==true && isset($result->output->data)){
            if($result->output->count>0) {
                foreach ($result->output->data as $data) {
                    $date_montaj = date("Y-m-d", strtotime($data->row->f12370));
                    if ($date_montaj == $date1) {
                        $id_montaj = $data->row->id;
                        $date1 = date("d-m-Y в H:i", strtotime($data->row->f12370));
                        TelegrammApi::sendMessage($chat_id, "\xF0\x9F\x88\x9A <b>Монтаж № $id_montaj</b> на $date1", 'key_montaj', $id_montaj);
                        $count++;
                    }
                }
                if($count==0)TelegrammApi::sendMessage($chat_id, "на завтра нет монтажей", 'key_montajnik');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montaj');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_montaj');}
    }
    public static function get_montaj_next($chat_id){
        $komand=new Comand('read',311,['id','f12370'],[['f12370','<>','0000-00-00 00:00:00','and'],['status','=','0']],[['f12370','ASC']]);
        $result=new Curl($komand);
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
                if($count==0)TelegrammApi::sendMessage($chat_id, "на будущих монтажей", 'key_montajnik');
            }
            else{
                TelegrammApi::sendMessage($chat_id, "Нет запланированых монтажей", 'key_montaj');
            }

        }else { TelegrammApi::sendMessage($chat_id,$result->status_message,'key_montaj');}
    }
    public static function get_montaj_detal($montaj_id){
        $komand=new Comand('read',311,['id','f12370','f17040','f17050','f12870','f5521','f16020','f17070','f12890','f17080','f17090'],[['id','=',$montaj_id]]);
        $result=new Curl($komand);
        $data=$result->output->data->$montaj_id->row;
        if(isset($data->id)    && $data->id!=null)$nomer=$data->id; else $nomer='eror';
        if(isset($data->f12370) && $data->f12370!=null)$date=$data->f12370; else $date='1900-01-01 00:00:00';
        if(isset($data->f17040)&& $data->f17040!=null)$fio=$data->f17040; else $fio='не указано';
        if(isset($data->f17050)&& $data->f17050!=null)$tell=$data->f17050; else $tell='не указан';
        if(isset($data->f12870)&& $data->f12870!=null)$tel2=$data->f12870; else $tel2='не указан';
        if(isset($data->f5521) && $data->f5521!=null)$addres=$data->f5521; else $addres='не указан';
        if(isset($data->f16020) && $data->f16020!=null)$coment=$data->f16020; else $coment='-';
        if(isset($data->f17070) && $data->f17070!=null)$montajnik=$data->f17070; else $montajnik='-';
        if(isset($data->f12890) && $data->f12890!=null)$soglasovan=$data->f12890; else $soglasovan='-';
        $ar['message']=Api::textmessegemontaj($nomer,$date,$fio,$tell,$tel2,$addres,$coment,$montajnik,$soglasovan);
        $ar['coordinate']['lat']=$data->f17080;
        $ar['coordinate']['long']=$data->f17090;
        return $ar;
    }
	public static function textmessege ($nomer,$date,$fio,$tell,$addres,$coment,$zamershik=null,$dopinfo){
		$time=date("H:i",strtotime($date));
		$date=date("d-m-Y",strtotime($date));
		$textmessege="Замер №$nomer\n Дата: $date  Время: $time\n Клиент: $fio \n Телефон: $tell\n Адрес:  $addres\n Комментарий: $coment\n Замерщик:   $zamershik  \n $dopinfo ";
		return $textmessege;
	}
    public static function textmessegemontaj ($nomer,$date,$fio,$tell,$tel2,$addres,$coment,$montajnik,$soglasovan){
        $time=date("H:i",strtotime($date));
        $date=date("d-m-Y",strtotime($date));
        $textmessege="Монтаж №$nomer\n Дата:  $date Время:  $time\n Клиент:   $fio \n Телефон:  $tell\n Доп.Телефон:  $tel2\n Адрес: $addres\n Монтажник:   $montajnik \n Монтаж согласован:   $soglasovan \n Комментарий:  $coment\n";
        return $textmessege;
    }
}