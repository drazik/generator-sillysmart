<?php
class SLS_BoEditType extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$sql = SLS_Sql::getInstance();		
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$errors = array();
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
		$xmlFk = new SLS_XMLToolbox($pathsHandle);
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
		$xmlType = new SLS_XMLToolbox($pathsHandle);
		$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
		$xmlFilter = new SLS_XMLToolbox($pathsHandle);
		
		// Get the table name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$column= $this->_http->getParam("column");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		$file  = ucfirst($db).".".SLS_String::tableToClass($table);
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		if ($sql->tableExists($table))
		{
			if ($this->_http->getParam("reload") == "true")
			{
				$columnWanted = $this->_http->getParam("column");
				$typeWanted = $this->_http->getParam("type");
				
				$result = $xmlType->getTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."']");
				
				// If an entry already exists in the XML, delete this record
				if (!empty($result))
				{
					$xmlTmp = $xmlType->deleteTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."']");					
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlTmp);
					$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
					$xmlType = new SLS_XMLToolbox($pathsHandle);
				}
								
				// If file type, check possible thumbs
				if ($typeWanted == "file")
				{
					$typeFile = $this->_http->getParam("file");
					$file_thumb = $this->_http->getParam("file_thumb");
					$multilang = $this->_http->getParam("multilanguage");
					$thumbs = array();
										
					$typeWanted = $typeWanted."_".$typeFile;
					
					if ($typeFile == "img" && !empty($file_thumb))
					{
						for($i=0 ; $i<10 ; $i++)
						{
							$width = $this->_http->getParam("width".$i);
							$height = $this->_http->getParam("height".$i);
							$suffix = $this->_http->getParam("suffix".$i);
							
							if (!empty($suffix) && (!empty($width) || !empty($height)))
								array_push($thumbs,array('width' => $width, 'height' => $height, 'suffix' => $suffix));
						}
					}
					
					$rules = "*|*|*";
					if ($typeFile == "img")
					{
						$settings = $this->_http->getParam("imgSettings");
						$ratio = str_replace(",",".",$settings["ratio"]);
						$minWidth = str_replace(",",".",$settings["min-width"]);
						$minHeight = str_replace(",",".",$settings["min-height"]);
						$ratio = (!is_numeric($ratio) || (is_numeric($ratio) && $ratio <= 0)) ? "*" : round($ratio,2);
						$minWidth = (!is_numeric($minWidth) || (is_numeric($minWidth) && $minWidth < 0)) ? "*" : round($minWidth,0);
						$minHeight = (!is_numeric($minHeight) || (is_numeric($minHeight) && $minHeight < 0)) ? "*" : round($minHeight,0);
						$rules = $ratio."|".$minWidth."|".$minHeight;
					}
					
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" rules="'.$rules.'" thumbs="'.str_replace('"','||#||',serialize($thumbs)).'" multilanguage="'.$multilang.'" type="'.$typeWanted.'" />';				
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());
				}
				else if ($typeWanted == "ip")
				{
					$type = $this->_http->getParam("ip");
					
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" type="'.$typeWanted."_".$type.'" />';				
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());
				}
				else if ($typeWanted == "complexity")
				{
					$complexity = $this->_http->getParam("complexity");
					$complexityMin = $this->_http->getParam("complexity_min");					
					$complexity = (empty($complexity)) ? array() : $complexity;
					if (!empty($complexityMin) && is_numeric($complexityMin) && $complexityMin >= 0)
						$complexity[] = "min".$complexityMin;
					$complexity = implode("|",$complexity);
					
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" rules="'.$complexity.'" type="complexity" />';			
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());					
				}			
				else if ($typeWanted == "num")
				{
					$type = $this->_http->getParam("num");
					
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" type="'.$typeWanted."_".$type.'" />';				
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());
				}
				// Else, it's email, url
				else
				{
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" type="'.$typeWanted.'" />';				
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());
				}
				
				// Update Model
				$fileName  = ucfirst($db).".".SLS_String::tableToClass($table).".model.php";
				$contentM = $this->getModelSource($table,$db);
				file_put_contents($this->_generic->getPathConfig("models").$fileName,$contentM);
				
				$controllers = $this->_generic->getTranslatedController("SLS_Bo","EditModel");
				$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller']."/name/".$db."_".$table);			
			}
			
			// Get generic object
			$this->_generic->useModel($table,$db,"user");
			$object = new $class();
			
			// Get current entry
			$attributes = array_shift($xmlType->getTagsAttributes("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$column."']",array("type","thumbs","multilanguage","hash","rules")));					
						
			// Get object's infos			
			$columnsP = $object->getParams();
			$pk = $object->getPrimaryKey();
			$multilanguage = $object->isMultilanguage();		
			$xml->startTag("model");
			$xml->addFullTag("table",$table,true);
			$xml->addFullTag("db",$db,true);
			$xml->addFullTag("class",$class,true);
			$xml->addFullTag("pk",$pk,true);
			$xml->addFullTag("multilanguage",($multilanguage) ? "true" : "false",true);
			$xml->addFullTag("column",$column,true);
			$xml->addFullTag("type",$attributes["attributes"][0]["value"],true);			
			$xml->startTag("thumbs");
			$thumbs = unserialize(str_replace("||#||",'"',$attributes["attributes"][1]["value"]));
			if (!empty($thumbs))
            {
	            for($i=0 ; $i<$count=count($thumbs) ; $i++)
	            {
	            	$xml->startTag("thumb");
	            	$xml->addFullTag("width",$thumbs[$i]["width"],true);
	            	$xml->addFullTag("height",$thumbs[$i]["height"],true);
	            	$xml->addFullTag("suffix",$thumbs[$i]["suffix"],true);	            	
	            	$xml->endTag("thumb");
	            }
            }
			$xml->endTag("thumbs");
			$xml->addFullTag("multilanguage",($attributes["attributes"][2]["value"] == "true") ? "true" : "false",true);
			$xml->addFullTag("hash",$attributes["attributes"][3]["value"],true);
			$xml->addFullTag("rules",$attributes["attributes"][4]["value"],true);
			$xml->endTag("model");
			
			$plugin = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configPlugins")."/plugins.xml"));
			$pluginImg = array_shift($plugin->getTags("//plugins/plugin[@code='image']"));
			$xml->addFullTag("plugin_img",empty($pluginImg) ? 'false' : 'true',true);
			$xml->addFullTag("plugin_url",$this->_generic->getFullPath("SLS_Bo","Plugins"),true);
		}
		else
		{
			$xml->addFullTag("error","Sorry this table doesn't exist anymore",true);
		} 
		
		$this->saveXML($xml);
	}
}