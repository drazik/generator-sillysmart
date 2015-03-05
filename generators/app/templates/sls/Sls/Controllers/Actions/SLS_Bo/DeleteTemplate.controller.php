<?php
class SLS_BoDeleteTemplate extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$tpls 	= $this->getAppTpls();
		
		$tpl = SLS_String::trimSlashesFromString($this->_http->getParam("name"),"_");
		
		if (in_array($tpl,$tpls))
		{
			try {
				unlink($this->_generic->getPathConfig("viewsTemplates").$tpl.".xsl");
			}
			catch (Exception $e)
			{
				SLS_Tracing::addTrace($e);
			}
		}
		
		$this->_generic->goDirectTo("SLS_Bo","Templates");
	}	
}
?>