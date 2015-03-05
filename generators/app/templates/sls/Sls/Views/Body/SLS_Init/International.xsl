<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="International">
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
						<span class="focus">International</span> <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						DataBase <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Mails</div>
					<h1>Installation</h1>
					<h2>International Settings</h2>
					<fieldset>
						<legend>International Settings</legend>
						<xsl:if test="count(//View/errors/error) &gt; 0">
							<ul style="display:block;width:70%;font-size:0.8em;margin:0 auto;margin-bottom:20px;text-align:center;color:red;font-weight:900">
								<xsl:for-each select="//View/errors/error">
									<li><xsl:value-of select="." /></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
						
						<!-- Par défaut -->
						<xsl:if test="//View/step = '0'">
							<form method="post" id="settingsForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='INTERNATIONAL']/href}">
								<label for="charset">Select witch languages you need in your application:</label>
								<select name="international_langs[]" id="charset" style="display:block;" multiple="multiple" size="20"> 
									<xsl:for-each select="//View/langs/lang">
										<option value="{.}">
											<xsl:value-of select="php:functionString('ucwords', .)" />											
										</option>
									</xsl:for-each>
								</select>
								
								<div id="buttons_panel">
									<input type="hidden" name="reload_international_step1" value="true" />
									<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}" title="Next" class="next">Next</a>
								</div>
							</form>
						</xsl:if>
						<!-- /Par défaut -->
						
						<!-- Suite -->
						<xsl:if test="//View/step = '1'">
							<form method="post" id="settingsForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='INTERNATIONAL']/href}">
							<input type="hidden" name="international_languages" >
								<xsl:attribute name="value">
									<xsl:value-of select="//View/hidden_langs" />
								</xsl:attribute>
							</input>
								<h2>Select your default language:</h2>
								<table>
								<xsl:for-each select="//View/choose_langs/choose_lang">
									<tr>
										<td colspan="2">								
											<input type="radio" name="default_lang" value="{iso}" id="{iso}" style="width:20px">
												<xsl:if test="position() = 1 and count(//View/InternationalMemory/default) = 0">
													<xsl:attribute name="checked" select="'checked'" />
												</xsl:if>
												<xsl:if test="//View/InternationalMemory/default = iso">
													<xsl:attribute name="checked" select="'checked'" />
												</xsl:if>
											</input>
											<label for="{iso}">
												<xsl:value-of select="php:functionString('ucfirst',label)" />
											</label>
										</td>
									</tr>	
								</xsl:for-each>
								</table>
								<xsl:for-each select="//View/choose_langs/choose_lang">
								<xsl:variable name="isoLang" select="iso" />
								<xsl:variable name="row1" select="concat(iso,'_home_mod')" />
								<xsl:variable name="row2" select="concat(iso,'_home_desc')" />
								<xsl:variable name="row2-1" select="concat(iso,'_home_description')" />
								<xsl:variable name="row2-2" select="concat(iso,'_home_keywords')" />
								<xsl:variable name="row3" select="concat(iso,'_home_index')" />
								<xsl:variable name="row4" select="concat(iso,'_error_mod')" />
								<xsl:variable name="row5" select="concat(iso,'_error_404_desc')" />
								<xsl:variable name="row5-1" select="concat(iso,'_error_404_description')" />
								<xsl:variable name="row5-2" select="concat(iso,'_error_404_keywords')" />
								<xsl:variable name="row6" select="concat(iso,'_error_404_url')" />
								<xsl:variable name="row7" select="concat(iso,'_error_403_desc')" />
								<xsl:variable name="row7-1" select="concat(iso,'_error_403_description')" />
								<xsl:variable name="row7-2" select="concat(iso,'_error_403_keywords')" />
								<xsl:variable name="row8" select="concat(iso,'_error_403_url')" />
								<xsl:variable name="row9" select="concat(iso,'_error_401_desc')" />
								<xsl:variable name="row9-1" select="concat(iso,'_error_403_description')" />
								<xsl:variable name="row9-2" select="concat(iso,'_error_403_keywords')" />
								<xsl:variable name="row10" select="concat(iso,'_error_401_url')" />
								<xsl:variable name="row11" select="concat(iso,'_error_400_desc')" />
								<xsl:variable name="row11-1" select="concat(iso,'_error_400_description')" />
								<xsl:variable name="row11-2" select="concat(iso,'_error_400_keywords')" />
								<xsl:variable name="row12" select="concat(iso,'_error_400_url')" />
								<xsl:variable name="row13" select="concat(iso,'_error_500_desc')" />
								<xsl:variable name="row13-1" select="concat(iso,'_error_500_description')" />
								<xsl:variable name="row13-2" select="concat(iso,'_error_500_keywords')" />
								<xsl:variable name="row14" select="concat(iso,'_error_500_url')" />								
								<xsl:variable name="row15" select="concat(iso,'_error_307_desc')" />
								<xsl:variable name="row15-1" select="concat(iso,'_error_307_description')" />
								<xsl:variable name="row15-2" select="concat(iso,'_error_307_keywords')" />
								<xsl:variable name="row16" select="concat(iso,'_error_307_url')" />								
								<xsl:variable name="row17" select="concat(iso,'_error_302_desc')" />
								<xsl:variable name="row17-1" select="concat(iso,'_error_302_description')" />
								<xsl:variable name="row17-2" select="concat(iso,'_error_302_keywords')" />
								<xsl:variable name="row18" select="concat(iso,'_error_302_url')" />
								<fieldset>
									<legend><xsl:value-of select="php:functionString('ucfirst',label)" /> (<xsl:value-of select="iso" />)</legend>
									<table>
										<tr height="30"></tr>
										<tr>
											<td colspan="2"><h1></h1></td>
										</tr>
										<tr>
											<td colspan="2">
												<h1>Main Controller (Index Controller)</h1>
												This page represents your welcome page : <b><xsl:value-of select="$sls_url_domain" /></b>
											</td>
										</tr>
										<tr>
											<td>	
												
												<label for="{$row1}">Name for Url Rewrite </label>
												<br />(Second URL where your home page <br />will be accessible, this url should be unique)
											</td>
											<td>
												<xsl:value-of select="concat($sls_url_domain, '/')" />
												<input type="text" name="{$row1}" id="{$row1}" onchange="document.getElementById('mod1-1').value=this.value+'/'">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Home'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>
										<tr>
											<td colspan="2"><h3>Action Home</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row2}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row2}" id="{$row2}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Welcome'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row2-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row2-1}" id="{$row2-1}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="''" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row2-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row2-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row2-2}" id="{$row2-2}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="''" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row2-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row3}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="concat($sls_url_domain, '/')" /><input type="text" id="mod1-1" disabled="disabled" value="Home/" />
												<input type="text" name="{$row3}" id="{$row3}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Index'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row3]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="30"></tr>
										<tr>
											<td colspan="2">
												<h1>Default Controller (Error Controller)</h1>
												This controller is called when an error is handled like Error 302, 307, 400, 401, 403, 404 or 500
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row4}">Name for Url Rewrite <br /><font style="font-size:0.8em;">(First argument in the URL)</font></label>
											</td>
											<td>
												<xsl:value-of select="concat($sls_url_domain, '/')" />
												<input type="text" name="{$row4}" id="{$row4}" onchange="document.getElementById('mod2-1').value=this.value+'/';document.getElementById('mod2-2').value=this.value+'/';document.getElementById('mod2-3').value=this.value+'/';document.getElementById('mod2-4').value=this.value+'/';document.getElementById('mod2-5').value=this.value+'/';">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Error'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row4]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>
										<tr>
											<td colspan="2"><h3>Action Url Not Found (404)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row5}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row5}" id="{$row5}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Sorry, Page cannot be found'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row5]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row5-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row5-1}" id="{$row5-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row5-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row5-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row5-2}" id="{$row5-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row5-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row6}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-1" disabled="disabled" value="Error/" />
												<input type="text" name="{$row6}" id="{$row6}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'NotFound'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row6]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Forbidden (403)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row7}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row7}" id="{$row7}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Forbidden Action'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row7]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row7-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row7-1}" id="{$row7-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row7-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row7-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row7-2}" id="{$row7-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row7-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row8}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-2" disabled="disabled" value="Error/" />
												<input type="text" name="{$row8}" id="{$row8}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Forbidden'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row8]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Not Authorized (401)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row9}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row9}" id="{$row9}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Action not Authorized'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row9]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row9-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row9-1}" id="{$row9-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row9-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row9-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row9-2}" id="{$row9-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row9-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row10}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-3" disabled="disabled" value="Error/" />
												<input type="text" name="{$row10}" id="{$row10}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'NotAuthorized'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row10]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Bad Request (400)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row11}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row11}" id="{$row11}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Bad request on the Server'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row11]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row11-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row11-1}" id="{$row11-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row11-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row11-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row11-2}" id="{$row11-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row11-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row12}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-4" disabled="disabled" value="Error/" />
												<input type="text" name="{$row12}" id="{$row12}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'BadRequest'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row12]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Server Error (500)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row13}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row13}" id="{$row13}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Server Error'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row13]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row13-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row13-1}" id="{$row13-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row13-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row13-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row13-2}" id="{$row13-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row13-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row14}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-5" disabled="disabled" value="Error/" />
												<input type="text" name="{$row14}" id="{$row14}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'ServerError'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row14]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Temporary Redirect (307)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row15}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row15}" id="{$row15}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Temporary Redirect'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row15]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row15-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row15-1}" id="{$row15-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row15-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row15-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row15-2}" id="{$row15-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row15-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row16}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-6" disabled="disabled" value="Error/" />
												<input type="text" name="{$row16}" id="{$row16}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'TemporaryRedirect'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row16]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										
										<tr height="10"></tr>								
										<tr>
											<td colspan="2"><h3>Action Maintenance (302)</h3></td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row17}">Page Title : </label>
											</td>
											<td>
												<input type="text" name="{$row17}" id="{$row17}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Maintenance'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row17]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row17-1}">Meta Description : </label>
											</td>
											<td>
												<input type="text" name="{$row17-1}" id="{$row17-1}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row17-1]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row17-2}">Meta Keywords : </label>
											</td>
											<td>
												<input type="text" name="{$row17-2}" id="{$row17-2}">
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row17-2]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										<tr>
											<td>
												
												<label for="{$row17}">Name for Url Rewrite : </label>
											</td>
											<td>
												<xsl:value-of select="$sls_url_domain" /><input type="text" id="mod2-7" disabled="disabled" value="Error/" />
												<input type="text" name="{$row18}" id="{$row18}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="'Maintenance'" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$row18]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										
										<tr height="30"></tr>
										<tr>
											<td colspan="2">
												<h1>Translation</h1>
												Following sentences have to be translated in the current language.
											</td>
										</tr>
										<tr height="10"></tr>
										<xsl:for-each select="//View/translate/*[name()=$isoLang]/sentence">
										<xsl:variable name="translate" select="concat($isoLang, '_TRANSLATION_', code)" />
										<tr>
											<td>
												
												<label for="{$translate}"><xsl:value-of select="php:functionString('ucfirst',name)" /> :</label>
											</td>
											<td>
												<input type="text" name="{$translate}" id="{$translate}">
													<xsl:if test="count(//View/InternationalMemory/row) = 0">
														<xsl:attribute name="value">
															<xsl:value-of select="value" />
														</xsl:attribute>
													</xsl:if>
													<xsl:if test="count(//View/InternationalMemory/row) &gt; 0">
														<xsl:attribute name="value">
															<xsl:value-of select="//View/InternationalMemory/row[name=$translate]/value" />
														</xsl:attribute>
													</xsl:if>
												</input>
											</td>
										</tr>
										</xsl:for-each>
									</table>
								</fieldset>
								</xsl:for-each>								 	
								<div id="buttons_panel">
									<input type="hidden" name="reload_international_step2" value="true" />
									<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}" title="Next" class="next">Next</a>
								</div>
							</form>
						</xsl:if>
						<!-- /Suite -->
					</fieldset>
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>