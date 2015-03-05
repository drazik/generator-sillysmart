<?php
class SLS_BoFlushCache extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		// Params
		$from = strtolower($this->_http->getParam("From"));
		$item = $this->_http->getParam("Item");
				
		if (in_array($from,array("static","component","controller","action","table","user","full")))
		{
			if ($from == "full" && substr(sha1($this->_generic->getSiteConfig("privateKey")),12,8) != $this->_http->getParam("Token"))
				$this->_generic->forward("SLS_Bo","ProdSettings");
			
			switch($from)
			{
				case "static": 		$this->_cache->flushStatic($item); 		break;
				case "component": 	$this->_cache->flushComponent($item); 	break;
				case "controller": 	$this->_cache->flushController($item); 	break;
				case "action": 		$this->_cache->flushAction($item); 		break;
				case "table": 		$this->_cache->flushFromTable($item); 	break;
				case "user": 		$this->_cache->flushUser($item); 		break;
				case "full": 		$this->_cache->flushFull();				break;
			}
			
			if ($from == "table")
				$this->_generic->forward("SLS_Bo","Models");
			else if ($from == "full")
				$this->_generic->forward("SLS_Bo","ProdSettings");
			else
				$this->_generic->forward("SLS_Bo","Controllers");
		}
		
		$this->_generic->forward("SLS_Default","UrlError");
	}
	
}
?>