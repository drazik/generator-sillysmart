<?php
/**
 * Controller SLS_Configs - Append configuration files to final XML
 * 
 * @author Florian Collot 
 * @copyright SillySmart
 * @package Sls.Controllers.Statics 
 * @see Sls.Configs.Site.site.xml - Configs.Site.mail.xml - Configs.Site.db.xml
 * @since 1.0
 */
class SLS_ConfigsController extends SLS_FrontStatic implements SLS_IStatic 
{
	private $_httpRequest;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Construct the XML
	 *
	 * @access private
	 * @since 1.0
	 */
	public function constructXML()
	{
		if ($this->_generic->getSiteConfig('isInstall'))
			$this->_xmlToolBox->addFullTag("Configs", "<site><domainName isSecure=\"false\" js=\"true\"><![CDATA[".$this->_generic->getSiteConfig('domainName')."]]></domainName><projectName isSecure=\"false\" js=\"true\"><![CDATA[".$this->_generic->getSiteConfig("projectName")."]]></projectName><protocol isSecure=\"false\" js=\"true\"><![CDATA[http]]></protocol><defaultBuildConfigsJsVars isSecure=\"false\" js=\"true\"><![CDATA[1]]></defaultBuildConfigsJsVars></site><mails>".trim($this->_generic->getMailXml()->getTagsByAttribute('//mails/*', 'isSecure', 'false'))."</mails><paths>".trim($this->_generic->getPathsXml()->getTagsByAttribute('//paths/*', 'isSecure', 'false'))."</paths><db>".trim($this->_generic->getDbXml()->getTagsByAttribute('//db/*', 'isSecure', 'false'))."</db>", false);
		else
		{
			$langs = $this->_lang->getSiteLangs();			
			usort($langs,array($this,'unshiftDefaultLang'));			
			$langsXml = '<langs isSecure="false" js="false">';
			foreach($langs as $lang)
				$langsXml .= '<name isSecure="false" js="false" active="'.(($this->_lang->isEnabledLang($lang)) ? "true" : "false").'"><![CDATA['.$lang.']]></name>';
			$langsXml .= '</langs>';
			$this->_xmlToolBox->addFullTag("Configs", "<site><domainName isSecure=\"false\" js=\"true\"><![CDATA[".$this->_generic->getSiteConfig('domainName')."]]></domainName>".$langsXml.trim($this->_generic->getSiteXml()->getTagsByAttribute('//configs/*[name()!="domainName" and name()!="langs"]', 'isSecure', 'false'))."</site><mails>".trim($this->_generic->getMailXml()->getTagsByAttribute('//mails/*', 'isSecure', 'false'))."</mails><paths>".trim($this->_generic->getPathsXml()->getTagsByAttribute('//paths/*', 'isSecure', 'false'))."</paths><db>".trim($this->_generic->getDbXml()->getTagsByAttribute('//db/*', 'isSecure', 'false'))."</db>", false);
		}
	}
	
	/**
	 * Order array langs to unshift default lang as first offset
	 * 
	 * @access private
	 * @param string $a $key lang
	 * @param string $b $key lang
	 * @return int -1|0|1
	 * @since 1.1
	 */
	private function unshiftDefaultLang($a,$b)
	{
		if ($a == $this->_lang->getDefaultLang())
			return -1;			
		if ($b == $this->_lang->getDefaultLang())
			return 1;
		
		return ($a < $b) ? -1 : 1;
	}
}
?>