<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderReportingBoEdit">
		<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'Reporting.css')}" />
		<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile',concat($sls_url_js_core_dyn,'Reporting.js'))}" />
		<script type="text/javascript">
			var _urls = {};
			_urls.reporting_getfields = '<xsl:value-of select="//View/url_reporting_getfields" />';
			_urls.reporting_getfieldsfrommutipletables = '<xsl:value-of select="//View/url_reporting_getfieldsfrommutipletables" />';
			function confirmDelete(link){if (confirm("Are you sure to delete this report?"))window.location = link;else return false;}
		</script>
	</xsl:template>
</xsl:stylesheet>