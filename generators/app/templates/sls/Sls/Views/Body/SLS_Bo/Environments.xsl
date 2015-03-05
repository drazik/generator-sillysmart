<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Environments">	
	
		<h1>Manage environments</h1>
		
		<xsl:choose>
			<xsl:when test="count(//View/environments/environment) &gt; 0">
				<table border="1" cellpadding="5" cellspacing="0" style="margin-top:20px;">
					<tr style="background-color:#E9E9E9;color:#000;">
						<th>Environment</th>
						<th>Settings</th>
						<th>Delete</th>
					</tr>
					<xsl:for-each select="//View/environments/environment">
						<tr>
							<td align="left"><xsl:value-of select="title" /></td>					
							<td align="center"><a href="{url_setting}"><img src="{$sls_url_img_core_buttons}preview.png" alt="" title="" /></a></td>
							<td align="center"><a href="#" onclick="confirmDelete('{url_delete}');return false;"><img src="{$sls_url_img_core_buttons}delete.png" alt="Edit" title="Edit" /></a></td>
						</tr>
					</xsl:for-each>			
				</table>
			</xsl:when>
			<xsl:otherwise>
				<span style="color:red;">No environment yet.</span>
			</xsl:otherwise>
		</xsl:choose>		
		
		<a href="{//View/url_add}" title="Batch" style="display:block;margin-top:20px;">
			<img src="{$sls_url_img_core_buttons}add.png" alt="Add" title="Add" align="absmiddle" /> Add environment
		</a>
				
	</xsl:template>
</xsl:stylesheet>