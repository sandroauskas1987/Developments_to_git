<?PHP
class KbApiComandfilter{	
		
	public function __construct($filt_ar){
		
		foreach($filt_ar as $filtr_row){
			$filterfield=$filtr_row[0];
			$this->$filterfield=new stdClass();
			$this->$filterfield->term=$filtr_row[1];
			$this->$filterfield->value=$filtr_row[2];
			if(isset($filtr_row[3]))$this->$filterfield->union=$filtr_row[3];
		}
	}
}