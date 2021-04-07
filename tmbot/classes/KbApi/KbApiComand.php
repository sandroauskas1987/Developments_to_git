<?PHP

/**
 * Class KbApiComand
 */
class KbApiComand{

    /**
     * @param $command
     * @param $table_id
     * @param $fields
     * @param null $filter
     * @param null $sort
     * @param null $file
     * @param null $filename
     * @param null $line_id
     */
    public function __construct($command,$table_id,$fields,$filter=Null,$sort=Null,$file=Null,$filename=Null,$line_id=Null){
		$this->command =$command;
		$this->table_id=$table_id;
		$this->fields  =$fields;
		if($filter!=Null){
			$this->filter=new KbApiComandfilter($filter);
		}
		if($sort!=Null){
			$this->sort=new stdClass();
			foreach($sort as $id=>$field){									
				$this->sort->{$field[0]}=$field[1];
			}
		}
		if($file!=Null)$this->file=$file;
        if($filename!=Null)$this->filename=$filename;
        if($line_id!=Null)$this->line_id=$line_id;
	}
}