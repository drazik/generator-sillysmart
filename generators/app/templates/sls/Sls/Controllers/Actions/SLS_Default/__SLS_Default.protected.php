<?php
/**
 * Enter description here...
 *
 */
class SLS_DefaultControllerProtected extends SiteProtected
{
	public function init()
    {
        parent::init();        
    }
	
	/**
	 * Map the source of the error	 
	 *
	 * @access private
	 * @return array $data => ['referer'] 
	 * 						  ['requestUri']
	 * 						  ['userAgent']
	 */
	protected function getHTTPErrors() 
	{
		$data['referer'] = $_SERVER['HTTP_REFERER'];
		if (empty($data['referer'])) 
		{
			$data['referer'] = $this->_generic->getObjectSession()->getParam('previousPage');
		}
		$data['lastController'] = $this->_generic->getObjectSession()->getParam('previousController');
		$data['lastScontroller'] = $this->_generic->getObjectSession()->getParam('previousScontroller');
		$data['requestUri'] = $_SERVER['REQUEST_URI'];
		$data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		return $data;
	}
	
}
?>