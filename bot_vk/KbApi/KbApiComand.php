<?PHP
class KbApiComand{
	
	public function __construct($command,$table_id,$fields,$filter=Null,$sort=Null,$file=Null,$filename=Null){
		$this->command =$command;
		$this->table_id=$table_id;
		$this->fields  =$fields;
		if($filter!=Null){
			$this->filter=new Comandfilter($filter);
		}
		if($sort!=Null){
			$this->sort=new stdClass();
			foreach($sort as $id=>$field){									
				$this->sort->{$field[0]}=$field[1];
			}
		}
		if($file!=Null)$this->file=$file;
        if($filename!=Null)$this->filename=$filename;
	}
}