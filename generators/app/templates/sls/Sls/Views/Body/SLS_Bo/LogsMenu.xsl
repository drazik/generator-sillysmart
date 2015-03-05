<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LogsMenu">
	
		<h1>Logs</h1>
		<ul>
			<li><a href="{//View/logs_monitoring}" title="Monitoring Logs">View Monitoring Logs</a></li>
			<li><a href="{//View/logs_app}" title="Production App Logs">View Production Application Logs</a></li>
			<li><a href="{//View/logs_mail}" title="Mail Logs">View Mail Logs</a></li>
		</ul>
				
	</xsl:template>
</xsl:stylesheet>