<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Settings">
	
		<h1>SillySmart Settings</h1>
		<ul>
			<xsl:for-each select="//View/settings_menu/setting_menu">
				<li><a href="{link}" title="{label}"><xsl:value-of select="label" /></a></li>
			</xsl:for-each>
		</ul>
				
	</xsl:template>
</xsl:stylesheet>