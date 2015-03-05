<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddAction">
	
		<h1>Manage your Controllers &amp; Actions</h1>
		
		<h2>Add a new Action to the controller <xsl:value-of select="View/controller/name" /></h2>
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
						<td><label for="actionName">Generic Action Name :</label></td>
						<td colspan="2">
							<input type="text" value="{//View/action/name}" name="actionName" id="actionName">
								<xsl:if test="count(//View/form) = 1 and //View/form/actionName != ''">
									<xsl:attribute name="value">
										<xsl:value-of select="//View/form/actionName" />
									</xsl:attribute>
								</xsl:if>
							</input>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="protocol">Protocol :</label></td>
						<td colspan="2">
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
						<td><label for="domains">Domains on which<br />this action is unreachable :</label></td>
						<td colspan="2">
							<select name="domains[]" id="domains" multiple="multiple">
								<xsl:attribute name="size"><xsl:choose><xsl:when test="count(//View/aliases/alias) &gt; 6">6</xsl:when><xsl:otherwise><xsl:value-of select="count(//View/aliases/alias)" /></xsl:otherwise></xsl:choose></xsl:attribute>
								<xsl:for-each select="//View/aliases/alias">
									<option value="{name}">
										<xsl:if test="(count(//View/aliases/alias[selected='true']) &gt; 0 and selected='true')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td colspan="3"><h2>Routes Translations</h2></td>
					</tr>
					<xsl:for-each select="//View/controller/translations/translation">
						<xsl:variable name="position" select="position()" />
						<xsl:variable name="inputName" select="concat(lang, '-action')" />
						<tr>
							<td>
								<label for="{$inputName}"><xsl:value-of select="concat('Translation in ', lang, ' :')" /></label>
							</td>
							<td>					
								<input type="text" disabled="disabled" value="{//View/controller/translations/translation[$position]/name}" />
								&#160;/&#160;
							</td>	 
							<td>
								<input type="text" name="{$inputName}" id="{$inputName}" value="">
									<xsl:if test="count(//View/form) = 1">
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
						<td><label for="template">Main view template :</label></td>
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
					<tr height="10"></tr>
					<tr>
						<td><label for="components">Components :</label></td>
						<td>
							<select name="components[]" id="components" multiple="multiple">
								<xsl:attribute name="size"><xsl:choose><xsl:when test="count(//View/components/component) &gt; 6">6</xsl:when><xsl:otherwise><xsl:value-of select="count(//View/components/component)" /></xsl:otherwise></xsl:choose></xsl:attribute>
								<xsl:for-each select="//View/components/component">
									<option value="{name}">
										<xsl:if test="(count(//View/components/component[selected='true']) &gt; 0 and selected='true')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="name" />
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="default">Default action of Controller <xsl:value-of select="View/controller/name" /> ?</label></td>
						<td>
							<input type="checkbox" name="default" id="default">
								<xsl:if test="//View/default = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Cache</legend>
				<table>					
					<tr>
						<td><label for="cache_visibility">Visibility :</label></td>
						<td>
							<select name="cache_visibility" id="cache_visibility">
								<option value="">--No Cache</option>
								<option value="private">
									<xsl:if test="//View/cache/cache_visibility = 'private'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									Private
								</option>
								<option value="public">
									<xsl:if test="//View/cache/cache_visibility = 'public'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									Public
								</option>
							</select>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="cache_scope">Scope :</label></td>
						<td>
							<select name="cache_scope" id="cache_scope">
								<option value="">--No scope</option>
								<option value="partial">
									<xsl:if test="//View/cache/cache_scope = 'partial'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									Partial
								</option>
								<option value="full">
									<xsl:if test="//View/cache/cache_scope = 'full'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									Full
								</option>
							</select>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="cache_responsive">Responsive :</label></td>
						<td>
							<input type="checkbox" name="cache_responsive" id="cache_responsive" value="true"><xsl:if test="//View/cache/cache_responsive = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
						</td>
					</tr>
					<tr height="10"></tr>
					<tr>
						<td><label for="cache_expiration">Expiration :</label></td>
						<td>
							<input type="text" name="cache_expiration" id="cache_expiration" value="{//View/cache/cache_expiration}" /> seconds <span style="font-style:italic;font-size:0.8em;color:#000;">&#160;(0: unlimited)</span>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>SEO</legend>
				<table>					
					<tr>
						<td colspan="2"><h2>Page Titles Translations</h2></td>
					</tr>
					<xsl:for-each select="//View/controller/translations/translation">
						<xsl:variable name="inputName" select="concat(lang, '-title')" />
						<tr>
							<td>
								<label for="{$inputName}"><xsl:value-of select="concat('Title Translation in ', lang, ' :')" /></label>
							</td>	 
							<td>										
								<input type="text" name="{$inputName}" id="{$inputName}" value="{title}">
									<xsl:if test="count(//View/form) = 1 and //View/form/*[name()=$inputName] != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="//View/form/*[name()=$inputName]" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</td>
						</tr>
					</xsl:for-each>
					<tr height="20"></tr>
					<tr>
						<td colspan="2"><h2>Descriptions Translations</h2></td>
					</tr>
					<xsl:for-each select="//View/controller/translations/translation">
						<xsl:variable name="inputName" select="concat(lang, '-description')" />
						<tr>
							<td>
								<label for="{$inputName}"><xsl:value-of select="concat('Description Translation in ', lang, ' :')" /></label>
							</td>	 
							<td>										
								<input type="text" name="{$inputName}" id="{$inputName}" value="{description}">
									<xsl:if test="count(//View/form) = 1 and //View/form/*[name()=$inputName] != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="//View/form/*[name()=$inputName]" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</td>
						</tr>
					</xsl:for-each>
					<tr height="20"></tr>
					<tr>
						<td colspan="2"><h2>Keywords Translations</h2></td>
					</tr>
					<xsl:for-each select="//View/controller/translations/translation">
						<xsl:variable name="inputName" select="concat(lang, '-keywords')" />
						<tr>
							<td>
								<label for="{$inputName}"><xsl:value-of select="concat('Keywords in ', lang, ' :')" /></label>
							</td>	 
							<td>										
								<input type="text" name="{$inputName}" id="{$inputName}" value="{keywords}">
									<xsl:if test="count(//View/form) = 1 and //View/form/*[name()=$inputName] != ''">
										<xsl:attribute name="value">
											<xsl:value-of select="//View/form/*[name()=$inputName]" />
										</xsl:attribute>
									</xsl:if>
								</input>
							</td>
						</tr>
					</xsl:for-each>
					<tr height="20"></tr>
					<tr>
						<td><label for="indexes">Behavior for this action :</label></td>
						<td>
							<select name="indexes" id="indexes">
								<option value="index,follow">Index the page and follow all links</option>
								<option value="noindex,nofollow">Doesn't index and doesn't follow links</option>
								<option value="index,nofollow">Index the page but doesn't follow links</option>
								<option value="noindex,follow">Doesn't index the page but follow links</option>
							</select>
						</td>
					</tr>
				</table>	
			</fieldset>
			
			<fieldset>
				<legend>Various</legend>
				<table>
					<tr>
						<td><label for="dynamic">This action will need dynamic parameters :</label></td>
						<td>
							<input type="checkbox" name="dynamic" id="dynamic">
								<xsl:if test="//View/dynamic = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</td>
					</tr>				
					<tr height="10"></tr>
					<tr>
						<td><label for="offline">This action is offline :</label></td>
						<td>
							<input type="checkbox" name="offline" id="offline">
								<xsl:if test="//View/offline = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</td>
					</tr>
				</table>
			</fieldset>
						
			<table>	
				<tr>
					<td colspan="2">
						<input type="submit" value="Add a new Action" />
						<input type="hidden" name="reload" value="true" />
					</td>
				</tr>
			</table>
		</form>
				
	</xsl:template>
</xsl:stylesheet>