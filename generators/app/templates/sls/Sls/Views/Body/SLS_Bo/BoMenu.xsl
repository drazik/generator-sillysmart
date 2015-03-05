<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoMenu">		
	
		<h1>Manage your back-office menu</h1>
		
		<xsl:if test="count(//View/categories/category) = 0">
			You don't have any back-office menu.<br />
		</xsl:if>
		<xsl:if test="count(//View/categories/category) &gt; 0">
			<table cellpadding="5" cellspacing="0" border="1">
				<tr style="background-color:#E9E9E9;color:#000;">
					<th>Category</th>
					<th>Delete</th>
				</tr>
				<xsl:for-each select="//View/categories/category">
					<xsl:sort select="." />
					<tr>
						<td><xsl:value-of select="." /></td>
						<td align="center"><a href="#" onclick="confirmDelete('{concat(//View/url_delete_category,'/category/',.)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<a href="{//View/url_add_category}" title="Add a category" style="display:block;"><img src="{concat($sls_url_img_core_icons,'bo16.png')}" title="View" alt="View" style="border:0" align="absmiddle" />&#160;Add a new category</a><br />
				
	</xsl:template>
</xsl:stylesheet>