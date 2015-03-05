<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="DataBase">
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
						<span class="focus">DataBase</span> <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Mails</div>
					<h1>Installation</h1>
					<h2>Database Settings</h2>
					<fieldset>
						<legend>Database Settings</legend>
						<xsl:if test="count(//View/errors/error) &gt; 0">
							<ul style="display:block;width:70%;font-size:0.8em;margin:0 auto;margin-bottom:20px;text-align:center;color:red;font-weight:900">
								<xsl:for-each select="//View/errors/error">
									<li><xsl:value-of select="." /></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
						<form method="post" id="settingsForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='DATABASE']/href}">
							<input type="hidden" name="ping" id="ping" value="false" />
							<table>
								<xsl:if test="//View/step = '0'">
									<tr>
										<td>
											Will you use MySQL connection in your application?
										</td>
										<td align="left">
											<input type="radio" name="database_useSql" id="database_useSql1" value="true" onclick="document.getElementById('many_databases').style.display='block';" /><label for="database_useSql1">Yes</label><br />
											<input type="radio" name="database_useSql" id="database_useSql2" value="false" onclick="document.getElementById('many_databases').style.display='none';" /><label for="database_useSql2">No</label>
											<input type="hidden" name="database_reload" value="1" />										
										</td>
									</tr>
									<tr id="many_databases" style="display:none;">
										<td>
											How many databases will you need to use ?
										</td>
										<td align="left">
											<input type="text" name="nb_databases" value="1" style="text-align:right;width:40px;" />
										</td>
									</tr>
								<tr height="10"></tr>
								</xsl:if>
								<xsl:if test="//View/step = '1'">
									<xsl:for-each select="//View/nb_databases/nb_database">
										<xsl:variable name="db_occurence" select="concat('db_',position())" />
										<tr>
											<td colspan="2"><u>Database n°<xsl:value-of select="position()" /></u></td>
										</tr>									
										<xsl:if test="position() = 1">
											<tr>
												<td>
													<label for="{concat('database_default_',position())}">Default database :</label>
												</td>
												<td>
													<input type="checkbox" id="{concat('database_default_',position())}" disabled="disabled" checked="checked" />
												</td>
											</tr>
										</xsl:if>
										<tr>
											<td>
												<label for="{concat('database_alias_',position())}">Database alias :</label>
											</td>
											<td>
												<input type="text" name="{concat('database_alias_',position())}" id="{concat('database_alias_',position())}" value="{//View/dbs/*[name()=$db_occurence]/alias}" />
											</td>
										</tr>
										<tr>
											<td>Charset</td>
											<td>
												<select name="{concat('database_charset_',position())}" style="width:145px;">													
													<xsl:for-each select="//View/charsets/charset">
														<option value="{.}">
															<xsl:if test="count(//View/dbs/*[name()=$db_occurence]/charset) = 0 and . = 'utf8'">
																<xsl:attribute name="selected">selected</xsl:attribute>
															</xsl:if>
															<xsl:if test="//View/dbs/*[name()=$db_occurence]/charset = .">
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
												<label for="{concat('database_host_',position())}">MySQL Hostname :</label>
											</td>
											<td>
												<input type="text" name="{concat('database_host_',position())}" id="{concat('database_host_',position())}" value="{//View/dbs/*[name()=$db_occurence]/host}" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="{concat('database_name_',position())}">Database name :</label>
											</td>
											<td>
												<input type="text" name="{concat('database_name_',position())}" id="{concat('database_name_',position())}" value="{//View/dbs/*[name()=$db_occurence]/name}" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="{concat('database_user_',position())}">Username :</label>
											</td>
											<td>
												<input type="text" name="{concat('database_user_',position())}" id="{concat('database_user_',position())}" value="{//View/dbs/*[name()=$db_occurence]/user}" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="{concat('database_pass_',position())}">Password :</label>
											</td>
											<td>
												<input type="password" name="{concat('database_pass_',position())}" id="{concat('database_pass_',position())}" /> 
												<label for="{concat('database_no_pass_',position())}">No password</label> 
												<input type="checkbox" name="{concat('database_no_pass_',position())}" id="{concat('database_no_pass_',position())}">
													<xsl:if test="//View/dbs/*[name()=$db_occurence]/no_pass = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>
									</xsl:for-each>
									<input type="hidden" name="database_reload" value="2" />
									<input type="hidden" name="nb_databases" value="{count(//View/nb_databases/nb_database)}" />
								</xsl:if>	
								<tr height="10"></tr>
								<xsl:if test="//View/step = '1'">
									<tr>
										<td colspan="2">
											<input type="submit" value="Test Connection(s)" onclick="document.getElementById('ping').value='true';" />
										</td>
									</tr>
								</xsl:if>
								<xsl:if test="count(//View/ping) &gt; 0">
									<tr>
										<td colspan="2">											
											<xsl:if test="//View/ping = 'true'">
												<div style="color:green;">Connection(s) succeed</div>
											</xsl:if>
											<xsl:if test="//View/ping != 'true'">
												<div>
													<ul>
														<xsl:for-each select="//View/ping_msgs/ping_msg">
															<xsl:value-of select="." disable-output-escaping="yes" />
														</xsl:for-each>
													</ul>
												</div>
											</xsl:if>
										</td>
									</tr>
								</xsl:if>
								<tr>
									<td colspan="2">										
										<div id="buttons_panel">										
										<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='DATABASE']/href}" title="Next" class="next">Next</a>
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