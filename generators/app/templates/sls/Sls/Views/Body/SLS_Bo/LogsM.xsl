<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LogsM">
	
		<h1>Logs > Monitoring</h1>
		<xsl:if test="count(//View/logs/log) &gt; 0">
			<ul>						
				<xsl:for-each select="//View/logs/log">
					<li><a href="{concat(//View/view_log,'/date/',concat(year,'-',month,'-',day))}" title="View this log"><xsl:value-of select="litteral" /></a></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
		<xsl:if test="count(//View/logs/log) = 0">
			Sorry you doesn't have yet logs...<br />.
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>