<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderReportingBoView">
		<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'Reporting.css')}" />
		<script type="text/javascript">
			function confirmDelete(link){if (confirm("Are you sure to delete this report?"))window.location = link;else return false;}
		</script>
		<style ttype="text/css">
			a.report{
				display: inline-block;
				*display: inline;
				zoom: 1;
				position: relative;
				top: 3px;
				width: 16px;
				height: 17px;
				background: url(/Sls/Style/Img/Icons/on-off-small.png) 0 0 no-repeat transparent;
			}
			a.report.on,
			a.report.off:hover{
				background-position: 0 -17px;
			}
			a.report.off,
			a.report.on:hover{
				background-position: 0 0;
			}
		</style>
	</xsl:template>
</xsl:stylesheet>