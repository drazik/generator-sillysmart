<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ManageRights">
	
		<h1>Manage your back-offices access</h1>
		<xsl:if test="count(//View/accounts/account) = 0">
			You don't have any back-office accounts.<br />
		</xsl:if>
		<xsl:if test="count(//View/accounts/account) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Login</th>
					<th>Password</th>
					<th>Enabled</th>
					<th>Name</th>
					<th>Firstname</th>
					<th>Bo Color</th>														
					<th>Modify</th>
					<th>Delete</th>
				</tr>
				<xsl:for-each select="//View/accounts/account">
					<tr>
						<td><a href="{concat(//View/edit,'/name/',login)}" title="Modify" style="color:#A2A2A2;"><strong><xsl:value-of select="login" /></strong></a></td>
						<td><xsl:value-of select="password" /></td>
						<td style="text-align:center;">
							<xsl:choose>
								<xsl:when test="enabled = 'true'">
									<a href="{concat(//View/url_status,'/name/',login)}" class="report on" title="Disable"></a>
								</xsl:when>
								<xsl:otherwise>
									<a href="{concat(//View/url_status,'/name/',login)}" class="report off" title="Enable"></a>
								</xsl:otherwise>
							</xsl:choose>
						</td>						
						<td><xsl:value-of select="name" /></td>
						<td><xsl:value-of select="firstname" /></td>
						<td><xsl:value-of select="color" /></td>																										
						<td align="center"><a href="{concat(//View/edit,'/name/',login)}" title="Modify"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Modify" alt="Modify" style="border:0" /></a></td>
						<td align="center"><a href="#" onclick="confirmDelete('{concat(//View/delete,'/name/',login)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<xsl:if test="//View/admins_exist = 'false'">
			<span style="color:red;">Warning, no admins accounts have been configured to access on your customer back-office, we strongly recommend creating at least a root account to access your bo.</span><br />						
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADD']/href}" title="Manage your bo access" style="display:block;">Add a back-office access</a>
				
	</xsl:template>
</xsl:stylesheet>