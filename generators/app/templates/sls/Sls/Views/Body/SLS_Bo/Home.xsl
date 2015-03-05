<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Home">
	
		<h1>Welcome on your SillySmart DashBoard</h1>
		<h2>Actions</h2>
		<ul>
			<xsl:for-each select="//View/Actions/Action">
				<li><a href="{link}" title="{name}"><xsl:value-of select="name" disable-output-escaping="yes" /></a></li>
			</xsl:for-each>
		</ul>
				
	</xsl:template>
</xsl:stylesheet>