<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="MailTemplatesPreview">
	
		<xsl:value-of select="//View/template/header" disable-output-escaping="yes" />
		<pre>
			Your email content will be located here.
		</pre>
		<xsl:value-of select="//View/template/footer" disable-output-escaping="yes" />
				
	</xsl:template>
</xsl:stylesheet>