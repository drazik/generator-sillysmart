<?php
/**
* Class BoUpload into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUpload extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		// Objects
		$types = array("all","img");
		$imgExtensions = array("jpg","jpeg","png","gif","bmp","tiff","xbm");
		
		// Params
		$upload = $this->_http->getParam("upload");
		$file = (is_array($upload["file"])) ? $upload["file"] : array("name" 		=> "",
																	  "type" 		=> "",
																	  "size" 		=> "0",
																	  "tmp_name" 	=> "",
																	  "error" 		=> "4");
		$type = (in_array($upload["type"],$types)) ? $upload["type"] : array_shift($types);
		
		switch($file["error"])
		{
			case 0:
				// Move & rename file
				if (!file_exists($this->_generic->getPathConfig("files")."__Uploads"))
					@mkdir($this->_generic->getPathConfig("files")."__Uploads");
				if (!file_exists($this->_generic->getPathConfig("files")."__Uploads/__Deprecated"))
					@mkdir($this->_generic->getPathConfig("files")."__Uploads/__Deprecated");
				$fileName = $this->_generic->getPathConfig("files")."__Uploads/__Deprecated"."/".SLS_String::sanitize(SLS_String::substrBeforeLastDelimiter($file["name"],"."),"_")."_".substr(md5(time().substr(sha1(microtime()),0,rand(5,12))),mt_rand(1,20),10).((SLS_String::contains($file["name"],".")) ? ".".SLS_String::substrAfterLastDelimiter($file["name"],".") : "");
				try 
				{
					rename($file["tmp_name"],$fileName);
					$file["tmp_name"] = $fileName;
					
					if ($type == "all")
					{
						$fileExtension = strtolower(SLS_String::substrAfterLastDelimiter($fileName,"."));
						// Img in file_all
						if (!empty($fileExtension) && in_array($fileExtension,$imgExtensions))
							$image = $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$fileName;
						// Mime-Type
						else
						{
							if (file_exists($this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",$file["type"]).".png")))
								$image = $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/".strtolower(str_replace("/","-",$file["type"]).".png");
							else
								$image = $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreImg")."BO-2014/Mime-Types/application-octet-stream.png";
						}
					}
					else
						$image = $this->_bo->_boProtocol."://".$this->_generic->getSiteConfig("domainName")."/".$fileName;
					
					$this->_bo->_render["status"] = "OK";
					$this->_bo->_render["result"] = array("data"  => $file,
													 	  "thumb" => $image);
					
				}
				catch (Exception $e)
				{
					$this->_bo->_render["errors"][] = $file["tmp_name"].": ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_WRITE'];
				}
				
				break;
			case (in_array($file["error"],array(1,2,3))):
				$this->_bo->_render["errors"][] = $file["tmp_name"]." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_SIZE']." (max: ".$this->getUploadMaxFilesize().")";
				break;
			case (in_array($file["error"],array(4,6,7,8))):
				$this->_bo->_render["errors"][] = $file["tmp_name"].": ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_WRITE'];
				break;
		}

		if ($this->_bo->_async)
		{
			echo json_encode($this->_bo->_render);
			die;
		}
		
		// Render
		$render = '<script type="text/javascript">';
		if (isset($upload['callback']) && isset($upload['id']))
		{
			$render .= 'window.top.window.document.getElementById("'.$upload['id'].'").fireEvent("'.$upload['callback'].'", {'.
							'data: {'.
								'name: "'.$file["name"].'",'.
								'type: "'.$file["type"].'",'.
								'size: "'.$file["size"].'",'.
								'tmp_name: "'.$file["tmp_name"].'",'.
								'error: "'.$file["error"].'",'.
							'},'.
							'thumb: "'.$image.'",'.
							'status: "'.((count($this->_bo->_render["errors"]) == 0) ? "OK" : "ERROR").'",'.
							'errors: '.json_encode($this->_bo->_render["errors"]).','.
						'});';
		}
		else
		{
			$render .= 'window.top.window.document.getElementById("'.$upload['inputId'].'").fireEvent("uploaded", {'.
							'data: {'.
								'name: "'.$file["name"].'",'.
								'type: "'.$file["type"].'",'.
								'size: "'.$file["size"].'",'.
								'tmp_name: "'.$file["tmp_name"].'",'.
								'error: "'.$file["error"].'",'.
							'},'.
							'thumb: "'.$image.'",'.
							'status: "'.((count($this->_bo->_render["errors"]) == 0) ? "OK" : "ERROR").'",'.
							'errors: '.json_encode($this->_bo->_render["errors"]).','.
						'});';
		}
		$render .= '</script>';

		header("Content-Type: text/html; charset=UTF-8");
		print($render);
		die;
	}
	
	public function after()
	{
		parent::after();
	}
	
	/**
	 * Format upload max size
	 * 
	 * @return string formated upload max size
	 */
	public function getUploadMaxFilesize()
	{
		$uploadMaxFilesize = ini_get("upload_max_filesize");
		$unite = strtolower(substr(trim($uploadMaxFilesize), -1));
		switch ($unite)
	    {   
			case 'k': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024;					break;
	    	case 'm': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024 * 1024; 			break;
			case 'g': $uploadMaxFilesize = (int)$uploadMaxFilesize * 1024 * 1024 * 1024;	break;
			default: $uploadMaxFilesize = $uploadMaxFilesize;								break;
	    }
	    return $uploadMaxFilesize;
	}
}
?>