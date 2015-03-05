<?php
/**
* Class GenerateBoAction into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoGenerateBoAction extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$controllersXML = $this->_generic->getControllersXML();
		$controller = $controllersXML->getTag("//controllers/controller[@isBo='true']/@name");
		$tokenSecret = sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3));
		
		// Param
		$actions = explode("|",$this->_http->getParam("Actions"));
		$firstAction = (count($actions) > 0) ? $actions[0] : "";
		
		$action = $controllersXML->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$firstAction."']");
		
		// Check if bo controller already exist
		if (empty($controller))
		{
			$xml->startTag("errors");
			$xml->addFullTag("error","Back-office controller could not be found. Please follow the following link to create it",true);
			$xml->endTag("errors");
			$xml->addFullTag("error_type","rights",true);
		}
		// If action already exist
		else if (!empty($action))
		{
			$xml->startTag("errors");
			$xml->addFullTag("error",$firstAction." action already exists",true);
			$xml->endTag("errors");			
		}
		// Else, let's generate action
		else 
		{
			$langs = $this->_generic->getObjectLang()->getSiteLangs();
		
			foreach($actions as $action)
			{
				if (file_exists($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/".$action.".controller.php"))
				{
					// Generate Action
					$params = array(0 => array("key" 	=> "reload",
							  				   "value" 	=> "true"),
							  		1 => array("key" 	=> "Controller",
							  				   "value" 	=> $controller),
								 	2 => array("key" 	=> "actionName",
							  				   "value" 	=> $action),
							  		3 => array("key"	=> "token",
							  				   "value"	=> $tokenSecret),
							  		4 => array("key"	=> "template",
							  				   "value" 	=> "bo"),
							  		5 => array("key"	=> "dynamic",
							  				   "value" 	=> "on"),
							  		6 => array("key"	=> "indexes",
							  				   "value"	=> "noindex,nofollow")
								    );
					foreach($langs as $lang)
					{
						$tmpParam = array("key" 	=> $lang."-action",
										  "value" 	=> $action."_".$lang);
						$tmpTitle = array("key" 	=> $lang."-title",
										  "value" 	=> $action);
						array_push($params,$tmpParam);
						array_push($params,$tmpTitle);
					}
					file_get_contents($this->_generic->getFullPath("SLS_Bo",
																  "AddAction",
																  $params,
																  true));
					
					// Erase Action
					$source = str_replace(array("{{USER_BO}}"),array($controller),file_get_contents($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/".$action.".controller.php"));
					file_put_contents($this->_generic->getPathConfig("actionsControllers").$controller."/".$action.".controller.php",$source);
					
					// Erase View Head
					if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$action.".xsl"))
						file_put_contents($this->_generic->getPathConfig("viewsHeaders").$controller."/".$action.".xsl",file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$action.".xsl"));
					
					// Erase View Body
					if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$action.".xsl"))
						file_put_contents($this->_generic->getPathConfig("viewsBody").$controller."/".$action.".xsl",file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$action.".xsl"));
				}
			}
			
			$this->_generic->forward("SLS_Bo","Bo");
		}
		
		$xml->addFullTag("url_add_controller",$this->_generic->getFullPath("SLS_Bo","AddController",array(0=>array("key"=>"isBo","value"=>"true"))),true);
		$this->saveXML($xml);
	}
}
?>