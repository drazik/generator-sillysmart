<?php
class SLS_BoLogsMonitoring extends SLS_BoControllerProtected 
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
		
		if (is_array($dateE) && count($dateE) == 3 && is_dir($this->_generic->getPathConfig("logs")."monitoring/".$dateE[0]."-".$dateE[1]))
		{
			$content = "";
			$i = 0;
			
			while(file_exists($this->_generic->getPathConfig("logs")."monitoring/".$dateE[0]."-".$dateE[1]."/".$dateE[0]."-".$dateE[1]."-".$dateE[2]."_".$i.".log"))
			{
				$content .= file_get_contents($this->_generic->getPathConfig("logs")."monitoring/".$dateE[0]."-".$dateE[1]."/".$dateE[0]."-".$dateE[1]."-".$dateE[2]."_".$i.".log");
				$i++;
			}
			$batchs = explode("#|end|#",$content);
			
			$xml->startTag("batchs");
			$xml->addFullTag("date",$dateL->getDate("FULL_LITTERAL"));
			foreach($batchs as $batch)
			{
				if (!empty($batch))
				{
					$lines = explode("\n",$batch);
					
					$times = array ("Render"			=> 0,
									"XML/XSL Parsing"	=> 0,									
									"MySQL Query"		=> 0,
									"Controller Action"	=> 0,
									"Controller Front"	=> 0,
									"Controller Static" => 0);
					$msg = "";
					$totalTime = 0;
					$endTime = 0;
					
					$xml->startTag("batch");
						$xml->startTag("lines");
						foreach($lines as $line)
						{
							$infos = explode("||",$line);
							$infos = array_map('trim',$infos);
							
							if (count($infos) > 4)
							{
								$times[$infos[0]] += $infos[2];
								if ($infos[0] == "Render")
								{
									$totalTime = $infos[2];
									$endTime = SLS_String::substrAfterFirstDelimiter($infos[1]," ");
								}
								if ($infos[0] == "Controller Front")								
									$msg = SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($infos[3],")"),"(");
								
								$xml->startTag("line");
									$xml->addFullTag("msg",$infos[3],true);
									$xml->addFullTag("type",$infos[0],true);
									$xml->addFullTag("more",str_replace(array("|n|","|t|","    "),array("<br />","&#160;&#160;","&#160;&#160;&#160;&#160;"),$infos[4]),true);
									$xml->addFullTag("time",SLS_String::substrAfterFirstDelimiter($infos[1]," "),true);
									$xml->addFullTag("duration",$infos[2],true);
								$xml->endTag("line");
							}
						}
						$xml->endTag("lines");
						$xml->startTag("infos");
							$xml->addFullTag("name",$endTime." - ".$msg,true);
							$xml->startTag("times");
								$xml->addFullTag("total",$totalTime,true);
								foreach($times as $key => $value)
									$xml->addFullTag(SLS_String::stringToUrl(trim($key),"_"),$value,true);
							$xml->endTag("times");
						$xml->endTag("infos");
						$sum = 0;
						$xml->startTag("ratios");
						foreach($times as $key => $value)
						{
							if ($key == "Render")
								continue;
							else
								$sum += $value;
								
							$xml->startTag("ratio");
								$xml->addFullTag("label",str_replace(" ","+",trim($key)),true);
								$xml->addFullTag("duration",$times[$key],true);								
								$xml->addFullTag("degree",($totalTime > 0) ? 360 * $value / $totalTime : "360",true);
							$xml->endTag("ratio");
						}
						if ($totalTime - $sum > 0)
						{
							$xml->startTag("ratio");
								$xml->addFullTag("label","Others",true);
								$xml->addFullTag("duration",$sum,true);	
								$xml->addFullTag("degree",($totalTime > 0) ? 360 * $sum / $totalTime : "360",true);
							$xml->endTag("ratio");
						}
						$xml->endTag("ratios");
					$xml->endTag("batch");
				}
			}
			$xml->endTag("batchs");			
		}
		
		$this->saveXML($xml);
	}
	
}
?>