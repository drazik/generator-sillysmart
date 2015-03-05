<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Models">
	
		<h1>Manage your Models</h1><br />
		<xsl:if test="count(//View/models/model) = 0">
			You don't have any models.<br />
		</xsl:if>
		<xsl:if test="count(//View/models/model) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Class</th>
					<th>Database</th>
					<th>Table</th>
					<th>PK</th>
					<th>Columns</th>
					<th>Modify</th>
					<th>Delete</th>
					<th>Cache</th>
					<th>Status</th>					
				</tr>
				<xsl:for-each select="//View/models/model">
					<tr>
						<td><a href="{concat(//View/edit,'/name/',db,'_',table)}" title="{class}" style="color:#A2A2A2;"><xsl:value-of select="class" /></a></td>
						<td><xsl:value-of select="db" /></td>
						<td><xsl:value-of select="table" /></td>
						<td><xsl:value-of select="pk" /></td>
						<td align="center"><xsl:value-of select="nbColumns" /></td>
						<td align="center"><a href="{concat(//View/edit,'/name/',db,'_',table)}" title="Modify"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Modify" alt="Modify" style="border:0" /></a></td>
						<td align="center"><a href="#" onclick="confirmDelete('{concat(//View/delete,'/name/',db,'_',table)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></td>
						<td align="center"><a href="#" onclick="confirmFlush('{url_flush_cache}');return false;"><img src="{concat($sls_url_img_core_buttons,'cache_flush.png')}" title="Flush cache" alt="Flush cache" style="border:0" /></a></td>
						<td align="center">
							<xsl:choose>
								<xsl:when test="up_to_date = 'true'">
									<img src="{$sls_url_img_core_icons}tick.png" alt="Up to date !" title="Up to date !" align="absmiddle" /> Up to date
								</xsl:when>
								<xsl:otherwise>
									<a href="{url_update}" title="Update your Model">
										<img src="{$sls_url_img_core_icons}exclamation.png" alt="Update your Model" title="Update your Model" align="absmiddle" /> Deprecated
									</a>
								</xsl:otherwise>
							</xsl:choose>
						</td>						
					</tr>
				</xsl:for-each>
			</table><br />
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='PROPERTIES']/href}" title="Models properties"><img src="{concat($sls_url_img_core_icons,'boadmin16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Models Properties</a> | <a href="{//Statics/Sls/Configs/action/links/link[name='GENERATE']/href}" title="Generate your models"><img src="{concat($sls_url_img_core_icons,'script_add.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate models</a>
		
	</xsl:template>
</xsl:stylesheet>