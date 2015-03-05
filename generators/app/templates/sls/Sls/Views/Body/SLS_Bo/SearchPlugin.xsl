<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="SearchPlugin">
	
		<h1>Plugins Management</h1>
		Search More Plugins...
		<div>
			<a href="{//Statics/Sls/Configs/action/links/link[name='CREATE']/href}" title="Create you own plugin...">Create you own plugin...</a>
		</div>
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<div style="color:red;">
				<xsl:for-each select="//View/errors/error">
					<xsl:value-of select="." /><br />
				</xsl:for-each>
			</div>
		</xsl:if>
		<xsl:if test="count(//View/servers/server) &gt; 0">
			<xsl:for-each select="//View/servers/server">
			<xsl:variable name="serverID" select="@id" />
				<h1>
					<xsl:value-of select="@name" />
					<xsl:if test="url/@status = 1">
						<span style="color:green;font-size:0.8em;"> available</span>
					</xsl:if>
					<xsl:if test="url/@status = 0">
						<span style="color:red;font-size:0.8em;"> unavailable</span>
					</xsl:if>
				</h1>
				<xsl:if test="count(plugins/plugin) &gt; 0">
					<table>
						<tr>
							<th>Name</th>
							<th>Version</th>
							<th>Compatible</th>
							<th>Download</th>
							<th>About</th>
						</tr>
						<xsl:for-each select="plugins/plugin">
							<xsl:if test="@has = 0">
								<tr>
									<td>
										<strong><xsl:value-of select="name" /></strong><br />
										<xsl:value-of select="desc"/><br />
										Author : <xsl:value-of select="author" />
										
									</td>
									<td><xsl:value-of select="@version" /></td>
									<td>
										<xsl:if test="@compability = 1">
											<img src="{concat($sls_url_img_core_icons, 'tick.png')}" title="Compatible" alt="Compatible" />
										</xsl:if>
										<xsl:if test="@compability = 0">
											<img src="{concat($sls_url_img_core_icons, 'cross.png')}" title="Compatible" alt="Compatible" />
										</xsl:if>
									</td>
									<td>
										<xsl:if test="@has = 0 and @compability = 1">
											<a href="{dl}"><img src="{concat($sls_url_img_core_icons, 'plugin_add.png')}" title="Download it" alt="Download it" /></a>
										</xsl:if>
										<xsl:if test="@has = 0 and @compability = 0">
											<img src="{concat($sls_url_img_core_icons, 'plugin_add.png')}" title="Download it" alt="Download it" class="disabled" />
										</xsl:if>
										<xsl:if test="@has = 1">
											<a href=""><img src="{concat($sls_url_img_core_icons, 'plugin_delete.png')}" title="Delete it" alt="Delete it" /></a>
										</xsl:if>
									</td>
									<td>
										<a href="{doc}" title="About" target="_blank"><img src="{concat($sls_url_img_core_icons, 'information.png')}" title="About" alt="About" /></a>
									</td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</table>
				</xsl:if>
			</xsl:for-each>
		</xsl:if>
	
	</xsl:template>
</xsl:stylesheet>