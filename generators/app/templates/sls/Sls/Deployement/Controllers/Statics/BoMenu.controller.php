<?php
/**
* Controller Static BoMenuController
*
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Statics.BoMenuController
* @see Sls.Controllers.Core.SLS_FrontStatic
* @since 1.0
*/
class BoMenuController extends SLS_FrontStatic implements SLS_IStatic 
{	
	public function __construct()
	{	
		parent::__construct(true);
	}

	public function constructXML()
	{
		$this->_boController = $this->_generic->getBo();
		
		if ($this->_generic->getGenericControllerName() == $this->_boController)
		{
			@include_once($this->_generic->getPathConfig("actionLangs").$this->_boController."/__".$this->_boController.".".$this->_lang->getLang().".lang.php");
			
			$db = SLS_Sql::getInstance();
			$dbsAlias = $db->getDbs();
			$comments = array();
			foreach($dbsAlias as $dbAlias)
			{
				$db->changeDb($dbAlias);
				$tables = $db->showTables();			
				for($i=0 ; $i<$count=count($tables) ; $i++)
				{
					$table = $tables[$i]->Name;
					$key = ucfirst(strtolower($dbAlias))."_".SLS_String::tableToClass($table);
					if (!array_key_exists($key,$comments))
					{
						$comment = $tables[$i]->Comment;
						if (SLS_String::startsWith($comment,"sls:lang:"))
						{
							$globalKey = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
							$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) ? $table : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey];
						}
						$comments[$key] = (empty($comment)) ? $table : $comment;
					}
				}
			}
			$db->changeDb($db->connectToDefaultDb());
			
