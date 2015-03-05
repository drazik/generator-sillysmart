<?php
class SLS_BoCompressor extends SLS_BoControllerProtected 
{	
	public function action()
	{
		// Objects
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);		
		$errors = array();
		$successes = array();
		$js = false;
		$css = false;
		$totalOldSize = 0;
		$totalNewSize = 0;
		$totalRatio = 0;
		$files = array("js" 	=> array(),
					   "css" 	=> array());
		
		# Step 1: extension choice
		if ($this->_http->getParam("reload_type") == "true")
		{
			switch($this->_http->getParam("type"))
			{
				case "both":	$js = true;		$css = true;	break;
				case "js":		$js = true; 	$css = false;	break;
				case "css":		$js = false;	$css = true;	break;
			}			
			
			# Fetching files
			if ($js)
			{
				$files["js"] = $this->recursiveList($files["js"],$this->_generic->getPathConfig("jsStatics"),".js");
				$files["js"] = $this->recursiveList($files["js"],$this->_generic->getPathConfig("jsDyn"),".js");
			}
			if ($css)
			{
				$files["css"] = $this->recursiveList($files["css"],$this->_generic->getPathConfig("css"),".css");
			}
			
			$xml->startTag("files");
			foreach($files as $type => $list)
			{
				$xml->startTag($type);
				foreach($list as $file)
					$xml->addFullTag("file",$file,true);
				$xml->endTag($type);
			}
			$xml->endTag("files");
			# /Fetching files
		}
		# /Step 1: extension choice
		
		# Step 2: files choice
		if ($this->_http->getParam("reload_files") == "true")
		{
			$compress = $this->_http->getParam("compress");
			$params = $this->_http->getParams();
			
			# Compress
			if ($compress == "compress")
			{
				foreach($params as $param => $file)
				{
					if (SLS_String::startsWith($param,"file_compress_"))
					{
						include_once 'Sls/Plugins/CSSmin.php';
						include_once 'Sls/Plugins/JSMinPlus.php';
						$cssMin = new CSSmin();
						$extension = (SLS_String::endsWith($file,"js")) ? "js" : "css";
						
						try 
						{
							if ($extension == "css")
								$compressed = $cssMin->run(file_get_contents($file));
							else
								$compressed = JSMinPlus::minify(file_get_contents($file));
							
							// Rename old file to uncompressed and replace old file by compressed file
							if (!file_exists($file.".uncompressed"))
							{
								copy($file,$file.".uncompressed");
								$oldSize = filesize($file.".uncompressed");
								$totalOldSize += $oldSize;
								file_put_contents($file,$compressed);
								$newSize = filesize($file);
								$totalNewSize += $newSize;
								array_push($successes,array("file"		=> $file,
															"old_size"	=> $oldSize,
														  	"new_size"	=> $newSize,
														  	"ratio"		=> ($oldSize > 0 && $newSize > 0) ? 100 - round($newSize*100/$oldSize,2) : 0));
							}
							else
							{
								$oldSize = filesize($file);
								$totalOldSize += $oldSize;
								$newSize = filesize($file);
								$totalNewSize += $newSize;
								array_push($successes,array("file"		=> $file,
															"old_size"	=> $oldSize,
														  	"new_size"	=> $newSize,
														  	"ratio"		=> ($oldSize > 0 && $newSize > 0) ? 100 - round($newSize*100/$oldSize,2) : 0));
							}							
							
						}
						catch (Exception $e)
						{
							array_push($errors,$file);
						}
					}
				}
			}
			# /Compress
			
			# Uncompress
			else
			{
				foreach($params as $param => $file)
				{
					if (SLS_String::startsWith($param,"file_compress_"))
					{
						$extension = (SLS_String::endsWith($file,"js")) ? "js" : "css";
						
						if (!file_exists($file.".uncompressed"))
							array_push($errors,$file.".uncompressed");
						else
						{
							$oldSize = filesize($file);
							$totalOldSize += $oldSize;														
							$newSize = filesize($file.".uncompressed");
							$totalNewSize += $newSize;
							@unlink($file);
							rename($file.".uncompressed",$file);
							array_push($successes,array("file"		=> $file,
														"old_size"	=> $oldSize,
													  	"new_size"	=> $newSize,
													  	"ratio"		=> ($oldSize > 0 && $newSize > 0) ? 100 - round($newSize*100/$oldSize,2) : 0));	
						}
					}
				}
			}
			# /Uncompress
			
			# Successes files
			$xml->startTag("successes");
			foreach($successes as $success)
			{			
				$xml->startTag("success");
					foreach($success as $key => $value)
						$xml->addFullTag($key,$value,true);
				$xml->endTag("success");
				
				$xml->startTag("total");
					$xml->addFullTag("old_size",$totalOldSize,true);
					$xml->addFullTag("new_size",$totalNewSize,true);
					$xml->addFullTag("ratio", ($totalOldSize > 0 && $totalNewSize > 0) ? 100 - round($totalNewSize*100/$totalOldSize,2) : 0,true);
				$xml->endTag("total");
			}
			$xml->endTag("successes");
			# /Successes files
			
			# Errors files
			$xml->startTag("errors");
			foreach($errors as $error)
				$xml->addFullTag("error",$error,true);
			$xml->endTag("errors");
			# /Errors files
			
			// Action wanted ('compress'||'uncompress')
			$xml->addFullTag("compress",$compress,true);
		}
		# /Step 2: files choice
		
		$xml->addFullTag("current_step",($this->_http->getParam("reload_type") == "" && $this->_http->getParam("reload_files") == "") ? "0" : (($this->_http->getParam("reload_files") == "true") ? "2" : "1"),true);
		$this->saveXML($xml);
	}
	
	/**
	 * Recursive files listing
	 * 
	 * @param array $files array of files
	 * @param string $root path
	 * @param string $extension extension to fetch
	 */
	protected function recursiveList($files,$root,$extension=".js")
	{
		if (SLS_String::endsWith($root,"/"))
			$root = SLS_String::substrBeforeLastDelimiter($root,"/");
		$handle = opendir($root);
		while (false !== ($file = readdir($handle)))
		{			
			if (is_dir($root."/".$file)  && substr($file, 0, 1) != ".")							
				$this->recursiveList($files,$root."/".$file,$extension);			
			if (!is_dir($root."/".$file) && substr($file, 0, 1) != ".")				
				if (SLS_String::endsWith($file,$extension))
					array_push($files,$root."/".$file);					
		}
		return $files;
	}
}