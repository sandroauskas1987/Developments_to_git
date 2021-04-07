<?

/**
 * Class TelegrammApi
 */
class TelegrammApi{
	
	public static $Token='540574772:*****************************';
    public static $Url='https://api.telegram.org/bot';

    //отправка сообщений
    /**
     * @param $metod
     * @param $chat_id
     * @param $param
     */
    public function call($metod,$chat_id,$param){
		$call=self::$Url.self::$Token.'/'.$metod.'?chat_id='.$chat_id.$param.'';
		file_get_contents($call);	  
	}

	//Отправляем сообщение
    /**
     * @param $id
     * @param $message
     * @param null $keyboard
     * @param null $param1
     * @param null $param2
     * @param null $reply_to_message_id
     */
    static function sendMessage(
	$id,
    $message,       //текст сообщения
	$keyboard=null, //название клавиатуры 
	$param1=null,   //параметры для клавиатуры
	$param2=null,    //параметры для клавиатуры
    $reply_to_message_id=null
    )
	{
		$call=self::$Url.self::$Token."/sendMessage?chat_id=".$id."&parse_mode=HTML&text=";
		$call.=urlencode($message);
		switch ($keyboard){
			Case 'null': Break;
            Case 'key_clear':             $call.="&reply_markup=".RaplyKeyboardMarkup::key_clear(); Break;
            Case 'key_direct_sale_menu':  $call.="&reply_markup=".RaplyKeyboardMarkup::key_direct_sale_menu(); Break;
			Case 'key_default_menu':      $call.="&reply_markup=".RaplyKeyboardMarkup::key_default_menu(); Break;
			Case 'key_sotrud_menu' :      $call.="&reply_markup=".RaplyKeyboardMarkup::key_sotrud_menu(); Break;
			Case 'key_montaj_menu' :      $call.="&reply_markup=".RaplyKeyboardMarkup::key_montaj_menu(); Break;
            Case 'key_delivery_menu' :    $call.="&reply_markup=".RaplyKeyboardMarkup::key_delivery_menu(); Break;
            Case 'key_foto_object':       $call.="&reply_markup=".InlineKeyboardMarkup::key_foto_object(); Break;
            Case 'stage_of_construction': $call.="&reply_markup=".InlineKeyboardMarkup::stage_of_construction(); Break;
            Case 'activity':              $call.="&reply_markup=".InlineKeyboardMarkup::activity(); Break;
            Case 'marketing':             $call.="&reply_markup=".InlineKeyboardMarkup::marketing(); Break;
            Case 'consent_to_add':        $call.="&reply_markup=".InlineKeyboardMarkup::consent_to_add(); Break;
			Case 'key_zamer'  :           $call.="&reply_markup=".InlineKeyboardMarkup::key_zamer($param1,$param2);  Break;
			Case 'key_files'  :           $call.="&reply_markup=".InlineKeyboardMarkup::key_files(); Break;
            Case 'key_foto_zamer_list'  : $call.="&reply_markup=".InlineKeyboardMarkup::key_foto_zamer_list(); Break;
            Case 'key_montaj' :           $call.="&reply_markup=".InlineKeyboardMarkup::key_montaj($param1); Break;
            Case 'key_delivery' :         $call.="&reply_markup=".InlineKeyboardMarkup::key_delivery($param1); Break;
            Case 'key_objects_nearby':    $call.="&reply_markup=".InlineKeyboardMarkup::key_objects_nearby(); Break;
            Case 'key_objects':           $call.="&reply_markup=".InlineKeyboardMarkup::key_objects($param1); Break;
			Default: Break;
		}
        if($reply_to_message_id)$call.="&reply_to_message_id=".$reply_to_message_id;
        self::chat_log(file_get_contents($call.''));
	}

    /**
     * @param $shat_id
     * @param $latitude
     * @param $longitude
     * @param null $reply_to_message_id
     * @param null $reply_markup
     */
    static function sendLocation(
	    $shat_id,
        $latitude,
        $longitude,
        $reply_to_message_id=null,
        $reply_markup=null
    ){
        $call=self::$Url.self::$Token."/sendLocation?chat_id=".$shat_id."&latitude=".$latitude."&longitude=".$longitude;
        if($reply_to_message_id)$call.="&reply_to_message_id=".$reply_to_message_id;
        self::chat_log(file_get_contents($call.''));
    }

