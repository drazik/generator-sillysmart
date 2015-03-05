<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Templates">
	
		<h1>Manage your Views Templates</h1><br />
		<h2>Main Template</h2>
			<ul style="margin:10px 30px;">
				<li>Template __default</li>
			</ul>
		<h2>User Templates</h2>
		<ul style="margin:10px 30px;">
			<xsl:if test="count(//View/templates/template) &gt; 0">
				<xsl:for-each select="//View/templates/template">
					<li>Template `<xsl:value-of select="name" />`&#160;<a href="#" onclick="confirmDelete('{concat(//View/templates/url_delete,'/name/',name)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" /></a></li>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="count(//View/templates/template) = 0">
				You don't have any templates.
			</xsl:if>
		</ul>
		<a href="{//View/templates/url_add}"><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" alt="Add a new template" title="Add a new template" />&#160;Add a new template</a>
		
		<hr />
		
		<h1>Manage your Views Generics</h1>		
		<h2>User generics</h2>
		<ul style="margin:10px 30px;">
			<xsl:if test="count(//View/generics/generic) &gt; 0">
				<xsl:for-each select="//View/generics/generic">
					<li>Template `<xsl:value-of select="name" />`&#160;<a href="#" onclick="confirmDelete('{concat(//View/generics/url_delete,'/name/',name)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" /></a></li>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="count(//View/generics/generic) = 0">
				You don't have any generic.
			</xsl:if>
		</ul>
		<a href="{//View/generics/url_add}"><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" alt="Add a new template" title="Add a new template" align="absmiddle" />&#160;Add a new template</a>
		
	</xsl:template>
</xsl:stylesheet>