<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditController">
	
		<h1>Manage your Controllers &amp; Actions</h1>
		
		<h2>Modify a Controller</h2>
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
				<xsl:if test="count(//View/form) &gt; 0 and count(//View/errors/error) = 0">
					<tr>
						<td colspan="2" style="padding-bottom:20px;color:green;">
							Your modifications have been saved
						</td>
					</tr>
				</xsl:if>
			</table>
			<fieldset>
				<legend>Routing</legend>
				<table>				
					<tr>
						<td><label for="controllerName">Generic Controller Name:</label></td>
						<td>
							<xsl:if test="//View/controller/locked = 1">
								<input type="hidden" value="{//View/controller/name}" name="controllerName" id="controllerName" />
							</xsl:if>
							<input type="text" value="{//View/controller/name}" name="controllerName" id="controllerName">
								<xsl:if test="//View/controller/locked = 1">
									<xsl:attribute name="disabled" select="'disabled'" />
								</xsl:if>
								<xsl:if test="count(//View/form) = 1 and //View/form/controllerName != ''">
									<xsl:attribute name="value">
										<xsl:value-of select="//View/form/controllerName" />
									</xsl:attribute>
								</xsl:if>
							</input>
							<input type="hidden" name="oldName" value="{//View/controller/name}" />
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="protocol">Protocol:</label></td>
						<td>
							<select name="protocol" id="protocol">
								<option value="http">
									<xsl:if test="//View/protocol = 'http'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									http
								</option>
								<option value="https">
									<xsl:if test="//View/protocol = 'https'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									https
								</option>
							</select>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td colspan="2"><h2>Routes Translations</h2></td>
					</tr>
					<xsl:for-each select="//View/controller/translations/translation">
						<xsl:variable name="inputName" select="concat(lang, '-controller')" />
						<tr>
							<td>
								<label for="{$inputName}"><xsl:value-of select="concat('Translation in ', lang, ':')" /></label>
							</td>	 
							<td>
								<input type="text" name="{$inputName}" id="{$inputName}" value="{name}">
									<xsl:if test="count(//View/form) = 1 and //View/form/*[name()=$inputName] != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="//View/form/*[name()=$inputName]" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</fieldset>
			<fieldset>			
				<legend>Bindings</legend>
				<table>
					<tr>
						<td><label for="template">Template:</label></td>
						<td>
							<select name="template" id="template">
								<option value="-1">Default</option>
								<xsl:for-each select="//View/tpls/tpl[.!='__default']">
									<option value="{.}">
										<xsl:if test="//View/template = ."><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="." />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			<table>
				<tr>
					<td colspan="2">
						<input type="submit" value="Modify" />
						<input type="hidden" name="reload" value="true" />
					</td>
				</tr>
			</table>
		</form>
					
			
	</xsl:template>
</xsl:stylesheet>