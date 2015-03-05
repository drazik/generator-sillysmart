<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EnvironmentsAdd">	
	
		<h1>Manage environments</h1>
		
		<fieldset>
			<legend>Add a new environment</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<table>								
					<tr>
						<td>
							<label for="environment">Environment:</label>
						</td>
						<td>
							<input type="text" name="environment" id="environment" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="Add" />
						</td>
					</tr>
				</table>
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>