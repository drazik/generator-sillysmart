<?php
/**
 * Tool SLS_Date - Dates Handling
 *  
 * @author Laurent Bientz
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0 
 */ 
class SLS_Date
{
	private $_date 		= "";
	private $_day 		= "00";
	private $_month 	= "00";
	private $_year 		= "0000";
	private $_hour 		= "00";
	private $_minute 	= "00";
	private $_second	= "00";
	private $_monthLitteral = "";
	private $_dayLitteral = "";
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $date the starting date (date|datime|timestamp)
	 * @since 1.0
	 */
	public function __construct($date)
	{
		$this->_date = $date;		
		$this->explodeDate();		
	}
		
	/**
	 * Find date type and explode it into day,month,year,hour,minute,second
	 *
	 * @access private
	 * @since 1.0
	 */
	private function explodeDate()
	{
		if (!empty($this->_date))
		{
			if ($this->isDate($this->_date))
				$this->cutDate();			
			else if ($this->isDateTime($this->_date))
				$this->cutDateTime();			
			else if ($this->isTimestamp($this->_date))
				$this->cutTimestamp();		
		}
	}
	
	/**
	 * Cut date format
	 *
	 * @access private
	 * @since 1.0
	 */
	private function cutDate()
	{
		$dateExploded = explode("-",$this->_date);
		
		$this->_year 	= $dateExploded[0];
		$this->_month 	= $dateExploded[1];
		$this->_day 	= $dateExploded[2];
		$this->getLitteralArgs();
	}
	
	/**
	 * Get litteral args
	 *
	 * @access private
	 * @since 1.0
	 */
	private function getLitteralArgs()
	{
		$this->_monthLitteral = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_'.strtoupper(date('F', mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day, $this->_year)))];
		$this->_dayLitteral = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_'.strtoupper(date('l', mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day, $this->_year)))];
	}

	/**
	 * Cut datetime format
	 *
	 * @access private
	 * @since 1.0
	 */
	private function cutDateTime()
	{
		$dateExploded = explode("-",substr($this->_date,0,10));
		$timeExploded = explode(":",substr($this->_date,11,8));
		
		$this->_year 	= $dateExploded[0];
		$this->_month 	= $dateExploded[1];
		$this->_day 	= $dateExploded[2];
		$this->_hour 	= $timeExploded[0];
		$this->_minute 	= $timeExploded[1];
		$this->_second 	= $timeExploded[2];
		$this->getLitteralArgs();
	}
	
	/**
	 * Cut timestamp format
	 *
	 * @access private
	 * @since 1.0
	 */
	private function cutTimestamp()
	{
		$this->_year 	= date("Y",$this->_date);
		$this->_month 	= date("m",$this->_date);
		$this->_day 	= date("d",$this->_date);
		$this->_hour 	= date("H",$this->_date);
		$this->_minute 	= date("i",$this->_date);
		$this->_second 	= date("s",$this->_date);
		$this->getLitteralArgs();
	}
	
	/**
	 * Get a date relative from your current date
	 * 
	 * @access public
	 * @param float $float
	 * @return SLS_Date $date new SLS_Date object
	 * @since 1.0
	 */
	public function getDay($float)
	{
		if (!is_numeric($float))
			SLS_Tracing::addTrace(new Exception('Warning - The function SLS_Date::getDay() need a numeric argument'));
		$dif = 86400*abs($float);
		$timeStamp = SLS_Date::dateTimeToTimestamp($this->_year."-".$this->_month."-".$this->_day." ".$this->_hour.":".$this->_minute.":".$this->_second);
		$date = ($float < 0) ? $timeStamp-$dif : $timeStamp+$dif;
		return new SLS_Date($date);	
	}
	
	/**
	 * Get the diff between current object date & now()
	 *
	 * @access public
	 * @param string $date if you doesn't want to use current timestamp
	 * @return array array("delta" => "3", "unite" => "s|m|h|d")
	 * @since 1.0.1 
	 */
	public function getDiff($date="")
	{
		// Both dates between we want to get interval
		$time = strtotime($this->_year."-".$this->_month."-".$this->_day." ".$this->_hour.":".$this->_minute.":".$this->_second);
		$curr = (!empty($date) && strtotime($date) !== false) ? strtotime($date) : time();

		// Interval in seconds
		$shift = ($curr - $time > 0) ? ($curr - $time) : ($time - $curr);
	
		// Seconds
		if ($shift < 45)
		{
			$diff = $shift;
			$term = "s";
		}
		// Minutes
		else if ($shift < 2700)
		{
			$diff = round($shift / 60);
			$term = "i";
		}
		// Hours
		else if ($shift < 64800)
		{
			$diff = round($shift / 60 / 60);
			$term = "h";
		}
		// Days
		else if ($shift < 453600)
		{
			$diff = round($shift / 60 / 60 / 24);
			$term = "d";
		}
		// Weeks
		else if ($shift < 1814400)
		{
			$diff = round($shift / 60 / 60 / 24 / 7);
			$term = "w";
		}
		// Months
		else if ($shift < 21772800)
		{
			$diff = round($shift / 60 / 60 / 24 / 7 / 4);
			$term = "m";
		}
		// Years
		else
		{
			$diff = round($shift / 60 / 60 / 24 / 7 / 4 / 12);
			$term = "y";
		}
		
		return array("delta"=>$diff,"unite"=>$term);
	}
	
