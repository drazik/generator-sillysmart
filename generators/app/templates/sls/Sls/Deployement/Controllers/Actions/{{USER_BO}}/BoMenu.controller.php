<?php
/**
* Class BoMenu into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoMenu extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		$devLogs = $this->_session->getParam("sls_dev_logs");
		$this->_session->delParam("sls_dev_logs");
		
		if (is_array($devLogs))
		{
			$totalTime = $devLogs["flush_cache"]["time"]+$devLogs["statics"]["time"]+$devLogs["components"]["time"]+$devLogs["routing"]["time"]+$devLogs["init"]["time"]+$devLogs["action"]["time"]+$devLogs["sql"]["time"]+$devLogs["parsing_html"]["time"]+$devLogs["parsing_xsl"]["time"];
			$xml->startTag("dev_logs");
			if (array_key_exists("flush_cache",$devLogs))
			{
				$xml->startTag("flush_cache");
					$xml->addFullTag("time",(empty($devLogs["flush_cache"]["time"])) ? "0.0000" : $devLogs["flush_cache"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["flush_cache"]["time"]*100/$totalTime,2),true);
					$xml->startTag("children");	
				foreach($devLogs["flush_cache"]["logs"] as $log)
				{
					$xml->startTag("child");
						$xml->addFullTag("time",(empty($log["time"])) ? "0.0000" : $log["time"],true);
						$xml->addFullTag("msg",$log["msg"],true,array("type" => "sql"));
					$xml->endTag("child");
				}
					$xml->endTag("children");	
				$xml->endTag("flush_cache");
			}
			if (array_key_exists("statics",$devLogs))
			{
				$xml->startTag("statics");
					$xml->addFullTag("time",(empty($devLogs["statics"]["time"])) ? "0.0000" : $devLogs["statics"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["statics"]["time"]*100/$totalTime,2),true);
					$xml->startTag("children");	
				foreach($devLogs["statics"]["logs"] as $log)
				{
					$xml->startTag("child");
						$xml->addFullTag("time",(empty($log["time"])) ? "0.0000" : $log["time"],true);
						$xml->addFullTag("msg",$log["msg"],true);
					$xml->endTag("child");
				}
					$xml->endTag("children");	
				$xml->endTag("statics");
			}
			if (array_key_exists("components",$devLogs))
			{
				$xml->startTag("components");
					$xml->addFullTag("time",(empty($devLogs["components"]["time"])) ? "0.0000" : $devLogs["components"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["components"]["time"]*100/$totalTime,2),true);
					$xml->startTag("children");	
				foreach($devLogs["components"]["logs"] as $log)
				{
					$xml->startTag("child");
						$xml->addFullTag("time",(empty($log["time"])) ? "0.0000" : $log["time"],true);
						$xml->addFullTag("msg",$log["msg"],true);
					$xml->endTag("child");
				}
					$xml->endTag("children");	
				$xml->endTag("components");
			}
			if (array_key_exists("routing",$devLogs))
			{	
				$xml->startTag("routing");
					$xml->addFullTag("time",(empty($devLogs["routing"]["time"])) ? "0.0000" : $devLogs["routing"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["routing"]["time"]*100/$totalTime,2),true);
					$xml->addFullTag("msg","# ".((empty($devLogs["routing"]["time"])) ? "0.0000" : $devLogs["routing"]["time"])."s\n".str_replace("\t","    ",$devLogs["routing"]["msg"]),true,array("type" => "php"));	
				$xml->endTag("routing");
			}
			if (array_key_exists("init",$devLogs))
			{
				$xml->startTag("init");
					$xml->addFullTag("time",(empty($devLogs["init"]["time"])) ? "0.0000" : $devLogs["init"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["init"]["time"]*100/$totalTime,2),true);
					$xml->addFullTag("msg",$devLogs["init"]["msg"],true);	
				$xml->endTag("init");
			}
			if (array_key_exists("action",$devLogs))
			{
				$xml->startTag("action");
					$xml->addFullTag("time",(empty($devLogs["action"]["time"])) ? "0.0000" : $devLogs["action"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["action"]["time"]*100/$totalTime,2),true);
					$xml->addFullTag("msg",$devLogs["action"]["msg"],true);	
				$xml->endTag("action");
			}
			if (array_key_exists("sql",$devLogs))
			{
				$xml->startTag("sql");
					$xml->addFullTag("time",(empty($devLogs["sql"]["time"])) ? "0.0000" : $devLogs["sql"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["sql"]["time"]*100/$totalTime,2),true);
					$xml->startTag("children");	
				foreach($devLogs["sql"]["logs"] as $log)
				{
					$code = "sql";
					$msg = (empty($code)) ? $log["msg"] : "# ".((empty($log["time"])) ? "0.0000" : $log["time"])."s\n".$log["msg"];
					$type = SLS_String::substrBeforeFirstDelimiter(trim($log["msg"])," ");
					$xml->startTag("child");
						$xml->addFullTag("type",$type,true);
						$xml->addFullTag("time",(empty($log["time"])) ? "0.0000" : $log["time"],true);
						$xml->addFullTag("msg",$msg,true,array("type" => $code));
					$xml->endTag("child");
				}
					$xml->endTag("children");	
				$xml->endTag("sql");
			}
			if (array_key_exists("parsing_html",$devLogs))
			{
				$xml->startTag("parsing_html");
					$xml->addFullTag("time",(empty($devLogs["parsing_html"]["time"])) ? "0.0000" : $devLogs["parsing_html"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["parsing_html"]["time"]*100/$totalTime,2),true);
					$xml->addFullTag("msg",$devLogs["parsing_html"]["msg"],true);	
				$xml->endTag("parsing_html");
			}
			if (array_key_exists("parsing_xsl",$devLogs))
			{
				$xml->startTag("parsing_xsl");
					$xml->addFullTag("time",(empty($devLogs["parsing_xsl"]["time"])) ? "0.0000" : $devLogs["parsing_xsl"]["time"],true);
					$xml->addFullTag("percent",$this->formatPercent($devLogs["parsing_xsl"]["time"]*100/$totalTime,2),true);
					$xml->addFullTag("msg",$devLogs["parsing_xsl"]["msg"],true);	
				$xml->endTag("parsing_xsl");
			}
			$xml->startTag("render");
				$xml->addFullTag("time",$totalTime,true);
				$xml->addFullTag("msg","",true);	
			$xml->endTag("render");
			$xml->endTag("dev_logs");
		}
		$this->saveXML($xml);
	}
	
	public function formatPercent($percent,$precision=2)
	{
		if (empty($percent))
			return "00.00";
		$percent = number_format($percent,$precision);
		if (strlen($percent) < 5)
			$percent = "0".$percent;
		return $percent;
	}
}
?>