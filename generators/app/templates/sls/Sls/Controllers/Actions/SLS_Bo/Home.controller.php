<?php
class SLS_BoHome extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$this->saveXML($xml);		
	}
	
}
?>