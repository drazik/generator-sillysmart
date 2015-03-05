<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderMaintenanceError">
	<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'Global.css')}" />
		<xsl:comment>[IF IE]&gt;
		&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'GlobalIe.css')" />" /&gt;
		&lt;![endif]</xsl:comment>
		<xsl:comment>[IF lt IE 7]&gt;
		&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'GlobalIe6.css')" />" /&gt;
		&lt;![endif]</xsl:comment>
	</xsl:template>
</xsl:stylesheet>