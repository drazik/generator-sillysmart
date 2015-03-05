<?php
class SLS_BoUpdateSLS extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$confirmation = $this->_http->getParam("confirm", 'post');
		$password = SLS_String::trimSlashesFromString($this->_http->getParam("password", 'post'));
		$login = SLS_String::trimSlashesFromString($this->_http->getParam("login", 'post'));
		$stepPassword = false;
		if ($confirmation == 'update')
		{
			$stepPassword = true;
			if (!empty($password) && !empty($login))
			{
				$slsXml = $this->_generic->getCoreXML('sls');
				$passXML = array_shift($slsXml->getTags("//sls_configs/auth/users/user[@login='".sha1($login)."' and @level='0']/@pass"));
				
				if (!empty($passXML) && $passXML == sha1($password))
				{					
					$syncServer = array_shift($slsXml->getTags("slsnetwork"));
					$slsVersion = array_shift($slsXml->getTags("version"));
					
					$serversJSON = @file_get_contents($syncServer);		
					if ($serversJSON !== false && !empty($serversJSON))
					{						
						$servers = json_decode($serversJSON);
						$updateServer = array_shift($servers->servers->update);						
						if ($updateServer === false || empty($updateServer))
						{
							$xml->addFullTag("error_server","Update server can't be found, please retry later");
						}
						else
						{							
							$serverContent = @file_get_contents($updateServer->url);
							$jsonContent = json_decode($serverContent);
							
							if ($jsonContent === false || empty($jsonContent))
							{
								$xml->addFullTag("error_server","Update server not available, please retry later");
							}
							else
							{
								$allSls = $jsonContent->releases;								
								$currentSls = $slsVersion;
								
								// Foreach releases, check if we need to update it
								for($i=0 ; $i<$count=count($allSls) ; $i++)
								{
									$version = $allSls[$i]->version;
										
									// If it's we have to update to this version
									if ($version > $currentSls)
									{
										// If we can't download install files, abort update
										if (SLS_Remote::remoteFileExists($updateServer->domain.$allSls[$i]->archive) != 0 || SLS_Remote::remoteFileExists($updateServer->domain.$allSls[$i]->script) != 0)
										{
											$xml->addFullTag("error_server","Update process failed");
											break;
										}
										// Else, it's cool, update this version
										else
										{
											// If 'Releases' directory doesn't exists, create it
											if (!is_dir($this->_generic->getPathConfig("coreTmpDownload")."Releases"))
												mkdir($this->_generic->getPathConfig("coreTmpDownload")."Releases");
												
											// If 'Releases/[Release Version]' directory doesn't exists, create it
											if (!is_dir($this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version))
												mkdir($this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version);
											
											// Archive & script names
											$archive = $this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version."/archive.tar";
											$script  = $this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version."/update.php";
											
											// Copy archive & script
											$resultTar = @copy($updateServer->domain.$allSls[$i]->archive, $archive);
											$resultScript = @copy($updateServer->domain.$allSls[$i]->script, $script);

											// If a copy has failed										
											if ($resultTar === false || $resultScript === false)
											{
												$xml->addFullTag("error_server","Update process failed");
												break;
											}																		
											
											// Execute script
											eval(file_get_contents($script));
											
											// Delete unstall files
											@unlink($this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version."/archive.tar");
											@unlink($this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version."/update.php");
											@rmdir($this->_generic->getPathConfig("coreTmpDownload")."Releases/".$allSls[$i]->version);
										}
									}									
								}
								$this->_generic->goDirectTo("SLS_Bo","Updates");
							}
						}
					}
					else
					{
						$xml->addFullTag("error_server","Synchronisation server not available, please retry later");
					}
				}
			}
		}
		$xml->addFullTag('stepPassword', ($stepPassword) ? 'yes' : 'no', true);
		$this->saveXML($xml);
	}
	
}
?>