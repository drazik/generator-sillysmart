<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderAddFilter">
		<script type="text/javascript">
			function checkFilter(){document.getElementById('hash').style.display = (document.getElementById('filter').value == 'hash') ? 'inline' : 'none';}
		</script>
	</xsl:template>
</xsl:stylesheet>