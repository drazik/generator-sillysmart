<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GenerateBoAction">
	
		<h1>Generate a custom action into Bo</h1>
		<xsl:choose>
			<xsl:when test="count(//View/errors/error) &gt; 0">
				<div style="font-weight:bold;color:red">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
					<xsl:if test="//View/error_type = 'rights'">
						<a href="{//View/url_add_controller}" title="Add your back-office controller">Add your back-office controller</a>
					</xsl:if>
				</div>
			</xsl:when>
		</xsl:choose>
	
	</xsl:template>
</xsl:stylesheet>