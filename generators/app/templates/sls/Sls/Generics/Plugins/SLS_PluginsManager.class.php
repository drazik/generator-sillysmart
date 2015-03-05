<?php
/**
 * Tool SLS_PluginsManager - Plugins Management
 *  
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0 
 */ 
class SLS_PluginsManager
{
	public static $_pluginsXML;
	private $_pluginXML;
	private $_generic;
	public $_name;
	public $_code;
	public $_id;
	public $_version;
	public $_compability;
	public $_customizable = false;
	public $_file;
	public $_author;
	public $_description;
	public $_beta;
	public $_output;
	public $_path;
	public $_dependencies = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $plugin_id the plugin id
	 * @return bool $existed true if the plugin exists, else false
	 * @since 1.0
	 */
	public function __construct($plugin_id)
	{
		$this->_generic = SLS_Generic::getInstance();
		
		if (!SLS_PluginsManager::isExists($plugin_id))
			return false;
		else 
		{
			$code = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$plugin_id.'"]/@code'));
			if (is_file($this->_generic->getPathConfig('configPlugins').$plugin_id."_".$code.".xml") && array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$plugin_id.'"]/@customizable')) == 1)
			{
				$this->_pluginXML = $this->_generic->getPluginXml($plugin_id."_".$code);
				$this->_customizable = true;	
			}
			$this->_id = $plugin_id;
			$this->_code = $code;
			$this->_name = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/name'));
			$this->_description = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/description'));
			$this->_author = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/author'));
			$this->_version = (float)array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@version'));
			$this->_compability = (float)array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@compability'));
			$this->_file = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@file'));
			$this->_output = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@output'));
			$this->_path = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@path'));
			$this->_beta = (array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$this->_id.'"]/@beta')) == 1) ? true : false;
			return true;
		}
	}
	
	/**
	 * Check if a given plugin exist
	 *
	 * @access public static
	 * @param int $id the plugin id
	 * @return bool $existed true if plugin exists, else false
	 * @since 1.0
	 */
	public static function isExists($id)
	{
		SLS_PluginsManager::returnPluginsXML();
		$code = array_shift(SLS_PluginsManager::$_pluginsXML->getTags('//plugins/plugin[@id="'.$id.'"]/@code'));
		if (empty($code))
			return false;
		return true;
	}
	
	/**
	 * Init static xml variable
	 *
	 * @access public static
	 * @return SLS_XmlToolbox $pluginXML the xml configuration of plugins
	 * @since 1.0
	 */
	public static function returnPluginsXML()
	{
		if (!is_object(SLS_PluginsManager::$_pluginsXML))
			SLS_PluginsManager::$_pluginsXML = SLS_Generic::getInstance()->getPluginXml("plugins");
			
		return SLS_PluginsManager::$_pluginsXML;
	}
	
	/**
	 * Check if the current plugin version is compatible with the current SillySmart release
	 *
	 * @access public
	 * @return bool $compatible true if this plugin is compatible with your current SillySmart release, else false
	 * @since 1.0
	 */
	public function checkVersion()
	{
		$coreXml = $this->_generic->getCoreXML('sls');
		$slsVersion = (float)array_shift($coreXml->getTags("//sls_configs/version"));
		if ($slsVersion < $this->_compability)
			return false;
		return true;
	}
	
	/**
	 * Check if the plugin is customizable
	 * 
	 * @access public
	 * @return bool $customizable true if the plugin is customizable, else false
	 * @since 1.0
	 */
	public function isCustomizable()
	{
		if ($this->_customizable)
			return true;
		return false;
	}
	
	/**
	 * Get the wanted plugin XML
	 *
	 * @access public
	 * @return mixed $pluginXML false or the SLS_XMLToolbox instance of the XML
	 * @since 1.0
	 */
	public function getXML()
	{
		if ($this->isCustomizable())
			return $this->_pluginXML;
		else 
			return false;
	}
	
	/**
	 * Save the xml
	 *
	 * @access public
	 * @param SLS_XMLToolbox $pluginXML the xml object
	 * @since 1.0
	 */
	public function saveXML($pluginXML)
	{
		$this->_pluginXML = $pluginXML;
		file_put_contents($this->_generic->getPathConfig('configPlugins').$this->_id."_".$this->_code.".xml", $this->_pluginXML->getXML());
	}
	
	/**
	 * Get fields
	 *
	 * @access public
	 * @return SLS_XMLToolbox $xml all the xml configuration fields
	 * @see SLS_PluginsManager::recursiveFields
	 * @since 1.0
	 */
	public function getFields()
	{
		$xml = new SLS_XMLToolbox(false);
		$xml = $this->recursiveFields("//plugin", $xml);
		return $xml;
	}
	
	/**
	 * Recursive Fields
	 *
	 * @access private
	 * @param string $xpath the xpath way
	 * @param SLS_XMLToolbox $xml xml object
	 * @return SLS_XMLToolbox $xml xml object
	 * @see SLS_PluginsManager::getFields
	 * @since 1.0
	 */
	private function recursiveFields($xpath, $xml)
	{
		$childs = $this->_pluginXML->getChilds($xpath);
		$fields = array();
		$editField = $this->_generic->getTranslatedController('SLS_Bo', 'EditPluginField');
		foreach ($childs as $child)
		{
			if (array_shift($this->_pluginXML->getTags($xpath."/".$child."/@writable")) == 1)
			{
				$tag = array_shift(explode("[", $child));
				if (key_exists($tag, $fields))
					$fields[$tag]++;
				else 
					$fields[$tag] = 1;
				$xml->startTag('field');
				$xml->addFullTag('tag', $tag, true);
				$xml->addFullTag('path', str_replace("/", "|||", str_replace("]", "|#|", str_replace("[", "#|#", str_replace("//", "", $xpath))))."|||".str_replace("]", "|#|", str_replace("[", "#|#", $child)), true);
				$xml->addFullTag('label', array_shift($this->_pluginXML->getTags($xpath."/".$child."/@label")), true);
				$xml->addFullTag('clonable', (array_shift($this->_pluginXML->getTags($xpath."/".$child."/@clonable")) == '1') ? 'true' : 'false', true);
				if (array_shift($this->_pluginXML->getTags($xpath."/".$child."/@clonable")) == '1')
				{
					$xml->addFullTag('linkAdd', $editField['protocol']."://".$this->_generic->getSiteConfig('domainName')."/".$editField['controller']."/".$editField['scontroller']."/Action/add/Plugin/".$this->_id."/Field/".str_replace("/", "|||", str_replace("]", "|$|", str_replace("[", "$|$", str_replace("//", "", $xpath))))."|||".str_replace("]", "|$|", str_replace("[", "$|$", $child)).".sls", true);
					$xml->addFullTag('linkDel', $editField['protocol']."://".$this->_generic->getSiteConfig('domainName')."/".$editField['controller']."/".$editField['scontroller']."/Action/del/Plugin/".$this->_id."/Field/".str_replace("/", "|||", str_replace("]", "|$|", str_replace("[", "$|$", str_replace("//", "", $xpath))))."|||".str_replace("]", "|$|", str_replace("[", "$|$", $child)).".sls", true);
					
				}
				$xml->addFullTag('index', $fields[$tag], true);
				$xml->addFullTag('value', array_shift($this->_pluginXML->getTags($xpath."/".$child)), true);
				$xml->addFullTag('alias', array_shift($this->_pluginXML->getTags($xpath."/".$child."/@alias")), true);
				$type = (array_shift($this->_pluginXML->getTags($xpath."/".$child."/@type")) == "") ? "part" : array_shift($this->_pluginXML->getTags($xpath."/".$child."/@type"));
				$xml->addFullTag('type', $type, true);
				if ($type == 'select' || $type == "radio" || $type == "check")
				{
					$xml->startTag("values");
						$values = explode("|||", array_shift($this->_pluginXML->getTags($xpath."/".$child."/@values")));
						foreach ($values as $value)
							$xml->addFullTag('value', $value, true);
					$xml->endTag("values");
				}
				
				if ($this->_pluginXML->countChilds($xpath."/".$child) > 0)
					$xml = $this->recursiveFields($xpath."/".$child, $xml);
				
				$xml->endTag('field');
			}
		}
		
		return $xml;
	}	
}
?>