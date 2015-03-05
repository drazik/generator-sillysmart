<?php
/**
 * Tool SLS_XMLToArray - A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * @access static
 * @author Lalit Patel
 * @author Laurent Bientz
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.1
 * 
 * See SLS_XMLToArray: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (07 Dec 2011)
 * Version: 0.2 (04 Mar 2012)
 * 			Fixed typo 'DomDocument' to 'DOMDocument'
 *
 * Usage:
 *       $array = SLS_XMLToArray::createArray($xml);
 */
class SLS_XMLToArray
{

    private static $xml = null;
	private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * 
     * @access public static
     * @param $version
     * @param $encoding
     * @param $format_output
     * @since 1.1
     */
    public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true)
    {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
    }

    /**
     * Convert an XML to Array
     * 
     * @access public static
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - aray to be converterd
     * @return DOMDocument
     * @since 1.1
     */
    public static function &createArray($input_xml)
    {
        $xml = self::getXMLRoot();
		if(is_string($input_xml))
		{
			$parsed = $xml->loadXML($input_xml);
			if(!$parsed) 
			{
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		} 
		else 
		{
			if(get_class($input_xml) != 'DOMDocument') 
			{
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    /**
     * Convert an Array to XML
     * 
     * @access private static
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @return mixed
     * @since 1.1
     */
    private static function &convert($node) 
    {
		$output = array();
		
		switch ($node->nodeType) 
		{
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = true;
				$output['@value'] = trim($node->textContent);
				$output['@xpath'] = self::calculateXPath($node);
				break;

			case XML_TEXT_NODE:
				$output['@cdata'] = false;
				$output = trim($node->textContent);

				break;

			case XML_ELEMENT_NODE:
				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) 
				{
					$child = $node->childNodes->item($i);
					$xpath = self::calculateXPath($child);
					$v = self::convert($child);
					
					if(isset($child->tagName)) 
					{
						$t = $child->tagName;
						
						// assume more nodes of same kind are coming
						if(!isset($output[$t])) 
						{
							$output[$t] = array();
						}
						if(!is_array($v)) 
						{
							$v = array('@cdata' => false, '@value' => $v, '@xpath' => $xpath);
						}
						$output[$t][] = $v;
					} 
					else 
					{
						//check if it is not an empty text node
						if($v !== '') 
						{
							$output = $v;
						}
					}
				}
				if(is_array($output)) 
				{
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) 
					{
						if(is_array($v) && count($v)==1) 
						{
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) 
					{
						//for empty nodes
						$output = '';
					}
				}
				// loop through the attributes and collect them
				if($node->attributes->length) 
				{
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) 
					{
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					
					if(!is_array($output)) 
					{
						$output = array('@cdata' => false, '@value' => $output, '@xpath' => $xpath);
					}
					$output['@attributes'] = $a;
				}
				else
				{
					if (!is_array($output))
						$output = array('@cdata' => false, '@value' => $output, '@xpath' => $xpath);
					$output['@attributes'] = array();
				}
				break;
		}
		return $output;
    }

    /**
     * Calculate xPath of the given DOMNode
     * 
     * @access public static
     * @param DOMNode $node
     * @return string $xpath xPath
     * @since 1.1
     */
	public static function calculateXPath(DOMNode $node)
	{
		// Easy
		if (method_exists($node,'getNodePath'))
			return '/'.str_replace('/text()','',$node->getNodePath());
		
		// Let's go...
	    $q     = new DOMXPath($node->ownerDocument);
	    $xpath = '';
	    do
	    {
	    	$prevSibling = $q->query('preceding-sibling::*[name()="' . $node->nodeName . '"]', $node)->length;
	    	$nextSibling = $q->query('following-sibling::*[name()="' . $node->nodeName . '"]', $node)->length;
	        $position = 1 + $prevSibling;
	        if (!SLS_String::startsWith($node->nodeName,"#"))
	        {
	        	$xpathS    = '/' . $node->nodeName;
	        	if ($prevSibling > 0 || $nextSibling > 0)
	        		$xpathS .= '[' . $position . ']';
	        	$xpath = $xpathS.$xpath;
	        }
	        $node     = $node->parentNode;
	    }
	    while (!$node instanceof DOMDocument);
	    return '/'.$xpath;
	}
    
    /**
     * Get the root XML node, if there isn't one, create it.
     * 
     * @access private static
     */
    private static function getXMLRoot()
    {
        if(empty(self::$xml)) 
        {
            self::init();
        }
        return self::$xml;
    }
}
?>