<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Plugins">
	
		<h1>Plugins Management</h1>
		<h2>Plugins installed</h2>
		<xsl:if test="count(View/plugin) &gt; 0">
			<table>
				<tr>
					<th>Plugin Name</th>
					<th>Version</th>
					<th>Edit</th>
					<th>Update</th>
					<th>Delete</th>
					<th>About</th>
				</tr>
				<xsl:for-each select="View/plugin[beta='0']">
					<tr>
						<td>
							<strong><xsl:value-of select="name" /></strong><br />
							<xsl:value-of select="description"/><br />
							Author : <xsl:value-of select="author" />
						</td>
						<td><xsl:value-of select="version" /></td>
						<td>
							<xsl:if test="custom = 1">
								<a href="{edit}" title="edit"><img src="{concat($sls_url_img_core_icons, 'plugin_edit.png')}" alt="edit" title="edit" /></a>
							</xsl:if>
							<xsl:if test="custom = 0">
								<img src="{concat($sls_url_img_core_icons, 'plugin_disabled.png')}" alt="Not editable" title="Not editable" />
							</xsl:if>
						</td>
						<td>
							<xsl:if test="uptodate = 'yes'">
								<img src="{concat($sls_url_img_core_icons, 'tick.png')}" alt="This plugin is up to date" title="This plugin is up to date" />
							</xsl:if>
							<xsl:if test="uptodate = 'no'">
								<a href="{update}" title="This plugin is not up to date" class="update_plugins"><img src="{concat($sls_url_img_core_icons, 'plugin_error.png')}" alt="This plugin is not up to date" title="This plugin is not up to date" /></a>
							</xsl:if>
						</td>
						<td><a href="{delete}" title="delete" class="delete_plugins"><img src="{concat($sls_url_img_core_icons, 'bin.png')}" alt="delete" title="delete" /></a></td>
						<td>
							<xsl:if test="count(doc) &gt; 0">
								<a href="{doc}" title="About" target="_blank"><img src="{concat($sls_url_img_core_icons, 'information.png')}" title="About" alt="About" /></a>
							</xsl:if>
						</td>
					</tr>
				</xsl:for-each>
				<xsl:if test="count(//View/plugin[beta='1']) &gt; 0">
					<tr>
						<th colspan="6">
							Your own plugins
						</th>
					</tr>
					<xsl:for-each select="//View/plugin[beta='1']">
						<tr>
							<td>
								<strong><xsl:value-of select="name" /></strong><br />
								<xsl:value-of select="description"/><br />
								Author : <xsl:value-of select="author" />
							</td>
							<td><xsl:value-of select="version" /></td>
							<td>
								<xsl:if test="custom = 1">
									<a href="{edit}" title="edit"><img src="{concat($sls_url_img_core_icons, 'plugin_edit.png')}" alt="edit" title="edit" /></a>
								</xsl:if>
								<xsl:if test="custom = 0">
									<img src="{concat($sls_url_img_core_icons, 'plugin_disabled.png')}" alt="Not editable" title="Not editable" />
								</xsl:if>
							</td>
							<td>
								<xsl:if test="uptodate = 'yes'">
									<img src="{concat($sls_url_img_core_icons, 'tick.png')}" alt="This plugin is up to date" title="This plugin is up to date" />
								</xsl:if>
								<xsl:if test="uptodate = 'no'">
									<a href="{update}" title="This plugin is not up to date" class="update_plugins"><img src="{concat($sls_url_img_core_icons, 'plugin_error.png')}" alt="This plugin is not up to date" title="This plugin is not up to date" /></a>
								</xsl:if>
							</td>
							<td><a href="{delete}" title="delete" class="delete_plugins"><img src="{concat($sls_url_img_core_icons, 'bin.png')}" alt="delete" title="delete" /></a></td>
							<td>
								<xsl:if test="count(doc) &gt; 0">
									<a href="{doc}" title="About" target="_blank"><img src="{concat($sls_url_img_core_icons, 'information.png')}" title="About" alt="About" /></a>
								</xsl:if>
							</td>
						</tr>
					</xsl:for-each>
				</xsl:if>
			</table>
		</xsl:if>
		<xsl:if test="count(View/plugin) = 0">
			You have any plugins enabled for your application
		</xsl:if>
		<div>
			<a href="{//View/search}" title="">Add plugins...</a>					
		</div>
				
	</xsl:template>
</xsl:stylesheet>