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
				<xsl:call-template name="Boheaders" />
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