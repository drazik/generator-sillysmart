<?php
class SLS_BoAddController extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$langs = $this->_lang->getSiteLangs();
		$listing = true;
		$errors = array();
		$controllersXML = $this->_generic->getControllersXML();
		$controller = $this->_http->getParam('Controller');
		$protocol = $this->_generic->getSiteConfig("protocol");
				
		// Check if bo controller
		$isBo = ($this->_http->getParam('isBo')=="true") ? true : false;
				
		if ($this->_http->getParam('reload') == 'true')
		{
			$postControllerName = SLS_String::trimSlashesFromString($this->_http->getParam('controllerName'));
			$newControllerName = SLS_String::stringToUrl(ucwords($postControllerName), "", false);
			$postProtocol = SLS_String::trimSlashesFromString($this->_http->getParam('protocol'));
			$tpl = SLS_String::trimSlashesFromString($this->_http->getParam('template'));
			$postControllerLangs = array();
			if (empty($newControllerName))
				array_push($errors, "The generic controller name is required to add a new Controller");
			if (empty($postProtocol) || ($postProtocol != 'http' && $postProtocol != 'https'))
				array_push($errors, "Protocol must be http or https");
			else 
				$protocol = $postProtocol;
			foreach ($langs as $lang)
			{
				$langController = SLS_String::stringToUrl(SLS_String::trimSlashesFromString($this->_http->getParam($lang."-controller")), "", false);
				if (empty($langController))
					array_push($errors, "The translation in ".$lang." is required to add a new Controller");
				else
					$postControllerLangs[$lang] = $langController;
			}
			
							
			if (empty($errors))
			{
				$test = $controllersXML->getTags("//controllers/controller[@name='".$newControllerName."']");
				if (count($test) != 0)
					array_push($errors, ($newControllerName == $postControllerName) ? "The controller Name '".$postControllerName."' already exist" : "The controller Name '".$postControllerName."' (".$newControllerName.") already exist");
			}
			if (empty($errors))
			{
				$translatedController = $controllersXML->getTags("//controllers/controller/controllerLangs/controllerLang",  'id');
				foreach ($postControllerLangs as $key=>$value)
				{
					if (in_array($value, $translatedController))
						array_push($errors, "The translated name '".$value."' for this controller is alredy used");
				}
				if (empty($errors))
				{
				
					$controllerID = $this->_generic->generateControllerId();
					$str = "<controller name=\"".$newControllerName."\" side=\"user\" protocol=\"".$protocol."\" id=\"".$controllerID."\"";
					
					if (!$isBo && $tpl != -1)
						$str .= " tpl=\"".$tpl."\"";
					if ($isBo)
						$str .= " isBo=\"true\" tpl=\"bo\"";
					
					$str .= "><controllerLangs>";

					foreach ($postControllerLangs as $key=>$value)
						$str .= "<controllerLang lang=\"".$key."\"><![CDATA[".$value."]]></controllerLang>";
					
					$str .= "</controllerLangs><scontrollers></scontrollers></controller>";
					$controllersXML->appendXMLNode("//controllers", $str);
					
					$strControllerProtected = '<?php'."\n".
						   '/**'."\n".
						   '* Class generic for the controller '.$newControllerName.''."\n".
						   '* Write here all your generic functions you need in your '.$newControllerName.' Actions'."\n".
						   '* @author SillySmart'."\n".
						   '* @copyright SillySmart'."\n".
						   '* @package Mvc.Controllers.'.$newControllerName.''."\n".
						   '* @see Mvc.Controllers.SiteProtected'."\n".
						   '* @see Sls.Controllers.Core.SLS_GenericController'."\n".
						   '* @since 1.0'."\n".
						   '*/'."\n".
						   'class '.$newControllerName.'ControllerProtected extends SiteProtected'."\n".
						   '{'."\n".
						   t(1).'public function init()'."\n".
						   t(1).'{'."\n".
						   		t(2).'parent::init();'."\n".
						   t(1).'}'."\n".
						   '}'."\n".
						   '?>';
					
					// Create the Controller Directory and Protected Function file					
					mkdir($this->_generic->getPathConfig("actionsControllers").$newControllerName);					
					file_put_contents($this->_generic->getPathConfig("actionsControllers").$newControllerName."/__".$newControllerName.".protected.php", $strControllerProtected);
					// Create Lang Directory
					mkdir($this->_generic->getPathConfig("actionLangs").$newControllerName);
					
					// Create Lang Files
					foreach ($langs as $lang)
					{
						$strLang = '<?php'."\n".
											'/**'."\n".
											'* '.strtoupper($lang).' File for all the Controller '.$newControllerName."\n".
											'* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
											'* '.t(1).'Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
											'* '.t(1).'Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
											'*'."\n".
											'* '."\t".'You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'" '."\n".
											'* @author SillySmart'."\n".
											'* @copyright SillySmart'."\n".
											'* @package Langs.Actions.'.$newControllerName."\n".
											'* @since 1.0'."\n".
											'*'."\n".
											'*/'."\n".
											'?>';
						file_put_contents($this->_generic->getPathConfig("actionLangs").$newControllerName."/__".$newControllerName.".".strtolower($lang).".lang.php", $strLang);
					}	
						
					// Create Views Directory
					mkdir($this->_generic->getPathConfig("viewsHeaders").$newControllerName);
					mkdir($this->_generic->getPathConfig("viewsBody").$newControllerName);
					// Save the new XML
					file_put_contents($this->_generic->getPathConfig('configSecure')."controllers.xml", $controllersXML->getXML());
					// Insert Into Meta
					$metasXML = $this->_generic->getCoreXML('metas');
					$strMetas = "<action id=\"".$controllerID."\" />";
					$metasXML->appendXMLNode("//sls_configs", $strMetas);
					file_put_contents($this->_generic->getPathConfig('configSls')."metas.xml", $metasXML->getXML());
					
					# isBo
					if ($isBo)
					{
						# Public/Files/__Uploads/images/bo
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads") && !is_dir($this->_generic->getPathConfig("files")."__Uploads"))
							mkdir($this->_generic->getPathConfig("files")."__Uploads");
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/images") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images"))
							mkdir($this->_generic->getPathConfig("files")."__Uploads/images");
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/images/bo") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images/bo"))
							mkdir($this->_generic->getPathConfig("files")."__Uploads/images/bo");
						if (file_exists($this->_generic->getPathConfig("installDeployement")."Public/Files/__Uploads/images/bo") && is_dir($this->_generic->getPathConfig("installDeployement")."Public/Files/__Uploads/images/bo"))
						{
							$files = scandir($this->_generic->getPathConfig("installDeployement")."Public/Files/__Uploads/images/bo");
							foreach($files as $file)
							{
								if (!SLS_String::startsWith($file,"."))
								{
									@copy($this->_generic->getPathConfig("installDeployement")."Public/Files/__Uploads/images/bo/".$file, $this->_generic->getPathConfig("files")."__Uploads/images/bo/".$file);
								}
							}
						}
						# /Public/Files/__Uploads/images/bo
						
						# Controller langs
						foreach($langs as $lang)
						{
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.".$lang.".lang.php"))
								$langContent = str_replace(array("{{USER_BO}}"),array($newControllerName),file_get_contents($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.".$lang.".lang.php"));
							else
								$langContent = str_replace(array("{{USER_BO}}"),array($newControllerName),file_get_contents($this->_generic->getPathConfig("installDeployement")."Langs/Actions/{{USER_BO}}/__{{USER_BO}}.en.lang.php"));
							if (!empty($langContent))
								file_put_contents($this->_generic->getPathConfig("actionLangs").$newControllerName."/__".$newControllerName.".".$lang.".lang.php",$langContent);
						}
						# /Controller langs
						
						# XSL Templates
						$boTemplates = array("bo.xsl","bo_light.xsl","bo_blank.xsl");
						foreach($boTemplates as $boTemplate)
						{
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Templates/".$boTemplate) && !is_dir($this->_generic->getPathConfig("installDeployement")."Views/Templates/".$boTemplate))
							{
								@copy($this->_generic->getPathConfig("installDeployement")."Views/Templates/".$boTemplate, $this->_generic->getPathConfig("viewsTemplates").$boTemplate);
							}
						}
						# /XSL Templates
						
						# XSL Generics
						$boGenerics = array("Boactionsbar.xsl","Boheaders.xsl","Bomenu.xsl");
						foreach($boGenerics as $boGeneric)
						{
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Generics/".$boGeneric) && !is_dir($this->_generic->getPathConfig("installDeployement")."Views/Generics/".$boGeneric))
							{
								@copy($this->_generic->getPathConfig("installDeployement")."Views/Generics/".$boGeneric, $this->_generic->getPathConfig("viewsGenerics").$boGeneric);
							}
						}
						# /XSL Generics
						
						# Controllers Statics
						$boStatics = array("BoMenu.controller.php");
						foreach($boStatics as $boStatic)
						{
							if (file_exists($this->_generic->getPathConfig("installDeployement")."Controllers/Statics/".$boStatic) && !is_dir($this->_generic->getPathConfig("installDeployement")."Controllers/Statics/".$boStatic))
							{
								@copy($this->_generic->getPathConfig("installDeployement")."Controllers/Statics/".$boStatic, $this->_generic->getPathConfig("staticsControllers").$boStatic);
							}
						}
						# /Controllers Statics
						
						# __{{USER_BO}}.protected.php
						if (file_exists($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/__{{USER_BO}}.protected.php") && !is_dir($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/__{{USER_BO}}.protected.php"))
						{
							@copy($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/__{{USER_BO}}.protected.php", $this->_generic->getPathConfig("actionsControllers").$newControllerName."/__".$newControllerName.".protected.php");
							@file_put_contents($this->_generic->getPathConfig("actionsControllers").$newControllerName."/__".$newControllerName.".protected.php",str_replace(array("{{USER_BO}}"),array($newControllerName),file_get_contents($this->_generic->getPathConfig("actionsControllers").$newControllerName."/__".$newControllerName.".protected.php")));
						}
						# /__{{USER_BO}}.protected.php
						
						# Native actions
						$controllerPath = $this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}";
						$boActions = scandir($controllerPath);
						$boLightActions = array("BoLogin","BoRenewPwd","BoForgottenPwd");
						$boBlankActions = array("BoMenu");
						$tokenSecret = sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3));
						foreach($boActions as $boAction)
						{
							if ( SLS_String::startsWith($boAction,"Bo") 			&& // Real boAction
								 file_exists($controllerPath."/".$boAction) 		&& // File exist
								!is_dir($controllerPath."/".$boAction)				&& // Not a directory 
								!SLS_String::startsWith($boAction,"BoUser") 		&& // Exclude custom action "BoUser(*)" 
								!SLS_String::startsWith($boAction,"Boi18n") 		&& // Exclude custom action "Boi18n" 
								!SLS_String::startsWith($boAction,"BoFileUpload") 	&& // Exclude custom action "BoFileUpload"
								!SLS_String::startsWith($boAction,"BoProjectSettings") // Exclude custom action "BoProjectSettings"
							)
							{
								// Generate Action
								$action = SLS_String::substrBeforeFirstDelimiter($boAction,".");
								$params = array(0 => array("key" 	=> "reload",
										  				   "value" 	=> "true"),
										  		1 => array("key" 	=> "Controller",
										  				   "value" 	=> $newControllerName),
											 	2 => array("key" 	=> "actionName",
										  				   "value" 	=> $action),
										  		3 => array("key"	=> "token",
										  				   "value"	=> $tokenSecret),
										  		4 => array("key"	=> "template",
										  				   "value" 	=> (in_array($action,$boLightActions)) ? "bo_light" : ((in_array($action,$boBlankActions)) ? "bo_blank" : "bo")),
										  		5 => array("key"	=> "dynamic",
										  				   "value" 	=> "on"),
										  		6 => array("key"	=> "indexes",
										  				   "value"	=> "noindex,nofollow")
											    );
								if ($action == "BoLogin")
									$params[] = array("key" 	=> "default",
										  			  "value"  => "on");
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
								$source = str_replace(array("{{USER_BO}}"),array($newControllerName),file_get_contents($this->_generic->getPathConfig("installDeployement")."Controllers/Actions/{{USER_BO}}/".$action.".controller.php"));
								file_put_contents($this->_generic->getPathConfig("actionsControllers").$newControllerName."/".$action.".controller.php",$source);
								
								// Erase View Head
								if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$action.".xsl"))
									file_put_contents($this->_generic->getPathConfig("viewsHeaders").$newControllerName."/".$action.".xsl",file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Headers/{{USER_BO}}/".$action.".xsl"));
								
								// Erase View Body
								if (file_exists($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$action.".xsl"))
									file_put_contents($this->_generic->getPathConfig("viewsBody").$newControllerName."/".$action.".xsl",file_get_contents($this->_generic->getPathConfig("installDeployement")."Views/Body/{{USER_BO}}/".$action.".xsl"));
							}
						}
						# /Native actions
					}
					# /isBo
						
					$controllersRedirect = $this->_generic->getTranslatedController('SLS_Bo', 'Controllers');
					$this->_generic->redirect($controllersRedirect['controller']."/".$controllersRedirect['scontroller']);
				}
			}
			
			if (!empty($errors))
			{
				$xml->startTag("errors");
					foreach ($errors as $error)
						$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
				$xml->startTag('form');
				$xml->addFullTag("controllerName", $postControllerName);
					foreach ($postControllerLangs as $key=>$value)
						$xml->addFullTag($key."-controller", $value, true);
						
				$xml->endTag('form');
			}
			
		}
		// Build all tpls
		$tpls = $this->getAppTpls();
		$xml->startTag("tpls");
		foreach($tpls as $template)
			$xml->addFullTag("tpl",$template,true);
		$xml->endTag("tpls");
		
		$xml->startTag('controller');
		$xml->addFullTag('isBo',($isBo) ? "true" : "false",true);
			$xml->startTag('translations');
			foreach ($langs as $lang)
			{
				$xml->startTag('translation');
					$xml->addFullTag("lang", $lang, true);	
				$xml->endTag('translation');
			}
			$xml->endTag('translations');
		$xml->endTag('controller');
		$listing = false;
		$xml->addFullTag('request', 'addController', true);
		$xml->addFullTag('protocol', $protocol, true);
		$xml->addFullTag('template', $tpl, true);
		$this->saveXML($xml);
	}
	
}
?>