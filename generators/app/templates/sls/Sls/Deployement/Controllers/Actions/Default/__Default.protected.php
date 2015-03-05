<?php
/**
 * Class generic for the controller Default
 * Write here all your generic functions you need in your Default Actions
 * 
 * @author SillySmart
 * @copyright SillySmart
 * @package Mvc.Controllers.Default
 * @see Mvc.Controllers.SiteProtected
 * @see Sls.Controllers.Core.SLS_GenericController
 * @since 1.0
 */
class DefaultControllerProtected extends SiteProtected 
{
	public function init()
    {
        parent::init();
    }
	
	/**
	 * Map the source of the error	 
	 *
	 * @access protected
	 * @return array $data array('referer','requestUri','userAgent','lastController','lastScontroller');
	 */
	protected function getHTTPErrors() 
	{
		$data['referer'] = $_SERVER['HTTP_REFERER'];
		if (empty($data['referer'])) 		
			$data['referer'] = $this->_generic->getObjectSession()->getParam('previousPage');		
		$data['lastController'] = $this->_generic->getObjectSession()->getParam('previousController');
		$data['lastScontroller'] = $this->_generic->getObjectSession()->getParam('previousScontroller');
		$data['requestUri'] = $_SERVER['REQUEST_URI'];
		$data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		return $data;
	}
	
}
?>