<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoMenu">

		<div id="sls-front-toolbar">
			<xsl:call-template name="Bomenu">
				<xsl:with-param name="loadScript" select="'true'" />
			</xsl:call-template>
		</div>
		
	</xsl:template>
</xsl:stylesheet>