<?php
/**
* Class BoUserModify into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUserModify extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		$actions = array("model" 	=> array(),
						 "custom" 	=> array(),
						 "dashboard"=> array());
		$actionsSelected = array();
		$errors = array();
		$complexity = array();
		$color = "pink";
			
		// Params
		$name = SLS_String::trimSlashesFromString($this->_http->getParam("id"));
		$result = $this->_bo->_xmlRight->getTags("//sls_configs/entry[@login='".strtolower($name)."']");
		if (empty($result))
			$this->_generic->forward($this->_bo->_boController,"BoUserList");
		
		$login 	= $name;
		$pwd 	= "";
	
		$results = $this->_generic->getControllersXML()->getTagsAttributes("//controllers/controller[@isBo='true']/scontrollers/scontroller",array("name","id"));
		for($i=0 ; $i<$count=count($results) ; $i++)
		{
			$name = $results[$i]["attributes"][0]["value"];
			$aid = $results[$i]["attributes"][1]["value"];
			switch($name)
			{
				case (SLS_String::startsWith($name,"List") && SLS_String::contains($name,"_")):
					$actions["model"][SLS_String::substrAfterFirstDelimiter($name,"List")]["read"] = $aid;
					break;
				case (SLS_String::startsWith($name,"Add") && SLS_String::contains($name,"_")):
					$actions["model"][SLS_String::substrAfterFirstDelimiter($name,"Add")]["add"] = $aid;	
					break;
				case (SLS_String::startsWith($name,"Modify") && SLS_String::contains($name,"_")):
					$actions["model"][SLS_String::substrAfterFirstDelimiter($name,"Modify")]["edit"] = $aid;
					break;
				case (SLS_String::startsWith($name,"Delete") && SLS_String::contains($name,"_")):
					$actions["model"][SLS_String::substrAfterFirstDelimiter($name,"Delete")]["delete"] = $aid;
					break;
				case (SLS_String::startsWith($name,"Clone") && SLS_String::contains($name,"_")):
					$actions["model"][SLS_String::substrAfterFirstDelimiter($name,"Clone")]["clone"] = $aid;
					break;
				default:
					if (!in_array($name,$this->_bo->_publicActions) && !in_array($name,$this->_bo->_forbiddenActions) && !in_array($name,$this->_bo->_autoActions))
					{
						$id = array_shift($this->_generic->getControllersXML()->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$name."']/@id"));
						if (!empty($id))
							$actions["custom"][$name] = $id;
					}
					break;
			}
		}
		$dashboardId = $this->_generic->getControllersXML()->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='BoDashBoard']/@id");
		if ($this->_bo->_db->tableExists("sls_graph"))
		{
			$this->_generic->useModel("Sls_graph",$this->_bo->_defaultDb,"sls");
			$className = ucfirst($this->_bo->_defaultDb)."_Sls_graph";
			$slsGraph = new $className;
			$slsGraphs = $slsGraph->searchModels("sls_graph",array(),array(),array(),array("sls_graph_title" => "asc"));
			for($i=0 ; $i<$count=count($slsGraphs) ; $i++)
				$actions["dashboard"][$slsGraphs[$i]->sls_graph_title] = $dashboardId."_sls_graph_".$slsGraphs[$i]->sls_graph_id;
		}
		
		$userInfos = array("complexity_pwd_min_char_nb" => "8",
						   "renew_pwd_nb" 				=> "2",
						   "renew_pwd_unite" 			=> "month",
						   "renew_pwd_log_nb" 			=> "3",
						   "enabled" 					=> "true");
				
		$userInfos = array("login" 							=> $login,
						   "complexity_pwd_lc" 				=> (SLS_String::contains($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"lc")) ? "true" : "",
						   "complexity_pwd_uc" 				=> (SLS_String::contains($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"uc")) ? "true" : "",
						   "complexity_pwd_digit" 			=> (SLS_String::contains($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"digit")) ? "true" : "",
						   "complexity_pwd_special_char" 	=> (SLS_String::contains($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@complexity_pwd'),"special_char")) ? "true" : "",
						   "enabled" 						=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@enabled') == "true") ? "true": "false",
						   "color" 							=> $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/settings/setting[@key="color"]'),
						   "last_connection" 				=> $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@last_connection'),
						   "complexity_pwd_min_char" 		=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd') == "") ? "" : "true",
						   "complexity_pwd_min_char_nb" 	=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd') == "") ? "8" : $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@min_chars_pwd'),
						   "reset_pwd" 						=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@reset_pwd') == "") ? "" : "true",
						   "renew_pwd" 						=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "" : "true",
						   "renew_pwd_nb" 					=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "2" : trim(SLS_String::substrBeforeFirstDelimiter($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd')," ")),
						   "renew_pwd_unite" 				=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd') == "") ? "month" : trim(SLS_String::substrAfterFirstDelimiter($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd')," ")),
						   "renew_pwd_log" 					=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb') == "") ? "" : "true",
						   "renew_pwd_log_nb" 				=> ($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb') == "") ? "3" : $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@renew_pwd_nb'),
						   "name"  							=> ucwords(strtolower(($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@name') == "") ? "" : $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@name'))),
						   "firstname" 						=> ucwords(strtolower(($this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@firstname') == "") ? "" : $this->_bo->_xmlRight->getTag('//sls_configs/entry[@login="'.($login).'"]/@firstname')))
						   );
		
		$oldName = $userInfos["name"];
		$oldFirstname = $userInfos["firstname"];
		$photoChange = false;
		
		if ($this->_http->getParam("reload-edit") == "true")
		{
			$complexity = array();
			$minChars = "";

			$userInfos 						= $this->_http->getParam("user");
			$lastname 						= SLS_String::trimSlashesFromString($userInfos["name"]);
			$firstname						= SLS_String::trimSlashesFromString($userInfos["firstname"]);
			$pwd 							= SLS_String::trimSlashesFromString($userInfos["password"]);
			$enabled						= SLS_String::trimSlashesFromString($userInfos["enabled"]);
			$last_connection				= SLS_String::trimSlashesFromString($userInfos["last_connection"]);
			$complexity_pwd_lc 				= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_lc"]);
			$complexity_pwd_uc 				= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_uc"]);
			$complexity_pwd_digit 			= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_digit"]);
			$complexity_pwd_special_char 	= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_special_char"]);
			$complexity_pwd_min_char 		= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_min_char"]);
			$complexity_pwd_min_char_nb 	= SLS_String::trimSlashesFromString($userInfos["complexity_pwd_min_char_nb"]);
			$reset_pwd 						= SLS_String::trimSlashesFromString($userInfos["reset_pwd"]);
			$renew_pwd 						= SLS_String::trimSlashesFromString($userInfos["renew_pwd"]);
			$renew_pwd_nb 					= SLS_String::trimSlashesFromString($userInfos["renew_pwd_nb"]);
			$renew_pwd_unite 				= SLS_String::trimSlashesFromString($userInfos["renew_pwd_unite"]);
			$renew_pwd_log 					= SLS_String::trimSlashesFromString($userInfos["renew_pwd_log"]);
			$renew_pwd_log_nb 				= SLS_String::trimSlashesFromString($userInfos["renew_pwd_log_nb"]);
			$color 							= SLS_String::trimSlashesFromString($userInfos["color"]);
			$photo 							= $userInfos["photo"];
			$privileges 					= $this->_http->getParam("action");
			if ($enabled != "true")
				$enabled = "false";
						
			if (empty($lastname) || empty($firstname))
				array_push($errors,$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_USER_ERROR_REQUIRED_NAME']);
				
			if ($complexity_pwd_lc == "true")
				array_push($complexity,"lc");
			if ($complexity_pwd_uc == "true")
				array_push($complexity,"uc");
			if ($complexity_pwd_digit == "true")
				array_push($complexity,"digit");
			if ($complexity_pwd_special_char == "true")
				array_push($complexity,"special_char");
			if (array_key_exists("file",$photo))
				$photo = $photo["file"];
			if (!empty($photo) && is_array($photo))
			{
				if (array_key_exists("size",$photo))
					$size = $photo["size"];
				if (array_key_exists("data",$photo))
					$photo = $photo["data"];
				if (array_key_exists("error",$photo) && $photo["error"] != 4)
				{
					if (!file_exists($this->_generic->getPathConfig("files")."__Uploads") && !is_dir($this->_generic->getPathConfig("files")."__Uploads"))
						mkdir($this->_generic->getPathConfig("files")."__Uploads");
					if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/images") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images"))
						mkdir($this->_generic->getPathConfig("files")."__Uploads/images");
					if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/images/bo") && !is_dir($this->_generic->getPathConfig("files")."__Uploads/images/bo"))
						mkdir($this->_generic->getPathConfig("files")."__Uploads/images/bo");
					
					$plugin = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configPlugins")."/plugins.xml"));
					$pluginProject = $plugin->getTag("//plugins/plugin[@code='image']");
					if (empty($pluginProject))
					{
						// Force Image plugin download
						file_get_contents($this->_generic->getFullPath("SLS_Bo",
																	  "SearchPlugin",
																	  array("Action" => "Download",
																			"Server" => "4",
																			"Plugin" => "1",
																			"token"	 => sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))),
																	  true,
														  			  "en"));
						@include_once($this->_generic->getPathConfig("plugins")."SLS_Image.class.php");
					}
					
					$img = new SLS_Image($photo["tmp_name"]);
					if ($img->getParam("existed"))
					{
						// Default crop
						if (empty($size))
							$size = array("x" => "0", "y" => "0", "w" => $img->getParam("width"), "h" => $img->getParam("height"));
						// Crop image
						$img->crop($size["x"],$size["y"],$size["w"],$size["h"]);
						// Convert to jpg
						if (strtolower(SLS_String::substrBeforeLastDelimiter($photo["tmp_name"],".")) != "jpg")
							$img->transform($size["w"],$size["h"],SLS_String::substrBeforeLastDelimiter($photo["tmp_name"],".").".jpg","jpg");
						
						$photoChange = rename($photo["tmp_name"],$this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($userInfos["name"]."_".$userInfos["firstname"],"_").".jpg");
					}
				}
			}
				
			if ($complexity_pwd_min_char == "true")
				$minChars = $complexity_pwd_min_char_nb;
			
			if (empty($errors))
			{
				if (!empty($pwd))
					$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("password" => sha1($pwd)));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("name" => SLS_String::stringToUrl($lastname," ")));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("firstname" => SLS_String::stringToUrl($firstname," ")));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("enabled" => $enabled));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("last_connection" => $last_connection));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("last_renew_pwd" => date("Y-m-d")));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("complexity_pwd" => implode("|",$complexity)));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("min_chars_pwd" => $minChars));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("reset_pwd" => (($reset_pwd=="true") ? "true" : "")));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("renew_pwd" => (($renew_pwd=="true") ? $renew_pwd_nb." ".$renew_pwd_unite : "")));
				$this->_bo->_xmlRight->setTagAttributes('//sls_configs/entry[@login="'.($login).'"]', array("renew_pwd_nb" => (($renew_pwd_log=="true") ? $renew_pwd_log_nb : "")));
				
				# Need to change filename ?
				$imgPath = $this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($oldName."_".$oldFirstname,"_").".jpg";
				if (!$photoChange && // Photo not reuploaded
					($userInfos["name"] != $oldName || $userInfos["firstname"] != $oldFirstname) && // Name or firstname change
					file_exists($imgPath) && !is_dir($imgPath)) // Old image exists
				{
					rename($imgPath,$this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($userInfos["name"]."_".$userInfos["firstname"],"_").".jpg");
				}
				
				# Delete old privileges
				$xmlNew = '';
				$this->_bo->_xmlRight->deleteTags('//sls_configs/entry[@login="'.($login).'"]/action', count($this->_bo->_xmlRight->getTags('//sls_configs/entry[@login="'.($login).'"]/action')));
				$this->_bo->_xmlRight->setTag('//sls_configs/entry[@login="'.($login).'"]/settings/setting[@key="color"]',(empty($color)) ? "pink" : $color);
				$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");			
				$this->_bo->_xmlRight->refresh();
				foreach($this->_bo->_autoActions as $actionName)
				{
					$aid = $this->_generic->getControllersXML()->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$actionName."']/@id");
					$xmlNew .= '    <action id="'.$aid.'" role="custom" entity="" />'."\n";
				}
				foreach($privileges as $actionId => $properties)
				{
					if (is_array($properties))
					{
						foreach($properties as $role => $entity)
						{
							if (!empty($entity))
							{
								$isModel = (in_array($role,array("read","add","edit","clone","delete"))) ? true : false;
								$xmlNew .= '    <action id="'.$actionId.'" role="'.$role.'" entity="'.(($isModel) ? $entity : '').'" />'."\n";
								$actionsSelected[] = ($isModel) ? $actionId."_".$role : $actionId;
							}
						}
					}
				}					
				$this->_bo->_xmlRight->appendXMLNode('//sls_configs/entry[@login="'.($login).'"]',$xmlNew);
				$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
				$this->_bo->_xmlRight->refresh();
				
				$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_EDIT']);
				
				$this->_generic->forward($this->_bo->_boController,"BoUserList");
			}
			else
			{
				foreach($privileges as $actionId => $properties)
				{
					if (is_array($properties))
					{
						foreach($properties as $role => $entity)
						{
							if (!empty($entity))
							{
								$isModel = (in_array($role,array("read","add","edit","clone","delete"))) ? true : false;
								$xmlNew .= '    <action id="'.$actionId.'" role="'.$role.'" entity="'.(($isModel) ? $entity : '').'" />'."\n";
								$actionsSelected[] = ($isModel) ? $actionId."_".$role : $actionId;
							}
						}
					}
				}
				
				$xml->startTag("errors");
				foreach($errors as $error)
					$xml->addFullTag("error",$error,true);
				$xml->endTag("errors");
			}
		}
		else
		{	
			$rights = $this->_bo->_xmlRight->getTagsAttributes('//sls_configs/entry[@login="'.($login).'"]/action',array("id","role","entity"));
			for($i=0 ; $i<$count=count($rights) ; $i++)
			{
				$actionId = $rights[$i]["attributes"][0]["value"];
				$role = $rights[$i]["attributes"][1]["value"];
				$entity = $rights[$i]["attributes"][2]["value"];
				
				$isModel = (in_array($role,array("read","add","edit","clone","delete"))) ? true : false;
				$actionsSelected[] = ($isModel) ? $actionId."_".$role : $actionId;
			}
		}
		
		$xml->startTag("user");
		foreach($userInfos as $key => $value)
			if (!is_array($value))
				$xml->addFullTag($key,$value,true);
		$imgPath = $this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($userInfos["name"]."_".$userInfos["firstname"],"_").".jpg";
		$xml->addFullTag("img",(file_exists($imgPath) && !is_dir($imgPath)) ? $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$imgPath : /*$this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/default_account.jpg"*/"",true);
		$xml->endTag("user");
		$xml->startTag("actions");
			$xml->startTag("models");
			foreach($actions["model"] as $model => $privileges)
			{
				$xml->startTag("model");
					$xml->addFullTag("name",$model,true);
					$xml->startTag("privileges");
						$xml->addFullTag("read",(!empty($privileges["read"])) ? $privileges["read"] : "",true,array("selected" => (in_array($privileges["read"]."_read",$actionsSelected)) ? "true" : "false"));
						$xml->addFullTag("add",(!empty($privileges["add"])) ? $privileges["add"] : "",true,array("selected" => (in_array($privileges["add"]."_add",$actionsSelected)) ? "true" : "false"));
						$xml->addFullTag("edit",(!empty($privileges["edit"])) ? $privileges["edit"] : "",true,array("selected" => (in_array($privileges["edit"]."_edit",$actionsSelected)) ? "true" : "false"));	
						$xml->addFullTag("clone",(!empty($privileges["clone"])) ? $privileges["clone"] : "",true,array("selected" => (in_array($privileges["clone"]."_clone",$actionsSelected)) ? "true" : "false"));						
						$xml->addFullTag("delete",(!empty($privileges["delete"])) ? $privileges["delete"] : "",true,array("selected" => (in_array($privileges["delete"]."_delete",$actionsSelected)) ? "true" : "false"));
					$xml->endTag("privileges");
				$xml->endTag("model");
			}
			$xml->endTag("models");
			$xml->startTag("customs");
			foreach($actions["custom"] as $name => $id)
				$xml->addFullTag("custom",$name,true,array("id" => $id, "selected" => (in_array($id,$actionsSelected)) ? "true" : "false"));
			$xml->endTag("customs");
			$xml->startTag("dashboards");
			foreach($actions["dashboard"] as $name => $id)
				$xml->addFullTag("dashboard",$name,true,array("id" => $id, "selected" => (in_array($id,$actionsSelected)) ? "true" : "false"));
			$xml->endTag("dashboards");
		$xml->endTag("actions");
		
		$this->saveXML($xml);
	}
	
	public function after()
	{
		parent::after();
	}
}
?>