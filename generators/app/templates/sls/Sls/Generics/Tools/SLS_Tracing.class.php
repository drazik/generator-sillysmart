<?php
/**
 * Tool SLS_Tracing - Application Logging
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0 
 */ 
class SLS_Tracing 
{	
	static $_exceptionThrown = false;
	
	/**
	 * Add a trace in the application :
	 * - If server is in production, we write into the logs
	 * - If server is in development, output is used	 
	 *
	 * @access public static
	 * @param Exception $exception the raised exception contained the message to trace
	 * @param bool $stack flag if we want the stacktrace call
	 * @param string $complementary html complementary description
	 * @since 1.0
	 */
	public static function addTrace($exception,$stack=true,$complementary="")
	{		
		$generic = SLS_Generic::getInstance();
		$e = $exception->getTrace();	
					
		// If logs
		if ($generic->isProd())
		{
			$nbOccurencesFiles = 0;
			$nbMaxLines = 2000;
			$directory = date("Y-m");
			$fileName = date("Y-m-d");
			$filePath = ""; 
			
			$traces = SLS_Tracing::stackTracing($e,false);
						
			// If month directory doesn't exists, create it			
			if (!file_exists($generic->getPathConfig("logs").$directory))			
				mkdir($generic->getPathConfig("logs").$directory,0777);					
			
			// Count the number of hits of log file			
			$handle = opendir($generic->getPathConfig("logs").$directory);
			while (false !== ($file = readdir($handle)))			
				if (SLS_String::startsWith($file,$fileName))
					$nbOccurencesFiles++;
	    	closedir($handle);
	    
	    	// If the current file log doesn't exists, create it		    
		    if ($nbOccurencesFiles == 0)
		    {
		    	touch($generic->getPathConfig("logs").$directory."/".$fileName."_0.log");
		    	$filePath = $generic->getPathConfig("logs").$directory."/".$fileName."_0.log";
		    }
		    // Else, locate it
		    else	    
		    	$filePath = $generic->getPathConfig("logs").$directory."/".$fileName."_".($nbOccurencesFiles-1).".log";
		    
		    // If the max number of lines has been reach, increase the file log version		    
		    if (SLS_String::countLines(file_get_contents($filePath)) >= $nbMaxLines)
		    {
		    	touch($generic->getPathConfig("logs").$directory."/".$fileName."_".$nbOccurencesFiles.".log");
		    	$filePath = $generic->getPathConfig("logs").$directory."/".$fileName."_".$nbOccurencesFiles.".log";
		    }
		    
		    // Then, if and only if the file log has been located, increased or created : write into the logs		    
		    if (is_file($filePath))
		    {
		    	$oldContentLog = file_get_contents($filePath);
		    	$newContentLog = date("Y-m-d H:i:s").' - '.$exception->getMessage()."\n";
		    	
		    	for ($i=0 ; $i<count($traces) ; $i++)	    	
		    		$newContentLog .= "    ".trim($traces[$i]["call"]." ".$traces[$i]["file"])."\n";
		    		
		    	file_put_contents($filePath,$newContentLog.$oldContentLog,LOCK_EX); 
		    }	    
		}
		
		// Else output
		else 
		{	  
			$traces = SLS_Tracing::stackTracing($e,true);  	
			if (!SLS_Tracing::$_exceptionThrown)
			{
		    	echo 
					'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n".
					'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">'."\n".
						'<head>'."\n".
						'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n";
				SLS_Tracing::makeCss();
				SLS_Tracing::loadMootools();
				echo 
						'</head>'."\n".
						'<body>'."\n";
			}
			echo 
						'<div class="mainTitle">'.date("Y-m-d H:i:s").' - '.$exception->getMessage().''."\n".
						'<div>in '.SLS_Tracing::getTraceInfo($e[0],true).'</div></div>'."\n";
			
			if ($stack)
			{
				echo '<div class="stackTrace">'."\n";
				for($i=0 ; $i<count($traces) ; $i++)
				{
					$style = ($i<(count($traces)-1)) ? 'border-bottom:1px dotted darkgray;padding-bottom:2px;' : '';
					echo '<div style="'.$style.'">'.$traces[$i]["call"].'<span>'.$traces[$i]["file"].'</span></div>'."\n";
				}		
				echo '</div>'."\n";
			}
			if ($complementary != "")
			{
				echo $complementary;
			}
			
			//echo 
					'</body>'."\n".
			 	'</html>'."\n";
			SLS_Tracing::$_exceptionThrown = true;
		}
	}
	
