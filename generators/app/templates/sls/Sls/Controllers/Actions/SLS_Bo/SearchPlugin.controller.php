<?php
class SLS_BoSearchPlugin extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml = $this->makeMenu($this->getXML());
		$slsXml = $this->_generic->getCoreXML('sls');
		$errors = array();
		$syncServer = array_shift($slsXml->getTags("slsnetwork"));
		$action = $this->_http->getParam('Action');
		$serverID = $this->_http->getParam("Server");
		$pluginID = $this->_http->getParam("Plugin");
		$this->registerLink("CREATE", "SLS_Bo", "CreatePlugin");
		$controllers = $this->_generic->getTranslatedController('SLS_Bo', 'SearchPlugin');
		
		$serversJSON = @file_get_contents($syncServer);
		if ($serversJSON === false ||empty($serversJSON))
			$errors[] = "You are not connected to internet or synchronisation server is temporaly unavailable";
		else 
		{			
			$servers = json_decode($serversJSON);
			$pluginsServers = $servers->servers->plugins;
			$plugins = array();
			
			$xml->startTag("servers");
			
			foreach ($pluginsServers as $pluginsServer)
			{				
				$serverContent = @file_get_contents($pluginsServer->url);				
				
				$xml->startTag("server", array("name"=>$pluginsServer->name,"id"=>$pluginsServer->id));
					$xml->addFullTag("url", $pluginsServer->url, true, array("status"=>($serverContent === false || empty($serverContent)) ? "0" : "1"));

					if ($serverContent !== false && !empty($serverContent))
					{
						$serverContent = json_decode($serverContent);
						
						
						$xml->startTag("plugins");
						$plugs = $serverContent->plugins;
						foreach ($plugs as $plug)
						{
							
							// If we want to download this Plugin
							if ($action == "Download" && $pluginsServer->id == $serverID && $plug->id == $pluginID)
							{
								$way = $this->_http->getParam('Way');
								$pluginXML = $this->_generic->getPluginXml("plugins");
								if (count($pluginXML->getTags("//plugins/plugin[@id = '".$plug->id."']")) == 0 && count($pluginXML->getTags("//plugins/plugin[@code = '".$plug->code."' and @beta='1']")) == 0)
								{
									if (SLS_Remote::remoteFileExists($plug->file) != 0)
										$errors[] = $plug->name." is unavailable";
									else 
									{
										$filename = $this->_generic->getPathConfig("coreTmpDownload")."Plugins/".SLS_String::substrAfterLastDelimiter($plug->file, "/");
										if (!is_dir($this->_generic->getPathConfig("coreTmpDownload")."Plugins"))
											mkdir($this->_generic->getPathConfig("coreTmpDownload")."Plugins");
										$result = @copy($plug->file, $filename);
										if ($result === false)
											$errors[] = "The download of ".$plug->name." has failed";
										else 
										{
											$tar = new SLS_Tar();
											if ($tar->openTAR($filename) === false)
												$errors[] = "Plugin archive is corrupted";
											else 
											{
												$hasConf = false;
												$isFile = true;
												$pathName = "";
												foreach ($tar->directories as $directory)
												{
													if (SLS_String::startsWith($directory['name'], "Sources/"))
													{
														$dirName = SLS_String::substrAfterFirstDelimiter($directory['name'], "Sources/");
														if (!empty($dirName))
														{
															if (!is_dir($this->_generic->getPathConfig("plugins").$dirName))
																mkdir($this->_generic->getPathConfig("plugins").$dirName);
															if (empty($pathName))
																$pathName = (strpos($dirName, "/") !== false) ? SLS_String::substrBeforeLastDelimiter($dirName, "/") : $dirName;
															
															$isFile = false;
														}
													}
												}
												
												foreach ($tar->files as $file)
												{
													
													$copy = true;
													if (SLS_String::startsWith($file['name'], "Configs/") && SLS_String::endsWith($file['name'], ".xml"))
													{
														$hasConf = true;
														$copy = @file_put_contents($this->_generic->getPathConfig("configPlugins").$plug->id."_".$plug->code.".xml", $file['file']);
													}
													if (SLS_String::startsWith($file['name'], 'Sources/'))
													{
														if ($isFile === true && $pathName == "")
															$pathName = SLS_String::substrAfterFirstDelimiter($file['name'], "Sources/");
														$realPathFile = $this->_generic->getPathConfig("plugins").SLS_String::substrAfterFirstDelimiter($file['name'], "Sources/");
														$copy = @file_put_contents($realPathFile, $file['file']);
													}
													if ($copy === false)
													{
														$errors[] = "The copy of ".$file['name']." has failed";
													}
												}
												
												if (empty($errors))
												{
													$newPlugin = new SLS_XMLToolbox(false);
													$newPlugin->startTag("plugin", array("code"=>$plug->code,"id"=>$plug->id,"version"=>$plug->version,"compability"=>$plug->compability,"customizable"=>($hasConf) ? "1" : "0","file"=>($isFile)?"1":"0","path"=>$pathName));
														$newPlugin->addFullTag("name", $plug->name, true);
														$newPlugin->addFullTag("description", $plug->desc, true);
														$newPlugin->addFullTag("author", $plug->author, true);
														$newPlugin->addFullTag("dependencies", "", false);
													$newPlugin->endTag("plugin");
													$pluginXML->appendXMLNode("//plugins", $newPlugin->getXML('noHeader'));
													file_put_contents($this->_generic->getPathConfig("configPlugins")."plugins.xml", $pluginXML->getXML());
												}
												if (is_file($filename))
													unlink($filename);
											}
										}
									}
								}
								if ($way == "Maj")
								{
									$this->goDirectTo("SLS_Bo", "Plugins");
								}
								
							}//  /If we want to download this Plugin
							
							// We list all Plugins available on this server
							$exist = SLS_PluginsManager::isExists($plug->id);
							if ($exist)
							{
								$plugin = new SLS_PluginsManager($plug->id);
							}
							$xml->startTag("plugin", array("version"=>$plug->version,"code"=>$plug->code,"id"=>$plug->id,"compability"=>((float)$plug->compability<=(float)array_shift($slsXml->getTags("version"))) ? "1" : "0","has"=>($exist)?"1":"0"));
							$xml->addFullTag("name", $plug->name, true);
							$xml->addFullTag("doc", $pluginsServer->domain.$plug->doc, true);
							$xml->addFullTag("dl", $controllers['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$controllers['controller']."/".$controllers['scontroller']."/Action/Download/Server/".$pluginsServer->id."/Plugin/".$plug->id.".sls");
							$xml->addFullTag("desc", $plug->desc, true);
							$xml->addFullTag("author", $plug->author, true);
							$xml->endTag("plugin");
						}
						$xml->endTag("plugins");
					}
					
				$xml->endTag("server");
			}
			$xml->endTag("servers");
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