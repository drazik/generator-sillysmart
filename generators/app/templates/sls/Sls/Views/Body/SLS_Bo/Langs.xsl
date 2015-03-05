<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Langs">
	
		<h1>Manage your Langs</h1>
		<xsl:if test="count(//View/langs/lang) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Language</th>
					<th>Iso</th>
					<th>Is Default</th>
					<th>Is Active</th>
					<th>Delete</th>
					<th>Set Default</th>
				</tr>
				<xsl:for-each select="//View/langs/lang">
					<tr>
						<td><xsl:value-of select="language" /></td>
						<td align="center"><xsl:value-of select="iso" /></td>
						<td align="center"><xsl:value-of select="default" /></td>
						<td align="center">
							<xsl:choose>
								<xsl:when test="active = 'true'">
									<a href="{concat(//Statics/Sls/Configs/action/links/link[name='ENABLE']/href, '/Enable/off/iso/', iso, '.sls')}" class="report on" title="Disable"></a>
								</xsl:when>
								<xsl:otherwise>
									<a href="{concat(//Statics/Sls/Configs/action/links/link[name='ENABLE']/href, '/Enable/on/iso/', iso, '.sls')}" class="report off" title="Enable"></a>
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td align="center">
							<xsl:if test="default = 'true'">
								<img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Can't delete default lang" alt="Can't delete default lang" style="display:block;border:0;opacity:0.3;" />
							</xsl:if>
							<xsl:if test="default = 'false'">
								<a href="#" onclick="confirmDelete('{concat(//View/delete,'/name/',iso)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a>
							</xsl:if>
						</td>
						<td align="center">
							<xsl:if test="default = 'true'">
								<img src="{concat($sls_url_img_core_icons,'add16.png')}" title="Already default lang" alt="Already default lang" style="display:block;border:0;opacity:0.3;" />
							</xsl:if>
							<xsl:if test="default = 'false'">
								<a href="{concat(//View/default,'/name/',iso)}"><img src="{concat($sls_url_img_core_icons,'add16.png')}" title="Set as default lang" alt="Set as default lang" style="border:0" /></a>
							</xsl:if>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADD_LANG']/href}" title="Add a new lang" style="display:block;">Add a new lang</a>					
				
	</xsl:template>
</xsl:stylesheet>