	/**
	 * Display All Traces
	 *
	 * @access public static
	 * @since 1.0
	 */
	public static function displayTraces()
	{
		if (SLS_Tracing::$_exceptionThrown && !SLS_Generic::getInstance()->isProd())
		{
			echo 
					'</body>'."\n".
			 	'</html>'."\n";
			die();
		}
	}
	
	/**
	 * Get the stack tracing of the exception	 
	 *
	 * @access public static
	 * @param array $trace trace array recovered at the Exception raise
	 * @param bool $html if we want a HTML render (version dev)
	 * @return array $stack strack tracing shaped (html or plain)
	 * @since 1.0
	 */
	public static function stackTracing($trace,$html=false)
	{
		$stack = array();						
		foreach ($trace as $k => $line) 
		{
			$callInfo = ($k < count($trace) - 1) ? SLS_Tracing::getTraceInfo($trace[$k + 1],$html) : "";
			$stack[$k]["call"] = $callInfo . ' (' .basename($line['file']) .':'.$line['line'].')';
			$stack[$k]["file"] = 'in file '.$line['file'].' at line '.$line['line'];
		}
		return $stack;
	}
	
	/**
	 * Get trace infos	 
	 *
	 * @access public static
	 * @param array $traceElt associative array contained trace informations recovered by the raise of the exception
	 * @param bool $html if we want a HTML render (version dev)
	 * @return string $trace trace infos shaped (html ou plain)
	 * @since 1.0
	 */
	public static function getTraceInfo($traceElt,$html=false) 
	{
		$info = $traceElt['class'] . $traceElt['type'] . $traceElt['function'];
		$info .= '(';
		if ($traceElt['args']) 
		{			
			for ($i = 0; $i < count($traceElt['args']); $i++) 
			{
				$arg = $traceElt['args'][$i];				
				if ($html)
				{
					if (is_array($arg))
						$info .= '<a href="#" onclick="return false;" class="tooltip" title="'.gettype($arg).'" rel="<pre name=\'highlight_'.uniqid().'\' class=\'brush:php\'>'.SLS_String::printArray($arg).'</pre>">'.gettype($arg).'</a>';
					else if (is_object($arg))
						$info .= '<a href="#" onclick="return false;" class="tooltip" title="'.get_class($arg).'" rel="<pre name=\'highlight_'.uniqid().'\' class=\'brush:js\'>'.SLS_String::printObject(str_replace(array('"',':'),array("'",': '),json_encode($arg))).'</pre>">'.gettype($arg).'</a>';
					else 
					{
						if (is_string($arg) && (SLS_String::startsWith(trim(strtolower($arg)),"select") || SLS_String::startsWith(trim(strtolower($arg)),"update") || SLS_String::startsWith(trim(strtolower($arg)),"insert") || SLS_String::startsWith(trim(strtolower(arg)),"delete")))
							$info .= '<a href="#" onclick="return false;" class="tooltip" title="'.gettype($arg).'" rel="<pre name=\'highlight_'.uniqid().'\' class=\'brush:sql\'>'.(is_string($arg) ? trim($arg) : $arg).'</pre>">'.gettype($arg).'</a>';
						else	
							$info .= '<a href="#" onclick="return false;" class="tooltip" title="'.gettype($arg).'" rel="<pre name=\'highlight_'.uniqid().'\' class=\'brush:php\'>&#34;'.(is_string($arg) ? trim($arg) : $arg).'&#34;</pre>">'.gettype($arg).'</a>';
					}					
				}
				else
					$info .= is_object($arg) ? get_class($arg) : ''.gettype($arg).'';
				if ($i < count($traceElt['args']) - 1)
					$info .= ', ';				
			}
		}
		$info .= ')';
		return $info;
	}

