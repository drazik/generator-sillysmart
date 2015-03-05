<!-- 
 	- Global template for your application 	
 	- Don't change anything between marked delimiter |||dtd:tagName|||
 	- Beyond you can add additional headers or/and xhtml structure
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" omit-xml-declaration="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" indent="yes" encoding="|||sls:getCharset|||" />
	
	<!-- Variable Builder -->
	|||sls:buildUrlVars|||
	<!-- /Variable Builder -->
	
	<!-- Generic include -->
	|||sls:includeActionFileBody|||
	|||sls:includeActionFileHeader|||
	|||sls:includeStaticsFiles|||
	<!-- /Generic include -->
	
	<xsl:template match="root">
		<html xml:lang="|||sls:getLanguage|||" lang="|||sls:getLanguage|||">
			<head>
			
				<!-- Generic headers loading -->
				|||sls:loadCoreHeaders|||
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'Global.css')}" />
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'mootools-core-and-more-1.5.0.js'))}"></script>
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'highlight.css')}" />
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'highlight.js'))}"></script>
				|||sls:loadActionFileHeader|||
				<!-- /Generic headers loading -->
			
			</head>
			<body>
			
				<!-- Generic bodies loading -->
				|||sls:loadActionFileBody|||
				|||sls:loadCoreBody|||
				<!-- /Generic bodies loading -->
				
			</body>		
		</html>
	</xsl:template>
</xsl:stylesheet>