<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditStaticController">
	
		<h1>Manage your Controllers &amp; Actions</h1>
		
		<h2>Modify a Static Controller</h2>
		<form action="" method="post" enctype="multipart/form-data">
			<table>
				<xsl:if test="count(//View/form) &gt; 0 and count(//View/errors/error) &gt; 0">
					<tr>
						<td colspan="2" style="padding-bottom:20px;color:red;">
							<xsl:for-each select="//View/errors/error">
								<xsl:value-of select="." /><br />
							</xsl:for-each>
						</td>
					</tr>
				</xsl:if>
			</table>
			
			<fieldset>
				<legend>Routing</legend>
				<table>
					<tr>
						<td>Static Controller Name :</td>
						<td>
							<input type="text" value="{//View/controller/name}" name="controllerName">
								<xsl:if test="count(//View/form) = 1 and //View/form/controllerName != ''">
									<xsl:attribute name="value">
										<xsl:value-of select="//View/form/controllerName" />
									</xsl:attribute>
								</xsl:if>
							</input>
							<input type="hidden" name="oldName" value="{//View/controller/name}" />
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
			<legend>Cache</legend>
			<table>
				<tr>
					<td>Visibility :</td>
					<td>
						<select name="cache_visibility" id="cache_visibility">
							<option value="">--No Cache</option>
							<option value="private">
								<xsl:if test="//View/form/cache_visibility = 'private'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								Private
							</option>
							<option value="public">
								<xsl:if test="//View/form/cache_visibility = 'public'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								Public
							</option>
						</select>
					</td>
				</tr>
				<tr height="10"></tr>
				<tr>
					<td><label for="cache_responsive">Responsive :</label></td>
					<td>
						<input type="checkbox" name="cache_responsive" id="cache_responsive" value="true"><xsl:if test="//View/form/cache_responsive = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
					</td>
				</tr>
				<tr height="10"></tr>
				<tr>
					<td>Expiration :</td>
					<td>
						<input type="text" name="cache_expiration" id="cache_expiration" value="{//View/form/cache_expiration}" /> seconds <span style="font-style:italic;font-size:0.8em;color:#000;">&#160;(0: unlimited)</span>
					</td>
				</tr>
			</table>
		</fieldset>
			
			<table>
				<tr>
					<td colspan="2"><input type="submit" value="Modify" /><input type="hidden" name="reload" value="true" /></td>
				</tr>
			</table>
		</form>
					
				
	</xsl:template>
</xsl:stylesheet>