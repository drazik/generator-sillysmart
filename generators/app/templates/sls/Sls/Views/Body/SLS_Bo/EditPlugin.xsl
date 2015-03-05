<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditPlugin">
	
		<h1>Plugins Management</h1>
		<h2><xsl:value-of select="concat('Edit ', //View/plugin_infos/name, ' Settings (v', //View/plugin_infos/version, ')')" /></h2>
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<div style="color:red;">
				<xsl:for-each select="//View/errors/error">
					<xsl:value-of select="."/><br />
				</xsl:for-each>
			</div>
		</xsl:if>
		<xsl:if test="count(//View/success) &gt; 0 and //View/success = 'ok'">
			<div style="color:green;">
				Your modifications are saved
			</div>
		</xsl:if>
		<form action="" method="post" enctype="mutlipart/form-data">
			<xsl:call-template name="EditPlugins">
				<xsl:with-param name="xpath" select="//View/fields/field" />
			</xsl:call-template>
			<input type="submit" value="Save" />
			<input type="hidden" name="reload" value="true" />	
		</form>
			
	</xsl:template>
</xsl:stylesheet>