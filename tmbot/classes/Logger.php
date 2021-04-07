<?php

/**
 * Class Logger
 */
class Logger
{
    //����������� ����������
    public static $PATH;
    protected static $loggers=[];
 
    protected $name;
    protected $file;
    protected $fp;
	/*
	require_once(dirname(__FILE__).'\Logger.php');
	Logger::$PATH = 'c:\RootCer\logs';
	Logger::getLogger('info')->log(array("REMOTE_ADDR"=>$_SERVER['REMOTE_ADDR'],"method"=>$method,"url"=>$_SERVER['REQUEST_URI']));
	*/

    /**
     * @param $name
     * @param null $file
     */
    public function __construct($name, $file=null){
        $this->name=$name;
        $this->file=$file;
 
        $this->open();
    }
 
    public function open(){
        if(self::$PATH==null){
            return ;
        }
 
        $this->fp=fopen($this->file==null ? self::$PATH.'/'.$this->name.'.log' : self::$PATH.'/'.$this->file,'a+');
    }

    /**
     * @param string $name
     * @param null $file
     * @return mixed
     */
    public static function getLogger($name='root',$file=null){
        if(!isset(self::$loggers[$name])){
            self::$loggers[$name]=new Logger($name, $file);
        }
 
        return self::$loggers[$name];
    }

    /**
     * @param $message
     */
    public function log($message){
        if(!is_string($message)){
            $this->logPrint($message);
 
            return ;
        }
 
        $log='';
		if( ! ini_get('date.timezone') )
			{
			   date_default_timezone_set('GMT');
			}
 
        $log.='['.date('D M d H:i:s Y',time()).'] ';
        if(func_num_args()>1){
            $params=func_get_args();
 
            $message=call_user_func_array('sprintf',$params);
        }
 
        $log.=$message;
        $log.="\n";
 
        $this->_write($log);
    }

    /**
     * @param $obj
     */
    public function logPrint($obj){
        ob_start();
 
        print_r($obj);
 
        $ob=ob_get_clean();
        $this->log($ob);
    }

    /**
     * @param $string
     */
    protected function _write($string){
        fwrite($this->fp, $string);
 
        //echo $string;
    }
 
    public function __destruct(){
        fclose($this->fp);
    }
}

?>