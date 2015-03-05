<?php
class SLS_BoLogsMail extends SLS_BoControllerProtected 
{	
	public function action()
	{
		// Objects
		$xml = $this->getXML();
		
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		
		// Mail Logs recover
		$logs = "";
		try{
			$logs = file_get_contents($this->_generic->getPathConfig("logs")."mail.log");
		}catch (Exception $e) {}
		$nbLines = 0;
		
		if (!empty($logs))
		{		
			$xml->startTag("logs");
			$logsLine = explode("\n",$logs);
			for($i=0 ; $i<$count=count($logsLine) ; $i++)			
			{
				if (!empty($logsLine[$i]))
				{
					$value = trim($logsLine[$i]);
					if ($i<($count-2))
						$value .= "\n";					
					$xml->addFullTag("log",$value,true);					
					$nbLines += ceil(strlen($logsLine[$i])/93);
				}
			}
			$xml->endTag("logs");
		}
		$xml->addFullTag("height",(!empty($logs)) ? 15*$nbLines : 0,true);
		$this->saveXML($xml);
	}
}
?>