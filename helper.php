<?php
 /**
 * @author Rybalko Igor
 * @version 1.0
 * @copyright (C) 2018 http://wolfweb.com.ua
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
*/

class CurrencyCbrfHelper{
	
	private $cacheFile = __DIR__ . '/data.json';

	static private $_instance;
	private function __conctract(){}
	private function __clone(){}
	static function getInstance(){
		if(!self::$_instance){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
 
	private function _writeCache()
	{
	    
	    file_put_contents($this->cacheFile, json_encode($this->_getCbrfRate()));
	 
	}

	private function _xmlAttribute($object, $attribute){
	    if(isset($object[$attribute])){
	        return (string) $object[$attribute];
	    }
	}
	 
	
	public function getRates($cache_time){

		$curTime = time(); 

		if (!file_exists($this->cacheFile)) {
		    $this->_writeCache($this->cacheFile);
		} else {
		    $fMtime = filemtime($this->cacheFile);
		    if (($curTime - $fMtime) > $cache_time) {
		        $this->_writeCache($this->cacheFile);
		    }
		}


		$rates = json_decode(file_get_contents($this->cacheFile), 1);

		return $rates;
	}

	private function _roundRate($rate){
	    $result = sprintf("%.2f", ceil( (float) $rate * 100) / 100);
	     return $result;
	}

	private function _getCbrfRate(){
		$date  = getdate();
		$result = [];
		if($date['mon'] < 10){
			$month =  '0' . $date['mon'];
		}else{
			$month = $date['mon'];
		}
		if($date['mday'] < 10){
			$mday =  '0' . $date['mday'];
		}else{
			$mday = $date['mday'];
		}
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $mday . '/' .$month . '/' . $date['year']);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		$xml = curl_exec($curl_handle);
		curl_close($curl_handle);
		$currency = simplexml_load_string($xml);
		$doc_date = $this->_xmlAttribute($currency, 'Date');
		if ($doc_date) {
			foreach($currency as $v){
				switch ((string) $v->CharCode){
					case 'USD':
						$rateUSD = $this->_roundRate($v->Value);
						break;
					case 'EUR':
						$rateEUR = $this->_roundRate($v->Value);
						break;
				}
			}
			$result = [
				'usd'   => $rateUSD,
				'eur'   => $rateEUR,
				'date'  => $doc_date
			];
		}

		return $result;
	}
}