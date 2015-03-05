<?php
/**
* Class BoUserList into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUserList extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		$xml = $this->getXML();

		$attributes = array("photo"				=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_PHOTO'],
							"name" 				=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_LAST_NAME'],
							"firstname" 		=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_FIRST_NAME'],
							"login" 			=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_LOGIN'],
							"last_connection" 	=> $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_LAST_CONNECTION']);
		
		# Columns
		$xml->startTag("columns");
		foreach($attributes as $key => $label)
		{
			$xml->startTag("column");
				$xml->addFullTag("pk",($key=="login") ? "true" : "false",true);
				$xml->addFullTag("name",$key,true);
				$xml->addFullTag("label",$label,true);
				$xml->startTag("labels_html");
				$labels = explode(" ",trim($label));
				foreach($labels as $labelHtml)
					$xml->addFullTag("label_html",$labelHtml,true);
				$xml->endTag("labels_html");
			$xml->endTag("column");
		}
		$xml->endTag("columns");
		# /Columns
		
		# Users
		$users = $this->_bo->_xmlRight->getTagsAttributes("//sls_configs/entry",array("login","name","firstname","enabled","last_connection"));
		$xml->startTag("entities");
		for($i=0 ; $i<$count=count($users) ; $i++)
		{
			$imgPath = $this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($users[$i]["attributes"][1]["value"]."_".$users[$i]["attributes"][2]["value"],"_").".jpg";
			$xml->startTag("entity");
			for($j=0 ; $j<$count2=count($users[$i]["attributes"]) ; $j++)
			{
				if (SLS_String::contains($users[$i]["attributes"][$j]["key"],"name"))
					$xml->addFullTag($users[$i]["attributes"][$j]["key"],ucwords(strtolower($users[$i]["attributes"][$j]["value"])),true);
				else if (SLS_String::contains($users[$i]["attributes"][$j]["key"],"last_connection") && !empty($users[$i]["attributes"][$j]["value"]))
				{
					$date = new SLS_Date($users[$i]["attributes"][$j]["value"]);
					$xml->addFullTag($users[$i]["attributes"][$j]["key"],$date->getDate("FULL_TIME"),true);
				}
				else
					$xml->addFullTag($users[$i]["attributes"][$j]["key"],$users[$i]["attributes"][$j]["value"],true);
			}
			$xml->addFullTag("photo",(file_exists($imgPath) && !is_dir($imgPath)) ? $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$imgPath : $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/default_account.jpg",true);
			$xml->endTag("entity");
		}
		$xml->endTag("entities");
		# /Users
		
		$xml = $this->_bo->formatNotif($xml);
		$this->saveXML($xml);
	}
	
	public function after()
	{
		parent::after();
	}
}
?>