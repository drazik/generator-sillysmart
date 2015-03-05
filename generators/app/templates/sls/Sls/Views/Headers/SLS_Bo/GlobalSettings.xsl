<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderGlobalSettings">
		<script type="text/javascript">
			function displaySessionDomain(){document.getElementById('domainSession').style.display = (document.getElementById('session').checked) ? 'inline' : 'none';}
			function confirmDelete(link){if (confirm("Are you sure to delete this domain ?"))window.location = link;else return false;}
			function switchArea(){
				<xsl:for-each select="//View/timezones/areas/area">document.getElementById('settings_timezone_area_<xsl:value-of select="@id" />').style.display = 'none';
				</xsl:for-each>document.getElementById('settings_timezone_area_' + document.getElementById('timezone').value).style.display = 'inline';			
			}
		</script>
	</xsl:template>
</xsl:stylesheet>