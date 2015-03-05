<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="makeMenu">
		<ul id="globalMenu">
			<xsl:for-each select="//View/Actions/Action" >
				<li><a href="{link}" title="{php:functionString('strip_tags',name)}"><xsl:if test="selected = 'true'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><xsl:value-of select="name" disable-output-escaping="yes" /></a></li>
				<xsl:if test="count(sub) &gt; 0">
					<li>
						<ul class="submenu">
							<xsl:for-each select="sub">
								<li><a href="{link}" title="{php:functionString('strip_tags',name)}"><xsl:if test="selected = 'true'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><xsl:value-of select="name" disable-output-escaping="yes" /></a></li>
							</xsl:for-each>
						</ul>
					</li>
				</xsl:if>
			</xsl:for-each>			
		</ul>
	</xsl:template>
</xsl:stylesheet>