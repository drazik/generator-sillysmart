<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LogsMail">
	
		<h1>Logs > Mail</h1>
		<xsl:if test="count(//View/logs/log) &gt; 0">
			<textarea style="width:780px;height:{//View/height}px;" onclick="this.select();"><xsl:for-each select="//View/logs/log"><xsl:value-of select="." /></xsl:for-each></textarea>
		</xsl:if>
		<xsl:if test="count(//View/logs/log) = 0">
			Sorry you doesn't have yet mail logs
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>