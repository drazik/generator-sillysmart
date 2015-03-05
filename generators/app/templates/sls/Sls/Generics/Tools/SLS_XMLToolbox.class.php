<?php
/**
 * Tool SLS_XMLToolbox - XML Treatment
 * 
 * @author Laurent Bientz 
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0  
 */
class SLS_XMLToolbox
{	
	private $_xml;
	private $_dom;
	
	/**
	 * Constructor
	 *
	 * @access public 
	 * @param mixed $xml false if you want to create an empty object without header, "" if you want an empty object with header or valid xml string
	 * @since 1.0
	 */
	public function __construct($xml="")
	{
		$this->_dom = new DOMDocument();
		
		if ($xml === false)
			$this->_xml = "";	
		else if ($xml == "")
			$this->_xml = '<?xml version="1.0" encoding="UTF-8"?>';		
		else
			$this->_xml = $xml;
		
		if (!empty($this->_xml) && $this->_xml != "<?xml version=\"1.0\" encoding=\"UTF-8\"?>")
			$this->_dom = $this->loadXML($this->_dom, $this->_xml);
	}
	
	/**
	 * Refresh XML content
	 * 
	 * @access public
	 * @since 1.0.9
	 */
	public function refresh()
	{
		$this->_dom = $this->loadXML($this->_dom, $this->_xml);
	}
	
	/**
	 * Start a XML's tag
	 * 
	 * @access public 
	 * @param string $tag the tag to start	 
	 * @param array $attributes <code>array("attributeName" => "value")</code>
	 * @example
	 * // Create a node tag
	 * 		$xml->startTag('node', array('id'=>5,'name'=>'tagname'));
	 * // Will produce 
	 * 		<node id="5" name="tagname">
	 * @see SLS_XMLToolbox::endTag
	 * @see SLS_XMLToolbox::addFullTag
	 * @since 1.0
	 */
	public function startTag($tag, $attributes=array())
	{
		$tag = SLS_String::utf8Encode($tag);
		$xml = "<".$tag;
		if (count($attributes) > 0)
		{
			foreach ($attributes as $key=>$value)
			{
				$key = SLS_String::utf8Encode($key);
				$value = SLS_String::utf8Encode($value);
				$xml .= " ".$key."=\"".$value."\"";
			}
		}
		
		$xml .= ">";
		$this->_xml .= $xml;
	}
	
	/**
	 * Finish a XML's tag
	 * 
	 * @access public 
	 * @param string $tag the tag to finish
	 * @example 
	 * 		$xml->endTag('node')
	 * // Will produce
	 * 		</node>
	 * @see SLS_XMLToolbox::endTag
	 * @see SLS_XMLToolbox::addFullTag
	 * @since 1.0
	 */
	public function endTag($tag)
	{
		$tag = SLS_String::utf8Encode($tag);
		$this->_xml .= "</".$tag.">";
	}
	
	/**
	 * Add a value to a given tag
	 *
	 * @access public 
	 * @param string $value value to add
	 * @param bool $cdata true if you want to encapsulate data into cdata section
	 * @example 
	 * 		$xml->addValue('value', true); 	// [CDATA[value]]
	 * 		$xml->addValue('value');		// value
	 * @since 1.0
	 */
	public function addValue($value, $cdata=false)
	{
		$value = SLS_String::utf8Encode($value);
		
		if ($cdata)		
			$this->_xml .= "<![CDATA[".$value."]]>";		
		else 		
			$this->_xml .= $value;
	}
	
	/**
	 * Construct a full XML Tag
	 * 
	 * @access public 
	 * @param string $tag the tag
	 * @param string $content tag's content
	 * @param bool $cdata  true if you want to encapsulate data into cdata section
	 * @param array $attributes <code>array("arttributeName" => "attributeValue")</code>
	 * @example 
	 * 		$xml->addFullTag('node', 'value', true, array('id'=>5,'name'=>'att_name'));
	 * // Will produce
	 * 		<node id="5" name="att_name">[CDATA[value]></node>
	 * @see SLS_XMLToolbox::startTag
	 * @see SLS_XMLToolbox::endTag
	 * @since 1.0
	 */
	public function addFullTag($tag,$content,$cdata=false,$attributes=array())
	{
		$attr = " ";
		if (!empty($attributes))
		{
			foreach ($attributes as $key=>$attribute)
			{
				$key = SLS_String::utf8Encode($key);
				$attribute = SLS_String::utf8Encode($attribute);
				$attr .= $key."=\"".$attribute."\" ";
			}
		}
		$attr = substr($attr, 0, strlen($attr)-1);
		$content = SLS_String::utf8Encode($content);
		if (!$cdata)		
			$this->_xml .= "<".$tag.$attr.">".$content."</".$tag.">";		
		else
			$this->_xml .= "<".$tag.$attr."><![CDATA[".$content."]]></".$tag.">";					
	}
	
