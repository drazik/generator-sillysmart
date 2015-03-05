<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LogsApp">
	
		<h1>Logs > Application Production > View the '<xsl:value-of select="//View/errors/date" />' log</h1><br />
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<xsl:for-each select="//View/errors/error">
				<div class="mainTitle">
					<xsl:value-of select="title" />
				</div>
				<div class="stackTrace">
					<xsl:for-each select="traces/trace">
						<div style="border-bottom:1px dotted darkgray;padding-bottom:2px;">
							<xsl:value-of select="message" />
							<span><xsl:value-of select="file" /></span>
						</div>
					</xsl:for-each>
				</div><br />
			</xsl:for-each>
		</xsl:if>
		<xsl:if test="count(//View/errors/error) = 0">
			Sorry, it doesn't have any logs for this date.
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>