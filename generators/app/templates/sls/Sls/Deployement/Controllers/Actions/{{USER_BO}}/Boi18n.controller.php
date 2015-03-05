<?php
/**
* Class Boi18n into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}Boi18n extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		// Objects
		$langsError = array();
		
		// Params
		$file = str_replace("|","/",$this->_http->getParam("File"));
		$fileParts = explode("/",$file);
		$xml->startTag("file");
			$xml->addFullTag("type",(SLS_String::startsWith($file,"Generics/")) ? "SITE" : ((count($fileParts) == 3 && SLS_String::startsWith($fileParts[2],"__")) ? "CONTROLLER" : "ACTION"),true);
			$xml->addFullTag("name",(SLS_String::startsWith($file,"Generics/")) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_MENU_I18N_SITE'] : ((count($fileParts) == 3 && SLS_String::startsWith($fileParts[2],"__")) ? $fileParts[1] : $fileParts[1]."/".$fileParts[2]),true);
		$xml->endTag("file");
		
		// Get file
		if (file_exists($this->_generic->getPathConfig("langs").$file.".".$this->_bo->_defaultLang.".lang.php") && !is_dir($this->_generic->getPathConfig("langs").$file.".".$this->_bo->_defaultLang.".lang.php"))
		{
			$sentences = array();
			
			if ($this->_http->getParam("reload-i18n") == "true")
			{
				$errors = array();
				$translations = (is_array($this->_http->getParam("translations"))) ? $this->_http->getParam("translations") : array();
				
				foreach($translations as $lang => $types)
				{
					$types = (is_array($types)) ? $types : array($types);
					
					foreach($types as $type => $keys)
					{
						$keys = (is_array($keys)) ? $keys : array($keys);
						
						foreach($keys as $key => $value)
						{
							if (empty($value))
							{
								$errors[$key."_".$lang] = $key." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_EMPTY'];
								if (!in_array($lang,$langsError))
								{
									$this->_bo->pushNotif("error",sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_ERROR'],$lang));
									$langsError[] = $lang;
								}
							}
							
							$sentences[$key][$lang][$type] = str_replace(array('\"',"\n","\r","\t","’"),array('"',"&lt;br /&gt;","","&#160;&#160;","'"),htmlspecialchars($value,ENT_NOQUOTES,'UTF-8'));
						}
					}
				}
				
				if (empty($errors))
				{
					// Erase files
					foreach($this->_bo->_langs as $lang)
					{	
						if (file_exists($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php") && !is_dir($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php"))
						{
							$fileContent = '<?php'."\n".
										   '/**'."\n".
										   '* Translations in \''.strtoupper($lang).'\' for file Langs/'.$file.'.'.$lang.'.lang.php'.' '."\n".
										   '* You can create all your sentences variables here. To create it, follow the exemple :'."\n".
										   '* 	Access it with JS and XSL variable : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'JS\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
										   '* 	Access it with XSL variable only   : $GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'KEY_OF_YOUR_VARIABLE\'] = "value of your sentence in '.strtoupper($lang).'";'."\n".
										   '*'."\n".
										   '* 	You can customise the value \'KEY_OF_YOUR_VARIABLE\' and "value of your sentence in '.strtoupper($lang).'"'."\n".
										   '* @author SillySmart'."\n".
										   '* @copyright SillySmart'."\n".
										   '* @package Langs.'.str_replace('/','.',$file).''."\n".
										   '* @since 1.0'."\n".
										   '*'."\n".
										   '*/'."\n";
							foreach($sentences as $key => $translations)
							{
								$type = (!empty($translations[$lang]['JS'])) ? 'JS' : 'XSL';
								$value = $translations[$lang][strtoupper($type)];
								if (!empty($value))
									$fileContent .= '$GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\''.$type.'\'][\''.$key.'\'] = "'.str_replace('"','\"',$value).'";'."\n";
							}
							$fileContent .= '?>';
							file_put_contents($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php",$fileContent);
						}
					}
					
					// Flush cache
					if (SLS_String::startsWith($file,"Actions"))
					{
						$controllerInfos = explode("/",SLS_String::substrAfterFirstDelimiter($file,"Actions/"));
						if (count($controllerInfos) == 2)
						{
							$controllerName = $controllerInfos[0];
							$actionName = $controllerInfos[1];
							
							if (SLS_String::startsWith($actionName,"__"))
								$this->_cache->flushController($this->_generic->getControllerId($controllerName));
							else
								$this->_cache->flushAction($this->_generic->getActionId($controllerName,$actionName));
						}
					}
					else
						$this->_cache->flushFull();
					
					// Notif success
					$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_EDIT']);
				}
				else
				{
					$xml->startTag("errors");
					foreach($errors as $column => $error)
						$xml->addFullTag("error",$error,true,array("column"=>$column));
					$xml->endTag("errors");
				}
			}
			else
			{
				foreach($this->_bo->_langs as $lang)
				{	
					if (file_exists($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php") && !is_dir($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php"))
					{
						$lines = explode("\n",file_get_contents($this->_generic->getPathConfig("langs").$file.".".$lang.".lang.php"));
						array_map("trim",$lines);
		
						foreach($lines as $line)
						{
							if (SLS_String::startsWith($line,'$GLOBALS[$GLOBALS[\'PROJECT_NAME\']]'))
							{
								$type = (SLS_String::startsWith($line,'$GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\'XSL\'][\'')) ? "XSL" : "JS";
								$end = SLS_String::substrAfterFirstDelimiter($line,'$GLOBALS[$GLOBALS[\'PROJECT_NAME\']][\''.$type.'\'][\'');
		
								$key = SLS_String::substrBeforeFirstDelimiter($end,'\']');
								$value = SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($end,'";'),'"');
								$sentences[$key][$lang][$type] = $value;
							}
						}
					}
				}
			}
			
			$xml->startTag("sentences");
			foreach($sentences as $key => $values)
			{
				$dom = "input";
				$type = "XSL";
				$xml->startTag("sentence");
					$xml->addFullTag("title",$key,true);
					$xml->startTag("langs");
					foreach($values as $lang => $types)
					{
						foreach($types as $type => $sentence)
						{
							if (strlen(str_replace('\"','"',$sentence))>50)
								$dom = "textarea";
								
							$sentence = SLS_String::br2nl(htmlspecialchars_decode(str_replace(array('\"'),array('"'),$sentence),ENT_NOQUOTES));
								
							$xml->addFullTag("translation",$sentence,true,array("lang"=>$lang));
						}
					}
					$xml->endTag("langs");
					$xml->startTag("errors");
					foreach($this->_bo->_langs as $lang)
					{
						if (!empty($errors[$key."_".$lang]))
							$xml->addFullTag("error",$key." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_EMPTY'],true,array("lang"=>$lang));
					}
					$xml->endTag("errors");
					$xml->addFullTag("type",$type,true);
					$xml->addFullTag("dom",$dom,true);
				$xml->endTag("sentence");
			}
			$xml->endTag("sentences");
		}
		else
			$this->_generic->forward($this->_bo->_boController, "BoDashBoard");
		
		$xml = $this->_bo->formatNotif($xml);
		$this->saveXML($xml);
	}
}
?>