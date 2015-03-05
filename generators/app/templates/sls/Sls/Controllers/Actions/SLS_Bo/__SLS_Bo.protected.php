<?php
/**
 * Protected functions for SLS_Bo Controller
 *
 */
class SLS_BoControllerProtected extends SiteProtected 
{
	public function init()
    {
        parent::init(); 
        $this->sql = SLS_Sql::getInstance();
		$this->tableColumns = array();
		$this->tableAlias = array(); 
		$this->defaultDb = $this->_generic->getDbXML()->getTag("//dbs/db[@isDefault='true']/@alias");    
    }
	
	/**
	 * Generate the main Menu
	 *
	 * @param XmlToolBox $xml
	 * @return XmlToolBox
	 */
	protected function makeMenu($xml)
	{
		$slsXml = $this->_generic->getCoreXML('sls');
		
		$xml->startTag('Actions');
			$xml->startTag("Action");
				$xml->addFullTag("name", "Dashboard <span class='version'>(sls v".$slsXml->getTag("//sls_configs/version").")</span>", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Home'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Home')) ? "true" : "false", true);
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Controllers & Actions", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Controllers'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Controllers')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Controller", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddController'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddController')) ? "true" : "false", true);
				$xml->endTag('sub');				
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Static Controller", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddStaticController'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddStaticController')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Component Controller", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddComponentController'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddComponentController')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Models", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Models'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Models')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Models Properties", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ModelsProperties'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ModelsProperties')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Generate Models", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'GenerateModels'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'GenerateModels')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Views", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Templates'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Templates')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Template", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddTemplate'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddTemplate')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Generic", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddGeneric'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddGeneric')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Back-offices", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Bo'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Bo')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Generate Back-office", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'GenerateBo'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'GenerateBo')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Back-office menu", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'BoMenu'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'BoMenu')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Back-office rights", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ManageRights'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ManageRights')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Back-office reporting", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ReportingBo'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ReportingBo')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Plugins", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Plugins'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Plugins')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Plugin", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'SearchPlugin'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'SearchPlugin')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");			
			$xml->startTag("Action");
				$xml->addFullTag("name", "SillySmart Settings", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Settings'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Settings')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Global<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'GlobalSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'GlobalSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Databases<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'DataBaseSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'DataBaseSettings')) ? "true" : "false", true);
				$xml->endTag('sub');				
				$xml->startTag('sub');
					$xml->addFullTag("name", "SMTP Email<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'MailSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'MailSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Personnal<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ProjectSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ProjectSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "JS / Ajax<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'JSSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'JSSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Google<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'GoogleSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'GoogleSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Production Tools", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'SlsSettings'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'SlsSettings')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Production<br /> Settings", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ProdSettings'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ProdSettings')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Compressor /<br /> Uncompressor", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Compressor'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Compressor')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Configs<br /> Environments", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Environments'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Environments')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Configs<br /> Deployment", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ProductionDeployment'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ProductionDeployment')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Multilanguage", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Langs'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Langs')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Add Lang", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'AddLang'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'AddLang')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Logs", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'LogsMenu'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'LogsMenu')) ? "true" : "false", true);
				$xml->startTag('sub');
					$xml->addFullTag("name", "Monitoring Logs", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'LogsM'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'LogsM') || $this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'LogsMonitoring')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Production Logs", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Logs'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Logs') || $this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'LogsApp')) ? "true" : "false", true);
				$xml->endTag('sub');
				$xml->startTag('sub');
					$xml->addFullTag("name", "Mail Logs", true);
					$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'LogsMail'), true);
					$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'LogsMail')) ? "true" : "false", true);
				$xml->endTag('sub');
			$xml->endTag("Action");
			$xml->startTag("Action");
				$xml->addFullTag("name", "Sitemap", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'GenerateSiteMap'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'GenerateSiteMap')) ? "true" : "false", true);
			$xml->endTag("Action");
			/*$xml->startTag("Action");
				$xml->addFullTag("name", "Update SillySmart", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'Updates'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'Updates')) ? "true" : "false", true);
			$xml->endTag("Action");*/			
			$xml->startTag("Action");
				$xml->addFullTag("name", "Reset SillySmart", true);
				$xml->addFullTag("link", $this->_generic->getFullPath('SLS_Bo', 'ResetSLS'), true);
				$xml->addFullTag("selected", ($this->_generic->getActionId() == $this->_generic->getActionId('SLS_Bo', 'ResetSLS')) ? "true" : "false", true);
			$xml->endTag("Action");
		$xml->endTag('Actions');
		return $xml;
	}
	
	/**
	 * Check if the request is done by a authentificated people
	 *
	 * @return array or false
	 */
	protected function hasAuthorative()
	{
		$session = $this->_generic->getObjectSession();
		$controllers = $this->_generic->getControllersXML();
		$arrayUser = array();	
		$token = $this->_http->getParam('token');
		
		$sessionToken = substr(substr(sha1($this->_generic->getSiteConfig("privateKey")),12,31).substr(sha1($this->_generic->getSiteConfig("privateKey")),4,11),6);
		
		if ($session->getParam('SLS_SESSION_VALID_'.$sessionToken) == 'true' || ($_SERVER["REMOTE_ADDR"] == $_SERVER["SERVER_ADDR"] && $token == sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))))
		{
			$arrayUser['user'] = $session->getParam('SLS_SESSION_USER_'.$sessionToken);
			$arrayUser['pass'] = $session->getParam('SLS_SESSION_PASS_'.$sessionToken);
			$arrayUser['level'] = $session->getParam('SLS_SESSION_LEVEL_'.$sessionToken);
			return $arrayUser;
		}
		else 
		{
			$params = $this->_http->getParams();
			$more = "";
			foreach($params as $key => $value)
			{
				if (!in_array($key,array("mode","smode")))
					$more .= $key."|".$value."|";
			}
			if (SLS_String::endsWith(trim($more),"|"))
				$more = SLS_String::substrBeforeLastDelimiter(trim($more),"|");
			
			$this->_generic->redirect(array_shift($controllers->getTags("//controllers/controller[@name='SLS_Bo']/controllerLangs/controllerLang"))."/".array_shift($controllers->getTags("//controllers/controller[@name='SLS_Bo']/scontrollers/scontroller[@name='Index']/scontrollerLangs/scontrollerLang"))."/Redirect/".$this->_generic->getActionId()."/RedirectMore/".$more);			
		}
	}
	
	/**
	 * Logout current admin
	 * 
	 */
	protected function logout()
	{
		$session = $this->_generic->getObjectSession();
		$controllers = $this->_generic->getControllersXML();		
		$token = $this->_http->getParam('token');
		$redirect = $this->_http->getParam('Redirect');
		$lang = $this->_http->getParam('Lang');
		
		$sessionToken = substr(substr(sha1($this->_generic->getSiteConfig("privateKey")),12,31).substr(sha1($this->_generic->getSiteConfig("privateKey")),4,11),6);
		
		if ($session->getParam('SLS_SESSION_VALID_'.$sessionToken) == 'true' || ($_SERVER["REMOTE_ADDR"] == $_SERVER["SERVER_ADDR"] && $token == sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))))
		{
			$session->delParam('SLS_SESSION_VALID_'.$sessionToken);
			$session->delParam('SLS_SESSION_USER_'.$sessionToken);
			$session->delParam('SLS_SESSION_PASS_'.$sessionToken);
			$session->delParam('SLS_SESSION_LEVEL_'.$sessionToken);
			$session->delParam("SLS_BO_VALID_".$sessionToken);
	        $session->delParam("SLS_BO_USER_".$sessionToken);
	        $session->delParam("SLS_BO_USER");
	        $session->delParam("ckfinderAuthorized");
		}
		
		if (!empty($redirect) && $this->_generic->actionIdExists($redirect))
		{
			$route = $this->_generic->translateActionId($redirect,$lang);
			if (!empty($route['controller']) && !empty($route['scontroller']))
				$this->_generic->redirect($route['controller']."/".$route['scontroller'].".".$this->_generic->getSiteConfig("defaultExtension"));
		}
		
		$this->_generic->redirect($controllers->getTag("//controllers/controller[@name='SLS_Bo']/controllerLangs/controllerLang")."/".$controllers->getTag("//controllers/controller[@name='SLS_Bo']/scontrollers/scontroller[@name='Index']/scontrollerLangs/scontrollerLang"));
	}
	
	/**
	 * Return all the user models
	 *
	 * @return array list of all models
	 */
	protected function getAllModels()
	{
		// Get all models
		$models = array();
		$handle = opendir($this->_generic->getPathConfig("models"));
		while (false !== ($file = readdir($handle)))					
			if (!is_dir($this->_generic->getPathConfig("models").$file) && substr($file, 0, 1) != ".") 			
			{
				$modelExploded = explode(".",$file);
				array_push($models,strtolower($modelExploded[0]).".".$modelExploded[1]);
			}
		return $models;
	}
	
	/**
	 * If action bo already exist
	 *
	 * @param string $model the model to check
	 * @param string $alias the alias of the db
	 * @param string $action the bo action
	 * @return bool true if already exist, else false
	 */
	protected function boActionExist($model,$alias,$action="List")
	{		
		$controllersXML = $this->_generic->getControllersXML();
		$test = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='".ucfirst(strtolower($action)).ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));		
		return (empty($test)) ? false : true;
	}
	
	/**
	 * Get all bo actions for one model
	 *
	 * @param string $model the model to check
	 * @param string $alias the alias of the db
	 * @return array $actions the list of bo actions
	 */
	protected function getActionsBo($model,$alias)
	{
		$actions = array();
		$controllersXML = $this->_generic->getControllersXML();
		
		$list = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='List".ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));
		if (!empty($list))
			array_push($actions,"List".ucfirst($alias)."_".SLS_String::tableToClass($model));		
		$add = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='Add".ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));
		if (!empty($add))
			array_push($actions,"Add".ucfirst($alias)."_".SLS_String::tableToClass($model));
		$edit = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='Modify".ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));
		if (!empty($edit))
			array_push($actions,"Modify".ucfirst($alias)."_".SLS_String::tableToClass($model));
		$del = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='Delete".ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));
		if (!empty($del))
			array_push($actions,"Delete".ucfirst($alias)."_".SLS_String::tableToClass($model));
		$clone = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/scontrollers/scontroller[@name='Clone".ucfirst($alias)."_".SLS_String::tableToClass($model)."']"));
		if (!empty($clone))
			array_push($actions,"Clone".ucfirst($alias)."_".SLS_String::tableToClass($model));
		return $actions;
	}
	
	/**
	 * Delete a action bo
	 *
	 * @param string $model the model described the bo
	 * @param string $actionType the action you want to delete ('list'|'add'|'modify'|'delete'|'clone'|'email')
	 * @param string $alias the alias of the db
	 * @return bool true if ok, else false
	 */
	protected function deleteActionBo($model,$actionType="list",$alias)
	{		
		$controllersXML = $this->_generic->getControllersXML();
		$controller = array_shift($controllersXML->getTags("//controllers/controller[@isBo='true']/@name"));
		$actionTypes = array("list","add","modify","delete","clone");
		if (!in_array($actionType,$actionTypes) || empty($controller))
			return false;
		$params = array(0 => array("key" 	=> "reload",
				  				   "value" 	=> "true"),
				  		1 => array("key" 	=> "Controller",
				  				   "value" 	=> $controller),
					 	2 => array("key" 	=> "Action",
				  				   "value" 	=> ucfirst($actionType).ucfirst($alias)."_".ucfirst($model)),
				  		3 => array("key"	=> "token",
				  				   "value"	=> sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))
				  			)
					    );		
		file_get_contents($this->_generic->getFullPath("SLS_Bo",
													  "DeleteAction",
													  $params,
													  true));
		return true;
	}
	
	/**
	 * Just copy all actions langs files from given controllers
	 *
	 * @param array $controllers a list of all controllers in which we want to copy actions
	 * @param string $lang the iso code of the new lang
	 */
	protected function copyActionsLang($controllers=array(),$iso)
	{
		if (!empty($controllers) && !empty($iso))
		{
			$actions = array();
			
			foreach($controllers as $controller)
			{
				$handle = opendir($this->_generic->getPathConfig("actionLangs").$controller);
				while (false !== ($file = readdir($handle)))
					if (!is_dir($this->_generic->getPathConfig("actionLangs").$controller."/".$file) && substr($file, 0, 1) != ".")
						if (!in_array(SLS_String::substrBeforeFirstDelimiter($file,"."),$actions))
							array_push($actions,SLS_String::substrBeforeFirstDelimiter($file,"."));
				
				foreach($actions as $action)				
					file_put_contents($this->_generic->getPathConfig("actionLangs").$controller."/".$action.".".$iso.".lang.php",file_get_contents($this->_generic->getPathConfig("actionLangs").$controller."/".$action.".".$this->_generic->getSiteConfig("defaultLang").".lang.php"));
				$actions = array();
			}
		}
	}
	
	/**
	 * Delete all actions lang files from given lang
	 *
	 * @param string $root the path to list
	 * @param string $iso the lang
	 */
	protected function deleteActionsLang($root,$iso)
	{
		$handle = opendir($root);
		while (false !== ($file = readdir($handle)))
		{
			// Recursive call			
			if (is_dir($root."/".$file)  && substr($file, 0, 1) != ".")				
				$this->deleteActionsLang($root."/".$file,$iso);
			// Check if file in correct lang, if yes, delete it !
			if (!is_dir($root."/".$file) && substr($file, 0, 1) != ".")				
				if (SLS_String::endsWith($file,".".$iso.".lang.php"))
					@unlink($root."/".$file);
		}
	}
	
	/**
	 * Get all the application templates
	 *
	 * @return array
	 */
	protected function getAppTpls()
	{
		$tpls = array();		
	
		$handle = opendir($this->_generic->getPathConfig("viewsTemplates"));
		while($file = readdir($handle))
		{
			if (is_file($this->_generic->getPathConfig("viewsTemplates").$file) && substr($file, 0, 1) != ".")
			{
				$fileName 	= SLS_String::substrBeforeLastDelimiter($file,".");
				$extension 	= SLS_String::substrAfterLastDelimiter($file,".");
				
				if ($extension == "xsl")				
					array_push($tpls,$fileName);				
			}
		}
		closedir($handle);
		
		return $tpls;
	}
	
	/**
	 * Get all the application xsl
	 *
	 * @return array
	 */
	protected function getAppXsl($path="",$generics = array())
	{
		$paths = (empty($path)) ? array($this->_generic->getPathConfig("views"),$this->_generic->getPathConfig("coreViews")) : array($path);
		
		foreach($paths as $path)
		{
			$handle = opendir($path);
			while($file = readdir($handle))
			{
				if (is_file($path.$file) && substr($file, 0, 1) != ".")
				{
					$fileName 	= SLS_String::substrBeforeLastDelimiter($file,".");
					$extension 	= SLS_String::substrAfterLastDelimiter($file,".");
					
					if ($extension == "xsl")
					{
						if (!in_array(strtolower($fileName),$generics))
							array_push($generics,strtolower($fileName));
						
						$content = file_get_contents($path.$file);
						preg_match_all('/<xsl:template name="(.*)">/', $content, $matches);
						if (is_array($matches) && count($matches) > 1)
							foreach($matches[1] as $match)
								if (!in_array(strtolower($match),$generics))
									array_push($generics,strtolower($match));
					}				
				}
				else if (is_dir($path.$file) && substr($file, 0, 1) != ".")
					$generics = $this->getAppXsl($path.$file."/",$generics);
			}
			closedir($handle);
		}
		
		return $generics;
	}
	
	/**
	 * Get the source code of a model from his table
	 * 
	 * @param string $tableName the table name 
	 * @param string $db the alias of the database on which the model is located
	 * @return string $contentM the source code
	 */
	protected function getModelSource($tableName,$db)
	{
		$arrayConvertTypes = array(
			'varchar'	=>	'string',
			'tinyint'	=>	'int',
			'text'		=>	'string',
			'date'		=>	'string',
			'smallint'	=>	'int',
			'mediumint'	=>	'int',
			'int'		=>	'int',
			'bigint'	=>	'int',
			'float'		=>	'float | int',
			'double'	=>	'float | int',
			'decimal'	=>	'float',
			'datetime'	=>	'string',
			'timestamp'	=>	'int',
			'time'		=>	'string | int',
			'year'		=>	'int',
			'char'		=>	'string',
			'tinyblob'	=>	'string',
			'tinytext'	=>	'string',
			'blob'		=>	'string',
			'mediumblob'=>	'string',
			'mediumtext'=>	'string',
			'longblob'	=>	'string',
			'longtext'	=>	'string',
			'enum'		=>	'string',
			'set'		=>	'string',
			'bool'		=>	'int',
			'binary'	=>	'string',
			'varbinary'	=>	'string'
		);
		$sql 		= SLS_Sql::getInstance();
		$className 	= ucfirst($db)."_".SLS_String::tableToClass($tableName);
		$file 		= ucfirst($db).".".SLS_String::tableToClass($tableName);
		
		// Create empty source
		$contentM = "";
		$primaryKey = "";
		$multiLanguage = 'false';
		$sql->changeDb($db);
		$columns = $sql->showColumns($tableName);
		$fileName = ucfirst($db).".".SLS_String::tableToClass($tableName).".model.php";
		$primaryKey = "";
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
		$xmlType = new SLS_XMLToolbox($pathsHandle);
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
		$xmlFk = new SLS_XMLToolbox($pathsHandle);
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
		$xmlFilter = new SLS_XMLToolbox($pathsHandle);
		$fkMethods = array();
		$uniquesMultilang = array();
				
		// Create Model		
		$contentM = '<?php'."\n".
				   '/**'."\n".
				   ' * Object '.$className.''."\n".
				   ' * @author SillySmart'."\n".
				   ' * @copyright SillySmart'."\n".
				   ' * @package Mvc.Models.Objects'."\n".
				   ' * @see Sls.Models.Core.SLS_FrontModel'."\n".
				   ' * @since 1.0'."\n".
				   ' */'."\n".
				   'class '.$className.' extends SLS_FrontModel'."\n".
				   '{'."\n".
					   t(1).'/**'."\n".
				       t(1).' * Class variables'."\n".
					   t(1).' */'."\n";				   
		$pkFound = false;
		for($i=0 ; $i<$count=count($columns) ; $i++)
		{
			if (!$pkFound && $columns[$i]->Key == "PRI")
			{
				$primaryKey = SLS_String::removePhpChars($columns[$i]->Field);
				$pkFound = true;
			}
			if ($columns[$i]->Field == "pk_lang" && $columns[$i]->Key == "PRI")
				$multiLanguage = 'true';
			$contentM .= t(1).'protected $__'.SLS_String::removePhpChars($columns[$i]->Field).';'."\n";
		}
		
		$contentM .= t(1).'protected $_table = "'.$tableName.'";'."\n".
					 t(1).'protected $_db = "'.$db.'";'."\n".
					 t(1).'protected $_primaryKey = "'.$primaryKey.'";'."\n";
		
		// Show create table
		if ($multiLanguage == 'true')
		{
			$create = array_shift($sql->select("SHOW CREATE TABLE `".$tableName."`"));
			$instructions = array_map("trim",explode("\n",$create->{Create." ".Table}));						
			foreach($instructions as $instruction)
			{
				if (SLS_String::startsWith($instruction,"UNIQUE KEY"))
				{
					$uniqueColumns = explode(",",SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($instruction,"("),")"));
					if (count($uniqueColumns) == 2 && in_array("`pk_lang`",$uniqueColumns))
					{
						$uniqueColumn = array_shift($uniqueColumns);
						if ($uniqueColumn == "`pk_lang`")
							$uniqueColumn = array_shift($uniqueColumns);
							
						$uniquesMultilang[] = str_replace("`","",$uniqueColumn);
					}
				}
			}
		}
					 
		// Get FKs to create access reference functions
		$fks = $xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$db."_".$tableName."']",array("columnFk","tablePk"));
		$fkFunctions = "";
		$fkCursor = "";
		for($i=0 ; $i<$count=count($fks) ; $i++)
		{
			$tablePk = $fks[$i]["attributes"][1]["value"];
			$functionName = SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($tablePk,"_"))," ",false)),"");
			$functionName{0} = strtolower($functionName{0});
			$functionNameModified = $functionName;
			while(in_array($functionNameModified,$fkMethods))							
				$functionNameModified = $functionName."_".($fkCursor = (empty($fkCursor)) ? 2 : $fkCursor+1);			
			$fkMethods[] = $functionNameModified;
			$fkFunctions .= "'".$functionNameModified."'";
			if ($i < ($count - 1))
				$fkFunctions .= ", ";
		}
				
		$contentM .= t(1).'protected $_fks = array('.$fkFunctions.');'."\n";
		
		$contentM .= t(1).'public $_typeErrors = array();'."\n\n".
					 t(1).'/**'."\n".
				     t(1).' * Constructor '.$className.''."\n".
				     t(1).' * @author SillySmart'."\n".
				     t(1).' * @copyright SillySmart'."\n".
				     t(1).' * @param bool $mutlilanguage true if multilanguage content, else false'."\n".
				     t(1).' */'."\n".
					 t(1).'public function __construct($multilanguage='.$multiLanguage.')'."\n".
					 t(1).'{'."\n".
						 t(2).'parent::__construct($multilanguage);'."\n".
						 t(2).'$this->buildDefaultValues();'."\n".
					 t(1).'}'."\n\n";
		
		$contentM .= t(1).'/**'."\n".
				     t(1).' * Build default values for specific columns'."\n".
				     t(1).' * @author SillySmart'."\n".
				     t(1).' * @copyright SillySmart'."\n".
				     t(1).' */'."\n".
					 t(1).'public function buildDefaultValues()'."\n".
					 t(1).'{'."\n";
		
		for($i=0 ; $i<$count=count($columns) ; $i++)
		{
			$columnType = (strpos($columns[$i]->Type, "(")) ? SLS_String::substrBeforeFirstDelimiter(strtolower($columns[$i]->Type), "(") : $columns[$i]->Type;
			$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",SLS_String::removePhpChars($columns[$i]->Field))," ",false)),"");
			
			if ($columns[$i]->Null == "NO")
			{			
				// Dates
				if ($columnType == "date" || $columnType == "datetime" || $columnType == "timestamp" || $columnType == "year" || $columnType == "time")
				{
					switch ($columnType)
					{
						case "date":
							$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = date("Y-m-d");'."\n";
							break;
						case "time":
							$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = date("H:i:s");'."\n";
							break;
						case "datetime":
							$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = date("Y-m-d H:i:s");'."\n";
							break;
						case "timestamp":
							$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = date("Y-m-d H:i:s");'."\n";
							break;
						case "year":
							$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = date("Y");'."\n";
							break;
					}
				}
				
				// Uniqid
				$result = $xmlType->getTags("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".SLS_String::removePhpChars($columns[$i]->Field)."' and @type='uniqid']");
				if (!empty($result))
				{
					$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = substr(md5(time().substr(sha1(microtime()),0,rand(12,25))),mt_rand(1,20),40);'."\n";
				}
				
				// IP Address
				$result = array_shift($xmlType->getTags("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".SLS_String::removePhpChars($columns[$i]->Field)."' and (@type='ip_both' or @type='ip_v4' or @type='ip_v6')]/@type"));
				if (!empty($result))
				{
					$contentM .= t(2).'$this->__'.$columns[$i]->Field.' = $_SERVER["REMOTE_ADDR"];'."\n";
				}
			}
		}
		$contentM .= t(1).'}'."\n\n";
		
		for($i=0 ; $i<$count=count($columns) ; $i++)
		{
			$isPassword = false;
			
			if ($columns[$i]->Key != "PRI")
			{
				$column = SLS_String::removePhpChars($columns[$i]->Field);
				$columnType = (strpos($columns[$i]->Type, "(")) ? SLS_String::substrBeforeFirstDelimiter(strtolower($columns[$i]->Type), "(") : $columns[$i]->Type;
				$functionName = "set".SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(str_replace("_"," ",$column)," ",false)),"");
				
				$contentM .= t(1).'/**'."\n".
						     t(1).' * Set the value of '.$column.''."\n".
						     t(1).' * Errors can be catched with the public variable $this->_typeErrors[\''.$column.'\']'."\n".
						     t(1).' * @author SillySmart'."\n".
						     t(1).' * @copyright SillySmart'."\n".
						     t(1).' * @param '.$arrayConvertTypes[$columnType].' $value'."\n".
						     t(1).' * @return bool true if updated, false if not'."\n".
						     t(1).' */'."\n".
							 t(1).'public function '.$functionName.'($value';
				
				if ($columns[$i]->Default !== null)
					$contentM .= '="'.SLS_String::addSlashesToString($columns[$i]->Default,false).'"';
					
				$contentM .= ')'."\n";
				$contentM .= t(1).'{'."\n";
				
				// Nullable case
				if ($columns[$i]->Null == "YES")
				{
					$contentM .= t(2).'if (empty($value))'."\n".
								 t(2).'{'."\n".
									 t(3).'$this->__set(\''.$column.'\', $value);'."\n".
									 t(3).'$this->flushError(\''.$column.'\');'."\n".
									 t(3).'return true;'."\n".
								 t(2).'}'."\n\n";
				}
				
				// Recover Fk
				$res = $xmlFk->getTagsByAttributes("//sls_configs/entry",array("tableFk","columnFk"),array($db."_".$tableName,$column));
				if (!empty($res))
				{
					$tableTm = substr($res,(strpos($res,'tablePk="')+9),(strpos($res,'"/>')-(strpos($res,'tablePk="')+9)));							
					$tablePk = SLS_String::substrAfterFirstDelimiter($tableTm,"_");
					$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tableTm,"_");
					$contentM .= 	 t(2).'$this->_generic->useModel("'.SLS_String::tableToClass($tablePk).'","'.$dbPk.'","user");'."\n".
									 t(2).'$object = new '.ucfirst($dbPk).'_'.SLS_String::tableToClass($tablePk).'();'."\n".
									 t(2).'if ($object->getModel($value) === false)'."\n".										 
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_KEY";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n".										 
									 t(2).'$this->__set(\''.$column.'\', $value);'."\n".
									 t(2).'$this->flushError(\''.$column.'\');'."\n".
									 t(2).'return true;'."\n".
								 t(1).'}'."\n\n";
				}
				
				// If not a fk
				else
				{
					// Check filters
					$results = $xmlFilter->getTags("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."']/@filter");														
					for($j=0 ; $j<$countJ=count($results) ; $j++)
					{									
						switch($results[$j])
						{
							case "hash":
								$isPassword = true;
								$contentM .= t(2).'if (empty($value))'."\n".
										 	 	t(3).'$value = $this->__'.$column.';'."\n";
								break;
							default:
								$contentM .= t(2).'$value = SLS_String::filter($value,"'.$results[$j].'");'."\n";
								break;
						}
					}
					if (count($results) > 0)
						$contentM .= "\n";
						
					$result = $xmlType->getTags("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."']");
					
					// Force specific type numeric
					if ($this->containsRecursive($columnType,array("int","float","double","decimal","real")))
					{
						$typeExists =($xmlType->getTag("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."']/@type"));
						if (empty($typeExists))
						{
							file_get_contents($this->_generic->getFullPath("SLS_Bo",
																		  "AddType",
																		  array("name" => strtolower($db."_".$tableName),
																		  		"column" => $column,
																		  		"reload" => "true",
																		  		"type" => "num",
																		  		"num" => (SLS_String::contains($columns[$i]->Type,"unsigned")) ? "gte" : "all",
																		  		"token"	 => sha1(substr($this->_generic->getSiteConfig("privateKey"), 0, 3).substr($this->_generic->getSiteConfig("privateKey"), strlen($this->_generic->getSiteConfig("privateKey"))-3))),
																		  true));
							$xmlType->refresh();
						}
					}
					if (!empty($result))
					{
						$type = "";
						$array = array('color','uniqid','email','ip_both','ip_v4','ip_v6','url','file_all','file_img','position','num_all','num_gt','num_gte','num_lt','num_lte','complexity');
						for($j=0 ; $j<count($array) ; $j++)
						{
							$result = $xmlType->getTags("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."' and @type='".$array[$j]."']");
							if (!empty($result))
							{
								$type = $array[$j];
								switch($type)
								{
									case "position":
										$contentM .= t(2).'if ($value == "" || $value == null || !is_int(intval($value)) || intval($value) < 0)'."\n".
											         t(2).'{'."\n".
												         t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
												         t(3).'return false;'."\n".
											         t(2).'}'."\n\n".
													 t(2).'$qbd = new SLS_QueryBuilder();'."\n".
													 t(2).'$old_'.$column.' = $this->__'.$column.';'."\n".
													 t(2).'if (empty($old_'.$column.'))'."\n".
											         t(2).'{'."\n".
											         	t(3).'$qbd->update()'."\n".
													         t(4).'->from("'.$tableName.'")'."\n".
													         t(4).'->set("`'.$column.'` = `'.$column.'` + 1")'."\n".
													         t(4).'->where($qbd->expr()->gte("'.$column.'",$value))'."\n".
															 t(4).'->groupBy("`'.$primaryKey.'`")'."\n".
													         t(4).'->execute();'."\n".
											         t(2).'}'."\n".
											         t(2).'else'."\n".
											         t(2).'{'."\n".
												    	 t(3).'if ($old_'.$column.' != $value)'."\n".
												    	 t(3).'{'."\n".
											    		 	t(4).'$qbd->update()'."\n".
													    		 t(5).'->from("'.$tableName.'")'."\n".
													    		 t(5).'->set("`'.$column.'` = `'.$column.'` ".(($old_'.$column.' < $value) ? "-" : "+")." 1")'."\n".
													    		 t(5).'->where($qbd->expr()->{($old_'.$column.' < $value) ? "gt" : "gte"}("'.$column.'",($old_'.$column.' < $value) ? $old_'.$column.' : $value))'."\n".
													    		 t(5).'->whereAnd($qbd->expr()->{($old_'.$column.' < $value) ? "lte" : "lt"}("'.$column.'",($old_'.$column.' < $value) ? $value : $old_'.$column.'))'."\n".
																 t(5).'->groupBy("`'.$primaryKey.'`")'."\n".
													    		 t(5).'->execute();'."\n".
														 	t(4).'$qbd->update()'."\n".
													    		 t(5).'->from("'.$tableName.'")'."\n".
													    		 t(5).'->set($qbd->expr()->eq("'.$column.'",$value))'."\n".
													    		 t(5).'->where($qbd->expr()->eq("'.$primaryKey.'",$this->__'.$primaryKey.'))'."\n".
																 t(5).'->groupBy("`'.$primaryKey.'`")'."\n".
													    		 t(5).'->execute();'."\n".
											    		 t(3).'}'."\n".													 
											    	 t(2).'}'."\n\n";
										break;
									case "color":
										$contentM .= t(2).'if (!ctype_xdigit($value))'."\n";
										$contentM .= t(2).'{'."\n".
														 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
														 t(3).'return false;'."\n".
													 t(2).'}'."\n\n";
										break;
									case "uniqid":										
										$contentM .= t(2).'if (empty($value))'."\n".
													 	t(3).'$value = substr(md5(time().substr(sha1(microtime()),0,rand(12,25))),mt_rand(1,20),40);'."\n\n";
										break;
									case "email":
										$contentM .= t(2).'if (!SLS_String::validateEmail($value))'."\n";
										$contentM .= t(2).'{'."\n".
												 		 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
												 		 t(3).'return false;'."\n".
											 		 t(2).'}'."\n\n";
										break;
									case "url":
										$contentM .= t(2).'if (!SLS_String::isValidUrl($value))'."\n";
										$contentM .= t(2).'{'."\n".
												 		 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
												 		 t(3).'return false;'."\n".
											 		 t(2).'}'."\n\n";
										break;
									case (in_array($type,array("ip_both","ip_v4","ip_v6"))):
										$type = SLS_String::substrAfterLastDelimiter($type,"_");
										
										$contentM .= t(2).'if (empty($value))'."\n".
													 	t(3).'$value = $_SERVER["REMOTE_ADDR"];'."\n\n";
										$contentM .= t(2).'if (!SLS_String::isIp($value,"'.$type.'"))'."\n";
										$contentM .= t(2).'{'."\n".
														 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
														 t(3).'return false;'."\n".
													 t(2).'}'."\n\n";												
										break;
									case (in_array($type,array("num_all","num_gt","num_gte","num_lt","num_lte"))):
										$type = SLS_String::substrAfterLastDelimiter($type,"_");										
										switch($type)
										{
											case "gt": 	$operator = "<="; 	break;
											case "gte": $operator = "<"; 	break;
											case "lt": 	$operator = ">="; 	break;
											case "lte": $operator = ">"; 	break;
										}
										if ($type != 'all')
										{
											$contentM .= t(2).'if ($value '.$operator.' 0)'."\n";
											$contentM .= t(2).'{'."\n".
													 		 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
													 		 t(3).'return false;'."\n".
												 		 t(2).'}'."\n\n";
										}
										break;
									case "complexity":
										$rules = $xmlType->getTag("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."']/@rules");										
										$rules = explode("|",$rules);
										$complexityMin = false; 
										foreach($rules as $rule)
										{
											if (SLS_String::startsWith($rule,"min"))
											{
												$complexityMin = SLS_String::substrAfterFirstDelimiter($rule,"min");
												unset($rules[array_shift(array_keys($rules,$rule))]);
											}
											else
												$rules[array_shift(array_keys($rules,$rule))] = '"'.$rule.'"';
										}
										$rules = implode(",",$rules);
										$contentM .= t(2).'$complexity = array('.$rules.');'."\n".
													 t(2).'if ((in_array("lc",$complexity) && preg_match(\'`[[:lower:]]`\', $value) === 0) || (in_array("uc",$complexity) && preg_match(\'`[[:upper:]]`\', $value) === 0) || (in_array("digit",$complexity) && preg_match(\'`[[:digit:]]`\', $value) === 0) || (in_array("wild",$complexity) && preg_match(\'`[^a-zA-Z0-9]`\', $value) === 0))'."\n".
													 t(2).'{'."\n".
														t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_COMPLEXITY";'."\n".
														t(3).'return false;'."\n".
													 t(2).'}'."\n\n";
										if (is_numeric($complexityMin))
											$contentM .= t(2).'if (strlen(utf8_decode($value)) < '.$complexityMin.')'."\n".
														 t(2).'{'."\n".
															t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_LENGTH";'."\n".
															t(3).'return false;'."\n".
														 t(2).'}'."\n\n";
										break;
									case "file_all":
										$contentM .= t(2).'if (!empty($this->__'.$column.') && $this->__'.$column.' != "'.$columns[$i]->Default.'" && $this->__'.$column.' != $value && SLS_String::startsWith((is_array($value)) ? ((array_key_exists("data",$value)) ? $value["data"]["tmp_name"] : $value["tmp_name"]) : $value, $this->getTable()."/"))'."\n".
													 t(2).'{'."\n".
														 t(3).'$this->deleteFiles(array("'.$column.'"));'."\n".
														 t(3).'$this->__'.$column.' = "__Uploads/__Deprecated/".$this->__'.$column.';'."\n".
														 t(3).'$this->save();'."\n".
													 t(2).'}'."\n\n".
													 t(2).'if (is_array($value))'."\n".
													 t(2).'{'."\n".
													 	t(3).'if (array_key_exists("size",$value) && is_array($value["size"]))'."\n".
															t(4).'$size = $value["size"];'."\n".
														t(3).'if (array_key_exists("data",$value))'."\n".
															t(4).'$value = $value["data"];'."\n";
										if ($columns[$i]->Null == "YES") 
											$contentM .= t(3).'if ($value["error"] == 4)'."\n".
														 t(3).'{'."\n".
															 t(4).'$this->__set(\''.$column.'\',(empty($this->__'.$column.')) ? "" : $this->__'.$column.');'."\n".
															 t(4).'$this->flushError(\''.$column.'\');'."\n".
															 t(4).'return true;'."\n".
														 t(3).'}'."\n";
										$contentM .= t(3).'if ($value["error"] == 1 || $value["error"] == 2)'."\n".
													 t(3).'{'."\n".
														 t(4).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_SIZE";'."\n".
														 t(4).'return false;'."\n".
													 t(3).'}'."\n".
													 t(3).'else'."\n".
													 t(3).'{'."\n".
														 t(4).'try {'."\n".
															 t(5).'if (!file_exists($this->_generic->getPathConfig("files").$this->_table))'."\n".
														 	 	t(6).'mkdir($this->_generic->getPathConfig("files").$this->_table,0755);'."\n\n".
															 t(5).'$token = substr(md5(time().substr(sha1(microtime()),0,rand(5,12))),mt_rand(1,20),10);'."\n".
															 t(5).'$fileName = SLS_String::sanitize(SLS_String::substrBeforeLastDelimiter($value["name"],"."),"_")."_".$token.".".SLS_String::substrAfterLastDelimiter($value["name"],".");'."\n".
															 t(5).'rename($value["tmp_name"],$this->_generic->getPathConfig("files").$this->_table."/".$fileName);'."\n".
															 t(5).'$value = $this->_table."/".$fileName;'."\n".
														 t(4).'}'."\n".
														 t(4).'catch (Exception $e) {'."\n".
															 t(5).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_WRITE";'."\n".
															 t(5).'return false;'."\n".
														 t(4).'}'."\n".
													 t(3).'}'."\n".
													 t(2).'}'."\n\n";
										break;
									case "file_img":
										$contentM .= t(2).'if (!empty($this->__'.$column.') && $this->__'.$column.' != "'.$columns[$i]->Default.'" && $this->__'.$column.' != $value && SLS_String::startsWith((is_array($value)) ? ((array_key_exists("data",$value)) ? $value["data"]["tmp_name"] : $value["tmp_name"]) : $value, $this->getTable()."/"))'."\n".
													 t(2).'{'."\n".
														 t(3).'$this->deleteFiles(array("'.$column.'"));'."\n".
														 t(3).'$this->__'.$column.' = "__Uploads/__Deprecated/".$this->__'.$column.';'."\n".
														 t(3).'$this->save();'."\n".
													 t(2).'}'."\n\n".
													 t(2).'if (is_array($value))'."\n".
													 t(2).'{'."\n".
													 	t(3).'if (array_key_exists("size",$value) && is_array($value["size"]))'."\n".
															t(4).'$size = $value["size"];'."\n".
														t(3).'if (array_key_exists("data",$value))'."\n".
															t(4).'$value = $value["data"];'."\n";
										if ($columns[$i]->Null == "YES")
											$contentM .= t(3).'if ($value["error"] == 4)'."\n".
														 t(3).'{'."\n".
															 t(4).'$this->__set(\''.$column.'\',(empty($this->__'.$column.')) ? "" : $this->__'.$column.');'."\n".
															 t(4).'$this->flushError(\''.$column.'\');'."\n".
															 t(4).'return true;'."\n".
														 t(3).'}'."\n";
										$contentM .= t(3).'if ($value["error"] == 1 || $value["error"] == 2)'."\n".
													 t(3).'{'."\n".
														 t(4).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_SIZE";'."\n".
														 t(4).'return false;'."\n".
													 t(3).'}'."\n".
													 t(3).'else'."\n".
													 t(3).'{'."\n".
														 t(4).'try {'."\n".
															 t(5).'if (!file_exists($this->_generic->getPathConfig("files").$this->_table))'."\n".
												 			 	t(6).'mkdir($this->_generic->getPathConfig("files").$this->_table,0755);'."\n\n".
															 t(5).'$tmpName = $value["tmp_name"];'."\n".
															 t(5).'if (!SLS_String::startsWith($tmpName,$this->_generic->getPathConfig("files")) || !SLS_String::contains($tmpName,"."))'."\n".
															 t(5).'{'."\n".
																 t(6).'if (!file_exists($this->_generic->getPathConfig("files")."__Uploads"))'."\n".
																 	t(7).'@mkdir($this->_generic->getPathConfig("files")."__Uploads");'."\n".
																 t(6).'if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated"))'."\n".
																 	t(7).'@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated");'."\n".
																 t(6).'$newName = $this->_generic->getPathConfig("files")."__Uploads/__Deprecated/".SLS_String::substrAfterLastDelimiter($tmpName,"/").((!SLS_String::contains($tmpName,".")) ? ".".SLS_String::substrAfterLastDelimiter($value["name"],".") : "");'."\n".
																 t(6).'rename($tmpName,$newName);'."\n".
																 t(6).'$tmpName = $newName;'."\n".
															 t(5).'}'."\n".
															 t(5).'$token = substr(md5(time().substr(sha1(microtime()),0,rand(5,12))),mt_rand(1,20),10);'."\n".
															 t(5).'$fileName = SLS_String::sanitize(SLS_String::substrBeforeLastDelimiter($value["name"],"."),"_")."_".$token;'."\n".
														 	 t(5).'$extension = pathinfo($tmpName, PATHINFO_EXTENSION);'."\n\n".
															 t(5).'// Check img'."\n".
															 t(5).'$img = new SLS_Image($tmpName);'."\n".
															 t(5).'if ($img->getParam("existed"))'."\n".
															 t(5).'{'."\n".
																 t(6).'// Default crop'."\n".
																 t(6).'if (empty($size))'."\n".
																 	t(7).'$size = array("x" => "0", "y" => "0", "w" => $img->getParam("width"), "h" => $img->getParam("height"));'."\n\n".
																 t(6).'// Crop image'."\n".
																 t(6).'$img->crop($size["x"],$size["y"],$size["w"],$size["h"]);'."\n".											 
																 t(6).'// Check thumbs'."\n".
																 t(6).'$xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));'."\n".
																 t(6).'$result = array_shift($xmlType->getTagsAttribute("//sls_configs/entry[@table=\'".$this->getDatabase()."_".$this->getTable()."\' and @column=\''.$column.'\' and @type=\'file_img\']","thumbs"));'."\n".
																 t(6).'$thumbs = unserialize(str_replace("||#||",\'"\',$result["attribute"]));'."\n".
																 t(6).'if (!empty($thumbs))'."\n".
																 t(6).'{'."\n".
																	 t(7).'for($i=0 ; $i<$count=count($thumbs) ; $i++)'."\n".
																	 t(7).'{'."\n".
															 			t(8).'$img->transform($thumbs[$i]["width"],$thumbs[$i]["height"],$this->_generic->getPathConfig("files").$this->_table."/".$fileName.$thumbs[$i]["suffix"].".".$extension,$extension);'."\n".
																	 t(7).'}'."\n".
															 	 t(6).'}'."\n\n".
															 	 t(6).'// Move original'."\n".
															 	 t(6).'rename($tmpName,$this->_generic->getPathConfig("files").$this->_table."/".$fileName.".".$extension);'."\n".
															 t(5).'}'."\n".
															 t(5).'else'."\n".
															 t(5).'{'."\n".
																 t(6).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
																 t(6).'return false;'."\n".
															 t(5).'}'."\n".
															 t(5).'$value = $this->_table."/".$fileName.".".$extension;'."\n".
														 t(4).'}'."\n".
														 t(4).'catch (Exception $e) {'."\n".
															 t(5).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_WRITE";'."\n".
															 t(5).'return false;'."\n".
														 t(4).'}'."\n".
													 t(3).'}'."\n".
												t(2).'}'."\n\n";
										break;
								}
								break;
							}
						}
					}
					 			 
					// Not Nullable
					if ($columns[$i]->Null == "NO")
					{
						$contentM .= t(2).'if ($value === "")'."\n".
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_EMPTY";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n\n";
					}
					
					if ($isPassword)
					{
						$result = array_shift($xmlFilter->getTagsAttribute("//sls_configs/entry[@table='".$db."_".$tableName."' and @column='".$column."']","hash"));
						$hash = (empty($result["attribute"])) ? "sha1" : $result["attribute"];
						$contentM .= t(2).'if ($value != $this->__'.$column.')'."\n".
									 	t(3).'$value = SLS_String::filter($value,"hash","'.$hash.'");'."\n\n";
					}
					
					// Not Nullable
					if ($columns[$i]->Null == "NO")
					{
						$contentM .= t(2).'if (is_null($value))'."\n".
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_NULL";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n\n";
					}
					
					// Check change
					$contentM .= t(2).'if ($this->__'.$column.' == $value)'."\n".
								 t(2).'{'."\n".
									t(3).'$this->flushError(\''.$column.'\');'."\n".								 
					 			 	t(3).'return true;'."\n".
					 			 t(2).'}'."\n\n";
					
					// Unique
					if ($columns[$i]->Key == "UNI" || in_array($column,$uniquesMultilang))
					{
						$contentM .= t(2).'if (!$this->isUnique(\''.SLS_String::addSlashes($column, 'QUOTES').'\',$value))'."\n".
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_UNIQUE";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n\n";
					}
					
					// Float types
					if (SLS_String::startsWith($columnType,"float") || SLS_String::startsWith($columnType,"double") || SLS_String::startsWith($columnType,"decimal"))
					{
						$length = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($columns[$i]->Type, ')'), '('), ",");
						if (empty($length))
							$length = "25";
						$contentM .= t(2).'$decimal = (strpos($value, \',\')) ? str_replace(\',\', \'.\', $value) : (!strpos($value, \'.\')) ? $value.\'.0\' : $value;'."\n".									
									 t(2).'if (!is_numeric($decimal))'."\n".
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n\n".
									 t(2).'if ((strlen($decimal)-1) > '.$length.')'."\n".
									 t(2).'{'."\n".
										 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_LENGTH";'."\n".
										 t(3).'return false;'."\n".
									 t(2).'}'."\n\n";
					}
					
					// Enum types
					else if ($columnType == "enum" || $columnType == "set")
					{						
						$values = SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($columns[$i]->Type, "')"), "('");
						
						if ($columnType == "enum")
						{
							$contentM .= t(2).'$values = explode("\',\'", "'.str_replace("''", "\'", $values).'");'."\n".									
										 t(2).'if (!in_array($value, $values))'."\n".
										 t(2).'{'."\n".
											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_CONTENT";'."\n".
											 t(3).'return false;'."\n".
										 t(2).'}'."\n\n";
						}
						else
						{
							$contentM .= t(2).'$values = explode("\',\'", "'.str_replace("''", "\'", $values).'");'."\n".
										 t(2).'$valueE = explode(",",$value);'."\n".
								         t(2).'foreach($valueE as $set)'."\n".
								         t(2).'{'."\n".
											 t(3).'if (!in_array($set, $values))'."\n".
											 t(3).'{'."\n".
												 t(4).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_CONTENT";'."\n".
												 t(4).'return false;'."\n".
											 t(3).'}'."\n".
										 t(2).'}'."\n\n";
						}									 
					}
					else 
					{						
						if (strpos($columns[$i]->Type, "("))
						{
							$length = SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($columns[$i]->Type, ")"), "(");
							$contentM .= t(2).'if (strlen(utf8_decode($value)) > '.$length.')'."\n".
										 t(2).'{'."\n".
											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_LENGTH";'."\n".
											 t(3).'return false;'."\n".
										 t(2).'}'."\n\n";
						}
						if(SLS_String::endsWith($columnType, "int"))
						{
							$contentM .= t(2).'if (!is_numeric($value))'."\n".
										 t(2).'{'."\n".
											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
											 t(3).'return false;'."\n".
										 t(2).'}'."\n\n";
						}
						else if ($columnType == "date" || $columnType == "datetime" || $columnType == "timestamp")
						{
							switch ($columnType)
							{
								case "date":
									$contentM .= t(2).'if (!SLS_Date::isDate($value))'."\n";
									break;
								case "datetime":
									$contentM .= t(2).'if (!SLS_Date::isDateTime($value))'."\n";
									break;
								case "timestamp":
									$contentM .= t(2).'if (!SLS_Date::isDateTime($value))'."\n";
									break;
							}
							$contentM .= t(2).'{'."\n".
											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
											 t(3).'return false;'."\n".
										 t(2).'}'."\n\n";
						}
						else if ($columnType == "time" || $columnType == "year")
						{
							switch ($columnType)
							{
								case "time":
									$contentM .= t(2).'if (strpos(\':\', $value) && substr_count($value, \':\') != 2)'."\n".
	 											 t(2).'{'."\n".
		 											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
													 t(3).'return false;'."\n".
												 t(2).'}'."\n\n".
												 t(2).'$check = explode(\':\', $value);'."\n".	
												 t(2).'if (count($check) == 1 && !is_numeric($check[0]))'."\n".	
												 t(2).'{'."\n".
		 											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
													 t(3).'return false;'."\n".
												 t(2).'}'."\n".
												 t(2).'else if ((count($check) > 1) && (!is_numeric($check[0]) || (!is_numeric($check[1]) || strlen($check[1]) > 2) || (!is_numeric($check[2]) || strlen($check[2]) > 2)))'."\n".	
												 t(2).'{'."\n".
		 											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
													 t(3).'return false;'."\n".
												 t(2).'}'."\n\n";
									break;
								case "year":
									$contentM .= t(2).'if (!mktime(0, 0, 0, 0, 0, $value))'."\n".
	 											 t(2).'{'."\n".
		 											 t(3).'$this->_typeErrors[\''.SLS_String::addSlashes($column, 'QUOTES').'\'] = "E_TYPE";'."\n".
													 t(3).'return false;'."\n".
												 t(2).'}'."\n\n";
									break;
							}
						}
						
					}
					$contentM .= 		t(2).'$this->__set(\''.$column.'\', $value);'."\n".
										t(2).'$this->flushError(\''.$column.'\');'."\n".
										t(2).'return true;'."\n".
									t(1).'}'."\n\n";
				}
			}						
		}
		
		// Get FKs to create access reference functions
		$fks = $xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$db."_".$tableName."']",array("columnFk","tablePk"));
		$fkMethods = array();
		$fkCursor = "";
		for($i=0 ; $i<$count=count($fks) ; $i++)
		{
			$columnFk = $fks[$i]["attributes"][0]["value"];
			$tablePk = $fks[$i]["attributes"][1]["value"];
			
			$functionName = SLS_String::fullTrim(ucwords(SLS_String::stringToUrl(SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($tablePk,"_"))," ",false)),"");
			$functionName{0} = strtolower($functionName{0});
			$functionNameModified = $functionName;
			while(in_array($functionNameModified,$fkMethods))							
				$functionNameModified = $functionName."_".($fkCursor = (empty($fkCursor)) ? 2 : $fkCursor+1);			
			$fkMethods[] = $functionNameModified;
			$contentM .= t(1).'/**'."\n".
					     t(1).' * Get the instance of '.SLS_String::substrAfterFirstDelimiter($tablePk,"_").'\'s Model described by '.$columnFk.''."\n".  
					     t(1).' * @author SillySmart'."\n".
					     t(1).' * @copyright SillySmart'."\n".
					     t(1).' * @return '.SLS_String::tableToClass($tablePk).' $object the instance of '.SLS_String::substrAfterFirstDelimiter($tablePk,"_").'\'s Model'."\n".
					     t(1).' */'."\n".
						 t(1).'public function '.$functionNameModified.'()'."\n".
						 t(1).'{'."\n".
							 t(2).'$this->_generic->useModel("'.SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($tablePk,"_")).'","'.SLS_String::substrBeforeFirstDelimiter($tablePk,"_").'","user");'."\n".
						     t(2).'$object = new '.ucfirst(SLS_String::substrBeforeFirstDelimiter($tablePk,"_")).'_'.SLS_String::tableToClass(SLS_String::substrAfterFirstDelimiter($tablePk,"_")).'();'."\n".
						     t(2).'$object->getModel($this->__'.$columnFk.');'."\n".
						     t(2).'return $object;'."\n".
						 t(1).'}';
			$contentM .= ($i == ($count-1)) ? "\n" : "\n\n";
		}
		
		$contentM .= '}'."\n".
					 '?>';
		
		return $contentM;
	}
	
	public function getEnvironments()
	{
		$environments = array();
		$filesToCheck = array("site","db","project","mail");
		if ($handle = opendir($this->_generic->getPathConfig("configSecure")))
		{
			while (false !== ($entry = readdir($handle)))
			{
				foreach($filesToCheck as $file)
				{
			        if (SLS_String::startsWith($entry,$file."_") && SLS_String::endsWith($entry,".xml"))
			        {
			        	$environment = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($entry,$file."_"),".xml");
			        	if (!in_array($environment,$environments))
			        		$environments[] = $environment;
			        }
				}
			}
		}
		return $environments;
	}
	
	public function getTableAlias($table)
	{
		if(!array_key_exists($table, $this->tableAlias))
			$this->tableAlias[$table] = 1;
		else
			$this->tableAlias[$table] = $this->tableAlias[$table]+1;
		return $table.'_'.$this->tableAlias[$table];
	}

	public function iterateFormatQueryWhereToArray($allQueryWheres, $slsGraphQueryWhereParentId)
	{
		$this->slsGraphQueryWhereParentId = $slsGraphQueryWhereParentId;
		$slsGraphQueryWheres = array_filter($allQueryWheres, array($this,'filterParent'));

		$tmp = array();
		foreach($slsGraphQueryWheres as $slsGraphQueryWhere)
		{
			$tmp[] = $slsGraphQueryWhere = array(
				'sls_graph_query_where_type' => $slsGraphQueryWhere->sls_graph_query_where_type,
				'sls_graph_query_where_condition' => $slsGraphQueryWhere->sls_graph_query_where_condition,
				'sls_graph_query_where_column' => ($slsGraphQueryWhere->sls_graph_query_where_type == 'group' ? '' : $slsGraphQueryWhere->sls_graph_query_where_table_alias.'.'.$slsGraphQueryWhere->sls_graph_query_where_column),
				'sls_graph_query_where_operator' => $slsGraphQueryWhere->sls_graph_query_where_operator,
				'sls_graph_query_where_value' => $slsGraphQueryWhere->sls_graph_query_where_value,
				'sls_graph_query_where_children' => $this->iterateFormatQueryWhereToArray($allQueryWheres, $slsGraphQueryWhere->sls_graph_query_where_id)
			);
		}

		return $slsGraphQueryWhereParentId == 0 ? $tmp[0] : $tmp;
	}

	public function iterateFormatQueryColumnToArray($columns, $joins)
	{
		$results = array();
		foreach($columns as $column)
		{
			$table = $column->sls_graph_query_column_table;
			$tableAlias = $column->sls_graph_query_column_table_alias;

			$columns = $this->getTableColumns($table);
			$columnName = $column->sls_graph_query_column_name;

			$this->columnName = $columnName;
			$column = array_shift(array_filter($columns, array($this,'filterColumnName')));

			$result = array(
				'sls_graph_query_column_label' => $columnName,
				'sls_graph_query_column_value' => $table.'.'.$columnName
			);

			$this->iterateFormatQueryColumn($tableAlias, $joins, $result);
			array_push($results, $result);
		}
		return $results;
	}

	public function iterateFormatQueryColumn($tableAlias, $joins, &$result)
	{
		$this->tableAlias3 = $tableAlias;
		$join = array_shift(array_filter($joins, array($this,'filterTable')));
		
		if(!empty($join))
		{	
			$tableTarget = $join->sls_graph_query_join_table_target;
			$tableAliasTarget = $join->sls_graph_query_join_table_alias_target;
			$columnTarget = $join->sls_graph_query_join_column_target;
			$columnsTarget = $this->getTableColumns($tableTarget);
			$this->columnTarget = $columnTarget;
			$columnTarget = array_shift(array_filter($columnsTarget, array($this,'filterColumnTarget')));
			$result['sls_graph_query_column_label'] = $columnTarget->Field.' / '.$result['sls_graph_query_column_label'];
			$result['sls_graph_query_column_value'] = $join->sls_graph_query_join_table_target.'.'.$join->sls_graph_query_join_column_target.'|'.$result['sls_graph_query_column_value'];
			
			$this->iterateFormatQueryColumn($tableAliasTarget, $joins, $result);
		}
	}

	public function getTableColumns($table)
	{
		if(!array_key_exists($table, $this->tableColumns))
			$this->tableColumns[$table] = $this->sql->showColumns($table);

		return $this->tableColumns[$table];
	}

	public function deleteQuery($queryId)
	{
		$sql = new SLS_Sql();
		$req = '
			DELETE
			        gq,
			        gqc,
			        gqj,
			        gqw,
			        gqg,
			        gqo,
			        gql
			FROM
			        sls_graph_query gq LEFT JOIN
			        sls_graph_query_column gqc ON (gq.sls_graph_query_id = gqc.sls_graph_query_id) LEFT JOIN
			        sls_graph_query_join gqj ON (gq.sls_graph_query_id = gqj.sls_graph_query_id) LEFT JOIN
			        sls_graph_query_where gqw ON (gq.sls_graph_query_id = gqw.sls_graph_query_id) LEFT JOIN
			        sls_graph_query_group gqg ON (gq.sls_graph_query_id = gqg.sls_graph_query_id) LEFT JOIN
			        sls_graph_query_order gqo ON (gq.sls_graph_query_id = gqo.sls_graph_query_id) LEFT JOIN
			        sls_graph_query_limit gql ON (gq.sls_graph_query_id = gql.sls_graph_query_id)
			WHERE
			        gq.sls_graph_query_id = '.$queryId;
		$sql->delete($req);
	}

	public function iterateSetSlsGraphQueryWhere($node, &$nodeIndex, &$nodeSiblingIndex, &$slsGraphQueryWheres, &$errors, $joins)
	{
		if($nodeIndex > 0 && !$this->isValidQueryWhere($node))
			return;
		$nodeIndex++;
		$nodeSiblingIndex++;

		$className = $this->defaultDb."_Sls_graph_query_where";
		$slsGraphQueryWhere = new $className();
		if(!$slsGraphQueryWhere->setSlsGraphQueryWhereType($node['sls_graph_query_where_type']))
			$errors['sls_graph_query_where'][$nodeIndex]['sls_graph_query_where_type'] = "Invalid type";

		if($nodeSiblingIndex > 1 && !$slsGraphQueryWhere->setSlsGraphQueryWhereCondition($node['sls_graph_query_where_condition']))
			$errors['sls_graph_query_where'][$nodeIndex]['sls_graph_query_where_condition'] = "Invalid condition";

		if($node['sls_graph_query_where_type'] == 'clause')
		{
			$tmp = explode('.', $node['sls_graph_query_where_column']);
			$tableAlias = $tmp[0];
			$column = $tmp[1];

			$this->tableAlias2 = $tableAlias;
			$join = array_shift(array_filter($joins, array($this,'filterTable2')));

			if(!$slsGraphQueryWhere->setSlsGraphQueryWhereColumn($column) || empty($column) || !$slsGraphQueryWhere->setSlsGraphQueryWhereTableAlias($tableAlias) || !$slsGraphQueryWhere->setSlsGraphQueryWhereTable($join['sls_graph_query_join_table_source']))
				$errors['sls_graph_query_where'][$nodeIndex]['sls_graph_query_where_column'] = "Invalid column";

			if(!$slsGraphQueryWhere->setSlsGraphQueryWhereOperator($node['sls_graph_query_where_operator']) || empty($node['sls_graph_query_where_operator']))
				$errors['sls_graph_query_where'][$nodeIndex]['sls_graph_query_where_operator'] = "Invalid operator";

			if(!$slsGraphQueryWhere->setSlsGraphQueryWhereValue($node['sls_graph_query_where_value']) || (in_array($node['sls_graph_query_where_operator'], $this->queryOperatorsNeedField) && empty($node['sls_graph_query_where_value'])))
				$errors['sls_graph_query_where'][$nodeIndex]['sls_graph_query_where_value'] = "Invalid value";
		}

		$slsGraphQueryWheres[$nodeIndex] = $slsGraphQueryWhere;

		if(!empty($node['sls_graph_query_where_children']))
		{
			$nodeSiblingIndexCurrent = $nodeSiblingIndex;
			$nodeSiblingIndex = 0;
			foreach($node['sls_graph_query_where_children'] as $nodeChild)
				$this->iterateSetSlsGraphQueryWhere($nodeChild, $nodeIndex, $i, $slsGraphQueryWheres, $errors,  $joins);
			$nodeSiblingIndex = $nodeSiblingIndexCurrent;
		}
	}

	public function isValidQueryWhere($node)
	{
		if($node['sls_graph_query_where_type'] == 'clause')
			return true;

		if(!empty($node['sls_graph_query_where_children']))
		{
			foreach($node['sls_graph_query_where_children'] as $nodeChild)
			{
				if($this->isValidQueryWhere($nodeChild))
					return true;
			}
		}
		return false;
	}

	public function iterateCreateQueryWhere($node, $parentQueryWhereId, &$nodeIndex, &$slsGraphQueryWheres, $queryId)
	{
		if($nodeIndex > 0 && !$this->isValidQueryWhere($node))
			return;
		$nodeIndex++;

		$slsGraphQueryWheres[$nodeIndex]->setSlsGraphQueryId($queryId);
		$slsGraphQueryWheres[$nodeIndex]->setParentSlsGraphQueryWhereId($parentQueryWhereId);

		$slsGraphQueryWhereId = $slsGraphQueryWheres[$nodeIndex]->create();

		if(!empty($node['sls_graph_query_where_children']))
		{
			$nodeSiblingIndex = 0;
			foreach($node['sls_graph_query_where_children'] as $nodeChild)
				$this->iterateCreateQueryWhere($nodeChild, $slsGraphQueryWhereId, $nodeIndex, $slsGraphQueryWheres, $queryId);
		}

	}

	public function iterateAddXmlQueryWhere($node, &$nodeIndex, &$nodeSiblingIndex, &$xml)
	{
		if($nodeIndex > 0 && !$this->isValidQueryWhere($node))
			return;
		$nodeIndex++;
		$nodeSiblingIndex++;

		$xml->startTag('sls_graph_query_where');
		$xml->addFullTag('sls_graph_query_where_num', $nodeIndex, true);
		$xml->addFullTag('sls_graph_query_where_type', $node['sls_graph_query_where_type'], true);
		$xml->addFullTag('sls_graph_query_where_condition', ($nodeSiblingIndex == 1) ? '' : $node['sls_graph_query_where_condition'], true);

		$xml->addFullTag('sls_graph_query_where_column', ($node['sls_graph_query_where_type'] == 'group') ? '' : $node['sls_graph_query_where_column'], true);
		$xml->addFullTag('sls_graph_query_where_operator', $node['sls_graph_query_where_operator'], true);
		$xml->addFullTag('sls_graph_query_where_value', $node['sls_graph_query_where_value'], true);

		$xml->startTag('sls_graph_query_where_children');
		if(!empty($node['sls_graph_query_where_children']))
		{
			$nodeSiblingIndexCurrent = $nodeSiblingIndex;
			$nodeSiblingIndex = 0;
			foreach($node['sls_graph_query_where_children'] as $nodeChild)
				$this->iterateAddXmlQueryWhere($nodeChild, $nodeIndex, $nodeSiblingIndex, $xml);
			$nodeSiblingIndex = $nodeSiblingIndexCurrent;
		}
		$xml->endTag('sls_graph_query_where_children');
		$xml->endTag('sls_graph_query_where');
	}

	public function getQueryJoin($queryTable, $queryTableAlias, $columns)
	{
		$joins = array();
		if(count($columns) == 0)
			return $joins;

		foreach($columns as $col)
		{
			if (!is_array($col))
				continue;
		
			$column = $col['sls_graph_query_column_value'];
			$path = explode('|', $column);
			$nbJoins = count($path);

			$data = explode('.', $path[$nbJoins-1]);
			$table = $data[0];
			$column = $data[1];

			# joins
			for($k = 0; $k<$nbJoins ; $k++)
			{
				if($k == 0)
				{
					$columns = $this->sql->showColumns($queryTable);
					$columnSource = array_shift(array_filter($columns, array($this,'filterPK')));
					$columnSourcePK = $columnSource->Field;

					$join = array(
						'sls_graph_query_join_table_source' => $queryTable,
						'sls_graph_query_join_column_source' => $columnSourcePK
					);
				}
				else
				{
					$dataSource = explode('.', $path[$k]);
					$dataTarget = explode('.', $path[$k-1]);

					$tableSource = $dataSource[0];
					$tableTarget = $dataTarget[0];
					$columnSource = $dataSource[1];
					$columnTarget = $dataTarget[1];

					$columns = $this->sql->showColumns($tableSource);
					$columnSourcePK = array_shift(array_filter($columns, array($this,'filterPK')))->Field;

					$columns = $this->sql->showColumns($tableTarget);
					$this->columnTarget = $columnTarget;
					$columnTargetComment = array_shift(array_filter($columns, array($this,'filterColumnTarget')))->Comment;

					$join = array(
						'sls_graph_query_join_table_target' => $tableTarget,
						'sls_graph_query_join_column_target' =>$columnTarget,
						'sls_graph_query_join_table_comment_target' => $columnTargetComment,
						'sls_graph_query_join_table_source' => $tableSource,
						'sls_graph_query_join_column_source' => $columnSourcePK
					);
				}

				$joinSearch = $this->array_search_multi($join, $joins);
				if(empty($joinSearch))
				{
					$join['sls_graph_query_join_table_alias_source'] = ($k == 0) ? $queryTableAlias : $this->getTableAlias($join['sls_graph_query_join_table_source']);
					array_push($joins, $join);
				}
				else
					$join = $joinSearch;
			}
			# /joins
		}

		return $joins;
	}
	
	/**
	 * Contains recursive
	 * 
	 * @param string $hay the string in which you search
	 * @param mixed $needles the string or the array of occurences searched
	 * @return mixed false if not contain, else occurence found
	 */
	public function containsRecursive($hay,$needles)
	{
		if (is_array($needles))
		{
			foreach($needles as $needle)
			{				
				if (SLS_String::contains($hay,$needle))				
					return $needle;
			}
		}
		else
			return SLS_String::contains($hay,$needles);
			
		return false;
	}
		
	/**
	 * Used by usort
	 * 
	 * @param string $a
	 * @param string $b
	 */
	public function cmpTables($a, $b)
	{
		return $a->Name > $b->Name;
	}
	
	public function array_search_multi($search, $array)
	{
		unset($search['sls_graph_query_join_table_alias_source']);
		unset($search['sls_graph_query_join_table_alias_target']);
	
		foreach($array as $e)
		{
			$aliasSource = $e['sls_graph_query_join_table_alias_source'];
			unset($e['sls_graph_query_join_table_alias_source']);
	
			$aliasTarget = $e['sls_graph_query_join_table_alias_target'];
			unset($e['sls_graph_query_join_table_alias_target']);
	
			if($search == $e)
			{
				$e['sls_graph_query_join_table_alias_source'] = $aliasSource;
				$e['sls_graph_query_join_table_alias_target'] = $aliasTarget;
				return $e;
			}
		}
	
		return false;
	}
	
	public function filterParent($e)
	{
		return $e->parent_sls_graph_query_where_id == $this->slsGraphQueryWhereParentId;
	}
	
	public function filterTable($e)
	{
		return $e->sls_graph_query_join_table_alias_source == $this->tableAlias3;
	}
	
	public function filterTable2($e)
	{
		return $e['sls_graph_query_join_table_alias_source'] == $this->tableAlias2;
	}
	
	public function filterColumnName($e)
	{
		return $e->Field == $this->columnName;
	}
	
	public function filterColumnTarget($e)
	{
		//var_dump($this->columnTarget);
		return $e->Field == $this->columnTarget;
	}
	
	public function filterPK($e)
	{
		return $e->Key == 'PRI';
	}
}
?>