<?php
class SLS_BoResetSLS extends SLS_BoControllerProtected 
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
		if ($confirmation == 'reset')
		{
			$stepPassword = true;
			if (!empty($password) && !empty($login))
			{
				$slsXml = $this->_generic->getCoreXML('sls');
				$passXML = array_shift($slsXml->getTags("//sls_configs/auth/users/user[@login='".sha1($login)."' and @level='0']/@pass"));
				if (!empty($passXML) && $passXML == sha1($password))
				{
					
					 $paths = array(
						'actionsControllers',
					 	'staticsControllers',
					 	'componentsControllers',
						'viewsBody',
						'viewsHeaders',
					 	'viewsGenerics',
						'models',
						'modelsSql',
					 	'coreSlsModels',
					 	'coreSlsModelsSql',
						'actionLangs',
						'genericLangs',
						'plugins',
						'jsStatics',
						'jsDyn',
						'img',
						'css',
						'fonts',
						'configPlugins',
					 	'files',
					 	'cache',
					 	'logs'
					); 
					
					// Destroy Files
					foreach ($paths as $path)
					{
						if (!is_dir($this->_generic->getPathConfig($path)))
							continue;
						$files = scandir($this->_generic->getPathConfig($path));
						for($i=0;$i<$count=count($files);$i++)
						{
							if ($files[$i] != "." && $files[$i] != ".." && $files[$i] != ".svn")
								(is_file($this->_generic->getPathConfig($path).$files[$i])) ? @unlink($this->_generic->getPathConfig($path).$files[$i]) : $this->_generic->rm_recursive($this->_generic->getPathConfig($path).$files[$i]);							
						}
					}
					
					// Destroy xsl templates except __default
					$files = scandir($this->_generic->getPathConfig("viewsTemplates"));
					for($i=0;$i<$count=count($files);$i++)
					{
						if (!SLS_String::startsWith($files[$i],".") && $files[$i] != "__default.xsl" && is_file($this->_generic->getPathConfig("viewsTemplates").$files[$i]))
							@unlink($this->_generic->getPathConfig("viewsTemplates").$files[$i]);						
					}
					
					// Destroy Generics Translations
					$files = scandir($this->_generic->getPathConfig("coreGenericLangs"));
					for($i=0;$i<$count=count($files);$i++)
					{
						if ($files[$i] != "." && $files[$i] != ".." && $files[$i] != ".svn")
							(is_file($this->_generic->getPathConfig("coreGenericLangs").$files[$i])) ? @unlink($this->_generic->getPathConfig("coreGenericLangs").$files[$i]) : $this->_generic->rm_recursive($this->_generic->getPathConfig("coreGenericLangs").$files[$i]);
					}
					
					// Deploy the sls Generic En Lang
					@copy($this->_generic->getPathConfig('installDeployement')."Langs/Generics/generic.en.lang.php", $this->_generic->getPathConfig("coreGenericLangs")."generic.en.lang.php");
					
					// Update Controllers XML
					$controllersXML = $this->_generic->getControllersXML();
					$controllersXML->deleteTags("//controllers/controller[@side='user']");
										
					// Reset Metas
					$metasXML = new SLS_XMLToolBox(file_get_contents($this->_generic->getPathConfig("configSls")."metas.xml"));
					$slsControllers = $controllersXML->getTags("//controllers/controller[@side='sls']/@id");	
					$slsLangs = $controllersXML->getTags("//controllers/controller[@side='sls'][1]/scontrollers/scontroller[1]/scontrollerLangs/scontrollerLang/@lang");
					foreach ($slsControllers as $controller)
					{
						$slsActions = $controllersXML->getTags("//controllers/controller[@side='sls' and @id='".$controller."']/scontrollers/scontroller/@id");
						foreach ($slsActions as $slsAction)
						{
							foreach ($slsLangs as $lang)
							{
								$title = array_shift($metasXML->getTags("//sls_configs/action[@id='".$slsAction."']/title[@lang='".$lang."']"));
								$controllersXML->setTagAttributes("//controllers/controller[@side='sls' and @id='".$controller."']/scontrollers/scontroller[@id='".$slsAction."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']", array("title"=>$title));
							}
							$controllersXML->deleteTagAttribute("//controllers/controller[@side='sls' and @id='".$controller."']/scontrollers/scontroller[@id='".$slsAction."']", "id");
						}
						$controllersXML->deleteTagAttribute("//controllers/controller[@side='sls' and @id='".$controller."']", "id");
					}
										
					// Reset Bo Access
					$controllersXML->deleteContentTag("//controllers/controller[@side='sls' and @name='SLS_Bo']/controllerLangs/controllerLang[@lang='en']");
					$controllersXML->saveXML($this->_generic->getPathConfig("configSecure")."controllers.xml",$controllersXML->getXML());
					$controllersXML->refresh();
					
					// Upate Others XML
					$configs = scandir($this->_generic->getPathConfig("configSecure"));
					foreach($configs as $config)
					{
						if (!SLS_String::startsWith($config,".") && $config != "controllers.xml" && $config != "paths.xml")
							@unlink($this->_generic->getPathConfig("configSecure").$config);
					}
					@copy($this->_generic->getPathConfig('installDeployement')."Configs/Site/cache.xml", $this->_generic->getPathConfig("configSecure")."cache.xml");
					@copy($this->_generic->getPathConfig('installDeployement')."Configs/Site/db.xml", $this->_generic->getPathConfig("configSecure")."db.xml");
					@copy($this->_generic->getPathConfig('installDeployement')."Configs/Site/mail.xml", $this->_generic->getPathConfig("configSecure")."mail.xml");
					@copy($this->_generic->getPathConfig('installDeployement')."Configs/Site/site.xml", $this->_generic->getPathConfig("configSecure")."site.xml");
					@copy($this->_generic->getPathConfig('installDeployement')."Configs/Site/project.xml", $this->_generic->getPathConfig("configSecure")."project.xml");
					
					// Reset the SLS Config Files					
					$xmls = $this->_generic->recursiveReadDir($this->_generic->getPathConfig('installDeployement')."Configs/Sls/", array(), array("xml","json"));
					foreach ($xmls as $file)
					{
						$fileName = SLS_String::substrAfterLastDelimiter($file, "/");
						@unlink($this->_generic->getPathConfig("configSls").$fileName);
						@copy($file, $this->_generic->getPathConfig("configSls").$fileName);
					}
					
					// Reset Plugins Config
					$xmls = $this->_generic->recursiveReadDir($this->_generic->getPathConfig('installDeployement')."Configs/Plugins/", array(), array("xml"));
					foreach ($xmls as $file)
					{
						$fileName = SLS_String::substrAfterLastDelimiter($file, "/");
						@copy($file, $this->_generic->getPathConfig("configPlugins").$fileName);
					}
					
					// Reset session
					$this->_generic->getObjectSession()->destroy();
					 
					header("Location: /");
					exit; 
				}
			}
		}
		$xml->addFullTag('stepPassword', ($stepPassword) ? 'yes' : 'no', true);
		$this->saveXML($xml);
	}
	
}
?>