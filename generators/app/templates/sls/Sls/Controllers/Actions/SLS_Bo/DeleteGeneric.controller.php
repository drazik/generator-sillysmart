<?php
class SLS_BoDeleteGeneric extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$generics 	= $this->getAppXsl();
		
		$generic = SLS_String::trimSlashesFromString($this->_http->getParam("name"),"_");
		
		if (in_array(strtolower($generic),$generics))
		{
			try {
				unlink($this->_generic->getPathConfig("viewsGenerics").$generic.".xsl");
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