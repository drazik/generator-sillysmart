<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderEditBo">
		<script type="text/javascript">
			function confirmDelete(link){if (confirm("Are you sure to delete this record ?"))window.location = link;else return false;}
		</script>
		<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'Sortables.js'))}"></script>
		<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'Bo.js'))}"></script>
	</xsl:template>
</xsl:stylesheet>