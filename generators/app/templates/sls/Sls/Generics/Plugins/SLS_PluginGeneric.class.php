<?php
/**
 * Interface SLS_IPlugin
 *
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Plugins
 * @since 1.0 
 */
interface SLS_IPlugin
{
	public function __construct();
	public function checkDependencies();	
}

/**
 * Interface SLS_IPluginOutput
 *
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Plugins
 * @since 1.0 
 */
interface SLS_IPluginOutput
{
	
}

/**
 * SLS_PluginGeneric
 *
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Plugins
 * @since 1.0 
 */
class SLS_PluginGeneric 
{
	protected $_xml;
	protected $_name;
	protected $_className;
	protected $_id;
	protected $_code;
	protected $_version;
	protected $_pluginManager;
	
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param object $plugin the plugin object
	 * @since 1.0
	 */
	protected function __construct($plugin)
	{
		$this->_className = get_class($plugin);
		$this->_code = strtolower($this->_className);
		$pluginsXML = SLS_PluginsManager::returnPluginsXML();
		if (count($pluginsXML->getTags("//plugins/plugin[@code='".$this->_code."']/@id")) == 0)
			SLS_Tracing::addTrace(new Exception("Cannot find plugin Configuration for ".$this->_className));				
		else 
		{
			$this->_id = array_shift($pluginsXML->getTags("//plugins/plugin[@code='".$this->_code."']/@id"));
			$this->_pluginManager = new SLS_PluginsManager($this->_id);
			$this->_xml = $this->_pluginManager->getXML();
			$this->_version = $this->_pluginManager->_version;
		}
	}
}
?>