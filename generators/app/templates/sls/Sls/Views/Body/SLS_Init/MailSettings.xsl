<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="MailSettings">
	<script type="text/javascript">
		var settingsForm = null;
		window.addEvent('domready', function(){
			settingsForm = new Form('settingsForm');
			$('submit').addEvent('click', function(e) {
				settingsForm.submit();
				e.stop();
			});
		});
	</script>
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<div id="breadcrumbs">Directories Rigths <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Authentication <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Global Settings <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						International <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						DataBase <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						<span class="focus">Mails</span></div>
					<h1>Installation</h1>
					<h2>SMTP Settings</h2>
					<fieldset>
						<legend>SMTP Settings</legend>
						<xsl:if test="count(//View/errors/error) &gt; 0">
							<ul style="display:block;width:70%;font-size:0.8em;margin:0 auto;margin-bottom:20px;text-align:center;color:red;font-weight:900">
								<xsl:for-each select="//View/errors/error">
									<li><xsl:value-of select="." /></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
						<form method="post" id="settingsForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='MAILSETTINGS']/href}">
							<input type="hidden" name="ping" id="ping" value="false" />
							<table>
								<xsl:if test="//View/step = '0'">
									<tr>
										<td>
											Will you need SMTP connection for your application?
										</td>
										<td style="text-align:center;">
											<input type="radio" name="mails_useSmtp" id="mails_useSmtp1" value="true" /><label for="mails_useSmtp1">Yes</label>
											<input type="radio" name="mails_useSmtp" id="mails_useSmtp2" value="false" /><label for="mails_useSmtp2">No</label>
											<input type="hidden" name="mails_reload" value="1" />										
										</td>
									</tr>
								<tr height="10"></tr>
								</xsl:if>
								<xsl:if test="//View/step = '1'">
									<tr>
										<td>
											<label for="host">SMTP Hostname :</label>
										</td>
										<td>
											<input type="text" name="host" id="host" value="{//View/host}" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="port">SMTP Port :</label>
										</td>
										<td>
											<input type="text" name="port" id="port" value="{//View/port}" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="username">SMTP Username :</label>
										</td>
										<td>
											<input type="text" name="username" id="username" value="{//View/username}" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="password">SMTP Password :</label>
										</td>
										<td>
											<input type="password" name="password" id="password" />											
										</td>
									</tr>
									<tr>
										<td>
											<label for="password2">SMTP Password (2) :</label>
										</td>
										<td>											
											<input type="password" name="password2" id="password2" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="defaultDomain">SMTP Domain :</label>
										</td>
										<td>
											<input type="text" name="defaultDomain" id="defaultDomain" value="{//View/defaultDomain}" onchange="document.getElementById('domain1').value=this.value;document.getElementById('domain2').value=this.value;document.getElementById('domain3').value=this.value;" />
										</td>
									</tr>
									<tr>
										<td>
											<label for="defaultNameSender">From :</label>
										</td>
										<td>
											<input type="text" name="defaultNameSender" id="defaultNameSender" value="{//View/defaultNameSender}" /> &lt;<input type="text" name="defaultSender" id="defaultSender" value="{//View/defaultSender}" /><input type="text" disabled="disabled" id="domain1" value="{concat('@',//View/defaultDomain)}" />&gt;
										</td>
									</tr>
									<tr>
										<td>
											<label for="defaultNameReply">Reply To :</label>
										</td>
										<td>
											<input type="text" name="defaultNameReply" id="defaultNameReply" value="{//View/defaultNameReply}" /> &lt;<input type="text" name="defaultReply" id="defaultReply" value="{//View/defaultReply}" /><input type="text" disabled="disabled" id="domain2" value="{concat('@',//View/defaultDomain)}" />&gt;
										</td>
									</tr>
									<tr>
										<td>
											<label for="defaultNameReturn">Return-Path :</label>
										</td>
										<td>
											<input type="text" name="defaultNameReturn" id="defaultNameReturn" value="{//View/defaultNameReturn}" /> &lt;<input type="text" name="defaultReturn" id="defaultReturn" value="{//View/defaultReturn}" /><input type="text" disabled="disabled" id="domain3" value="{concat('@',//View/defaultDomain)}" />&gt;
										</td>
									</tr>
									<tr>
										<td>
											<label for="header">HTML Template Header</label>
										</td>
										<td>
											<textarea name="header" style="width:450px;height:150px;"><xsl:value-of select="//View/header" /></textarea>
										</td>
									</tr>
									<tr>
										<td>
											<label for="footer">HTML Template Footer</label>
										</td>
										<td>
											<textarea name="footer" style="width:450px;height:150px;"><xsl:value-of select="//View/footer" /></textarea>
										</td>
									</tr>
									<tr>
										<td colspan="2">
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
									<input type="hidden" name="mails_reload" value="2" />
								</xsl:if>	
								<tr height="10"></tr>
								<tr>
									<td colspan="2">
										<div id="buttons_panel">
										
										<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='MAILSETTINGS']/href}" title="Next" class="next">Next</a>
										</div>
									</td>
								</tr>
							</table>
						</form>
					</fieldset>
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>