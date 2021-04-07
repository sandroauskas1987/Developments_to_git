<?PHP

/**
 * Class TelegrammBot
 */
class TelegrammBot{
	public $admin_telegram_id=['451029189'];//root id администраторов которые будут получать данные не зависимо от закрепления  --'451029189'-- Задорожний ---
	public $in_arr;
	public $from;
	public $chat_id;
	public $user_id;
	public $sotrud_id;
    public $sotrud_group=[];
	public $message_id;
	public $bot_command;
	public $text;
	public $Api;
	public $data;
	public $is_admin=false;


    /**
     * @param $array
     * @param $incoming_message_object
     */
    public function __construct($array,$incoming_message_object){
		$this->in_arr=$array;

        //Все запросы пишем в Кб и заодно проверяем доступен ли сервер
        $curl_result=new KbApiCurl('test connection');
        if($curl_result->status==true)
        {
                if (array_key_exists('message', $array)) {
                    $this->chat_id = $array['message']['chat']['id'];
                    $this->user_id = $array['message']['from']['id'];
                    if (in_array($this->user_id, $this->admin_telegram_id)) $this->is_admin = true; else $this->is_admin = false;
                    $this->from = isset($this->in_arr['message']['from']['first_name']) ? $this->in_arr['message']['from']['first_name'] : '';
                    $this->from = isset($this->in_arr['message']['from']['last_name']) ? $this->from . ' ' . $this->in_arr['message']['from']['last_name'] : $this->from . '';
                    $this->message_id = $array['message']['message_id'];
                    if(!$this->get_sotrud_id()) {
                        KbApi::log_add_kb($this->user_id, $this->chat_id, $this->message_id, $this->text, $this->data, $incoming_message_object, $this->sotrud_id, $this->from);
                        TelegrammApi::sendMessage($this->chat_id,'Неизвестный сотрудник','key_default');
                        die('Access denied');
                    }
                    // запишим все сообщения в сессию, для очистки в нужны момент
                    TelegrammApi::chat_log_add($this->chat_id,$this->message_id);
                    // Выполнение сценария
                    $this->script();
                    // команды
                    if (array_key_exists('entities', $array['message'])) {
                        foreach ($array['message']['entities'] as $entities) {
                            if ($entities['type'] == 'bot_command') {
                                $this->bot_command = substr($array['message']['text'], $entities['offset'], $entities['length']);
                                $this->run_command();
                            }
                        }
                    }
                    // Текст
                    if (array_key_exists('text', $array['message'])) {
                        $this->text = $array['message']['text'];
                        $this->reply_message();
                    }
                    // Фото
                    if (array_key_exists('photo', $array['message'])) {
                        if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                            if ($_SESSION['Script_name'] == 'Photo') {
                                $this->script();
                            }
                        }elseif (!array_key_exists('Script_name',$_SESSION)){
                            $_SESSION['Script_name'] = 'Photo';
                            $_SESSION['data'] = ["message_id"=>$this->message_id,"sotrud_id"=>$this->sotrud_id];
                            $this->script();
                        }
                        //TelegrammApi::sendMessage($this->chat_id, "Спасибо за фото! <b>Повешусь в рамочку!</b> Вышли мне файлы!", 'key_default');
                    }
                    // Файлы
                    if (array_key_exists('document', $array['message'])) {
                        if ($this->sotrud_id != 0 or in_array($this->user_id, $this->admin_telegram_id)) {
                            $file_id = $array['message']['document']['file_id'];
                            TelegrammApi::sendMessage($this->chat_id, "<b>Что за файл? ".$array['message']['document']['file_name']."</b>", 'key_files', $file_id);
                        } else TelegrammApi::sendMessage($this->chat_id, "Вы не являетесь сотрудником - я не могу принять от Вас файлы.", 'key_default');
                    }
                    if (array_key_exists('location', $array['message'])) {
                        TelegrammApi::sendMessage($this->chat_id, "Спасибо! <b>Отмечено успешно!</b>", 'key_default');
                    }

                } elseif (array_key_exists('callback_query', $array)) {
                    $this->chat_id = $array['callback_query']['message']['chat']['id'];
                    $this->user_id = $array['callback_query']['from']['id'];
                    $this->sotrud_id = $this->get_sotrud_id();
                    $this->text = $array['callback_query']['message']['text'];
                    $this->from = isset($this->in_arr['callback_query']['from']['first_name']) ? $this->in_arr['callback_query']['from']['first_name'] : " ";
                    $this->from .= isset($this->in_arr['callback_query']['from']['last_name']) ? ' ' . $this->in_arr['callback_query']['from']['last_name'] : " ";
                    $this->message_id = $array['callback_query']['message']['message_id'];
                    $this->data = $array['callback_query']['data'];
                    if(!$this->get_sotrud_id()) {
                        KbApi::log_add_kb($this->user_id, $this->chat_id, $this->message_id, $this->text, $this->data, $incoming_message_object, $this->sotrud_id, $this->from);
                        TelegrammApi::sendMessage($this->chat_id,'Неизвестный сотрудник. Доступ ограничен.','key_default');
                        die('Access denied');
                    }
                    // Выполнение сценария
                    $this->script();
                    // Выполнения команд с кнопок колбек клавиатуры
                    $this->callback_command();
                }
                KbApi::log_add_kb($this->user_id, $this->chat_id, $this->message_id, $this->text, $this->data, $incoming_message_object, $this->sotrud_id, $this->from);
            }
        else {
            TelegrammApi::sendMessage($array['message']['chat']['id'],"Serwer error: ".$curl_result->status_message);
        }
	}
	protected function script(){
        if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
            $run_script_Class=$_SESSION['Script_name'];
            new $run_script_Class($this->in_arr);
        }
        return;
    }
	//получаем ID сотрудника с КБ по ID телеграмма
    /**
     * @return bool
     */
    protected function get_sotrud_id(){
	    $flag=false; $users_name=null;
		$obj=KbApi::get_user();
		if($obj->status==true && isset($obj->output->data)){
            $data=$obj->output->data;
			foreach ($data as $val){
                $users_name[$val->row->f1400]=$val->row->f483;
				if(isset($val->row->id) && $val->row->f13940==$this->chat_id){
					$this->sotrud_id = $val->row->id;
					if(isset($val->row->f484))$this->sotrud_group[] = $val->row->f484;
                    if(isset($val->row->f14300))$this->sotrud_group[] = $val->row->f14300;
					$flag=true;
				}
			}
		}elseif ($obj->status==false){
            TelegrammApi::sendMessage($this->chat_id,"User: ".$obj->status_message);
        }else{
            TelegrammApi::sendMessage($this->chat_id,"Доступ запрещён. Пользователь не опознан.");
        }
        if(isset($_SESSION) && !array_key_exists('users_name',$_SESSION)) {
            $_SESSION['users_name']=$users_name;
        }
		return $flag;
	}

	protected function callback_command(){
		$function=substr($this->data,0,strpos($this->data,'='));
        $params=explode(';',substr($this->data,strpos($this->data,'=')+1,strlen($this->data)));
		
		if(isset($function)){
			switch($function){
            case 'get_the_objects_by_distance':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'visit_to_object':
                    if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                        if ($_SESSION['Script_name'] == 'Visit_to_object') {
                            $this->script();
                        }
                    }elseif (!array_key_exists('Script_name',$_SESSION)){
                        $_SESSION['Script_name'] = 'Visit_to_object';
                        $_SESSION['data'] = ["object_id"=>$params['0'],"message_id"=>$this->message_id];
                        $this->script();
                    }
                    break;
            case 'get_Location_zamer':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_Location_montaj':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_Location_delivery':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_Location_objects':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_montaj_hide':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
			case 'get_montaj_detal':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_delivery_hide':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
            case 'get_delivery_detal':
                    KbApi::$function($this->chat_id,$this->message_id,$params['0']);
                    break;
			case 'get_zamer_hide':
                $result=KbApi::$function($params['0']);
                if(isset($result)){
                    foreach ($result as $val){
                        $id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
                        TelegrammApi::editMessageText($this->chat_id,$this->message_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_zamer',$id_zamer,$accepted);
                    }
                }
				break;
			case 'get_zamer_detal':			
				$message=KbApi::$function($params['0']);
				TelegrammApi::editMessageText($this->chat_id,$this->message_id,$message,'key_hide',$params['0']);
				break;
			case 'accepted':			
				$message=KbApi::$function($params['0']);
				if(isset($message)){
					foreach ($message as $val){
						$id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
						TelegrammApi::editMessageText($this->chat_id,$this->message_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_zamer',$id_zamer,$accepted);
					}
				}
				break;
			case 'file_zamer_list':
                TelegrammApi::editMessageText($this->chat_id,$this->message_id,"Выберите замер к которому прикрепить файл",'file_zamer_list',$params['0'],$this->sotrud_id);
			    break;
            case 'file_zamer_list_get':
                try {
                    $result = KbApi::$function($params['0']);
                    Logger::getLogger('photo')->log($result);
                    if ($result!=null && is_array($result)) {
                        foreach ($result as $val) {
                            $id_zamer = $val['id'];
                            $name_file = explode("\r\n", $val['f5001']);
                            foreach ($name_file as $filename) {
                                $filename=urlencode($filename);
                                $linc = 'https://lk.dostupokna.org/dop/wrapper.php?line_id=' . $id_zamer . '&file_name=' .$filename.'&file_field_id=5001';
                                $file = file_get_contents($linc);
                                if ($file) {
                                    $identifier= explode(".", $filename);
                                    $filename=md5($filename).".".$identifier[1];
                                    if(file_put_contents('c:/web/OSPanel/domains/localhost/cb/dop/tmbot/files/' . $filename, $file)){
                                        $linc ="https://lk.dostupokna.org/dop/tmbot/files/" . $filename; //"https://95001.prohoster.biz/tmbot/files/"
                                        $res = TelegrammApi::sendPhoto($this->chat_id, $linc, $this->message_id);
                                        Logger::getLogger('photo')->log($res);
                                    }
                                } //TelegrammApi::sendMessage($this->chat_id,"Нет файла.");
                            }
                        }
                    } //TelegrammApi::sendMessage($this->chat_id,"Нет файла!");
                }catch (Exception $e){
                    Logger::getLogger('photo')->log($e);
                }
                break;
            case 'file_zamer_foto_object_get':
                    try {
                        $result = KbApi::$function($params['0']);
                        Logger::getLogger('photo')->log($result);
                        if ($result!=null && is_array($result)) {
                            foreach ($result as $val) {
                                $id_zamer = $val['id'];
                                $name_file = explode("\r\n", $val['f5841']);
                                foreach ($name_file as $filename) {
                                    $filename=urlencode($filename);
                                    $linc = 'https://lk.dostupokna.org/dop/wrapper.php?line_id=' . $id_zamer . '&file_name=' .$filename.'&file_field_id=5841';
                                    Logger::getLogger('photo')->log($linc);
                                    $file = file_get_contents($linc);
                                    if ($file) {
                                        $identifier= explode(".", $filename);
                                        $filename=md5($filename).".".$identifier[1];
                                        Logger::getLogger('photo')->log($filename);
                                        if(file_put_contents('c:/web/OSPanel/domains/localhost/cb/dop/tmbot/files/' . $filename, $file)){
                                            $linc ="https://lk.dostupokna.org/dop/tmbot/files/" . $filename; //"https://95001.prohoster.biz/tmbot/files/"
                                            $res = TelegrammApi::sendPhoto($this->chat_id, $linc, $this->message_id);
                                            Logger::getLogger('photo')->log($res);
                                        }
                                    }
                                }
                            }
                        }
                    }catch (Exception $e){
                        Logger::getLogger('photo')->log($e);
                    }
                    break;
			case 'send_metering':
                if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                    if ($_SESSION['Script_name'] == 'Send_metering') {
                        $this->script();
                    }
                }elseif (!array_key_exists('Script_name',$_SESSION)){
                    $_SESSION['Script_name'] = 'Send_metering';
                    $_SESSION['data'] = ["zamer_id"=>$params['0'],"message_id"=>$this->message_id];
                    $this->script();
                }
			    break;
            case 'to_send_installation':
                    if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                        if ($_SESSION['Script_name'] == 'To_send_installation') {
                            $this->script();
                        }
                    }elseif (!array_key_exists('Script_name',$_SESSION)){
                        $_SESSION['Script_name'] = 'To_send_installation';
                        $_SESSION['data'] = ["montaj_id"=>$params['0'],"message_id"=>$this->message_id];
                        $this->script();
                    }
                    break;
            case 'file_zamer_list_add':
                $file_name=TelegrammApi::getFile($params['0']);
                if($file_name!=null){
                    if(KbApi::file_zamer_list_add($params['1'],$file_name))TelegrammApi::editMessageText($this->chat_id,$this->message_id,"Замерный лист добавлен к Замеру №".$params['1']." newfilename=".$file_name);
                }
                break;
			default:
				$message='Простите. эта команда еще в разработке. '.$function;
				TelegrammApi::editMessageText($this->chat_id,$this->message_id,$message);
				break;	
			}
		}
	}
	protected function run_command(){
		switch($this->bot_command){
			case '/start':
				$message = 'Добро пожаловать '.$this->in_arr['message']['from']['first_name'].' '.$this->in_arr['message']['from']['last_name'];
				TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
				break;
			case '/sotrudnik':
				$message = '/';
				TelegrammApi::sendMessage($this->chat_id,$message,'key_sotrud_menu');
				break;
			case '/status':
				$message ="<b>Значения иконок (статусы замеров)</b>
				\xE2\x80\xBC  - <i>Замер не принят в работу</i>
				\xE2\x9A\xAA  - <i>Замер ожидается</i>
				\xE2\x9D\x8C  - <i>Отказ по замеру</i>
				\xE2\x98\x91  - <i>Замер выполнен</i>
				\xE2\x9C\x85  - <i>Замер перешёл в договор</i>
				\xE2\x99\xA8  - <i>Замер просрочен!</i>";
				TelegrammApi::sendMessage($this->chat_id,$message);				
				break;
			case '/files':
				$message ="<b>Отправка боту файлов</b>
				Высылайте БОТу по одному файлу и выберите куда и в качестве чего его закрепить.";
				TelegrammApi::sendMessage($this->chat_id,$message);				
				break;
            case '/session':
                $message =isset($_SESSION)? json_encode($_SESSION):"no session".session_id();
                TelegrammApi::sendMessage($this->chat_id,$message);
                break;
            case '/sessionclear':
                session_destroy();
                break;
            case '/clearchat':
                TelegrammApi::chat_clear();
                break;
			default:
                TelegrammApi::sendMessage($this->chat_id,"Доступны команды:\n"."/start\n/sotrudnik\n/status\n/files\n/sessionclear\n/clearchat\n");
				break;
		}
	}
	protected function reply_message(){
		if($this->sotrud_id!=0 or in_array($this->user_id,$this->admin_telegram_id)){
			switch($this->text){
                case 'Новый объект':
                    if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                        if ($_SESSION['Script_name'] == 'NewDirectSale') {
                            $this->script();
                        }
                    }elseif (!array_key_exists('Script_name',$_SESSION)){
                        $_SESSION['Script_name'] = 'NewDirectSale';
                        $_SESSION['data'] = [];
                        $this->script();
                    }
                    break;
                case 'Прямые продажи':
                    $message = 'Меню->Прямые продажи';
                    TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
                    break;
                case 'Объекты рядом':
                    if(isset($_SESSION) && array_key_exists('Script_name',$_SESSION)) {
                        if ($_SESSION['Script_name'] == 'Objects_near') {
                            $this->script();
                        }
                    }elseif (!array_key_exists('Script_name',$_SESSION)){
                        $_SESSION['Script_name'] = 'Objects_near';
                        $this->script();
                    }
                    //$message = 'Меню->Объекты рядом';
                    //TelegrammApi::sendMessage($this->chat_id,$message,'key_objects_nearby',null);
                    break;
                case 'Доставки':
                    $message = 'Меню Доставки';
                    TelegrammApi::sendMessage($this->chat_id,$message,'key_delivery_menu');
                    break;
                case 'Доставки сегодня':
                    KbApi::get_delivery_today($this->chat_id);
                    break;
                case 'Доставки завтра':
                    KbApi::get_delivery_tomorrow($this->chat_id);
                    break;
                case 'Все предстоящие доставки':
                    KbApi::get_delivery_next($this->chat_id);
                    break;
				case 'Замеры':
					$message = 'Меню Замеры';
					TelegrammApi::sendMessage($this->chat_id,$message,'key_sotrud_menu');
					break;
                case 'Монтажи':
                    $message = 'Меню Монтажей';
                    TelegrammApi::sendMessage($this->chat_id,$message,'key_montaj_menu');
                    break;
                case 'Монтажи сегодня':
                    KbApi::get_montaj_today($this->chat_id);
                    break;
                case 'Монтажи завтра':
                    KbApi::get_montaj_tomorrow($this->chat_id);
                    break;
                case 'Все предстоящие монтажи':
                    KbApi::get_montaj_next($this->chat_id);
                    break;
				case 'На сегодня':
						$zamer_list=KbApi::get_zamer_today($this->sotrud_id,$this->is_admin);
						if(isset($zamer_list)){
							foreach ($zamer_list as $id=>$val){
								$id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
								TelegrammApi::sendMessage($this->chat_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_zamer',$id_zamer,$accepted);
							}
						}else{
								TelegrammApi::sendMessage($this->chat_id,"Нет замеров на сегодня",'key_sotrud_menu');
						}
					break;
				case 'На завтра':
						$zamer_list=KbApi::get_zamer_tomorrow($this->sotrud_id,$this->is_admin);
						if(isset($zamer_list)){
							foreach ($zamer_list as $id=>$val){	
								$id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
								TelegrammApi::sendMessage($this->chat_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_zamer',$id_zamer,$accepted);
							}
						}else{
								TelegrammApi::sendMessage($this->chat_id,"Нет замеров на завтра",'key_sotrud_menu');
						}			
					break;
				case 'Все предстоящие':
						$zamer_list=KbApi::get_zamer_next($this->sotrud_id,$this->is_admin);
						if(isset($zamer_list)){
							foreach ($zamer_list as $id=>$val){	
								$id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
								TelegrammApi::sendMessage($this->chat_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_zamer',$id_zamer,$accepted);
							}
						}else{
								TelegrammApi::sendMessage($this->chat_id,"Нет предстоящих замеров",'key_sotrud_menu');
						}			
					break;
				case 'Все за 40 дней':
						$zamer_list=KbApi::get_zamer_previous($this->sotrud_id,$this->is_admin);
						if(isset($zamer_list)){
							foreach ($zamer_list as $id=>$val){	
								$id_zamer=$val['id']; $date1=$val['date'];$icon=$val['icon'];$accepted=$val['accepted'];
								TelegrammApi::sendMessage($this->chat_id,"$icon <b>Замер № $id_zamer</b> на $date1",'key_inform',$id_zamer,$accepted);
							}
						}else{
								TelegrammApi::sendMessage($this->chat_id,"у Вас нет предыдущих замеров",'key_sotrud_menu');
						}			
					break;
				case 'Назад':
                    //TelegrammApi::chat_clear();
					$message = 'Выберите пункт меню.';
					TelegrammApi::sendMessage($this->chat_id,$message,'key_default_menu');
					break;
                case 'Вернуться':
                    $message = 'Выберите пункт меню.';
                    TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
                    break;
                case 'Отмена':
                    unset($_SESSION['Script_name']);
                    unset($_SESSION['data']);
                    //$message = 'Выберите пункт меню.';
                    //TelegrammApi::sendMessage($this->chat_id,$message,'key_direct_sale_menu');
                    break;
				default:
					//$message = 'Ласковое слово и боту приятно';
					//TelegrammApi::sendMessage($this->chat_id,$message,'key_default');
					break;
			}	
			
		}else TelegrammApi::sendMessage($this->chat_id,"Вы не являетесь сотрудником - Информация не доступна",'key_default_menu');
		
	}	
}
