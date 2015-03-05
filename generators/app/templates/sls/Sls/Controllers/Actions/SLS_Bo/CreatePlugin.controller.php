<?php
class SLS_BoCreatePlugin extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$reload = $this->_http->getParam("reload");
		$action = $this->_http->getParam("Action");
		$errors = array();
		$slsXml = $this->_generic->getCoreXML('sls');
		$syncServer = array_shift($slsXml->getTags("slsnetwork"));
		$slsVersion = array_shift($slsXml->getTags("version"));
		$edit = $this->_generic->getTranslatedController('SLS_Bo', 'EditPlugin');		
		$editAppli = $this->_generic->getTranslatedController('SLS_Bo', 'CreatePlugin');		
		
		$pluginsXML = $this->_generic->getPluginXml("plugins");
		// List own Plugins
		if ($action == "")
		{
			$deleteController = $this->_generic->getTranslatedController("SLS_Bo", "DeletePlugin");
			if (($count = count($pluginsXML->getTags("//plugins/plugin[@beta='1']"))) > 0)
			{
				$xml->startTag("own_plugin");
					for($i=1;$i<=$count;$i++)
					{
						$id = array_shift($pluginsXML->getTags("//plugins/plugin[@beta='1'][".$i."]/@id"));
						$xml->startTag("plugin", array("code"=>array_shift($pluginsXML->getTags("//plugins/plugin[@beta='1'][".$i."]/@code")),"id"=>$id));
						$xml->addFullTag("description", array_shift($pluginsXML->getTags("//plugins/plugin[@beta='1'][".$i."]/description")), true);
						$xml->addFullTag("custom", array_shift($pluginsXML->getTags("//plugins/plugin[@beta='1'][".$i."]/@customizable")), true);
						$xml->addFullTag("edit", $edit['protocol']."://".$this->_generic->getSiteConfig('domainName')."/".$edit['controller']."/".$edit['scontroller']."/Plugin/".$id.".sls", true);
						$xml->addFullTag("name", array_shift($pluginsXML->getTags("//plugins/plugin[@beta='1'][".$i."]/name")), true);
						$xml->addFullTag("delete", $deleteController['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$deleteController['controller']."/".$deleteController['scontroller']."/Plugin/".$id.".sls", true);
						$xml->addFullTag("editAppli", $editAppli['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$editAppli['controller']."/".$editAppli['scontroller']."/Plugin/".$id."/Action/Edit.sls", true);						
						$xml->endTag("plugin");
						
					}
				$xml->endTag("own_plugin");
			}
			$this->registerLink("CREATE", "SLS_Bo", "CreatePlugin", array("Action"=>"Create"));
			$xml->addFullTag("step", "list", true);
		}
		// Create a new Plugin
		elseif ($action == "Create")
		{
			if ($reload == "true")
			{
				$name = SLS_String::trimSlashesFromString($this->_http->getParam("name"));
				$code = SLS_String::stringToUrl(strtolower(SLS_String::getSafeFilename(SLS_String::trimSlashesFromString($this->_http->getParam("code")))), "", true);
				$output = SLS_String::trimSlashesFromString($this->_http->getParam("output"));
				$custom = SLS_String::trimSlashesFromString($this->_http->getParam("custom"));
				$path = SLS_String::trimSlashesFromString($this->_http->getParam("path"));
				$pathName = SLS_String::stringToUrl(SLS_String::getSafeFilename(ucwords(SLS_String::trimSlashesFromString($this->_http->getParam("code")))), "", false);
				$description = SLS_String::trimSlashesFromString($this->_http->getParam("full_description"));
				
				// Get Plugins Versions
				$serversJSON = @file_get_contents($syncServer);
				$pluginsAvailable = array();
				$filesReserved = array();
				$dirsReserved = array();
				if ($serversJSON !== false && !empty($serversJSON))
				{					
					$servers = json_decode($serversJSON);
					$pluginsServers = $servers->servers->plugins;
					$plugins = array();
					
					foreach ($pluginsServers as $pluginsServer)
					{						
						$serverContent = @file_get_contents($pluginsServer->url);
						if ($serverContent !== false && !empty($serverContent))
						{
							$serverContent = json_decode($serverContent);
							$plugs = $serverContent->plugins;
							foreach ($plugs as $plug)
							{
								$pluginsAvailable[] = $plug->code;
								if ($plug->type == 'file')
									$filesReserved[] = $plug->path;
								else 
									$dirsReserved[] = $plug->path;
							}
						}
					}
				}
				
				$xml->startTag("form");
					$xml->addFullTag("name", $name, true);
					$xml->addFullTag("code", $code, true);
					$xml->addFullTag("custom", $custom, true);
					$xml->addFullTag("path", $path, true);
					$xml->addFullTag("path_name", $pathName, true);
					$xml->addFullTag("fill_description", $description, true);
					if (empty($name))
						$errors[] = "You must fill the common name";
					if (empty($code))
						$errors[] = "You must fill the code name";
					if (empty($output) || ($output != 'yes' && $output != 'no'))
						$errors[] = "Choose if your plugin is an output type";
					if (empty($custom) || ($custom != 'yes' && $custom != 'no'))
						$errors[] = "Choose if your plugin will be customizable";
					if (empty($path) || ($path != 'file' && $path != 'dir'))
						$errors[] = "Choose if your plugin require only a file or multiple files in a directory";	
					if (empty($pathName))
						$errors[] = "You must choose a filename or a directory name";	
					if (empty($description))
						$errors[] = "You must fill the full description in English";	
					if (empty($errors))
					{
						if (in_array($code, $pluginsAvailable))
							$errors[] = "This code name is already in use for another plugin";
						if ($path == 'file' && in_array($pathName.".class.php", $filesReserved))
							$errors[] = "This file name is already in use for another plugin";
						if ($path == 'dir' && in_array($pathName, $dirsReserved))
							$errors[] = "This directory name is already in use for another plugin";
						
						if (empty($errors))
						{
							
							$newId = md5(uniqid($this->_generic->getSiteConfig("privateKey")));
							$pathFile = $this->_generic->getPathConfig("plugins");
							if ($path =="dir")
								$pathFile .= $pathName."/";
							
							if ($output == 'no')
							{
								$str = '<?php'."\n".
										'/**'."\n".
										' * Plugin '.$name."\n". 
										' * '.str_replace("<br />", "\\n * ", nl2br($description))."\n".
										' *'."\n".
										' * @package Plugins'."\n".
										' * @since 1.0'."\n".
										' */'."\n". 
										'class '.$pathName.' extends SLS_PluginGeneric implements SLS_IPlugin'."\n".
										'{'."\n".
										t(1).'public function __construct()'."\n".
										t(1).'{'."\n".
											t(2).'parent::__construct($this);'."\n".
											t(2).'$this->checkDependencies();'."\n".
										t(1).'}'."\n\n".
										t(1).'public function checkDependencies()'."\n".
										t(1).'{'."\n".
										t(1).'}'."\n".
										'}'."\n".
										'?>';
							}
							else 
							{
								$str = '<?php'."\n".
										'/**'."\n".
										' * Plugin '.$name."\n". 
										' * '.str_replace("<br />", "\\n * ", nl2br($description))."\n".
										' *'."\n".
										' * @package Plugins'."\n".
										' * @since 1.0'."\n".
										' */'."\n". 
										'class '.$pathName.' extends SLS_PluginGeneric implements SLS_IPlugin, SLS_IPluginOutput'."\n".
										'{'."\n".
										t(1).'public function __construct()'."\n".
										t(1).'{'."\n".
											t(2).'parent::__construct($this);'."\n".
											t(2).'$this->checkDependencies();'."\n".
										t(1).'}'."\n\n".
										t(1).'public function checkDependencies()'."\n".
										t(1).'{'."\n".
										t(1).'}'."\n".
										'}'."\n".
										'?>';
							}
							
							if (@file_put_contents($pathFile.$pathName.".class.php", $str) === false)
								$errors[] = "Plugin Creation failed";
									
							
							if (empty($errors))
							{
								if ($custom == "yes")
								{
									$str = "<?xml version=\"1.0\" encoding=\"utf-8\"?><plugin><exemple_part writable=\"1\" label=\"Exemple Part\" clonable=\"1\" alias=\"main\"><exemple_row writable=\"1\" label=\"Exemple Row\" type=\"string\" clonable=\"0\" /></exemple_part></plugin>";
									if (@file_put_contents($this->_generic->getPathConfig("configPlugins").$newId."_".$code.".xml", $str) === false)
										$errors[] = "Plugin Creation failed";
								}
								
							}
							if (empty($errors))
							{
								$newPlugin = new SLS_XMLToolbox(false);
								$newPlugin->startTag("plugin", array(
									"beta"=>"1",
									"code"=>$code,
									"id"=>$newId,
									"version"=>"0.1",
									"compability"=>$slsVersion,
									"customizable"=>($custom=="yes")?"1":"0",
									"output"=>($output=="yes")?"1":"0",
									"file"=>($path=="file")?"1":"0",
									"path"=>($path=="file")?$pathName.".class.php" : $pathName
								));
								$newPlugin->addFullTag("name", $name, true);
								$newPlugin->addFullTag("description", $description, true);
								$newPlugin->addFullTag("author", "Me", true);
								$newPlugin->addFullTag("dependencies", "", false);
								$newPlugin->endTag("plugin");
								$pluginsXML->appendXMLNode("//plugins", $newPlugin->getXML('noHeader'));
								file_put_contents($this->_generic->getPathConfig("configPlugins")."plugins.xml", $pluginsXML->getXML());
								$this->goDirectTo("SLS_Bo", "CreatePlugin");
							}
							
						}
						
					}
					
				$xml->endTag("form");
			}
			$xml->addFullTag("step", "create", true);
		}
		elseif ($action == "Edit")
		{
			
			$pluginID = $this->_http->getParam("Plugin");
			if (SLS_PluginsManager::isExists($pluginID) === false)
				$this->goDirectTo("SLS_Bo", "CreatePlugin");
			
			$originalPlugin = new SLS_PluginsManager($pluginID);
				
			if ($reload == "true")
			{
				
				$name = SLS_String::trimSlashesFromString($this->_http->getParam("name"));
				$code = SLS_String::stringToUrl(strtolower(SLS_String::getSafeFilename(SLS_String::trimSlashesFromString($this->_http->getParam("code")))), "", true);
				$output = SLS_String::trimSlashesFromString($this->_http->getParam("output"));
				$custom = SLS_String::trimSlashesFromString($this->_http->getParam("custom"));
				$path = SLS_String::trimSlashesFromString($this->_http->getParam("path"));
				$pathName = SLS_String::stringToUrl(SLS_String::getSafeFilename(ucwords(SLS_String::trimSlashesFromString($this->_http->getParam("code")))), "", false);
				$description = SLS_String::trimSlashesFromString($this->_http->getParam("full_description"));
				
				// Get Plugins Versions
				$serversJSON = @file_get_contents($syncServer);
				$pluginsAvailable = array();
				$filesReserved = array();
				$dirsReserved = array();
				if ($serversJSON !== false && !empty($serversJSON))
				{					
					$servers = json_decode($serversJSON);
					$pluginsServers = $servers->servers->plugins;
					$plugins = array();
					
					foreach ($pluginsServers as $pluginsServer)
					{						
						$serverContent = @file_get_contents($pluginsServer->url);
						if ($serverContent !== false && !empty($serverContent))
						{
							$serverContent = json_decode($serverContent);
							$plugs = $serverContent->plugins;
							foreach ($plugs as $plug)
							{
								$pluginsAvailable[] = $plug->code;
								if ($plug->type == 'file')
									$filesReserved[] = $plug->path;
								else 
									$dirsReserved[] = $plug->path;
							}
						}
					}
				}
				
				$xml->startTag("form");
					$xml->addFullTag("name", $name, true);
					$xml->addFullTag("code", $code, true);
					$xml->addFullTag("custom", $custom, true);
					$xml->addFullTag("path", $path, true);
					$xml->addFullTag("path_name", $pathName, true);
					$xml->addFullTag("fill_description", $description, true);
					if (empty($name))
						$errors[] = "You must fill the common name";
					if (empty($code))
						$errors[] = "You must fill the code name";
					if (empty($output) || ($output != 'yes' && $output != 'no'))
						$errors[] = "Choose if your plugin is an output type";
					if (empty($custom) || ($custom != 'yes' && $custom != 'no'))
						$errors[] = "Choose if your plugin will be customizable";
					if (empty($path) || ($path != 'file' && $path != 'dir'))
						$errors[] = "Choose if your plugin require only a file or multiple files in a directory";	
					if (empty($pathName))
						$errors[] = "You must choose a filename or a directory name";	
					if (empty($description))
						$errors[] = "You must fill the full description in English";	
					if (empty($errors))
					{
						if ($code != $originalPlugin->_code && in_array($code, $pluginsAvailable))
							$errors[] = "This code name is already in use for another plugin";
						if ($pathName.".class.php" != $originalPlugin->_path && $path == 'file' && in_array($pathName.".class.php", $filesReserved))
							$errors[] = "This file name is already in use for another plugin";
						if ($pathName != $originalPlugin->_path && $path == 'dir' && in_array($pathName, $dirsReserved))
							$errors[] = "This directory name is already in use for another plugin";
						if (empty($errors))
						{
							if ($originalPlugin->_file == 1 && $path == 'dir')
							{
								if (!is_dir($this->_generic->getPathConfig("plugins").$originalPlugin->_path))
									mkdir($this->_generic->getPathConfig("plugins").$pathName);
								if (is_file($this->_generic->getPathConfig("plugins").$originalPlugin->_path))
									@unlink($this->_generic->getPathConfig("plugins").$originalPlugin->_path);
								
							}
							if ($originalPlugin->_file == 0 && $path == 'file')
							{
								if (is_dir($this->_generic->getPathConfig("plugins").$originalPlugin->_path))
									$this->_generic->rm_recursive($this->_generic->getPathConfig("plugins").$originalPlugin->_path);
								if ($output == 'no')
								{
									$str = '<?php'."\n".
											'/**'."\n".
											' * Plugin '.$name."\n". 
											' * '.str_replace("<br />", "\\n * ", nl2br($description))."\n".
											' *'."\n".
											' * @package Plugins'."\n".
											' * @since 1.0'."\n".
											' */'."\n". 
											'class '.$pathName.' extends SLS_PluginGeneric implements SLS_IPlugin'."\n".
											'{'."\n".
											t(1).'public function __construct()'."\n".
											t(1).'{'."\n".
												t(2).'parent::__construct($this);'."\n".
												t(2).'$this->checkDependencies();'."\n".
											t(1).'}'."\n\n".
											t(1).'public function checkDependencies()'."\n".
											t(1).'{'."\n".
											t(1).'}'."\n".
											'}'."\n".
											'?>';
								}
								else 
								{
									$str = '<?php'."\n".
											'/**'."\n".
											' * Plugin '.$name."\n". 
											' * '.str_replace("<br />", "\\n * ", nl2br($description))."\n".
											' *'."\n".
											' * @package Plugins'."\n".
											' * @since 1.0'."\n".
											' */'."\n". 
											'class '.$pathName.' extends SLS_PluginGeneric implements SLS_IPlugin, SLS_IPluginOutput'."\n".
											'{'."\n".
											t(1).'public function __construct()'."\n".
											t(1).'{'."\n".
												t(2).'parent::__construct($this);'."\n".
												t(2).'$this->checkDependencies();'."\n".
											t(1).'}'."\n\n".
											t(1).'public function checkDependencies()'."\n".
											t(1).'{'."\n".
											t(1).'}'."\n".
											'}'."\n".
											'?>';
								}
								
								if (@file_put_contents($this->_generic->getPathConfig("plugins").$pathName.".class.php", $str) === false)
									$errors[] = "Plugin Creation failed";
							}
							if ($originalPlugin->_file == 1 && $path == 'file' && $pathName.".class.php" != $originalPlugin->_path)
							{
								if (is_file($this->_generic->getPathConfig("plugins").$originalPlugin->_path))
									rename($this->_generic->getPathConfig("plugins").$originalPlugin->_path, $this->_generic->getPathConfig("plugins").$pathName.".class.php");
							}
							if ($originalPlugin->_file == 0 && $path == 'dir' && $pathName != $originalPlugin->_path)
							{
								if (is_dir($this->_generic->getPathConfig("plugins").$originalPlugin->_path))
									rename($this->_generic->getPathConfig("plugins").$originalPlugin->_path, $this->_generic->getPathConfig("plugins").$pathName);
							}
							if (empty($errors))
							{
								if ($custom == "yes" && $originalPlugin->_customizable == false)
								{
									$str = "<?xml version=\"1.0\" encoding=\"utf-8\"?><plugin><exemple_part writable=\"1\" label=\"Exemple Part\" clonable=\"1\" alias=\"main\"><exemple_row writable=\"1\" label=\"Exemple Row\" type=\"string\" clonable=\"0\" /></exemple_part></plugin>";
									if (@file_put_contents($this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$code.".xml", $str) === false)
										$errors[] = "Plugin Creation failed";
								}
								if ($custom == "no" && $originalPlugin->_customizable == true)
								{
									if (is_file($this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$originalPlugin->_code.".xml"))
										unlink($this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$originalPlugin->_code.".xml");
								}
								
							}
							if (empty($errors))
							{
								if ($code != $originalPlugin->_code)
								{
									$pluginsXML->setTagAttributes("//plugins/plugin[@beta='1' and @id='".$pluginID."']", array("code"=>$code));
									if (is_file($this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$originalPlugin->_code.".xml"))
										rename($this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$originalPlugin->_code.".xml", $this->_generic->getPathConfig("configPlugins").$originalPlugin->_id."_".$code.".xml");
								}
								if (($output == "yes" && $originalPlugin->_output == 0) || ($output == "no" && $originalPlugin->_output == 1))
									$pluginsXML->setTagAttributes("//plugins/plugin[@beta='1' and @id='".$pluginID."']", array("output"=>($output=="yes")?"1":"0"));
								if (($custom == "yes" && $originalPlugin->_customizable == false) || ($custom == "no" && $originalPlugin->_customizable == true))
									$pluginsXML->setTagAttributes("//plugins/plugin[@beta='1' and @id='".$pluginID."']", array("customizable"=>($custom=="yes")?"1":"0"));
								if (($originalPlugin->_file == 1 && $path == 'dir') || ($originalPlugin->_file == 0 && $path == 'file'))
									$pluginsXML->setTagAttributes("//plugins/plugin[@beta='1' and @id='".$pluginID."']", array("file"=>($path=="file")?"1":"0"));
								if (($path == 'file' && $originalPlugin->_path != $pathName.".class.php") || ($path == 'dir' && $originalPlugin->_path != $pathName))
									$pluginsXML->setTagAttributes("//plugins/plugin[@beta='1' and @id='".$pluginID."']", array("path"=>($path=="file")?$pathName.".class.php" : $pathName));
								if ($originalPlugin->_name != $name)
									$pluginsXML->setTag("//plugins/plugin[@beta='1' and @id='".$pluginID."']/name", $name, true);
								if ($originalPlugin->_description != $description)
									$pluginsXML->setTag("//plugins/plugin[@beta='1' and @id='".$pluginID."']/description", $description, true);
								
								file_put_contents($this->_generic->getPathConfig("configPlugins")."plugins.xml", $pluginsXML->getXML());
								$this->goDirectTo("SLS_Bo", "CreatePlugin");
							}
							
						}
						
					}
					
				$xml->endTag("form");
			}
			$originalPlugin = new SLS_PluginsManager($pluginID);
			$xml->startTag("plugin");
				$xml->addFullTag("name", $originalPlugin->_name, true);
				$xml->addFullTag("code", $originalPlugin->_code, true);
				$xml->addFullTag("output", ($originalPlugin->_output == 1)?"yes":"no", true);
				$xml->addFullTag("custom", ($originalPlugin->_customizable)?"yes":"no", true);
				$xml->addFullTag("path", ($originalPlugin->_file==1)?'file':'dir', true);
				$xml->addFullTag("path_name", ($originalPlugin->_file==1)?SLS_String::substrBeforeLastDelimiter($originalPlugin->_path, ".class.php"):$originalPlugin->_path, true);
				$xml->addFullTag("fill_description", $originalPlugin->_description, true);
			$xml->endTag("plugin");
			$xml->addFullTag("step", "edit", true);
		}
		
		if (!empty($errors))
		{
			$xml->startTag("errors");
			foreach ($errors as $error)
				$xml->addFullTag("error", $error, true);
			$xml->endTag("errors");
		}
		
		$this->saveXML($xml);		
	}
	
}
?>