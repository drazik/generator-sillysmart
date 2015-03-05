<?php
class SiteProtected extends SLS_GenericController 
{
	public function init()
    {
		
    }
    
	public function createXslTemplate($fileName,$doctype="xhtml_1.0_transitionnal",$ga="")
	{
		$doctypes = array("xhtml_1.0_transitionnal" => array("method"		  => "xml",
															 "doctype-system" => "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd",
															 "doctype-public" => "-//W3C//DTD XHTML 1.0 Transitional//EN"),
						  "xhtml_1.0_strict" 		=> array("method"		  => "xml",
															 "doctype-system" => "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd",
															 "doctype-public" => "-//W3C//DTD XHTML 1.0 Strict//EN"),
						  "xhtml_1.1_strict" 		=> array("method"		  => "xml",
															 "doctype-system" => "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd",
															 "doctype-public" => "-//W3C//DTD XHTML 1.1//EN"),
						  "html_5" 					=> array("method"		  => "html",
															 "doctype-system" => "about:legacy-compat",
															 "doctype-public" => null),
						  "html_4.01_transitionnal" => array("method"		  => "html",
															 "doctype-system" => "http://www.w3.org/TR/html4/loose.dtd",
															 "doctype-public" => "-//W3C//DTD HTML 4.01 Transitional//EN"),
						  "html_4.01_strict" 		=> array("method"		  => "html",
															 "doctype-system" => "http://www.w3.org/TR/html4/strict.dtd",
															 "doctype-public" => "-//W3C//DTD HTML 4.01//EN"));
		
		$str =  '<!--'."\n".
				 	'   - Global template for your application'."\n".
				 	'   - Don\'t change anything between marked delimiter |||dtd:tagName|||'."\n".
				 	'   - Beyond you can add additional headers or/and xhtml structure'."\n".
					'-->'."\n".
					'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">'."\n".
					t(1).'<xsl:output method="'.$doctypes[$doctype]["method"].'" omit-xml-declaration="yes" '.((!empty($doctypes[$doctype]["doctype-system"])) ? 'doctype-system="'.$doctypes[$doctype]["doctype-system"].'" ' : '').((!empty($doctypes[$doctype]["doctype-public"])) ? 'doctype-public="'.$doctypes[$doctype]["doctype-public"].'" ' : '').'indent="yes" encoding="|||sls:getCharset|||" />'."\n\n".	
					t(1).'<!-- Variable Builder -->'."\n".
					t(1).'|||sls:buildUrlVars|||'."\n".
					t(1).'<!-- /Variable Builder -->'."\n\n".	
					t(1).'<!-- Generic include -->'."\n".
					t(1).'|||sls:includeActionFileBody|||'."\n".
					t(1).'|||sls:includeActionFileHeader|||'."\n".
					t(1).'|||sls:includeStaticsFiles|||'."\n".
					t(1).'<!-- /Generic include -->'."\n\n".	
					t(1).'<xsl:template match="root">'."\n".					
						t(2).'<html xml:lang="|||sls:getLanguage|||" lang="|||sls:getLanguage|||">'."\n".
							t(3).'<head>'."\n\n".
								t(4).'<!-- Generic headers loading -->'."\n".
								t(4).'|||sls:loadCoreHeaders|||'."\n".
								t(4).'|||sls:loadActionFileHeader|||'."\n".
								t(4).'<!-- /Generic headers loading -->'."\n\n".			
							t(3).'</head>'."\n".
							t(3).'<body>'."\n\n".
								t(4).'<!-- Generic bodies loading -->'."\n".
								t(4).'|||sls:loadActionFileBody|||'."\n".
								t(4).'|||sls:loadCoreBody|||'."\n".
								t(4).'<!-- /Generic bodies loading -->'."\n\n";
		if(!empty($ga))
		{
			$str .= 			t(4)."<!-- GA loading -->"."\n".
								t(4)."<xsl:if test=\"//Statics/Sls/Configs/site/isProd = '1'\">"."\n".
									t(5)."<script type=\"text/javascript\">"."\n".
										t(6)."var _gaq = _gaq || [];"."\n".
										t(6)."_gaq.push(['_setAccount', 'UA-28279047-1']);"."\n".
										t(6)."_gaq.push(['_trackPageview']);"."\n".
										t(6)."(function() {"."\n".
											t(7)."var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;"."\n".
											t(7)."ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';"."\n".
											t(7)."var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);"."\n".
										t(6)."})();"."\n".
									t(5)."</script>"."\n".
								t(4)."</xsl:if>"."\n".
								t(4)."<!-- /GA loading -->"."\n\n";
		}	
		$str .= 			t(3).'</body>'."\n".
						t(2).'</html>'."\n".
					t(1).'</xsl:template>'."\n".
					'</xsl:stylesheet>';
		file_put_contents($this->_generic->getPathConfig("viewsTemplates").$fileName.".xsl",$str);
	}
}
?>