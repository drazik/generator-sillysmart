<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GlobalSettings">

		<h1>
			<xsl:choose>
				<xsl:when test="//View/is_prod = 'true'">								
					SillySmart Deployment <xsl:if test="//View/is_batch = 'true'">- <em style="font-size:0.8em;font-weight:normal;">Step <strong style="color:#D97878;font-weight:normal;">1</strong>/4</em></xsl:if>
				</xsl:when>
				<xsl:otherwise>
					Edit Your Settings
				</xsl:otherwise>
			</xsl:choose>						
		</h1>
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
				<xsl:if test="//View/is_batch != 'true' and //View/is_prod != 'true'">
					<input type="checkbox" name="export" id="export" />
					<label for="export">Export the configuration</label><br />
				</xsl:if>
								
				<fieldset>
					<legend>Domains</legend>
					<table border="0">
						<xsl:for-each select="//View/current_values/domains/domain">
							<tr>
								<td>
									<label for="{concat('domain_',alias)}"><xsl:value-of select="alias" /> :</label>
								</td>
								<td>
									<input type="text" name="{concat('domain_',alias)}" id="{concat('domain_',alias)}" value="{domain}" />
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="lang != ''">
											<span style="font-size:0.7em;color:#000;"><i>&lt;<xsl:value-of select="lang" />&gt;</i></span>
										</xsl:when>
										<xsl:otherwise>&#160;</xsl:otherwise>
									</xsl:choose>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="default = 'false'">
											&#160;<a href="#" onclick="confirmDelete('{delete_url}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />&#160;Delete</a>										
										</xsl:when>
										<xsl:otherwise>&#160;</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>	
					</table>					
					<xsl:if test="//View/is_batch != 'true' and //View/is_prod != 'true'"><br />
						<a href="{//View/add_domain_url}" title="Add domain"><img src="{concat($sls_url_img_core_icons,'add16.png')}" style="border:0;" align="absmiddle" />&#160;Add another domain</a>
					</xsl:if>
				</fieldset>
				
				<table border="0">
					<tr>
						<td><label for="project">Project Name :</label></td>
						<td><input type="text" name="project" id="project" value="{//View/current_values/project}" /></td>
					</tr>
					<tr>
						<td><label for="version">Version Name :</label></td>
						<td><input type="text" name="version" id="version" value="{//View/current_values/version}" /></td>
					</tr>
					<tr>
						<td><label for="extension">Default Extension :</label></td>
						<td><input type="text" name="extension" id="extension" value="{//View/current_values/extension}" /></td>
					</tr>
					<tr>
						<td><label for="charset">Default Charset :</label></td>
						<td>
							<select name="charset" id="charset">
								<xsl:for-each select="//View/charsets/charset">
									<option value="{.}">
										<xsl:if test="//View/current_values/charset = .">
											<xsl:attribute name="selected" select="'selected'" />
										</xsl:if>
										<xsl:value-of select="." />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="doctype">Default Doctype :</label></td>
						<td>
							<select name="doctype" id="doctype">
								<option value="xhtml_1.0_transitionnal">
									<xsl:if test="//View/current_values/doctype = 'xhtml_1.0_transitionnal'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.0 Transitionnal
								</option>
								<option value="xhtml_1.0_strict">
									<xsl:if test="//View/current_values/doctype = 'xhtml_1.0_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.0 Strict
								</option>
								<option value="xhtml_1.1_strict">
									<xsl:if test="//View/current_values/doctype = 'xhtml_1.1_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.1 Strict
								</option>
								<option value="html_5">
									<xsl:if test="//View/current_values/doctype = 'html_5'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 5
								</option>
								<option value="html_4.01_transitionnal">
									<xsl:if test="//View/current_values/doctype = 'html_4.01_transitionnal'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 4.01 Transitionnal
								</option>
								<option value="html_4.01_strict">
									<xsl:if test="//View/current_values/doctype = 'html_4.01_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 4.01 Strict
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="lang">Default Langage :</label></td>
						<td>
							<select name="lang" id="lang">
								<xsl:for-each select="//View/langs/lang">
									<option value="{.}">
										<xsl:if test="//View/current_values/lang = .">
											<xsl:attribute name="selected" select="'selected'" />
										</xsl:if>
										<xsl:value-of select="." />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="timezone">Default Timezone:</label></td>
						<td>
							<select name="settings_timezone_area" id="timezone" onchange="switchArea()">
								<xsl:for-each select="//View/timezones/areas/area">
									<option value="{@id}">
										<xsl:if test="//View/current_values/timezone/area = @id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="@id" />
									</option>
								</xsl:for-each>
							</select>
							<span> / </span>
							<xsl:for-each select="//View/timezones/areas/area">
								<select name="settings_timezone_area_{@id}" id="settings_timezone_area_{@id}" class="timezone_city">
									<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/current_values/timezone/area = @id or (count(//View/current_values/timezone/area) = 0 and position() = 1)">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
									<xsl:for-each select="cities/city">
										<option value="{.}">
											<xsl:if test="//View/current_values/timezone/city = ."><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
											<xsl:value-of select="." />
										</option>
									</xsl:for-each>
								</select>
							</xsl:for-each>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="domainSessionActive" id="session" onclick="displaySessionDomain()" style="float:left;">
								<xsl:if test="//View/current_values/domain_session != ''">
									<xsl:attribute name="checked">checked</xsl:attribute>
								</xsl:if>
							</input>
							<label for="session" style="float:left;margin-left:2px;">Allow sharing session</label>&#160;
						</td>
						<td>
							<div id="domainSession">								
								<xsl:attribute name="style">
									<xsl:if test="//View/current_values/domain_session != ''">float:left;display:inline;</xsl:if>
									<xsl:if test="//View/current_values/domain_session = ''">float:left;display:none;</xsl:if>
								</xsl:attribute>
								&#160;=> Domain pattern:&#160;<input type="text" name="domainSession" value="{//View/current_values/domain_session}" />
							</div>
						</td>
					</tr>
				</table>
				
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Confirm Changes" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>