	/**
	 * Add a XML flow to a given node
	 * 
	 * @access public 
	 * @deprecated
	 * @param string $tag the node to attach
	 * @param string $content the content to add
	 * @see SLS_XMLToolbox::appendXMLNode
	 * @since 1.0
	 */
	public function appendXML($tag,$content)
	{
		if (substr($tag, 0, 2) != "//")
			SLS_Tracing::addTrace(new Exception("You want to use the function SLS_XMLToolbox::appendXML() but this function is deprecated. So it's an alias of SLS_XMLToolbox::appendXMLNode(). To use this function, please check that your first argument is a xpath Query"));
		else
			$this->appendXMLNode($tag, $content);
		return;
	}	
	
	/**
	 * Add a XML flow to a given node
	 * 
	 * @access public 
	 * @param string $xPathTag the node to attach
	 * @param string $content the content to add 
	 * @param int $nb number of nodes found
	 * @param string $insertMode should be inside | before | after
	 * @example
	 * 		<node1>
	 * 			<node2 />
	 * 		</node1>
	 * 		$xml->appendXMLNode('//node1[0]/node2[0], '<tag>value</tag>', 1, 'after');
	 * 
	 * // Will produce
	 * 		<node1>
	 * 			<node2 />
	 * 			<tag>value</tag>
	 * 		</node1>
	 * @example		
	 * 		<node1>
	 * 			<node2 />
	 * 			<node2 />
	 * 		</node1>
	 * 		$xml->appendXMLNode('//node1[0]/node2, '<tag>value</tag>', 2, 'after');
	 * 
	 * // Will produce
	 * 		<node1>
	 * 			<node2 />
	 * 			<tag>value</tag>
	 * 			<node2 />
	 * 			<tag>value</tag>
	 * 		</node1>
	 * @since 1.0
	 */
	public function appendXMLNode($xPathTag,$content,$nb=1,$insertMode="inside")
	{
		if ($insertMode != "inside" && $insertMode != "after" && $insertMode != "before")
			SLS_Tracing::addTrace(new Exception("Wrong parameter : '".$insertMode."' to SLS_XMLToolbox::appendXML(). Must be 'inside', 'before' or 'after"));
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom = $this->loadXML($dom, $this->_xml);
		$xpath = new DOMXPath($dom);
		$tagsFound = $xpath->query($xPathTag);
		$count = 0;
		$content = "<tag_separate>".$content."</tag_separate>";		
		$nodes = array();	
		
		try { $simpleXML = simplexml_load_string($content); }
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception("Error during XML Parsing"), true, "<h2>".$e->getMessage()."</h2><div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:xml\">".htmlentities(SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($content, "</tag_separate>"), "<tag_separate>"), ENT_QUOTES)."</pre></div>");
		}
		try { $newContent = dom_import_simplexml($simpleXML); }
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception("Error during XML Parsing"), true, "<h2>".$e->getMessage()."</h2><div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:xml\">".htmlentities(SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($content, "</tag_separate>"), "<tag_separate>"), ENT_QUOTES)."</pre></div>");
		}
		$realContents = $newContent->childNodes;		
		if (is_object($realContents) && get_class($realContents) == "DOMNodeList")
		{
			foreach ($realContents as $realContent)
				array_push($nodes, $dom->importNode($realContent, true));
	
			foreach ($tagsFound as $tag)
			{
				if ($count < $nb)
				{
					foreach ($nodes as $domNode)
					{
						if ($insertMode == "inside")
							$tag->insertBefore($domNode);
						else if ($insertMode == "after")
							$tag->parentNode->insertBefore($domNode, $tag->nextSibling);
						else if ($insertMode == "before")
							$tag->parentNode->insertBefore($domNode, $tag->previousSibling);
					}
				}
				else 
					break;
				$count++;
			}
		}
		$this->_xml = $dom->saveXML();	
		return $this->_xml;
	}	
	
	/**
	 * Count the number of hits for a tag
	 * 
	 * @access public 
	 * @param string $tag the tag name to count
	 * @example
	 * 		<node_root>
	 * 			<node />
	 * 			<node />
	 * 		</node_root>
	 * 
	 * 		print($xml->countTag('node'));
	 * // Will print 2
	 * 
	 * @return int $nb the number of hits
	 * @since 1.0
	 */
	public function countTag($tag, $xml="")
	{
		(empty($xml)) ? $xml = $this->_xml : false;
		return substr_count($xml,"</".$tag.">");
	}
		
	/**
	 * Get contents from each tag	 
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @return array $values 
	 * @example 
	 * 		<node>
	 * 			<node1>value1</node1>
	 * 			<node1>value2</node1>
	 * 		</node>
	 * 		
	 * 		$xml->getTags("//node/node1");
	 * // Will return
	 * 		array( 0 => 'value1', 1 => 'value2' );
	 * 
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @see SLS_XMLToolbox::getTag
	 * @since 1.0
	 */
	public function getTags($path)
	{
		$values = array();
		$results = $this->returnXpathQuery($path);
		for ($i=0 ; $i<$results->length ; $i++)		
			array_push($values,(string)$results->item($i)->nodeValue);		
		
		return $values;
	}
	
	/**
	 * Get contents from each tag in associative array 
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @return array $values 
	 * @example 
	 * 		<node>
	 * 			<node1>value1</node1>
	 * 			<node1>value2</node1>
	 * 		</node>
	 * 		
	 * 		$xml->getTags("//node/node1");
	 * // Will return
	 * 		array(0 => array('node1' => 'value1'), 1 => array('node2' => 'value2'));
	 * 
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @see SLS_XMLToolbox::getTag
	 * @since 1.1
	 */
	public function getTagsAssoc($path)
	{
		$values = array();
		$results = $this->returnXpathQuery($path);		
		for ($i=0 ; $i<$results->length ; $i++)
		{
			
			$realContent = $results->item($i)->childNodes;
			//if (is_object($realContents) && get_class($realContents) == "DOMNodeList")
			
			$values[] = array("key" => (string)$results->item($i)->nodeName, "value" => (string)$results->item($i)->nodeValue, "children" => count($results->item($i)->childNodes->length));
		}
		return $values;
	}
	
	/**
	 * Same of getTags but return onlny first result
	 *
	 * @param string $xpath xPath way
	 * @return mixed
	 * @see SLS_XMLToolbox::getTags
	 * @since 1.0
	 */
	public function getTag($xpath)
	{
		return array_shift($this->getTags($xpath));
	}
	
	/**	 
	 * Execute a xPath query on the XML
	 *
	 * @access public 
	 * @param string $xpathQuery xPath query to execute
	 * @return mixed
	 * @since 1.0
	 */
	public function returnXpathQuery($xpathQuery)
	{
		#$dom = new DOMDocument();
		if ($this->_xml == "<?xml version=\"1.0\" encoding=\"UTF-8\"?>")
			SLS_Tracing::addTrace(new Exception("Your XML is not complete"));		
		#$dom = $this->loadXML($dom, $this->_xml);
		#$xpath = new DOMXPath($dom);
		$xpath = new DOMXPath($this->_dom);
		
		return $xpath->query($xpathQuery);
	}
	
	/**
	 * Get tags by attribute
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return string
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @see SLS_XMLToolbox::getTagsByAttributes
	 * @since 1.0
	 */
	public function getTagsByAttribute($path, $attributeName, $attributeValue=null)
	{
		(is_null($attributeValue)) ? $xpath = $path."[@".$attributeName."]" : $xpath = $path."[@".$attributeName."='".$attributeValue."']";
		$stringOutput = "";
		$results = $this->returnXpathQuery($xpath);
		for ($i=0 ; $i<$results->length ; $i++)		
		{
			$el = simplexml_import_dom($results->item($i));
			$stringOutput .= $el->asXML();
		}
		return $stringOutput;
	}
	
	/**
	 * Get tags by attribute
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return string
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @see SLS_XMLToolbox::getTagsByAttribute
	 * @since 1.0
	 */
	public function getTagsByAttributes($path, $attributeName=array(), $attributeValue=array())
	{
		if (count($attributeName) != count($attributeValue))
			return SLS_Tracing::addTrace(new Exception("Error - AttributeName & AttributeValue must have the same dimension"));
		
		$xpath = $path.'[';
		for($i=0 ; $i<$count=count($attributeName) ; $i++)
		{
			if ($i>0)
				$xpath .= 'and ';
			$xpath .= '@'.$attributeName[$i]."='".$attributeValue[$i]."' ";
		}
		$xpath .= ']';
				
		$stringOutput = "";
		$results = $this->returnXpathQuery($xpath);
		for ($i=0 ; $i<$results->length ; $i++)		
		{
			$el = simplexml_import_dom($results->item($i));
			$stringOutput .= $el->asXML();
		}
		return $stringOutput;
	}
		
	/**
	 * Get the current XML
	 *
	 * @access public 
	 * @param string $type the type of xml part wanted (null for all)
	 * @return string $xml the XML
	 * @since 1.0
	 */
	public function getXML($type=null) 
	{
		if ($type == null)
			return $this->_xml;
		else if ($type == 'noHeader')
			return (substr(trim($this->_xml), 0, 2) == "<?") ? SLS_String::substrAfterFirstDelimiter($this->_xml, ">") : $this->_xml;
	}
	
	/**
	 * Get an attribute for a node	 
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @param string $attributeName the attribute name
	 * @return array $values
	 * @see SLS_XMLToolbox::getTagsAttributes
	 * @since 1.0
	 */
	public function getTagsAttribute($path, $attributeName) 
	{
		$values = array();
		#$dom = new DOMDocument();
		#$dom = $this->loadXML($dom, $this->_xml);
		#$xpath = new DOMXPath($dom);
		
		$xpath = new DOMXPath($this->_dom);
		
		$results = $xpath->query($path);

		for ($i=0 ; $i<$results->length ; $i++) 
		{
			$values[$i]['value'] = (string)$results->item($i)->nodeValue;
			$values[$i]['attribute'] = (string)$results->item($i)->getAttribute($attributeName);
		}
		return $values;
	}
	
	/**
	 * Get all the attributes for a node	 
	 *
	 * @access public 
	 * @param string $path xPath way
	 * @param array $attributeNames attribute
	 * @return array $values
	 * @see SLS_XMLToolbox::getTagsAttribute
	 * @since 1.0
	 */
	public function getTagsAttributes($path, $attributeNames) 
	{
		$values = array();
		#$dom = new DOMDocument();
		#$dom = $this->loadXML($dom, $this->_xml);
		#$xpath = new DOMXPath($dom);
		
		$xpath = new DOMXPath($this->_dom);
		
		$results = $xpath->query($path);

		for ($i=0 ; $i<$results->length ; $i++) 
		{
			$values[$i]['value'] = (string)$results->item($i)->nodeValue;
			$values[$i]['attributes'] = array();			
			foreach($attributeNames as $attributeName)
			{
				$arrayTmp = array();
				$arrayTmp['key'] = $attributeName;
				$arrayTmp['value'] = (string)$results->item($i)->getAttribute($attributeName);
				array_push($values[$i]['attributes'],$arrayTmp);
			}
		}
		return $values;
	}

	/**
	 * Get the father node
	 *
	 * @access public 
	 * @param string $child child node
	 * @param int $index number of child
	 * @return string father name
	 * @since 1.0
	 */
	public function getParentTagName($child, $index) 
	{
		#$dom = new DOMDocument();
		#$dom = $this->loadXML($dom, $this->_xml);
		#$node = $dom->getElementsByTagName($child)->item($index);
		
		$node = $this->_dom->getElementsByTagName($child)->item($index);
		
		return $node->parentNode->tagName;
	}
	
	/**
	 * Load the xml
	 *
	 * @param DOMDocument $dom
	 * @param string $xml the xml string to load
	 * @return DOMDocument $dom the DOMDocument instance
	 * @since 1.0
	 */
	private function loadXML($dom, $xml)
	{
		if (@$dom->loadXML($xml) === false)
			SLS_Tracing::addTrace(new Exception("Incorrect XML"), true, "<h2>Wrong XML :</h2><div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:xml\">".htmlentities($xml, ENT_QUOTES)."</pre></div>");
		return $dom;
	}
	
	/**
	 * Get the index father's of one node	 
	 *
	 * @access public 
	 * @param string $child child node
	 * @param int $index number of child
	 * @return int $index father index
	 * @see SLS_XMLToolbox::getParentTagName
	 * @since 1.0
	 */
	public function getParentTagIndex($child, $index) 
	{
		$parentName = $this->getParentTagName($child, $index);
		
		#$dom = new DOMDocument();
		#$dom = $this->loadXML($dom, $this->_xml);
		
		$items = $this->_dom->getElementsByTagName($parentName);
		
		$childIndex = 0;
		$i = 0;
		$result = -1;
		foreach($items as $item) 
		{
			$childs = $item->getElementsByTagName($child);
			foreach($childs as $value) 
			{
				if ($childIndex == $index) 
				{
					$result = $i;
				}
				$childIndex++;
			}
			$i++;
		}		
		return $result;
	}
	
	/**
	 * Replace a content by another
	 *
	 * @access public 
	 * @param string $oldPart old part
	 * @param string $newPart new part
	 * @since 1.0
	 */
	public function replace($oldPart,$newPart)
	{		
		$this->_xml = str_replace(array("\n","\t"),array("",""),$this->_xml);
		$this->_xml = str_replace($oldPart,$newPart,$this->_xml);				
	}
	
	/**
	 * Save xml
	 *
	 * @access public 
	 * @param string $path the file to save
	 * @param string $xml the XML flow
	 * @since 1.0
	 */
	public function saveXML($path,$xml="")
	{
		if (empty($xml))
			$xml = $this->_xml;
		
		file_put_contents($path,$xml,LOCK_EX);
	}
	
	/**
	 * Delete the content together two bounds
	 *
	 * @access public 
	 * @param string $tag
	 * @param string $xml
	 * @return string $xml
	 * @since 1.0
	 */
	public function deleteContentTag($tag, $xml="")
	{		
		$ext = true;
		if (empty($xml))
		{
			$this->setTag($tag, "", false);
			$return = $this->_xml;
		}
		else 
		{
			if ($this->countTag($tag, $xml) == 1)
				$return = SLS_String::substrBeforeFirstDelimiter($xml, "<".$tag.">")."<".$tag."></".$tag.">".SLS_String::substrAfterLastDelimiter($xml, "</".$tag.">");
				
			if ($this->countTag($tag, $xml) < 1)
				return false;
		}	
		return $return;
	}
	
	/**
	 * Permit to set a existing Tag
	 *
	 * @access public 
	 * @param string $xPathTag xPath way
	 * @param mixed $value the value
	 * @param bool $cdata true if you want to encapsulate data into cdata section
	 * @see SLS_XMLToolbox::overwriteTags
	 * @see SLS_XMLToolbox::setTagAttributes
	 * @see SLS_XMLToolbox::deleteTagAttribute
	 * @see SLS_XMLToolbox::deleteTags
	 * @since 1.0
	 */
	public function setTag($xPathTag, $value, $cdata=true)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom = $this->loadXML($dom, $this->_xml);
		$newPortion = ($cdata == true) ? str_replace("&","&amp;",str_replace("&amp;","&","#<#![CDATA[".str_replace(array(0=>"<", 1=>">"), array(0=>"#<#", 1=>"#>#"), $value)."]]#>#")) : str_replace("&","&amp;",str_replace("&amp;","&",str_replace(array(0=>"<", 1=>">"), array(0=>"#<#", 1=>"#>#"), $value)));		
		$xpath = new DOMXPath($dom);
		$tagsFound = $xpath->query($xPathTag);
		foreach ($tagsFound as $tag)
		{
			$childs = $tag->childNodes;			
			foreach ($childs as $child)
				$tag->removeChild($child);				
			$tag->nodeValue = $newPortion;
		}
		$this->_xml = str_replace(array(0=>"#&lt;#", 1=>"#&gt;#"), array(0=>"<", 1=>">"), $dom->saveXML());
	}
	
	/**
	 * Overwrite an existing tag. If this one doesn't exist, create it
	 *
	 * @access public 
	 * @param string $xpath the xPath way
	 * @param string $newXml the value
	 * @see SLS_XMLToolbox::setTag
	 * @see SLS_XMLToolbox::setTagAttributes
	 * @see SLS_XMLToolbox::deleteTagAttribute
	 * @see SLS_XMLToolbox::deleteTags
	 * @since 1.0
	 */
	public function overwriteTags($xpath, $newXml)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		try { $domNode = simplexml_load_string($newXml); }
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception("Error during XML Parsing"), true, "<h2>".$e->getMessage()."</h2><div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:xml\">".htmlentities($newXml, ENT_QUOTES)."</pre></div>");
		}
		$xpath = (substr($xpath, strlen($xpath)-1) == "/") ? $xpath : $xpath."/";
		$nodeName = $domNode->getName();
		$nodeExist = $this->getTags($xpath.$nodeName);
		if (count($nodeExist) != 0)
			$this->deleteTags($xpath.$nodeName);
		
		$dom = $this->loadXML($dom, $this->_xml);
		$xpathParent = new DOMXPath($dom);
		$tagsParent = $xpathParent->query(substr($xpath, 0, strlen($xpath)-1));
		
		$node = dom_import_simplexml($domNode);
		$node = $dom->importNode($node, true);
		$tagsParent->item(0)->appendChild($node);
			
		$this->_xml = $dom->saveXML();
	}
	
	/**
	 * Set Tag Attributes 
	 *
	 * @access public 
	 * @param string $xPathTag the xPath way
	 * @param array $attributeName 
	 * <code>
	 * array(
	 * 		'attributeKey' => 'attributeValue',
	 * 		'attributeKeyToDelete' => null
	 * )
	 * </code>
	 * @see SLS_XMLToolbox::setTag
	 * @see SLS_XMLToolbox::overwriteTags
	 * @see SLS_XMLToolbox::deleteTagAttribute
	 * @see SLS_XMLToolbox::deleteTags
	 * @since 1.0
	 */
	public function setTagAttributes($xPathTag, $attributesKeysValues)
	{
		$modify = false;
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		if (substr(trim($this->_xml), 0, 2) != "<?")
		{
			$modify = true;
			$xml = '<?xml version="1.0" encoding="UTF-8"?><root>'.$this->_xml.'</root>';
			$xPathTag = (substr($xPathTag, 0, 2) == "//") ? "//root/".substr($xPathTag, 2) : "//root/".$xPathTag;
		}
		else 
			$xml = $this->_xml;
			
		$dom = $this->loadXML($dom, $xml);
		$xpath = new DOMXPath($dom);
		$tagsFound = $xpath->query($xPathTag);
		
		foreach ($tagsFound as $tag)
		{
			$attributes = $tag->attributes;
			foreach ($attributes as $attribute)
			{
				if (array_key_exists($attribute->name, $attributesKeysValues))
				{
					$attN = $attribute->name;
					$attV = $attributesKeysValues[$attN];
					$tag->removeAttribute($attN);
					if (!is_null($attV))
						$tag->setAttribute($attN, $attV);
					unset($attributesKeysValues[$attN]);
				}
			}
			foreach ($attributesKeysValues as $attN=>$attV)
			{
				if (!is_null($attV))
						$tag->setAttribute($attN, $attV);
			}
		}
		$xml = $dom->saveXML();
		if ($modify === true)
			$this->_xml = SLS_String::substrAfterFirstDelimiter(SLS_String::substrBeforeLastDelimiter($xml, "</root>"), "<root>");
		else
			$this->_xml = $xml;
	}
	
	/**
	 * Delete a Tag attribute
	 *
	 * @access public 
	 * @param string $xpath the xPath way
	 * @param string $attributeName the name of the attribute
	 * @return bool $deleted true if delete, else false
	 * @see SLS_XMLToolbox::setTag
	 * @see SLS_XMLToolbox::overwriteTags
	 * @see SLS_XMLToolbox::setTagAttributes
	 * @see SLS_XMLToolbox::deleteTags
	 * @since 1.0
	 */
	public function deleteTagAttribute($xpath, $attributeName)
	{
		$delete = array($attributeName => null);
		$this->setTagAttributes($xpath, $delete);
		return true;
	}
	
	/**
	 * Delete a tag
	 *
	 * @access public 
	 * @param string $xPathTag the xPath way
	 * @return string $xml the xml string modified
	 * @see SLS_XMLToolbox::setTag
	 * @see SLS_XMLToolbox::overwriteTags
	 * @see SLS_XMLToolbox::setTagAttributes
	 * @see SLS_XMLToolbox::deleteTagAttribute
	 * @since 1.0
	 */
	public function deleteTags($xPathTag, $nb=0)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom = $this->loadXML($dom, $this->_xml);
		$xpath = new DOMXPath($dom);
		$tagsFound = $xpath->query($xPathTag);
		$count = 0;
		foreach ($tagsFound as $tag)
		{
			if ($nb != 0)
				if ($count == $nb)
					break;
					
			$parent = $tag->parentNode;
			$parent->removeChild($tag);
			$count++;
		}
		$this->_xml = $dom->saveXML();
		return $this->_xml;
	}
	
	
	/**
	 * Get a node as String 
	 *
	 * @access public 
	 * @param string $xpath the xPath way
	 * @return string $string the value
	 * @since 1.0
	 */
	public function getNode($xpathTag)
	{
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom = $this->loadXML($dom, $this->_xml);
		$xpath = new DOMXPath($dom);
		$tagsFound = $xpath->query($xpathTag);
		$string = "";
		foreach ($tagsFound as $tag)
		{
			$simple = simplexml_import_dom($tag);
			$string .= $simple->asXML();
		}
		return $string;
	}
	
	/**
	 * Valid the current XML string
	 *
	 * @access public 
	 * @return bool $valid true if valid, else false
	 * @since 1.0
	 */
	public function validate()
	{
		$dom = new DOMDocument();
		$dom = $this->loadXML($dom, $this->_xml);
		return ($dom->validate()) ? true : false;		
	}
	
	/**
	 * Check if a given node has some childs
	 *
	 * @access public
	 * @param string $xpath the xPath way
	 * @return int $nb the number of childs
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @since 1.0
	 */
	public function countChilds($xpath)
	{
		$query = $this->returnXpathQuery($xpath."/node()");
		$nb = 0;
		for($i=0;$i<$query->length;$i++)
		{
			if (is_a($query->item($i), "DOMElement"))
				$nb++;
		}
		return $nb;
	}
	
	/**
	 * Return childs tag name
	 *
	 * @access public
	 * @param string $xpath the xPath way
	 * @param bool $justNames if set to true, return an array containing all names. if set to false, return all xpath node like 
	 * <code>
	 * array(
	 * 		0 => "node[1]",
	 *		1 => "node2[1]",
	 *		2 => "node[2]"
	 * )
	 * </code>
	 * @return array $arrayChilds the childs
	 * @see SLS_XMLToolbox::returnXpathQuery
	 * @see SLS_XMLToolbox::countChilds
	 * @since 1.0
	 */
	public function getChilds($xpath, $justNames=false)
	{
		$arrayChilds = array();
		$childsCount = array();
		if ($this->countChilds($xpath) == 0)
			return array();
		$query = $this->returnXpathQuery($xpath."/node()");
		$nb = 0;
		for($i=0;$i<$query->length;$i++)
		{
			if (is_a($query->item($i), "DOMElement"))
			{
				if ($justNames)
				{
					if (!in_array($query->item($i)->tagName, $arrayChilds))
						$arrayChilds[] = $query->item($i)->tagName;
				}
				else 
				{
					if (!key_exists($query->item($i)->tagName, $childsCount))
					{
						$arrayChilds[] = $query->item($i)->tagName."[1]";
						$childsCount[$query->item($i)->tagName] = 1;
					}
					else 
					{
						$arrayChilds[] = $query->item($i)->tagName."[".($childsCount[$query->item($i)->tagName]+1)."]";
						$childsCount[$query->item($i)->tagName]++;
					}
				}
			}
		}
		return $arrayChilds;
	}
}
?>