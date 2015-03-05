<?php
/**
* Class BoDeleteFile into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoDeleteFile extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		// Params
		$file = $this->_http->getParam("file");
		$db = $this->_http->getParam("db");
		$table = $this->_http->getParam("table");
		$column = $this->_http->getParam("column");
		$id = $this->_http->getParam("id");
		if (SLS_String::startsWith($file,"http://".$this->_generic->getSiteConfig("domainName")."/"))
			$file = SLS_String::substrAfterFirstDelimiter($file,"http://".$this->_generic->getSiteConfig("domainName")."/");
		if (SLS_String::startsWith($file,"https://".$this->_generic->getSiteConfig("domainName")."/"))
			$file = SLS_String::substrAfterFirstDelimiter($file,"https://".$this->_generic->getSiteConfig("domainName")."/");
		
		// Check file
		if (file_exists($file) && !is_dir($file))
		{
			// Realy a public ressource ?
			if (SLS_String::startsWith($file,$this->_generic->getPathConfig("files")))
			{
				// Already Deprecated ?
				if (SLS_String::contains($file,$this->_generic->getPathConfig("files")."__Uploads/__Deprecated/"))
				{
					try
					{
						unlink($file);
						$this->_bo->_render["status"] = "OK";
					}
					catch (Exception $e)
					{
						$this->_bo->_render["errors"][] = $file.": ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_WRITE'];
					}
				}
				// Else move it
				else
				{
					// Destination
					$target = $this->_generic->getPathConfig("files")."__Uploads/__Deprecated/";
					
					// Filename
					$fileName = SLS_String::substrAfterLastDelimiter($file,"/");
					
					// Try to get source folder
					$directory = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($file,$this->_generic->getPathConfig("files")),"/".$fileName);
					if (!empty($directory))
					{
						if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$directory))
							@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".$directory);
						$target .= $directory."/";
					}
					
					// Move file
					$target .= $fileName;
					try
					{
						rename($file,$target);
						$this->_bo->_render["status"] = "OK";
					}
					catch (Exception $e)
					{
						$this->_bo->_render["errors"][] = $file.": ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_WRITE'];
					}
				}
				
				// Recordset to update ?
				if (!empty($table) && !empty($column))
				{
					$db = (empty($db)) ? $this->_bo->_defaultDb : $db;
					$className = ucfirst(strtolower($db))."_".SLS_String::tableToClass($table);
					$this->_generic->useModel(SLS_String::tableToClass($table),$db,"user");
					$object = new $className();
					$langs = ($object->isMultilanguage()) ? $this->_lang->getSiteLangs() : array($this->_lang->getLang());
					foreach($langs as $lang)
					{
						$object->setModelLanguage($lang);
						if ($object->getModel($id) === true)
						{
							$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$column)," ",false)),"");
							if ($object->$functionName(""))
								$object->save();
						}
					}
				}				 
			}
			// Else, not authorized
			else
			{
				$this->_bo->_render["authorized"] = "false";
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_AUTHORIZED'];
			}
		}
		else
		{
			$this->_bo->_render["errors"][] = $file.": ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_EXIST'];
		}
		
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
}
?>