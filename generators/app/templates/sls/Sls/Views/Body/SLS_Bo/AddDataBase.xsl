<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddDataBase">
	
		<h1>Add a database</h1>
		<fieldset>
			<legend>Db settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<input type="hidden" name="ping" id="ping" value="false" />
				<table>								
					<tr>
						<td>
							<label for="alias">Database alias :</label>
						</td>
						<td>
							<input type="text" name="alias" id="alias" value="{//View/db/alias}" />
						</td>
					</tr>
					<tr>
						<td>Charset</td>
						<td>
							<select name="charset" style="width:145px;">
								<xsl:for-each select="//View/charsets/charset">
									<option value="{.}">
										<xsl:if test="count(//View/db/*) = 0 and . = 'utf8'">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>
										<xsl:if test="count(//View/db/*) &gt; 0 and //View/db/charset = .">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="." />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for="host">MySQL Hostname :</label>
						</td>
						<td>
							<input type="text" name="host" id="host" value="{//View/db/host}" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="db">Database name :</label>
						</td>
						<td>
							<input type="text" name="db" id="db" value="{//View/db/db}" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="user">Username :</label>
						</td>
						<td>
							<input type="text" name="user" id="user" value="{//View/db/user}" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="pass">Password :</label>
						</td>
						<td>
							<input type="password" name="pass" id="pass" /> 
							<label for="no_pass">No password</label> 
							<input type="checkbox" name="no_pass" id="no_pass">
								<xsl:if test="//View/db/no_pass = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="Add database" />
							<input type="submit" value="Test Connection" onclick="document.getElementById('ping').value='true';" />
						</td>
					</tr>
					<xsl:if test="count(//View/ping) &gt; 0">
						<tr>
							<td colspan="2">											
								<xsl:if test="//View/ping = 'true'">
									<div style="color:green;">Connection succeed</div>
								</xsl:if>
								<xsl:if test="//View/ping != 'true'">
									<div style="color:red;">Connection failed with message :<br /><xsl:value-of select="//View/ping" /></div>
								</xsl:if>
							</td>
						</tr>
					</xsl:if>
				</table>
			</form>
		</fieldset>					
				
	</xsl:template>
</xsl:stylesheet>