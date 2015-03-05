<?php
/**
* Class BoUploadProgress into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUploadProgress extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		// Params
		$fileUid = $this->_http->getParam("file_uid");
		
		// Try to get upload progress
		if (function_exists('apc_fetch'))
		{	
			$prefix = ini_get('apc.rfc1867_prefix');
			if (empty($prefix))
				$prefix = 'upload_';
			$status = apc_fetch($prefix.$fileUid);
			if ($status !== false)
			{
  	 			$percent = floor($status['current'] / $status['total'] * 100);
  	 			$this->_bo->_render["status"] = "OK";
  	 			$this->_bo->_render["result"] = array("percent" => $percent);
			}
			else
			{
				$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_UPLOAD_PROGRESS_ERROR'];
			}
		}
		else
		{
			$this->_bo->_render["errors"][] = $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_UPLOAD_PROGRESS_ERROR'];
		}
  	 	
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
}
?>