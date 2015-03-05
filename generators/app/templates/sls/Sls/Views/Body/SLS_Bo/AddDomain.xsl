<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddDomain">
	
		<h1>Edit Your Settings</h1>
		<h2>Global Settings</h2>
		<fieldset>
			<legend>Global Settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" name="" enctype="multipart/form-data" method="post">
				<input type="hidden" name="reload" value="true" />
				<table border="0">
					<tr>
						<td><label for="domain_alias">Alias</label></td>
						<td><input type="text" name="alias" id="domain_alias" value="{//View/domain/alias}" /></td>
					</tr>
					<tr>
						<td><label for="domain_domain">Domain</label></td>
						<td><input type="text" name="domain" id="domain_domain" value="{//View/domain/domain}" /></td>
					</tr>					
					<tr>
						<td><label for="domain_lang">Lang</label></td>
						<td>
							<select name="lang" id="domain_lang">
								<option value="">--None</option>
								<xsl:for-each select="//View/langs/lang">
									<option value="{.}">
										<xsl:if test="//View/domain/lang = ."><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="." />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="checkbox" name="cdn" id="domain_cdn" value="true"><xsl:if test="//View/domain/cdn = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input> <label for="domain_cdn">CDN ?</label><br /></td>	
					</tr>
				</table>
				<input type="submit" value="Add domain" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>