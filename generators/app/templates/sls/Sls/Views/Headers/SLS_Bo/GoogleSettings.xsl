<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderGoogleSettings">
		<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'shadowbox.js'))}"></script>
		<link rel="stylesheet" type="text/css" href="{php:functionString('SLS_String::callCachingFile',concat($sls_url_css_core,'shadowbox.css'))}" />
		<script type="text/javascript">
			Shadowbox.init();
		</script>
	</xsl:template>
</xsl:stylesheet>