	/**
	 * Delay a date from a given number & a given unit
	 * 
	 * @access public
	 * @param int $delay desired delay interval
	 * @param string $unit unit delay
	 * @return string part of date delayed wanted
	 * @since 1.0.8
	 */
	public function delay($delay=6,$unit="day")
	{
	    if (!in_array(strtolower($unit),array("year","month","day","hour","minute","second")) || !is_int(intval($delay)))
	        return false;
	    
	    $this->_date = strtotime("-".intval($delay)." ".strtolower($unit),strtotime($this->_year."-".$this->_month."-".$this->_day." ".$this->_hour.":".$this->_minute.":".$this->_second));        
	    $this->explodeDate();
	
	    return $this->{_.strtolower($unit)};
	}
	
	/**
	 * Get date part
	 *
	 * @access public
	 * @param string $mode the date pattern ('TIME','FULL_TIME','DATE','MONTH_LITTERAL','FULL_LITTERAL','FULL_LITTERAL_TIME','MONTH_LITTERAL_TIME') or direct php date() pattern (use false in 2nd parameter)
	 * @param bool $predefined true if you fill a predefined constant (see $mode pattern), else false (default true)
	 * @return string $date the date part
	 * @since 1.0
	 */
	public function getDate($mode="time",$predefined=true)
	{
		$date = "";
		if ($predefined && empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_DATE_PATTERN_'.strtoupper($mode)]))
			return SLS_Tracing::addTrace(new Exception("Argument should be TIME| FULL_TIME | DATE | MONTH_LITTERAL | FULL_LITTERAL | FULL_LITTERAL_TIME | MONTH_LITTERAL_TIME in SLS_Date::getDate();"));
		$pattern = ($predefined) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_DATE_PATTERN_'.strtoupper($mode)] : $mode;
		for($i=0;$i<$count = strlen($pattern);$i++)
		{
			$caract = $pattern{$i};			
			switch($caract)
			{
				case "\\":
					$date .= ($i != ($count-1)) ? $pattern{($i+1)} : '';
					$i++;
					break;
				case "H":
					$date .= $this->_hour;
					break;
				case "d":
					$date .= $this->_day;
					break;
				case "m":
					$date .= $this->_month;
					break;
				case "Y":
					$date .= $this->_year;
					break;
				case "F":
					$date .= ucfirst($this->_monthLitteral);
					break;
				case "M":
					$date .= ucfirst(substr($this->_monthLitteral,0,3));
					break;
				case "l":
					$date .= ucfirst($this->_dayLitteral);
					break;				
				case "i":
					$date .= $this->_minute;
					break;
				case "s":
					$date .= $this->_second;
					break;					
				default:
					$date .= date($caract,SLS_Date::dateTimeToTimestamp($this->_date));
					break;
			}
		}
			
		return $date;
	}

	/**
	 * Generic getter
	 * 
	 * @access public
	 * @param string $key the key you want
	 * @return string $value the value of your wanted key
	 */
	public function __get($key)
	{
	    return (isset($this->{_.$key})) ? $this->{_.$key} : "";
	}
		
	/**
	 * Check if is valid date
	 *
	 * @access public static	 
	 * @param string $month the month number
	 * @param string $day the day number
	 * @param string $year the year
	 * @return bool $valid true if yes, else false
	 * @see SLS_Date::isDate
	 * @see SLS_Date::isDateTime
	 * @see SLS_Date::isTimestamp
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Date::isValidDate("04","12","1972"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_Date::isValidDate("02","29","1995"));
	 * // will produce : false
	 */
	public static function isValidDate($month,$day,$year)
	{
		try 
		{
			return checkdate(intval($month),intval($day),intval($year));
		}
		catch (Exception $e)
		{
			SLS_Tracing::addTrace($e);
			return false;
		}
	}
	
	/**
	 * Check that date is in date format (yyyy-mm-dd)	 
	 * Warning: only check syntactically the date (not gregorian)
	 *
	 * @access public static	 
	 * @param string $date the date to check
	 * @return bool $valid true if yes, else false
	 * @see SLS_Date::isValidDate
	 * @see SLS_Date::isDateTime
	 * @see SLS_Date::isTimestamp
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Date::isDate("1986-04-30"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_Date::isDate("1986-04-3"));
	 * // will produce : false
	 */
	public static function isDate($date)
	{
		if (strlen($date) == 10 && strpos($date,"-") !== FALSE)
		{
			$dateExploded = explode("-",$date);			
			if (is_array($dateExploded) && !empty($dateExploded) && count($dateExploded) == 3)
			{				
				if (self::isValidDate($dateExploded[1],$dateExploded[2],$dateExploded[0]))
					return true;
				else
					return false;
			}
		}
		return false;
	}

	/**
	 * Check that date is in datetime format (yyyy-mm-dd hh:mm:ss)	 
	 * Warning: only check syntactically the date (not gregorian)
	 *
	 * @access public static	 
	 * @param string $date the date to check
	 * @return bool $valid true if yes, else false
	 * @see SLS_Date::isValidDate
	 * @see SLS_Date::isDate
	 * @see SLS_Date::isTimestamp
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Date::isDateTime("1986-04-30 12:22:10"));
	 * // will produce
	 * true
	 * @example 
	 * var_dump(SLS_Date::isDateTime("1986-04-30 12:22:1"));
	 * // will produce
	 * false
	 */
	public static function isDateTime($date)
	{
		if (strlen($date) == 19 && strpos($date,"-") !== FALSE && strpos($date,":") !== FALSE)
		{
			$dateExploded = explode("-",substr($date,0,10));
			$timeExploded = explode(":",substr($date,11,8));
			if (is_array($dateExploded) && !empty($dateExploded) && count($dateExploded) == 3 
			 && is_array($timeExploded) && !empty($timeExploded) && count($timeExploded) == 3)
			{
				if (self::isValidDate($dateExploded[1],$dateExploded[2],$dateExploded[0]))
					return true;
				else
					return false;
			}						
		}
		return false;
	}
	
	/**
	 * Check that date is in timestamp format
	 * Warning: only check syntactically the date (not gregorian)
	 *
	 * @access public static	 
	 * @param string $date the date to check
	 * @return bool $valid true if yes, else false
	 * @see SLS_Date::isValidDate
	 * @see SLS_Date::isDate
	 * @see SLS_Date::isDateTime
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_Date::isTimestamp("1258478192"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_Date::isTimestamp("125847819z"));
	 * // will produce : false
	 */
	public static function isTimestamp($date)
	{
		if (!self::isDate($date) && !self::isDateTime($date) && is_numeric($date))
		{
			$dateExploded = explode("-",date("Y-m-d",$date));
			if (is_array($dateExploded) && !empty($dateExploded) && count($dateExploded) == 3)
			{
				if (self::isValidDate($dateExploded[1],$dateExploded[2],$dateExploded[0]))
					return true;
				else
					return false;
			}
		}
		return false;
	}
	
	/**
	 * Convert date to datetime	 
	 *
	 * @access public static
	 * @param string $date the date
	 * @return string $datetime the datetime
	 * @see SLS_Date::dateToTimestamp
	 * @see SLS_Date::dateTimeToDate
	 * @see SLS_Date::dateTimeToTimestamp
	 * @see SLS_Date::timestampToDate
	 * @see SLS_Date::timestampToDateTime
	 * @since 1.0
	 */
	public static function dateToDateTime($date="")
	{
		$tmpDate = "00-00-00";
				
		if (self::isDate($date))
			return $date." 00:00:00";		
		else if (self::isTimestamp($date))
			return self::timestampToDateTime($date);
		else if (self::isDateTime($date))
			return $date;
		else
			return $tmpDate." 00:00:00";
	}
	
	/**
	 * Convert date to timestamp	 
	 *
	 * @access public static
	 * @param string $date the date
	 * @return string $timestamp the timestamp
	 * @see SLS_Date::dateToDateTime
	 * @see SLS_Date::dateTimeToDate
	 * @see SLS_Date::dateTimeToTimestamp
	 * @see SLS_Date::timestampToDate
	 * @see SLS_Date::timestampToDateTime
	 * @since 1.0
	 */
	public static function dateToTimestamp($date="")
	{		
		$dateExploded = explode("-",$date);
		
		if (self::isDate($date))
			return mktime(0,0,0,$dateExploded[1],$dateExploded[2],$dateExploded[0]);		
		else if (self::isDateTime($date))
			return self::dateTimeToTimestamp($date);
		else if (self::isTimestamp($date))
			return $date;
		else
			return mktime(0,0,0,0,0,0);		
	}
	
	/**
	 * Convert datetime to date
	 *
	 * @access public static
	 * @param string $date the datetime
	 * @return string $date the date
	 * @see SLS_Date::dateToDateTime
	 * @see SLS_Date::dateToTimestamp
	 * @see SLS_Date::dateTimeToTimestamp
	 * @see SLS_Date::timestampToDate
	 * @see SLS_Date::timestampToDateTime
	 * @since 1.0
	 */
	public static function dateTimeToDate($dateTime="")
	{
		$tmpDate = "00-00-00";
		
		if (self::isDateTime($dateTime))
			return substr($dateTime,0,10);
		else if (self::isTimestamp($dateTime))
			return self::timestampToDate($dateTime);
		else if (self::isDate($dateTime))
			return $dateTime;
		else
			return $tmpDate;
			
	}
	
	/**
	 * Convert datetime to timestamp
	 *
	 * @access public static
	 * @param string $date the datetime
	 * @return string $timestamp the timestamp
	 * @see SLS_Date::dateToDateTime
	 * @see SLS_Date::dateToTimestamp
	 * @see SLS_Date::dateTimeToDate
	 * @see SLS_Date::timestampToDate
	 * @see SLS_Date::timestampToDateTime
	 * @since 1.0
	 */
	public static function dateTimeToTimestamp($dateTime="")
	{
		$tmpDate = "00-00-00";
		$tmpTime = "00:00:00";
		
		if (self::isDateTime($dateTime))
		{		
			$dateExploded = explode("-",substr($dateTime,0,10));
			$timeExploded = explode(":",substr($dateTime,11,8));
			return mktime($timeExploded[0],$timeExploded[1],$timeExploded[2],$dateExploded[1],$dateExploded[2],$dateExploded[0]);
		}
		else if (self::isDate($dateTime))
			return self::dateToTimestamp($dateTime);
		else if (self::isTimestamp($dateTime))
			return $dateTime;
		else			
			return mktime(0,0,0,0,0,0);		
	}
	
	/**
	 * Convert timestamp to date
	 *
	 * @access public static
	 * @param string $date the timestamp
	 * @return string $date the date
	 * @see SLS_Date::dateToDateTime
	 * @see SLS_Date::dateToTimestamp
	 * @see SLS_Date::dateTimeToDate
	 * @see SLS_Date::dateTimeToTimestamp
	 * @see SLS_Date::timestampToDateTime
	 * @since 1.0
	 */
	public static function timestampToDate($timestamp="")
	{
		$tmpDate = "00-00-00";
		
		if (self::isTimestamp($timestamp))
			return date("Y-m-d",$timestamp);
		else if (self::isDateTime($timestamp))
			return self::dateTimeToDate($timestamp);
		else if (self::isDate($timestamp))
			return $timestamp;
		else
			return $tmpDate; 			
	}
	
	/**
	 * Convert timestamp to datetime
	 *
	 * @access public static
	 * @param string $date the timestamp
	 * @return string $datetime the datetime
	 * @see SLS_Date::dateToDateTime
	 * @see SLS_Date::dateToTimestamp
	 * @see SLS_Date::dateTimeToDate
	 * @see SLS_Date::dateTimeToTimestamp
	 * @see SLS_Date::timestampToDate
	 * @since 1.0
	 */
	public static function timestampToDateTime($timestamp="")
	{
		$tmpDate = "00-00-00";
		$tmpTime = "00:00:00";
		
		if (self::isTimestamp($timestamp))
			return date("Y-m-d H:i:s",$timestamp);
		else if (self::isDate($timestamp))
			return self::dateToDateTime($timestamp);
		else if (self::isDateTime($timestamp))
			return $timestamp;
		else
			return $tmpDate." ".$tmpTime; 			
	}
	
	/**
	 * Get age from date
	 * 
	 * @access public 
	 * @param string $birthdate birthdate (if empty, take the current object date)
	 * @return int $age age of people
	 * @since 1.0.9
	 */
	public function getAge($birthdate="")
	{
	    if (empty($birthdate))
	        $birthdate = $this->_year."-".$this->_month."-".$this->_day;
	        
	    if (self::isTimestamp($birthdate))
	    	$birthdate = self::timestampToDate($birthdate);
	    else if (self::isDateTime($birthdate))
	    	$birthdate = self::dateTimeToDate($birthdate);
		
	    if (!self::isDate($birthdate))
	    	return false;
	    	
	    $dateE = explode("-",$birthdate);	    
		$cur = time();
		$age = date('Y', $cur)-$dateE[0];
		if($dateE[1] > date('n', $cur) || ($dateE[1] == date('n', $cur) && $dateE[2] > date('j', $cur)))
			$age--;
		return $age;
	}
}
?>