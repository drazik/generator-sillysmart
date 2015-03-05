<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Bo">		
	
		<h1>Manage your back-offices</h1>
		<xsl:if test="count(//View/bos/bo) = 0">
			You don't have any back-offices.<br />
		</xsl:if>
		<xsl:if test="count(//View/bos/bo) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Class</th>
					<th>Database</th>
					<th>Table</th>
					<th>Category</th>
					<th>Actions</th>
					<th>Modify</th>
					<th>Delete</th>
				</tr>
				<xsl:for-each select="//View/bos/bo">
					<tr>
						<td><a href="{concat(//View/edit,'/name/',db,'_',table)}"  title="{concat(db,'_',table)}" style="color:#A2A2A2;"><xsl:value-of select="class" /></a></td>
						<td><xsl:value-of select="db" /></td>
						<td><xsl:value-of select="table" /></td>
						<td><xsl:if test="category = 'X'"><xsl:attribute name="style">text-align:center;</xsl:attribute></xsl:if><xsl:value-of select="category" /></td>
						<td align="center"><xsl:value-of select="nb_actions" /></td>									
						<td align="center"><a href="{concat(//View/edit,'/name/',db,'_',table)}" title="Modify"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Modify" alt="Modify" style="border:0" /></a></td>
						<td align="center"><a href="#" onclick="confirmDelete('{concat(//View/delete,'/name/',db,'_',table)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='GENERATE']/href}" title="Generate your back-offices" style="display:block;"><img src="{concat($sls_url_img_core_icons,'bo16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate Back-Offices</a><br />
		
		<h1>Back-office menu</h1>
		<a href="{//Statics/Sls/Configs/action/links/link[name='MANAGE_BOMENU']/href}" title="Generate your back-offices" style="display:block;"><img src="{concat($sls_url_img_core_icons,'bomenu16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Manage categories</a><br />
		
		<h1>Custom back-office actions</h1>
		<xsl:if test="count(//View/actions/action[existed='true']) = 0">
			You don't have any custom action.<br />
		</xsl:if>
		<xsl:if test="count(//View/actions/action[existed='true']) &gt; 0">
			<ul>
				<xsl:for-each select="//View/actions/action[existed='true']">
					<li><img src="{concat($sls_url_img_core_icons,icon)}" title="" alt="" style="border:0" align="absmiddle" />&#160;<xsl:value-of select="name"	/></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
		<xsl:if test="//View/actions/action[name='Translation']/existed = 'false'">
			<a href="{//Statics/Sls/Configs/action/links/link[name='TRANSLATION']/href}" title="Generate a translation action into Bo" style="display:block;"><img src="{concat($sls_url_img_core_icons,'boi18n16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate Translation Action</a>
		</xsl:if>
		<xsl:if test="//View/actions/action[name='ManageAdmin']/existed = 'false'">
			<a href="{//Statics/Sls/Configs/action/links/link[name='MANAGEADMIN']/href}" title="Generate a admin action into Bo" style="display:block;"><img src="{concat($sls_url_img_core_icons,'boadmin16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate Admin Management Action</a>
		</xsl:if>
		<xsl:if test="//View/actions/action[name='FileUpload']/existed = 'false'">
			<a href="{//Statics/Sls/Configs/action/links/link[name='FILEUPLOAD']/href}" title="Generate a file upload action into Bo" style="display:block;"><img src="{concat($sls_url_img_core_icons,'boupload16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate FileUpload Action</a>
		</xsl:if>
		<xsl:if test="//View/actions/action[name='ProjectSettings']/existed = 'false'">
			<a href="{//Statics/Sls/Configs/action/links/link[name='PROJECTSETTINGS']/href}" title="Generate a project settings action into Bo" style="display:block;"><img src="{concat($sls_url_img_core_icons,'bosettings16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Generate Project Settings Action</a>
		</xsl:if><br />
		
		<h1>Back-office rights</h1>
		<xsl:if test="//View/admins_exist = 'false'">
			<span style="color:red;">Warning, no admins accounts have been configured to access on your customer back-office, we strongly recommend creating at least a root account to access your bo.</span><br />
		</xsl:if>
		<a href="{//Statics/Sls/Configs/action/links/link[name='MANAGE_RIGHTS']/href}" title="Manage your bo access" style="display:block;"><img src="{concat($sls_url_img_core_icons,'admin16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Manage your back-office access</a><br />
				
		<h1>Manage custom reporting</h1>
		<xsl:choose>
			<xsl:when test="//View/nb_reporting = 0">
				You don't have any custom report yet, <a href="{//View/url_reporting}">add a new one</a>.
			</xsl:when>
			<xsl:otherwise>
				<a href="{//View/url_reporting}"><img src="{concat($sls_url_img_core_icons,'graph16.png')}" title="Manage reports" alt="Manage reports" style="border:0" align="absmiddle" /></a>&#160;Manage your <a href="{//View/url_reporting}"><xsl:value-of select="//View/nb_reporting" /> report<xsl:if test="//View/nb_reporting &gt; 1">s</xsl:if></a>.
			</xsl:otherwise>
		</xsl:choose>
				
	</xsl:template>
</xsl:stylesheet>