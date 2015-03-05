<?php
class SLS_BoLogsApp extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		// Objects
		$xml = $this->getXML();

		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		
		$date = $this->_http->getParam("date");
		$dateE = explode("-",$date);
		$dateL = new SLS_Date($date);
		
		if (is_array($dateE) && count($dateE) == 3 && is_dir($this->_generic->getPathConfig("logs").$dateE[0]."-".$dateE[1]))
		{
			$content = "";
			$i = 0;
			
			while(file_exists($this->_generic->getPathConfig("logs").$dateE[0]."-".$dateE[1]."/".$dateE[0]."-".$dateE[1]."-".$dateE[2]."_".$i.".log"))
			{
				$content .= file_get_contents($this->_generic->getPathConfig("logs").$dateE[0]."-".$dateE[1]."/".$dateE[0]."-".$dateE[1]."-".$dateE[2]."_".$i.".log");
				$i++;
			}
			$errors = explode($date,$content);
			$xml->startTag("errors");
			$xml->addFullTag("date",$dateL->getDate("FULL_LITTERAL"));
			foreach($errors as $error)
			{
				if (!empty($error))
				{
					$stack = explode("\n",$error);
					$xml->startTag("error");	
					$xml->addFullTag("title",$date.$stack[0]);
					$xml->startTag("traces");
					for($j=1 ; $j<$count=count($stack) ; $j++)
					{
						if (!empty($stack[$j]))
						{
							$trace = explode("in file",$stack[$j]);
							$xml->startTag("trace");
							$xml->addFullTag("message",$trace[0],true);
							$xml->addFullTag("file","in file".$trace[1],true);
							$xml->endTag("trace");
						}
					}
					$xml->endTag("traces");
					$xml->endTag("error");
				}
			}
			$xml->endTag("errors");			
		}
		
		$this->saveXML($xml);
	}
	
}
?>