	/**
	 * Construct the css in the output case	 
	 *
	 * @access public static
	 * @since 1.0
	 */
	public static function makeCss()
	{
		echo 
			'<style type="text/css">
			body {
				font-family:Verdana;
			}
			pre {
				font-family:Verdana;
				font-size:12px;
				margin: 0 40px;
				padding: 10px;
			}
			h2 {
				font-size:18px;
				font-weight:bold;
			}
			div.mainTitle {
				display:block;
				font-size:18px;
				font-weight:bold;
				margin-bottom:10px;
			}
			div.mainTitle div {
				display:block;
				font-family:Verdana;
				font-size:12px;
				font-style: italic;
				color:#000080;
			}
			div.mainTitle a{
				color:#5BA1CF;
			}
			div.stackTrace {
				display:block;
				margin: 0 40px;
				padding: 10px;
				border:1px solid #000;
			}
			div.stackTrace a.tooltip{
				color:#5BA1CF;
			}
			div.stackTrace div {
				display:block;
				width:100%;
				font-family:Verdana;
				font-size:12px;
				padding-top:2px;
			}
			div.stackTrace div span{
				display:block;
				margin-left:10px;
				font-family:Verdana;
				font-size:12px;
				font-style: italic;
				color:gray;
			}			
			.custom_tip .tip {
				color: #000;
				width: auto;
				z-index: 13000;
			}		 
			.custom_tip .tip-title {
				font-weight: bold;
				font-size: 11px;
				margin: 0;
				color: #FFF;
				padding: 8px 8px 4px;
				background: #5BA1CF;
				border-bottom: 1px solid #B5CF74;
			}			 
			.custom_tip .tip-text {
				font-size: 11px;
				padding: 4px 2px 5px 5px;
				background: #C6BBED;
			}
			.custom_tip .tip-text .syntaxhighlighter table,
			.custom_tip .tip-text .syntaxhighlighter table td.code{
				width:auto !important;
			}
			</style>
			';
	}
	
	/**
	 * Load Mootools in the output case	 
	 *
	 * @access public static
	 * @since 1.0
	 */
	public static function loadMootools()
	{
		$generic = SLS_Generic::getInstance();
		echo 	'<script src="'.$generic->getProtocol().'://'.$generic->getSiteConfig("domainName").'/'.$generic->getPathConfig("coreJsDyn").'mootools-core-and-more-1.5.0.js" type="text/javascript"></script>'."\n";
		echo 	'<link href="'.$generic->getProtocol().'://'.$generic->getSiteConfig('domainName').'/'.$generic->getPathConfig('coreCss').'highlight.css" rel="stylesheet" type="text/css" />'."\n";
		echo 	'<script src="'.$generic->getProtocol().'://'.$generic->getSiteConfig("domainName").'/'.$generic->getPathConfig("coreJsDyn").'highlight.js" type="text/javascript"></script>'."\n";	
		echo 	"<script type='text/javascript'>\n
					window.addEvent('load', function(){\n
						var toolTipsB = new Tips($$('.tooltip'), {\n
							className: 'custom_tip',\n
							showDelay: 200,\n
							hideDelay: 200,\n
							fixed: true,\n
							onShow: function(toolTip) {\n
								var element = new Element('div',{'html' : $$('.tip-text')[0].get('html')});\n
								var childrens = element.getChildren('pre')\n
								if (childrens.length > 0){\n
									for(i=0 ; i<childrens.length ; i++){\n
										SyntaxHighlighter.highlight(childrens[i]);\n
									}\n
								}\n
								toolTip.setStyle('display','block');\n								
							}\n
						});\n						
					});\n
					SyntaxHighlighter.all();\n
				</script>\n";
	}
}
?>