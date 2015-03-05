<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GenerateMetas">
		<meta http-equiv="Content-Language" content="{//root/Statics/Sls/Session/params/param[name='current_lang']/value}" />
		<meta name="author" content="{//Statics/Sls/Configs/action/metas/author}" />
		<meta name="copyright" content="{//Statics/Sls/Configs/action/metas/copyright}" />
		<meta name="description" content="{//Statics/Sls/Configs/action/metas/description}" />
		<meta name="keywords" content="{//Statics/Sls/Configs/action/metas/keywords}" />
		<xsl:choose>
			<xsl:when test="//Statics/Sls/Configs/site/isProd = '0'">
				<meta name="robots" content="noindex, nofollow" />
			</xsl:when>
			<xsl:otherwise>
				<meta name="robots" content="{//Statics/Sls/Configs/action/metas/robots}" />
			</xsl:otherwise>
		</xsl:choose>
		<title><xsl:value-of select="//Statics/Sls/Configs/action/metas/title" /></title>
		<xsl:if test="//Statics/Sls/Configs/action/metas/favicon != ''">
			<link rel="shortcut icon" href="{//Statics/Sls/Configs/action/metas/favicon}" />
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>