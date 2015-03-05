<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="MailSettings">
	
			<h1>
				<xsl:choose>
					<xsl:when test="//View/is_prod = 'true'">
						SillySmart Deployment <xsl:if test="//View/is_batch = 'true'">- <em style="font-size:0.8em;font-weight:normal;">Step <strong style="color:#D97878;font-weight:normal;">3</strong>/4</em></xsl:if>
					</xsl:when>
					<xsl:otherwise>
						Edit Your Settings
					</xsl:otherwise>
				</xsl:choose>						
			</h1>
			<h2>Mails</h2>
			<fieldset>
				<legend>Mails Settings</legend>
				<xsl:if test="count(//View/errors/error) &gt; 0">
					<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
						<xsl:for-each select="//View/errors/error">
							<xsl:value-of select="." /><br />
						</xsl:for-each>
					</div>
				</xsl:if>
				<form action="" name="" enctype="multipart/form-data" method="post">
					<input type="hidden" name="ping" id="ping" value="false" />
					<input type="hidden" name="reload" id="reload" value="true" />
					<xsl:if test="//View/is_batch != 'true' and //View/is_prod != 'true'">
						<input type="checkbox" name="export" id="export" />
						<label for="export">Export the configuration</label><br /><br />
					</xsl:if>							
					
					<table border="0">
						<tr>
							<td><label for="host">SMTP Host :</label></td>
							<td><input type="text" name="host" id="host" value="{//View/current_values/host}" /></td>
						</tr>
						<tr>
							<td><label for="port">Host Port :</label></td>
							<td><input type="text" name="port" id="port" value="{//View/current_values/port}" /></td>
						</tr>
						<tr>
							<td><label for="user">Username :</label></td>
							<td><input type="text" name="user" id="user" value="{//View/current_values/user}" /></td>
						</tr>
						<tr>
							<td><label for="needuser">This SMTP doesn't need username :</label></td>
							<td>
								<input type="checkbox" name="needuser" id="needuser">
									<xsl:if test="//View/current_values/user = ''">
										<xsl:attribute name="checked" select="'checked'" />
									</xsl:if>
								</input>
							</td>
						</tr>
						<tr>
							<td><label for="pass">Password :</label></td>
							<td><input type="password" name="pass" id="pass" value="" /></td>
						</tr>
						<tr>
							<td><label for="needpass">This SMTP doesn't need password :</label></td>
							<td>
								<input type="checkbox" name="needpass" id="needpass">
									<xsl:if test="//View/current_values/pass = ''">
										<xsl:attribute name="checked" select="'checked'" />
									</xsl:if>
								</input>
							</td>
						</tr>
						<tr>
							<td><label for="domain">Default Domain :</label></td>
							<td><input type="text" name="domain" id="domain" value="{//View/current_values/domain}" onChange="document.getElementById('domainName1').value=this.value;document.getElementById('domainName2').value=this.value;document.getElementById('domainName3').value=this.value;" /></td>
						</tr>
						<tr>
							<td><label for="nameReturn">Return :</label></td>
							<td><input type="text" name="nameReturn" id="nameReturn" value="{//View/current_values/nameReturn}" /> <input type="text" name="return" id="return" value="{//View/current_values/return}" />@<input type="text" value="{//View/current_values/domain}" id="domainName1" disabled="disabled" /></td>
						</tr>
						<tr>
							<td><label for="nameReply">Reply to :</label></td>
							<td><input type="text" name="nameReply" id="nameReply" value="{//View/current_values/nameReply}" /> <input type="text" name="reply" id="reply" value="{//View/current_values/reply}" />@<input type="text" value="{//View/current_values/domain}" id="domainName2" disabled="disabled" /></td>
						</tr>
						<tr>
							<td><label for="nameSender">Sender :</label></td>
							<td><input type="text" name="nameSender" id="nameSender" value="{//View/current_values/nameSender}" /> <input type="text" name="sender" id="sender" value="{//View/current_values/return}" />@<input type="text" value="{//View/current_values/domain}" id="domainName3" disabled="disabled" /></td>
						</tr>
					</table>
					
					<input type="submit" value="Confirm Changes" />
					<input type="submit" value="Test Connection" onclick="document.getElementById('ping').value='true';" />
				</form>
				<xsl:if test="count(//View/ping) &gt; 0">																	
					<xsl:if test="//View/ping = 'true'">
						<div style="color:green;">Connection succeed</div>
					</xsl:if>
					<xsl:if test="//View/ping != 'true'">
						<div style="color:red;">Connection failed with message :<br /><xsl:value-of select="//View/ping" /></div>
					</xsl:if>							
				</xsl:if>
			</fieldset>
		
		<a href="{//View/url_mail_templates}" title="Email Templates">Manage email templates</a>
				
	</xsl:template>
</xsl:stylesheet>