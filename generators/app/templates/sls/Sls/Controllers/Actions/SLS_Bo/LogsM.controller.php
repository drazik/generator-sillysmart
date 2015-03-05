<?php
class SLS_BoLogsM extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		// Objects
		$xml = $this->getXML();
		$xml->addFullTag("view_log",$this->_generic->getFullPath("SLS_Bo","LogsMonitoring",array(),false));
		
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		
		// Get the existing logs
		$dates = array();
		$all = array();
		$handle = opendir($this->_generic->getPathConfig("logs")."monitoring");
		
		// Foreach directories 
		while (false !== ($dir = readdir($handle)))
		{
			if (is_dir($this->_generic->getPathConfig("logs")."/monitoring/".$dir) && substr($dir, 0, 1) != ".") 
			{				
				$handle2 = opendir($this->_generic->getPathConfig("logs")."/monitoring/".$dir);				
				while (false !== ($file = readdir($handle2)))
				{					
					if (!is_dir($this->_generic->getPathConfig("logs")."/monitoring/".$dir."/".$file) && substr($file, 0, 1) != ".") 
					{
						$date = explode("-",substr($file,0,10));						
						if (!in_array(substr($file,0,10),$dates))
						{
							$array = array("year"=>$date[0],"month"=>$date[1],"day"=>$date[2]);
							array_push($all,$array);
							array_push($dates,substr($file,0,10));
						}
					}
				}
			}
		}
		if (!empty($all))
		{
			$all = SLS_String::arrayMultiSort($all,array(array('key'=>'year','sort'=>'desc'),array('key'=>'month','sort'=>'desc'),array('key'=>'day','sort'=>'desc')));
			
			$xml->startTag("logs");
			foreach($all as $allC)			
			{
				$dateL = new SLS_Date($allC["year"]."-".$allC["month"]."-".$allC["day"]);
				$xml->startTag("log");
				$xml->addFullTag("year",$allC["year"],true);
				$xml->addFullTag("month",$allC["month"],true);
				$xml->addFullTag("day",$allC["day"],true);
				$xml->addFullTag("litteral",ucwords($dateL->getDate("FULL_LITTERAL")));
				$xml->endTag("log");
			}
			$xml->endTag("logs");
		}
		
		$this->saveXML($xml);
	}
	
}
?>