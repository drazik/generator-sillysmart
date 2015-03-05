<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ReportingBo">
	
		<h1>Reporting</h1><br />
		
		<xsl:if test="count(//View/sls_graphs/sls_graph) = 0">
			You don't have any report.<br />
		</xsl:if>
		<xsl:if test="count(//View/sls_graphs/sls_graph) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Id</th>
					<th>Title</th>
					<th>Type</th>
					<th>Is Active</th>
					<th>Date add</th>
					<th>View</th>
					<th>Modify</th>
					<th>Delete</th>			
				</tr>
				<xsl:for-each select="//View/sls_graphs/sls_graph">
					<tr>
						<td style="text-align:center;"><xsl:value-of select="sls_graph_id" /></td>
						<td><a href="{concat(//View/url_view,'/id/',sls_graph_id)}" title="{sls_graph_title}" style="color:#A2A2A2;"><xsl:value-of select="sls_graph_title" /></a></td>
						<td><xsl:value-of select="sls_graph_type" /></td>
						<td style="text-align:center;">
							<xsl:choose>
								<xsl:when test="sls_graph_visible = 'yes'">
									<a href="{concat(//View/url_status,'/id/',sls_graph_id)}" class="report on" title="Disable"></a>
								</xsl:when>
								<xsl:otherwise>
									<a href="{concat(//View/url_status,'/id/',sls_graph_id)}" class="report off" title="Enable"></a>
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td style="text-align:center;"><xsl:value-of select="sls_graph_date_add" /></td>
						<td align="center"><a href="{concat(//View/url_view,'/id/',sls_graph_id)}" title="View"><img src="{concat($sls_url_img_core_icons,'magnifier.png')}" title="View" alt="View" style="border:0" /></a></td>
						<td align="center"><a href="{concat(//View/url_edit,'/id/',sls_graph_id)}" title="Modify"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Modify" alt="Modify" style="border:0" /></a></td>
						<td align="center"><a href="#" onclick="confirmDelete('{concat(//View/url_delete,'/id/',sls_graph_id)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></td>
					</tr>
				</xsl:for-each>
			</table><br />
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADD']/href}" title="Add dashboard"><img src="{concat($sls_url_img_core_icons,'script_add.png')}" title="Modify" alt="Modify" style="border:0" align="absmiddle" />&#160;Add custom report</a>
				
	</xsl:template>
</xsl:stylesheet>