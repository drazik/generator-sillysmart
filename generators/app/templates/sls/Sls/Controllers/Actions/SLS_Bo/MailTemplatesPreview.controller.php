<?php
class SLS_BoMailTemplatesPreview extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 		= $this->hasAuthorative();
		$mailXML 	= $this->_generic->getMailXML();
		
		$id = $this->_http->getParam("TplId");
		
		echo '<style type="text/css">html{background-color:#FFF;color:#000;}</style>'. 
			($mailXML->getTag("//mails/templates/item[@id='".$id."']/header")).'<pre style="text-align:center;">
				Your email content will be located here.
			</pre>'.
			($mailXML->getTag("//mails/templates/item[@id='".$id."']/footer"));
		die();	
	}
	
}
?>