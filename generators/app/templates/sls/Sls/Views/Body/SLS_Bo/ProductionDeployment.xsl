<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ProductionDeployment">	
	
		<h1>Deploy SillySmart in production</h1>
		<h2>Configuration files</h2>
		
		<xsl:for-each select="//View/environments/environment">
			<fieldset style="float:left;margin-right:10px;">
				<legend><xsl:value-of select="environment_title" /></legend>
				<table border="1" cellpadding="5" cellspacing="0" style="margin-top:20px;">
					<tr style="background-color:#E9E9E9;color:#000;">
						<th>File</th>
						<th>Status</th>
						<th>Edit</th>
					</tr>
					<xsl:for-each select="prod_files/prod_file">
						<tr>
							<td align="left"><xsl:value-of select="title" /></td>					
							<td align="right">
								<span>
									<xsl:attribute name="style">color:<xsl:choose><xsl:when test="exists = 'true'">green</xsl:when><xsl:otherwise>red</xsl:otherwise></xsl:choose></xsl:attribute>
									<xsl:choose><xsl:when test="exists = 'true'">YES</xsl:when><xsl:otherwise>NO</xsl:otherwise></xsl:choose>
								</span>
							</td>
							<td align="center"><a href="{url_edit}"><img src="{$sls_url_img_core_buttons}pencil.png" alt="Edit" title="Edit" /></a></td>
						</tr>
					</xsl:for-each>			
				</table>
				
				<a href="{url_batch}" title="Batch" style="display:block;margin-top:20px;">
					<img src="{$sls_url_img_core_buttons}process.png" alt="Batch" title="Batch" align="absmiddle" /> Complete Batch
				</a>
			</fieldset>			
		</xsl:for-each>
		<div class="clear"></div>
		<a href="{//View/url_add_environment}">Add a new environment</a>
				
	</xsl:template>
</xsl:stylesheet>