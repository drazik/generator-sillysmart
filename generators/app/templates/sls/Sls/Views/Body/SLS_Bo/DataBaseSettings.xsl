<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="DataBaseSettings">
	
		<h1>
			<xsl:choose>
				<xsl:when test="//View/is_prod = 'true'">
					SillySmart Deployment <xsl:if test="//View/is_batch = 'true'">- <em style="font-size:0.8em;font-weight:normal;">Step <strong style="color:#D97878;font-weight:normal;">2</strong>/4</em></xsl:if>
				</xsl:when>
				<xsl:otherwise>
					Edit Your Settings
				</xsl:otherwise>
			</xsl:choose>						
		</h1>
		<h2>DataBase</h2>					
		<fieldset>
			<legend>DataBase Settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" name="" enctype="multipart/form-data" method="post">
				<xsl:if test="//View/is_batch != 'true' and //View/is_prod != 'true'">
					<input type="checkbox" name="export" id="export" />
					<label for="export">Export the configuration</label><br />
				</xsl:if>
				<xsl:for-each select="//View/dbs/db">
					<fieldset style="float:left;margin:5px 5px 5px 0;">
						<legend><xsl:value-of select="alias" /><xsl:if test="position() &gt; 1">&#160;<a href="{url_delete}" title="Delete"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" /></a></xsl:if></legend>
						<table>
							<xsl:if test="default = 'true'">
								<tr>
									<td colspan="2">
										Default Database 
										<input type="checkbox" disabled="disabled" checked="checked" />
									</td>
								</tr>
							</xsl:if>
							<tr>
								<td>Charset</td>
								<td>
									<select name="{concat('charset_',alias)}">
										<xsl:variable name="charset" select="charset" />													
										<xsl:for-each select="//View/charsets/charset">
											<option value="{.}">
												<xsl:if test="$charset = '' and . = 'utf8'">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:if test="$charset = .">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:value-of select="." />
											</option>
										</xsl:for-each>
									</select>
								</td>
							</tr>
							<tr>
								<td>Host</td>
								<td><input type="text" name="{concat('host_',alias)}" value="{host}" /></td>
							</tr>
							<tr>
								<td>Database</td>
								<td><input type="text" name="{concat('base_',alias)}" value="{base}" /></td>
							</tr>
							<tr>
								<td>User</td>
								<td><input type="text" name="{concat('user_',alias)}" value="{user}" /></td>
							</tr>
							<tr>
								<td>Password</td>
								<td><input type="password" name="{concat('pass_',alias)}" /></td>
							</tr>
							<tr>
								<td colspan="2">
									<label for="{concat('no_pass_',alias)}">Don't require a password</label>
									<input type="checkbox" name="{concat('no_pass_',alias)}" id="{concat('no_pass_',alias)}">
										<xsl:if test="pass = ''"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
									</input>
								</td>											
							</tr>
						</table>
					</fieldset>
				</xsl:for-each>
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Confirm Changes" style="display:block;clear:both;" />
			</form>
		</fieldset>
		<a href="{//View/url_add_database}" title="Add a new database">Add a new database</a><br />
		You can't delete your default database but you can edit it !
				
	</xsl:template>
</xsl:stylesheet>