			# Nav
			if (SLS_BoRights::isLogged())
			{
				$xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
				$entries = $xmlBo->getTagsAttributes("//sls_configs/entry",array("type","name"));		
				$this->_xmlToolBox->startTag("nav");
					if (!empty($entries))
					{
						# BOs
						$this->_xmlToolBox->startTag("section");
							$this->_xmlToolBox->addFullTag("title",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_SITE'],"true");
							$this->_xmlToolBox->addFullTag("href","#","true");
							$this->_xmlToolBox->startTag("categories");
							for($i=0 ; $i<$count=count($entries) ; $i++)
							{
								$position = ($i+1);
								$type = $entries[$i]["attributes"][0]["value"];
								$name = $entries[$i]["attributes"][1]["value"];
								
								if ($type == "category")
								{
									$childs = $xmlBo->getTagsAttributes("//sls_configs/entry[".$position."]/entry",array("type","name"));
									$comment = $name;
									if (SLS_String::startsWith($comment,"sls:lang:"))
									{
										$globalKey = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
										$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) ? $name : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$globalKey]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$globalKey];
									}
									$this->_xmlToolBox->startTag("category",array("type"=>"category"));
										$this->_xmlToolBox->addFullTag("title",$comment,"true");
										$this->_xmlToolBox->addFullTag("href","#","true");
										if (!empty($childs))
										{
											$this->_xmlToolBox->startTag("items");
											for($j=0 ; $j<$count=count($childs) ; $j++)
											{
												$type = $childs[$j]["attributes"][0]["value"];
												$name = $childs[$j]["attributes"][1]["value"];
												$dbAlias = ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($name,"_")));
												$table = SLS_String::substrAfterFirstDelimiter($name,"_");							
												$right = SLS_BoRights::isAuthorized("read",$dbAlias."_".SLS_String::tableToClass($table));
												if ($right == 1)
												{
													$comment = $comments[$dbAlias."_".SLS_String::tableToClass($table)];
													if (SLS_String::startsWith($comment,"sls:lang:"))
													{
														$key = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
														$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $table : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
													}
													$actionIdsModel = array($this->_generic->getActionId($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table)),
																			$this->_generic->getActionId($this->_boController,"Add".$dbAlias."_".SLS_String::tableToClass($table)),
																			$this->_generic->getActionId($this->_boController,"Modify".$dbAlias."_".SLS_String::tableToClass($table)),
																			$this->_generic->getActionId($this->_boController,"Clone".$dbAlias."_".SLS_String::tableToClass($table)),
																			$this->_generic->getActionId($this->_boController,"Delete".$dbAlias."_".SLS_String::tableToClass($table)));
													if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table))))
													{
														$this->_xmlToolBox->startTag("item");
															$this->_xmlToolBox->addFullTag("db",$dbAlias,true);
															$this->_xmlToolBox->addFullTag("model",SLS_String::tableToClass($table),true);
															$this->_xmlToolBox->addFullTag("title",$comment,"true");
															$this->_xmlToolBox->addFullTag("like",(SLS_BoRights::isLike("read",$dbAlias."_".SLS_String::tableToClass($table))) ? "true" : "false","true");												
															$this->_xmlToolBox->addFullTag("selected",(in_array($this->_generic->getActionId(),$actionIdsModel)) ? "true" : "false","true");
															$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table)),"true");
														$this->_xmlToolBox->endTag("item");
													}
												}
											}
											$this->_xmlToolBox->endTag("items");
										}
									$this->_xmlToolBox->endTag("category");
								}
								else
								{
									$dbAlias = ucfirst(strtolower(SLS_String::substrBeforeFirstDelimiter($name,"_")));
									$table = SLS_String::substrAfterFirstDelimiter($name,"_");							
									$right = SLS_BoRights::isAuthorized("read",$dbAlias."_".SLS_String::tableToClass($table));
									if ($right == 1)
									{
										$comment = $comments[$dbAlias."_".SLS_String::tableToClass($table)];
										if (SLS_String::startsWith($comment,"sls:lang:"))
										{
											$key = strtoupper(SLS_String::substrAfterFirstDelimiter($comment,"sls:lang:"));
											$comment = (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key])) ? (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) ? $table : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][$key]) : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][$key];
										}
										$actionIdsModel = array($this->_generic->getActionId($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table)),
																$this->_generic->getActionId($this->_boController,"Add".$dbAlias."_".SLS_String::tableToClass($table)),
																$this->_generic->getActionId($this->_boController,"Modify".$dbAlias."_".SLS_String::tableToClass($table)),
																$this->_generic->getActionId($this->_boController,"Clone".$dbAlias."_".SLS_String::tableToClass($table)),
																$this->_generic->getActionId($this->_boController,"Delete".$dbAlias."_".SLS_String::tableToClass($table)));
										if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table))))
										{
											$this->_xmlToolBox->startTag("category",array("type"=>"table"));
												$this->_xmlToolBox->addFullTag("db",$dbAlias,true);
												$this->_xmlToolBox->addFullTag("model",SLS_String::tableToClass($table),true);
												$this->_xmlToolBox->addFullTag("title",$comment,"true");
												$this->_xmlToolBox->addFullTag("like",(SLS_BoRights::isLike("read",$dbAlias."_".SLS_String::tableToClass($table))) ? "true" : "false","true");
												$this->_xmlToolBox->addFullTag("selected",(in_array($this->_generic->getActionId(),$actionIdsModel)) ? "true" : "false","true");
												$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,"List".$dbAlias."_".SLS_String::tableToClass($table)),"true");
											$this->_xmlToolBox->endTag("category");
										}
									}
								}
							}
							$this->_xmlToolBox->endTag("categories");
							
						$this->_xmlToolBox->endTag("section");
						# /BOs
					}
					
					# i18n
					if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController, "Boi18n")) && SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController, "Boi18n")))
					{
						$file = $this->_http->getParam("File");
						$this->_xmlToolBox->startTag("section");
							$this->_xmlToolBox->addFullTag("title",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_I18N'],"true");
							$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, "Boi18n")) ? "true" : "false","true");
							$this->_xmlToolBox->addFullTag("href","#","true");
							$this->_xmlToolBox->startTag("categories");
								$controllers = $this->_generic->getControllersXML()->getTags("//controllers/controller[@side='user']/@name");
								sort($controllers,SORT_STRING);
								for($i=0 ; $i<$count=count($controllers) ; $i++)
								{
									$actions = $this->_generic->getControllersXML()->getTags("//controllers/controller[@name='".$controllers[$i]."' and @side='user']/scontrollers/scontroller/@name");
									sort($actions,SORT_STRING);
									$this->_xmlToolBox->startTag("category",array("type"=>"category"));
										$this->_xmlToolBox->addFullTag("title",$controllers[$i],"true");
										$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, "Boi18n") && SLS_String::startsWith($file, "Actions|".$controllers[$i]."|")) ? "true" : "false","true");
										$this->_xmlToolBox->addFullTag("href","#","true");
										$this->_xmlToolBox->startTag("items");
										if (file_exists($this->_generic->getPathConfig("actionLangs").$controllers[$i]."/__".$controllers[$i].".".$this->_lang->getLang().".lang.php") && ($translations = file_get_contents($this->_generic->getPathConfig("actionLangs").$controllers[$i]."/__".$controllers[$i].".".$this->_lang->getLang().".lang.php")) && SLS_String::contains(str_replace("","",$translations),'*/'."\n".'$GLOBALS') || (!SLS_String::contains($translations,"/**") && SLS_String::contains($translations,'$GLOBALS')))
										{
											$this->_xmlToolBox->startTag("item");
												$this->_xmlToolBox->addFullTag("title","__".$controllers[$i],"true");
												$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, "Boi18n") && $file == "Actions|".$controllers[$i]."|__".$controllers[$i]) ? "true" : "false","true");
												$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController, "Boi18n", array("File" => "Actions|".$controllers[$i]."|__".$controllers[$i])),"true");
											$this->_xmlToolBox->endTag("item");
										}
										for($j=0 ; $j<$countJ=count($actions) ; $j++)
										{
											if (file_exists($this->_generic->getPathConfig("actionLangs").$controllers[$i]."/".$actions[$j].".".$this->_lang->getLang().".lang.php") && ($translations = file_get_contents($this->_generic->getPathConfig("actionLangs").$controllers[$i]."/".$actions[$j].".".$this->_lang->getLang().".lang.php")) && SLS_String::contains(str_replace("","",$translations),'*/'."\n".'$GLOBALS') || (!SLS_String::contains($translations,"/**") && SLS_String::contains($translations,'$GLOBALS')))
											{
												$this->_xmlToolBox->startTag("item");
													$this->_xmlToolBox->addFullTag("title",$actions[$j],"true");
													$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, "Boi18n") && $file == "Actions|".$controllers[$i]."|".$actions[$j]) ? "true" : "false","true");
													$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController, "Boi18n", array("File" => "Actions|".$controllers[$i]."|".$actions[$j])),"true");
												$this->_xmlToolBox->endTag("item");
											}
										}
										$this->_xmlToolBox->endTag("items");
									$this->_xmlToolBox->endTag("category");
								}
								if (file_exists($this->_generic->getPathConfig("genericLangs")."site.".$this->_lang->getLang().".lang.php") && ($translations = file_get_contents($this->_generic->getPathConfig("genericLangs")."site.".$this->_lang->getLang().".lang.php")) && SLS_String::contains(str_replace("","",$translations),'*/'."\n".'$GLOBALS') || (!SLS_String::contains($translations,"/**") && SLS_String::contains($translations,'$GLOBALS')))
								{
									$this->_xmlToolBox->startTag("category",array("type"=>"table"));
										$this->_xmlToolBox->addFullTag("title","__".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_I18N_SITE'],"true");
										$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, "Boi18n") && $file == "Generics|site") ? "true" : "false","true");
										$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController, "Boi18n", array("File" => "Generics|site")),"true");
									$this->_xmlToolBox->endTag("category");
								}
							$this->_xmlToolBox->endTag("categories");
						$this->_xmlToolBox->endTag("section");
					}
					# /i18n
					
					# CkFinder
					if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController, "BoFileUpload")) && SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController, "BoFileUpload")))
					{
						$this->_xmlToolBox->startTag("section");
							$this->_xmlToolBox->addFullTag("title",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_FILE_MANAGER'],"true");
							$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController,"BoFileUpload")) ? "true" : "false","true");
							$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,"BoFileUpload"),"true");
						$this->_xmlToolBox->endTag("section");
					}
					# /CkFinder
					
					# ProjectSettings
					if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController, "BoProjectSettings")) && SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController, "BoProjectSettings")))
					{
						$this->_xmlToolBox->startTag("section");
							$this->_xmlToolBox->addFullTag("title",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_PROJECT_SETTINGS'],"true");
							$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController,"BoProjectSettings")) ? "true" : "false","true");
							$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,"BoProjectSettings"),"true");
						$this->_xmlToolBox->endTag("section");
					}
					# /ProjectSettings
					
					# Users
					if ($this->_generic->actionIdExists($this->_generic->getActionId($this->_boController, "BoUserList")) && SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController, "BoUserList")))
					{
						$actionsUser = array($this->_generic->getActionId($this->_boController, "BoUserList"),
											 $this->_generic->getActionId($this->_boController, "BoUserAdd"),
											 $this->_generic->getActionId($this->_boController, "BoUserModify"),
											 $this->_generic->getActionId($this->_boController, "BoUserDelete"),
											 $this->_generic->getActionId($this->_boController, "BoUserStatus"));
						$this->_xmlToolBox->startTag("section");
							$this->_xmlToolBox->addFullTag("title",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_USERS'],"true");
							$this->_xmlToolBox->addFullTag("selected",(in_array($this->_generic->getActionId(),$actionsUser)) ? "true" : "false","true");
							$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,"BoUserList"),"true");
						$this->_xmlToolBox->endTag("section");
					}
					# /Users
					
					# Developer actions
					$boActions = $this->_generic->getControllersXML()->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller/@name");
					for($i=0 ; $i<$count=count($boActions) ; $i++)
					{
						// If dev action & admin authorized
						if (!SLS_String::startsWith($boActions[$i],"Bo") && 	// Sls generated actions
							!SLS_String::startsWith($boActions[$i],"List") && 	// User action ListDb_Model
							!SLS_String::startsWith($boActions[$i],"Add") && 	// User action AddDb_Model
							!SLS_String::startsWith($boActions[$i],"Modify") && // User action ModifyDb_Model
							!SLS_String::startsWith($boActions[$i],"Delete") && // User action DeleteDb_Model
							!SLS_String::startsWith($boActions[$i],"Clone") && 	// User action CloneDb_Model
							!SLS_String::startsWith($boActions[$i],"Email") &&	// User action EmailDb_Model
							SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController, $boActions[$i])))
						{
							$this->_xmlToolBox->startTag("section");
								$this->_xmlToolBox->addFullTag("title",$this->_generic->getControllersXML()->getTag("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".$boActions[$i]."']/scontrollerLangs/scontrollerLang[@lang='".$this->_lang->getLang()."']"),"true");
								$this->_xmlToolBox->addFullTag("selected",($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController, $boActions[$i])) ? "true" : "false", "true");
								$this->_xmlToolBox->addFullTag("href",$this->_generic->getFullPath($this->_boController,$boActions[$i]),"true");
							$this->_xmlToolBox->endTag("section");
						}
					}
					# /Developer actions
					
				$this->_xmlToolBox->endTag("nav");
			}
			# /Nav
			
			# Admin
			$xmlRight = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/rights.xml"));
			$adminName = $this->_session->getParam("SLS_BO_USER_NAME");
			$adminFirstname = $this->_session->getParam("SLS_BO_USER_FIRSTNAME");
			if (!empty($adminName) && !empty($adminFirstname))
				$imgPath = $this->_generic->getPathConfig("files")."__Uploads/images/bo/".SLS_String::stringToUrl($adminName."_".$adminFirstname,"_").".jpg";
			else
				$imgPath = $this->_generic->getPathConfig("coreImg")."BO-2014/Pictos/default_dev_account_small.jpg";
			$this->_xmlToolBox->startTag("admin");
				$this->_xmlToolBox->addFullTag("img",(file_exists($imgPath) && !is_dir($imgPath)) ? $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$imgPath : $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("files")."__Uploads/images/bo/default_account.jpg",true);
				$this->_xmlToolBox->addFullTag("login",$this->_session->getParam("SLS_BO_USER"),true);
				$this->_xmlToolBox->addFullTag("name",ucwords(strtolower((!empty($adminName)) ? $adminName : "Developer")),true);
				$this->_xmlToolBox->addFullTag("firstname",((!empty($adminFirstname)) ? ucwords(strtolower($adminFirstname)) : "SillySmart"),true);
				$this->_xmlToolBox->addFullTag("type",SLS_BoRights::getAdminType(),true);			
				$nodeExists = $xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/@login");
				if (!empty($nodeExists))
				{
					$settings = array_combine($xmlRight->getTags("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting/@key"),$xmlRight->getTags("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/settings/setting"));
					$this->_xmlToolBox->startTag("settings");
					foreach($settings as $key => $value)
						$this->_xmlToolBox->addFullTag("setting",$value,true,array("key"=>$key));
					$this->_xmlToolBox->endTag("settings");
				}
				$xmlBoColors = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo_colors.xml"));
				$boColors = $xmlBoColors->getTagsAttributes("//sls_configs/template",array("name","hexa"));
				$this->_xmlToolBox->startTag("colors");
				for($i=0 ; $i<$count=count($boColors) ; $i++)
					$this->_xmlToolBox->addFullTag("color",$boColors[$i]["attributes"][0]["value"],true,array("hexa" => $boColors[$i]["attributes"][1]["value"]));
				$this->_xmlToolBox->endTag("colors");
			$this->_xmlToolBox->endTag("admin");
			# /Admin
			
			# URLs
			$this->_xmlToolBox->startTag("various");
				$this->_xmlToolBox->addFullTag("dashboard",$this->_generic->getFullPath($this->_boController,"BoDashBoard"),true,array("selected" => ($this->_generic->getActionId() == $this->_generic->getActionId($this->_boController,"BoDashBoard")) ? "true" : "false"));
				$this->_xmlToolBox->addFullTag("export",$this->_generic->getFullPath($this->_boController,"BoExport"),true);
				$this->_xmlToolBox->addFullTag("ac",$this->_generic->getFullPath($this->_boController,"BoFkAc"),true);
				$this->_xmlToolBox->addFullTag("like",$this->_generic->getFullPath($this->_boController,"BoLike"),true);
				$this->_xmlToolBox->addFullTag("setting",$this->_generic->getFullPath($this->_boController,"BoSetting"),true);
				$this->_xmlToolBox->addFullTag("unique",$this->_generic->getFullPath($this->_boController,"BoUnique"),true);
				$this->_xmlToolBox->addFullTag("upload",$this->_generic->getFullPath($this->_boController,"BoUpload"),true);
				$this->_xmlToolBox->addFullTag("upload_progress",$this->_generic->getFullPath($this->_boController,"BoUploadProgress"),true);
				$this->_xmlToolBox->addFullTag("delete_file",$this->_generic->getFullPath($this->_boController,"BoDeleteFile"),true);
				$this->_xmlToolBox->addFullTag("login",$this->_generic->getFullPath($this->_boController,"BoLogin"),true);
				$this->_xmlToolBox->addFullTag("logout",(SLS_BoRights::getAdminType() == "developer") ? $this->_generic->getFullPath("SLS_Bo","Logout",array("Redirect" => $this->_generic->getActionId($this->_boController,"BoLogin"), "Lang" => $this->_lang->getLang()),true,"en") : $this->_generic->getFullPath($this->_boController,"BoLogout"),true);
				$this->_xmlToolBox->addFullTag("forgotten_pwd",$this->_generic->getFullPath($this->_boController,"BoForgottenPwd"),true);
				$this->_xmlToolBox->addFullTag("renew_pwd",$this->_generic->getFullPath($this->_boController,"BoRenewPwd",array("Login"=>$this->_session->getParam("SLS_BO_USER"))),true);
				$this->_xmlToolBox->addFullTag("is_logged",$this->_generic->getFullPath($this->_boController,"BoIsLogged"),true);
				$this->_xmlToolBox->addFullTag("switch_lang",$this->_generic->getFullPath($this->_boController,"BoSwitchLang",array("Lang"=>""),false),true);
				$this->_xmlToolBox->addFullTag("user_add",$this->_generic->getFullPath($this->_boController,"BoUserAdd"),true,array("authorized" => (SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController,"BoUserAdd"))) ? "true" : "false"));
				$this->_xmlToolBox->addFullTag("user_modify",$this->_generic->getFullPath($this->_boController,"BoUserModify",array("id" => ''),false),true,array("authorized" => (SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController,"BoUserModify"))) ? "true" : "false"));
				$this->_xmlToolBox->addFullTag("user_delete",$this->_generic->getFullPath($this->_boController,"BoUserDelete",array("id" => ''),false),true,array("authorized" => (SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController,"BoUserDelete"))) ? "true" : "false"));
				$this->_xmlToolBox->addFullTag("user_status",$this->_generic->getFullPath($this->_boController,"BoUserStatus",array("id" => ''),false),true,array("authorized" => (SLS_BoRights::isAuthorized("","",$this->_generic->getActionId($this->_boController,"BoUserStatus"))) ? "true" : "false"));
			$this->_xmlToolBox->endTag("various");
			# /URLs
			
			# Server
			$apcUpload = (in_array(ini_get("apc.rfc1867"),array(1,"1","On","on",true,"true"))) ? "true" : "false";
			$apcUploadKey = ini_get("apc.rfc1867_name");
			$uploadMaxFilesize = ini_get("upload_max_filesize");
			$unite = strtolower(substr(trim($uploadMaxFilesize), -1));
			switch ($unite)
		    {   
				case 'k': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024;					break;
		    	case 'm': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024 * 1024; 			break;
				case 'g': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024 * 1024 * 1024;	break;
				default: $uploadMaxFilesize = $uploadMaxFilesize;								break;
		    }
			$this->_xmlToolBox->startTag("server");
				$this->_xmlToolBox->addFullTag("apc_upload",$apcUpload,true);
				$this->_xmlToolBox->addFullTag("apc_upload_key",$apcUploadKey,true);
				$this->_xmlToolBox->addFullTag("upload_max_size",$uploadMaxFilesize,true);
			$this->_xmlToolBox->endTag("server");
			# /Server
		}
	}
}
?>