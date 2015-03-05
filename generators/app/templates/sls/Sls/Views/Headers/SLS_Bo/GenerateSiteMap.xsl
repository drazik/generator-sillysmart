<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderGenerateSiteMap">
		<script type="text/javascript">
			function confirmDelete(link){if (confirm("Are you sure to delete this record ?"))window.location = link;else return false;}
			window.addEvent('domready', function(){
				SyntaxHighlighter.all();
			});
		</script>
	</xsl:template>
</xsl:stylesheet>