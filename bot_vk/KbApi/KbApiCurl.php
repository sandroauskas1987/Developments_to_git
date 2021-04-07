<?PHP
class KbApiCurl{
	public $output;
    public $status=true;
    public $status_message;
	
	public function __construct($postData){
		$this->post_send($postData);
	}
	function post_send($postData){
		$curl=curl_init('http://lk.dostupokna.org/****/*****.php');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type'=>'application/json', 'type'=>'request','Access-Control-Allow-Origin'=>' *']);//
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,10);
        $server_output = curl_exec ($curl);
        $server_output=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $server_output);

        // Check HTTP status code
        if (!curl_errno($curl)) {
            switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                case 200: if ($server_output) {$this->output = self::JsonToObj($server_output);}
                          else {$this->status=true; $this->status_message="Ответ пуст";}
                    break;
                default:
                    $this->status=false; $this->status_message="Прошу прощения! \n HTTP code: ".$http_code;
            }
       }
        curl_close ($curl);
	}
    private static function JsonToObj($data){
        $jsonObj = json_decode($data);
        //Logger::getLogger('json_last_error')->log(json_last_error());
        if ($jsonObj === null && json_last_error() !== JSON_ERROR_NONE) {
            $t = new stdClass();
            $t->errors = ['code'=>444,'message'=>'Ошибка получения данных c функции json_decode'];
            $t->status = false;
            return $t;
        }
        return $jsonObj;
    }
}