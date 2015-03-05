<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditRight">	
	
		<h1>Edit a back-office access of '<xsl:value-of select="//View/login" />'</h1>
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/errors/error" />
			</div>
		</xsl:if>
		<form method="post" action="">
			<input type="hidden" name="reload" value="true" />
			<table>
				<tr>
					<td><label for="name">Name</label></td>
					<td><input type="text" name="lastname" id="lastname" value="{//View/name}" /></td>
				</tr>
				<tr>
					<td><label for="firstname">Firstname</label></td>
					<td><input type="text" name="firstname" id="firstname" value="{//View/firstname}" /></td>
				</tr>
				<tr>
					<td><label for="login">Login</label></td>
					<td><input type="text" name="login" id="login" disabled="disabled" value="{//View/login}" /></td>
				</tr>
				<tr>
					<td><label for="password">New Password</label></td>
					<td><input type="password" name="password" id="password" /></td>
				</tr>
				<tr>
					<td><label for="color">Bo color</label></td>
					<td>
						<xsl:variable name="color"><xsl:choose><xsl:when test="//View/color = ''"><xsl:value-of select="//View/colors/color[1]/@hexa" /></xsl:when><xsl:otherwise><xsl:value-of select="//View/colors/color[. = //View/color]/@hexa" /></xsl:otherwise></xsl:choose></xsl:variable>
						<select name="color" id="color" style="background-color:{$color};" onchange="changeColor()">
							<xsl:for-each select="//View/colors/color">
								<option value="{.}" style="background-color:{@hexa};width:115px;">
									<xsl:if test=". = //View/color"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									<xsl:value-of select="." />
								</option>
							</xsl:for-each>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="enabled">Enabled</label></td>
					<td>
						<input type="radio" name="enabled" id="enabled" value="true"><xsl:if test="//View/enabled = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input><label for="enabled">True</label>
						<input type="radio" name="enabled" id="disabled" value="false"><xsl:if test="//View/enabled = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input><label for="disabled">False</label>
					</td>
				</tr>
				<tr>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td><label for="complexity_pwd_lc">Password Complexity</label></td>
					<td>
						<input type="checkbox" name="complexity_pwd_lc" id="complexity_pwd_lc" value="true">
							<xsl:if test="//View/complexity_pwd_lc = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="complexity_pwd_lc">Lowercase</label><br />
						<input type="checkbox" name="complexity_pwd_uc" id="complexity_pwd_uc" value="true">
							<xsl:if test="//View/complexity_pwd_uc = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="complexity_pwd_uc">Uppercase</label><br />
						<input type="checkbox" name="complexity_pwd_digit" id="complexity_pwd_digit" value="true">
							<xsl:if test="//View/complexity_pwd_digit = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="complexity_pwd_digit">Digits</label><br />
						<input type="checkbox" name="complexity_pwd_special_char" id="complexity_pwd_special_char" value="true">
							<xsl:if test="//View/complexity_pwd_special_char = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="complexity_pwd_special_char">Special chars</label><br />
						<input type="checkbox" name="complexity_pwd_min_char" id="complexity_pwd_min_char" value="true">
							<xsl:if test="//View/complexity_pwd_min_char = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<input type="text" name="complexity_pwd_min_char_nb" id="complexity_pwd_min_char_nb" value="{//View/complexity_pwd_min_char_nb}" style="width:15px" />
						<label for="complexity_pwd_min_char">Minimum chars</label><br />
					</td>
				</tr>
				<tr>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td><label for="renew_pwd">Password Renew</label></td>
					<td>
						<input type="checkbox" name="reset_pwd" id="reset_pwd" value="true">
							<xsl:if test="//View/reset_pwd = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="reset_pwd">Must be changed at the first authentication</label><br />
						<input type="checkbox" name="renew_pwd" id="renew_pwd" value="true">
							<xsl:if test="//View/renew_pwd = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="renew_pwd">Must be renewed every</label>&#160;
						<input type="text" name="renew_pwd_nb" id="renew_pwd_nb" value="{//View/renew_pwd_nb}" style="width:15px" />
						<select name="renew_pwd_unite" id="renew_pwd_unite">
							<option value="day">
								<xsl:if test="//View/renew_pwd_unite = 'day'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								days
							</option>
							<option value="week">
								<xsl:if test="//View/renew_pwd_unite = 'week'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								weeks
							</option>
							<option value="month">
								<xsl:if test="//View/renew_pwd_unite = 'month'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								months
							</option>
							<option value="year">
								<xsl:if test="//View/renew_pwd_unite = 'year'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								years
							</option>
						</select><br />
						<input type="checkbox" name="renew_pwd_log" id="renew_pwd_log" value="true">
							<xsl:if test="//View/renew_pwd_log = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
						</input>
						<label for="renew_pwd_log">Log</label>&#160;<input type="text" name="renew_pwd_log_nb" id="renew_pwd_log_nb" value="{//View/renew_pwd_log_nb}" style="width:15px" />&#160;<label for="renew_pwd_log">previous passwords (to prevent user choosing the same password)</label>
					</td>
				</tr>
				<tr>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td>Privilege</td>
					<td>
						<xsl:for-each select="//View/bo_groups/bo_group[count(action) &gt; 0]">
							<fieldset style="float:left;margin-right:20px;">
								<legend>
									<input type="checkbox" onclick="javascript:checkAll('{name}',{count(action)})" id="{name}">
										<xsl:if test="count(action) = count(action[selected='true'])"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
									</input>
									<label for="{name}"><xsl:value-of select="name" /></label>
								</legend>
								<div>
									<xsl:for-each select="action">
										<input type="checkbox" name="{concat('bo_action_',../name,'_',model,'_',name)}" id="{concat(../name,'_',position())}" value="{id}">
											<xsl:if test="selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
										</input>
										<label for="{concat(../name,'_',position())}"><xsl:value-of select="name" /></label><br />
									</xsl:for-each>
								</div>
							</fieldset>
						</xsl:for-each>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="left"><input type="submit" value="Update" /></td>								
				</tr>
			</table>
		</form>

	</xsl:template>
</xsl:stylesheet>