	//Редактируем сообщение
    /**
     * @param $id
     * @param $message_id
     * @param $message
     * @param null $keyboard
     * @param null $param1
     * @param null $param2
     */
    static function editMessageText(
	    $id,
        $message_id,
        $message,
        $keyboard=null,
        $param1=null,   //параметры для клавиатуры
        $param2=null    //параметры для клавиатуры
    )
    {
		$call=self::$Url.self::$Token."/editMessageText?chat_id=".$id."&message_id=".$message_id."&parse_mode=HTML&text=";
		$call.=urlencode($message);
        switch ($keyboard){
            Case null:               $call.="&reply_markup=".json_encode(['inline_keyboard' => []]); Break;
            Case 'key_hide' :          $call.="&reply_markup=".InlineKeyboardMarkup::key_hide($param1); Break;
            Case 'key_montaj' :        $call.="&reply_markup=".InlineKeyboardMarkup::key_montaj($param1); Break;
            Case 'key_montaj_hide' :   $call.="&reply_markup=".InlineKeyboardMarkup::key_montaj_hide($param1); Break;
            Case 'key_inform' :        $call.="&reply_markup=".InlineKeyboardMarkup::key_inform($param1);  Break;
            Case 'key_zamer':          $call.="&reply_markup=".InlineKeyboardMarkup::key_zamer($param1,$param2); Break;
            Case 'file_zamer_list' :   $call.="&reply_markup=".InlineKeyboardMarkup::file_zamer_list($param1); Break;
            Case 'key_delivery' :      $call.="&reply_markup=".InlineKeyboardMarkup::key_delivery($param1); Break;
            Case 'key_delivery_hide' : $call.="&reply_markup=".InlineKeyboardMarkup::key_delivery_hide($param1); Break;
        }

        self::chat_log(file_get_contents($call.''));
	}

    /**
     * @param $chat_id
     * @param $message_id
     */
    static function deleteMessage($chat_id,$message_id){
        $call=self::$Url.self::$Token."/deleteMessage?chat_id=".$chat_id."&message_id=".$message_id;
        Logger::getLogger('log_delete')->log(file_get_contents($call.''));
    }

    /**
     * @param $file_id
     * @return null
     */
    static function getFile($file_id){
        $call="https://api.telegram.org/bot".self::$Token."/getFile?file_id=$file_id";
        $array = json_decode(file_get_contents($call.''));
        $file_path=$array->result->file_path;
        $file_from_tgrm = "https://api.telegram.org/file/bot".self::$Token."/".$file_path;
        $explod=explode(".", $file_path);
        $ext =  end($explod);
        $name_file = $file_id.".".$ext;
        $file=file_get_contents($file_from_tgrm);// получим тело файла
        $file =base64_encode($file);//раскодируем для отправки

        $komand=new KbApiComand('files','1',null,null,null,$file,$name_file);
        $KbCurl=new KbApiCurl($komand);
        if($KbCurl->status)   return $KbCurl->output;
        else return null;
        // TelegrammApi::sendMessage('451029189','Status :'.$KbCurl->status.'  результат :'.$KbCurl->output);
    }

    /**
     * @param $chat_id
     * @param null $linc
     * @param null $message_id
     * @return mixed
     */
    static function sendPhoto($chat_id,$linc,$message_id){
	         $url = self::$Url . self::$Token . "/sendDocument";
            $post_fields = [
                'chat_id' => $chat_id,
                'reply_to_message_id'=>$message_id,
                //'caption'=>'Замерный лист замера №946',
                'document' => $linc
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type:multipart/form-data"
            ]);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            $output = curl_exec($ch);
            self::chat_log($output);
            curl_close($ch);
            //Logger::getLogger('photo')->log($output);
            return $output;
    }

    /**
     * @param $json_result
     */
    static function chat_log($json_result){
        $in_arr_result=json_decode($json_result,true);
        if($in_arr_result['ok']){
            $_SESSION['chat_log'][]=[
               'message_id'=> $in_arr_result['result']['message_id'],
                'chat_id'=> $in_arr_result['result']['chat']['id'],
            ];
        }else{
            //TelegrammApi::sendMessage('451029189','Ошибки при отправкке сообщений  KB_BOT:\n'.json_encode($json_result));
            Logger::getLogger('error_content')->log($in_arr_result);
        }
    }

    /**
     * @param $chat_id
     * @param $message_id
     */
    static function chat_log_add($chat_id,$message_id){

            $_SESSION['chat_log'][]=[
                'message_id'=> $message_id,
                'chat_id'=> $chat_id,
            ];

    }
    static function chat_clear(){
        if(isset($_SESSION['chat_log'])){
            foreach($_SESSION['chat_log'] as $id=>$message){
                self::deleteMessage($message['chat_id'],$message['message_id']);
                unset($_SESSION['chat_log'][$id]);
            }
        }
    }
}
