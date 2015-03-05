<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GlobalSettings">
	<script type="text/javascript">
		var settingsForm = null;
		window.addEvent('domready', function(){
			settingsForm = new Form('settingsForm');
			$('submit').addEvent('click', function(e) {
				settingsForm.submit();
				e.stop();
			});			
		});
		function switchArea(){
			$$('select.timezone_city').each(function(element, index){
				element.setStyle('display','none');
			});
			$('settings_timezone_area_' + $('timezone').get('value')).setStyle('display','block');
		}
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
						<span class="focus">Global Settings </span><img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						International <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						DataBase <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Mails</div>
					<h1>Installation</h1>
					<h2>Global Settings</h2>
					<fieldset class="sls_init_form">
						<legend>Global Settings</legend>
						<xsl:if test="count(//View/errors/error) &gt; 0">
							<ul style="display:block;width:70%;font-size:0.8em;margin:0 auto;margin-bottom:20px;text-align:center;color:red;font-weight:900">
								<xsl:for-each select="//View/errors/error">
									<li><xsl:value-of select="." /></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
						<form method="post" id="settingsForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='GLOBALSETTINGS']/href}">
							<label for="protocol">Global Protocol:</label>
							<select name="settings_protocol" id="protocol">
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
							</select><div style="clear:both;"></div>
							<label for="domain">Your Main Domain name:</label><input type="text" name="settings_domain" id="domain" value="{//View/domain}" onchange="document.getElementById('bolabel').value='http://'+this.value+'/';" style="width:155px;" /><div style="clear:both;"></div>
							<label for="project">Your Project name:</label><input type="text" name="settings_project" id="project" value="{//View/project}" style="width:200px;" /><div style="clear:both;"></div>
							<label for="description">Your Project description:</label><input type="text" name="settings_description" id="description" value="{//View/description}" style="width:200px;" /><div style="clear:both;"></div>
							<label for="keywords">Your Project Keywords:</label><input type="text" name="settings_keywords" id="keywords" value="{//View/keywords}" style="width:200px;" /><div style="clear:both;"></div>
							<label for="author">Author Name:</label><input type="text" name="settings_author" id="author" value="{//View/author}" style="width:200px;" /><div style="clear:both;"></div>
							<label for="copyright">Copyright:</label><input type="text" name="settings_copyright" id="copyright" value="{//View/copyright}" style="width:200px;" /><div style="clear:both;"></div>
							<label for="extension">Your default extension:</label><input type="text" name="settings_extension" id="extension" value="{//View/extension}" style="width:200px;" onchange="document.getElementById('bosuffix').value='/Home.'+this.value;" /><div style="clear:both;"></div>
							<label for="charset">Your Charset:</label>
							<select name="settings_charset" id="charset" style="display:block;width:200px;margin-top:5px;">
								<xsl:for-each select="//View/charsets/charset">
									<option value="{code}">
										<xsl:if test="count(//View/charset) = 0 and code = 'utf-8'">
											<xsl:attribute name="selected" value="'selected'" />
										</xsl:if>
										<xsl:if test="count(//View/charset) &gt; 0 and //View/charset = code">
											<xsl:attribute name="selected" value="'selected'" />
										</xsl:if>
										<xsl:value-of select="code" />
									</option>
								</xsl:for-each>
							</select><div style="clear:both;"></div>
							<label for="doctype">Default Doctype :</label>
							<select name="settings_doctype" id="doctype">
								<option value="xhtml_1.0_transitionnal">
									<xsl:if test="//View/doctype = 'xhtml_1.0_transitionnal'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.0 Transitionnal
								</option>
								<option value="xhtml_1.0_strict">
									<xsl:if test="//View/doctype = 'xhtml_1.0_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.0 Strict
								</option>
								<option value="xhtml_1.1_strict">
									<xsl:if test="//View/doctype = 'xhtml_1.1_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									xHTML 1.1 Strict
								</option>
								<option value="html_5">
									<xsl:if test="//View/doctype = 'html_5'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 5
								</option>
								<option value="html_4.01_transitionnal">
									<xsl:if test="//View/doctype = 'html_4.01_transitionnal'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 4.01 Transitionnal
								</option>
								<option value="html_4.01_strict">
									<xsl:if test="//View/doctype = 'html_4.01_strict'">
										<xsl:attribute name="selected" select="'selected'" />
									</xsl:if>
									HTML 4.01 Strict
								</option>
							</select><div style="clear:both;"></div>
							<label for="timezone">Timezone Area:</label>
							<select name="settings_timezone_area" id="timezone" style="display:block;width:200px;margin-top:5px;float:left;" onchange="switchArea()">
								<xsl:for-each select="//View/timezones/areas/area">
									<option value="{@id}">
										<xsl:if test="//View/timezone/area = @id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										<xsl:value-of select="@id" />
									</option>
								</xsl:for-each>
							</select>
							<div style="clear:both;"></div>
							<label for="timezone_city">Timezone City:</label>
							<xsl:for-each select="//View/timezones/areas/area">
								<select name="settings_timezone_area_{@id}" id="settings_timezone_area_{@id}" class="timezone_city">
									<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/timezone/area = @id or (count(//View/timezone/area) = 0 and position() = 1)">display:block;width:200px;margin-top:5px;float:left;</xsl:when><xsl:otherwise>display:none;width:200px;margin-top:5px;float:left;</xsl:otherwise></xsl:choose></xsl:attribute>
									<xsl:for-each select="cities/city">
										<option value="{.}">
											<xsl:if test="//View/timezone/city = ."><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
											<xsl:value-of select="." />
										</option>
									</xsl:for-each>
								</select>
							</xsl:for-each>
							<div style="clear:both;"></div>
							<label for="bo">The access to your SillySmart's Back-Office:</label><span style="float:left;"><input type="text" disabled="disabled" id="bolabel" value="http://" style="width:100px;text-align:right;margin-top:12px;" /></span><input type="text" name="settings_bo" id="bo" value="{//View/bo}" style="width:100px;margin-top:12px;" /><input type="text" id="bosuffix" disabled="disabled" value="/Home.sls" style="width:100px;margin-top:12px;" /><div style="clear:both;"></div>
							<div id="buttons_panel">
								<input type="hidden" name="globalSettings_reload" value="true" />
								<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}" title="Next" class="next">Next</a>
							</div>
						</form>
					</fieldset>
				</div>
			</div>			
		</div>		
	</xsl:template>
</xsl:stylesheet>