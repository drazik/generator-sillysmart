<?php
class SLS_BoAddType extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$sql = SLS_Sql::getInstance();		
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$errors = array();
		
		// Get the table name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
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
				
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
				$xmlFk = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
				$xmlType = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
				$xmlFilter = new SLS_XMLToolbox($pathsHandle);
				
				$result = $xmlType->getTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."']");
				
				// If an entry already exists in the XML, delete this record
				if (!empty($result))
				{
					$xmlTmp = $xmlType->deleteTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."']");					
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlTmp);
					$xmlType->refresh();
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
				// Else, it's email, url, color, uniqid, position, address
				else
				{
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" type="'.$typeWanted.'" />';				
					$xmlType->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlType->saveXML($this->_generic->getPathConfig("configSls")."/types.xml",$xmlType->getXML());
				}
				
				// Disable UserBo quick-edit feature on this column
				if ($typeWanted == "file")
				{
					$xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
					$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($db."_".$table)."']/columns/column[@name='".$columnWanted."']";
					$boExists = $xmlBo->getTag($boPath."/@allowEdit");
					if (empty($boExists))
						$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($db."_".$table)."']/columns/column[@name='".$columnWanted."']";
					$boExists = $xmlBo->getTag($boPath."/@allowEdit");
					if (!empty($boExists))
					{
						$xmlBo->setTagAttributes($boPath,array("allowEdit" => "false"));
						$xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml",$xmlBo->getXML());
						$xmlBo->refresh();	
					}
				}
				
				// Update model
				$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));			
			}
			
			// Get generic object
			$this->_generic->useModel($table,$db,"user");
			$object = new $class();
			
			// Get object's infos
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
			$xmlFk = new SLS_XMLToolbox($pathsHandle);
			$columnsP = $object->getParams();
			$pk = $object->getPrimaryKey();
			$multilanguage = $object->isMultilanguage();		
			$xml->startTag("model");
			$xml->addFullTag("table",$table,true);
			$xml->addFullTag("db",$db,true);
			$xml->addFullTag("class",$class,true);
			$xml->addFullTag("pk",$pk,true);
			$xml->addFullTag("multilanguage",($multilanguage) ? "true" : "false",true);
			$xml->startTag("columns");
			foreach($columnsP as $column => $value)
			{
				$res = $xmlFk->getTags("//sls_configs/entry[@tableFk='".$db."_".$table."' and @columnFk='".$column."']/@tablePk");				
				if ($object->getPrimaryKey() != $column && $column != "pk_lang" && empty($res))			
					$xml->addFullTag("column",$column,true);
			}		
			$xml->endTag("columns");
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