<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoUserModify">

		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_USER_EDIT_TITLE|||</span>&#160;|||sls:lang:SLS_BO_USER_EDIT_SUBTITLE|||</h1>
		</div>

		<div class="sls-bo-form-page main-core-content sls-bo-page-user-edition">
			<form action="" method="post" sls-validation="true" sls-lang="{//Statics/Sls/Session/params/param[name='current_lang']/value}">
				<input type="hidden" name="reload-edit" value="true" />
				
				<!-- USER INFORMATIONS -->
				<div class="sls-bo-form-page-section form-type-regulars" sls-db="{//View/page/model/db}" sls-model="{//View/page/model/table}">
					<xsl:variable name="sectionErrorNb" select="count(//View/page/columns/column[html_type != 'input_textarea' and html_type != 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]/errors/error[@lang = $lang]) + count(//View/page/bearers/column[(multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true']/errors/error[@lang = $lang])" />
					<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-basic">
						<h3>Profile Informations</h3>
					</div>
					<div class="sls-bo-form-page-section-wrapper">
						<div class="sls-bo-form-page-section-content">
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-name">|||sls:lang:SLS_BO_USER_LAST_NAME|||<sup class="required">*</sup></label>
								</div>
								<div class="sls-form-page-field-input">
									<input type="text" name="user[name]" id="user-name" value="{//View/user/name}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="true" />
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-name">|||sls:lang:SLS_BO_USER_FIRST_NAME|||<sup class="required">*</sup></label>
								</div>
								<div class="sls-form-page-field-input">
									<input type="text" name="user[firstname]" id="user-firstname" value="{//View/user/firstname}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="true" />
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-login">|||sls:lang:SLS_BO_USER_LOGIN|||<sup class="required">*</sup></label>
								</div>
								<div class="sls-form-page-field-input">
									<input type="text" name="user[login]" id="user-login" value="{//View/user/login}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="true" autocomplete="off" />
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-password">|||sls:lang:SLS_BO_USER_PASSWORD|||</label>
								</div>
								<div class="sls-form-page-field-input">
									<input type="password" name="user[password]" id="user-password" value="{//View/user/password}" placeholder="|||sls:lang:SLS_BO_GENERIC_CHOOSE_PASSWORD|||" autocomplete="off" />
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label>|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY|||</label>
								</div>
								<div class="sls-form-page-field-input">
									<div class="choice">
										<span class="checkbox">
											<input type="checkbox" name="user[complexity_pwd_lc]" id="user-complexity_pwd_lc" value="true">
												<xsl:if test="//View/user/complexity_pwd_lc = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-complexity_pwd_lc">|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY_LC|||</label>
									</div>
									<div class="choice">
										<span class="checkbox">
											<input type="checkbox" name="user[complexity_pwd_uc]" id="user-complexity_pwd_uc" value="true">
												<xsl:if test="//View/user/complexity_pwd_uc = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-complexity_pwd_uc">|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY_UC|||</label>
									</div>
									<div class="choice">
										<span class="checkbox">
											<input type="checkbox" name="user[complexity_pwd_digit]" id="user-complexity_pwd_digit" value="true">
												<xsl:if test="//View/user/complexity_pwd_digit = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-complexity_pwd_digit">|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY_DIGIT|||</label>
									</div>
									<div class="choice">
										<span class="checkbox">
											<input type="checkbox" name="user[complexity_pwd_special_char]" id="user-complexity_pwd_special_char" value="true">
												<xsl:if test="//View/user/complexity_pwd_special_char = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-complexity_pwd_special_char">|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY_WILD|||</label>
									</div>
									<div class="choice big">
										<span class="checkbox">
											<input type="checkbox" name="user[complexity_pwd_min_char]" id="user-complexity_pwd_min_char" value="true">
												<xsl:if test="//View/user/complexity_pwd_special_char = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<input type="text" name="user[complexity_pwd_min_char_nb]" id="user-complexity_pwd_min_char_nb" value="{//View/user/complexity_pwd_min_char_nb}" />
										<label for="user-complexity_pwd_min_char">|||sls:lang:SLS_BO_USER_PASSWORD_COMPLEXITY_MIN|||</label>
									</div>
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label>|||sls:lang:SLS_BO_USER_PASSWORD_RENEW|||</label>
								</div>
								<div class="sls-form-page-field-input">
									<div class="choice big">
										<span class="checkbox">
											<input type="checkbox" name="user[reset_pwd]" id="user-reset_pwd" value="true">
												<xsl:if test="//View/user/reset_pwd = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-reset_pwd">|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_FIRST|||</label>
									</div>
									<div class="choice big">
										<span class="checkbox">
											<input type="checkbox" name="user[renew_pwd]" id="user-renew_pwd" value="true">
												<xsl:if test="//View/user/renew_pwd = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-renew_pwd">|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_EVERY|||</label>
										<input type="text" name="user[renew_pwd_nb]" id="user-renew_pwd_nb" value="{//View/user/renew_pwd_nb}"/>
										<div class="select generic-select-mode">
											<select name="user[renew_pwd_unite]" id="renew_pwd_unite">
												<option value="day">
													<xsl:if test="//View/user/renew_pwd_unite = 'day'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_EVERY_DAYS|||
												</option>
												<option value="week">
													<xsl:if test="//View/user/renew_pwd_unite = 'week'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_EVERY_WEEKS|||
												</option>
												<option value="month">
													<xsl:if test="//View/user/renew_pwd_unite = 'month'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_EVERY_MONTHS|||
												</option>
												<option value="year">
													<xsl:if test="//View/user/renew_pwd_unite = 'year'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_EVERY_YEARS|||
												</option>
											</select>
										</div>
									</div>
									<div class="choice big">
										<span class="checkbox">
											<input type="checkbox" name="user[renew_pwd_log]" id="user-renew_pwd_log" value="true">
												<xsl:if test="//View/user/renew_pwd_log = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</span>
										<label for="user-renew_pwd_log">|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_LOG|||</label>
										<input type="text" name="user[renew_pwd_log_nb]" id="user-renew_pwd_log_start" value="{//View/user/renew_pwd_log_nb}" />
										<label for="user-renew_pwd_log">|||sls:lang:SLS_BO_USER_PASSWORD_RENEW_PREVIOUS|||</label>
									</div>
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label>|||sls:lang:SLS_BO_USER_COLOR|||</label>
								</div>
								<div class="sls-form-page-field-input colors-mosaic">
									<xsl:for-each select="//Statics/Site/BoMenu/admin/colors/color">
										<div class="color" style="background-color:{@hexa};">
											<xsl:choose>
												<xsl:when test="//View/user/color != ''">
													<xsl:if test=". = //View/user/color"><xsl:attribute name="class">color selected</xsl:attribute></xsl:if>
												</xsl:when>
												<xsl:otherwise>
													<xsl:if test="position() = 1"><xsl:attribute name="class">color selected</xsl:attribute></xsl:if>
												</xsl:otherwise>
											</xsl:choose>
											<input type="radio" name="user[color]" value="{.}">
												<xsl:choose>
													<xsl:when test="//View/user/color != ''">
														<xsl:if test=". = //View/user/color"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
													</xsl:when>
													<xsl:otherwise>
														<xsl:if test="position() = 1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
													</xsl:otherwise>
												</xsl:choose>
											</input>
										</div>
									</xsl:for-each>
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-enabled">|||sls:lang:SLS_BO_USER_ENABLED|||<sup class="required">*</sup></label>
								</div>
								<div class="sls-form-page-field-input">
									<div class="toggler-btn toggler-btn-radio horizontal disabled" sls-toggler-activated="true">
										<xsl:if test="//View/user/login = //Statics/Site/BoMenu/admin/login"><xsl:attribute name="sls-toggler-activated">false</xsl:attribute></xsl:if>
										<div class="toggler-btn-knob"></div>
										<input type="radio" name="user[enabled]" sls-toggler-state="on" value="true"><xsl:if test="//View/user/enabled = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
										<input type="radio" name="user[enabled]" sls-toggler-state="off" value="false"><xsl:if test="//View/user/enabled = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
									</div>
								</div>
							</div>
							<div class="sls-form-page-field">
								<div class="sls-form-page-field-label">
									<label for="user-photo">|||sls:lang:SLS_BO_USER_PHOTO|||</label>
								</div>
								<div class="sls-form-page-field-input sls-input-file sls-input-type-extended-img">
									<div class="sls-input-file-actions-bar">
										<p class="sls-input-file-description">|||sls:lang:SLS_BO_GENERIC_PHOTO_TITLE|||</p>
										<ul class="sls-input-file-actions">
											<li class="sls-input-file-actions-browse" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('user-photo').click();"><span class="text">|||sls:lang:SLS_BO_GENERIC_PHOTO_BROWSE|||</span></li>
											<li class="sls-input-file-actions-crop"></li>
											<li class="sls-input-file-actions-trash"></li>
										</ul>
									</div>
									<div class="sls-input-file-drop-zone" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('user-photo').click();">
										<xsl:if test="//View/user/img != ''">
											<div class="sls-input-file-params">
												<input type="hidden" name="user[photo][file]" value="{//View/user/img}"  sls-input-poster="{//View/user/img}"/>
											</div>
										</xsl:if>
										<input type="file" name="user[photo]" id="user-photo" sls-input-file-value="" sls-image-min-width="160" sls-image-ratio="1" sls-type="file" sls-type-extended="img" sls-file-uid="POUETPOUET" sls-required="false" onchange="if (this.retrieve('FileUpload')) FileUpload.prototype.submit.call(this.retrieve('FileUpload')); if (Browser.ie8)this.parentNode.submit();" />
										<div class="layer sls-drop-zone-caption">
											<p>|||sls:lang:SLS_BO_GENERIC_PHOTO_DRAG|||</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /USER INFORMATIONS -->

				<!-- USER RIGHTS -->
				<xsl:if test="count(//View/actions/models/model) &gt; 0">
					<div class="sls-bo-form-page-section">
						<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-rights-model">
							<h3>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_MODELS|||</h3>
						</div>
						<div class="sls-bo-form-page-section-wrapper">
							<div class="sls-bo-form-page-section-content">
								<p class="permissions-tutorial">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_EXPLAIN|||</p>
								<div class="user-rights">
									<table>
										<tr>
											<th></th>
											<th>
												<div class="permissions-square-container">
													<span class="permission-square" id="read-all"></span>
													<label>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_READ|||</label>
												</div>
											</th>
											<th>
												<div class="permissions-square-container">
													<span class="permission-square" id="add-all"></span>
													<label>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_ADD|||</label>
												</div>
											</th>
											<th>
												<div class="permissions-square-container">
													<span class="permission-square" id="edit-all"></span>
													<label>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_EDIT|||</label>
												</div>
											</th>
											<th>
												<div class="permissions-square-container">
													<span class="permission-square" id="clone-all"></span>
													<label>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_CLONE|||</label>
												</div>
											</th>
											<th>
												<div class="permissions-square-container">
													<span class="permission-square" id="delete-all"></span>
													<label>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_DELETE|||</label>
												</div>
											</th>
										</tr>
										<xsl:for-each select="//View/actions/models/model[privileges/*/text()]">
											<tr>
												<td class="model-name">
													<div class="permissions-square-container">
														<span class="permission-square"></span>
														<label for="model-all-{position()}"><xsl:value-of select="name" /></label>
													</div>
												</td>
												<td>
													<div class="permissions-square-container">
														<span class="permission-square">
															<xsl:choose>
																<xsl:when test="privileges/read = ''"><xsl:attribute name="class">permission-square idle</xsl:attribute></xsl:when>
																<xsl:when test="privileges/read/@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
																<xsl:when test="privileges/read/@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
															</xsl:choose>
															<input type="checkbox" name="action[{privileges/read}][read]" id="read-{position()}" value="{name}">
																<xsl:if test="privileges/read/@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="read-{position()}">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_READ|||</label>
													</div>
												</td>
												<td>
													<div class="permissions-square-container">
														<span class="permission-square">
															<xsl:choose>
																<xsl:when test="privileges/add = ''"><xsl:attribute name="class">permission-square idle</xsl:attribute></xsl:when>
																<xsl:when test="privileges/add/@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
																<xsl:when test="privileges/add/@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
															</xsl:choose>
															<input type="checkbox" name="action[{privileges/add}][add]" id="add-{position()}" value="{name}">
																<xsl:if test="privileges/add/@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="add-{position()}">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_ADD|||</label>
													</div>
												</td>
												<td>
													<div class="permissions-square-container">
														<span class="permission-square">
															<xsl:choose>
																<xsl:when test="privileges/edit = ''"><xsl:attribute name="class">permission-square idle</xsl:attribute></xsl:when>
																<xsl:when test="privileges/edit/@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
																<xsl:when test="privileges/edit/@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
															</xsl:choose>
															<input type="checkbox" name="action[{privileges/edit}][edit]" id="edit-{position()}" value="{name}">
																<xsl:if test="privileges/edit/@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="edit-{position()}">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_EDIT|||</label>
													</div>
												</td>
												<td>
													<div class="permissions-square-container">
														<span class="permission-square">
															<xsl:choose>
																<xsl:when test="privileges/clone = ''"><xsl:attribute name="class">permission-square idle</xsl:attribute></xsl:when>
																<xsl:when test="privileges/clone/@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
																<xsl:when test="privileges/clone/@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
															</xsl:choose>
															<input type="checkbox" name="action[{privileges/clone}][clone]" id="clone-{position()}" value="{name}">
																<xsl:if test="privileges/clone/@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="clone-{position()}">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_CLONE|||</label>
													</div>
												</td>
												<td>
													<div class="permissions-square-container">
														<span class="permission-square">
															<xsl:choose>
																<xsl:when test="privileges/delete = ''"><xsl:attribute name="class">permission-square idle</xsl:attribute></xsl:when>
																<xsl:when test="privileges/delete/@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
																<xsl:when test="privileges/delete/@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
															</xsl:choose>
															<input type="checkbox" name="action[{privileges/delete}][delete]" id="delete-{position()}" value="{name}">
																<xsl:if test="privileges/delete/@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="delete-{position()}">|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_DELETE|||</label>
													</div>
												</td>
											</tr>
										</xsl:for-each>
									</table>
								</div>
							</div>
						</div>
					</div>
				</xsl:if>

				<xsl:if test="count(//View/actions/customs/custom) &gt; 0">
					<div class="sls-bo-form-page-section">
						<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-rights-custom">
							<h3>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_CUSTOMS|||</h3>
						</div>
						<div class="sls-bo-form-page-section-wrapper">
							<div class="sls-bo-form-page-section-content">
								<div class="user-rights customs">
									<xsl:for-each select="//View/actions/customs/custom">
										<div class="permissions-square-container">
											<xsl:if test="position() mod 2 = 0"><xsl:attribute name="class">permissions-square-container even</xsl:attribute></xsl:if>
											<label for="custom-{position()}"><xsl:value-of select="." /></label>
											<div class="line">
												<div class="dots"></div>
											</div>
											<span class="permission-square">
												<xsl:choose>
													<xsl:when test="@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
													<xsl:when test="@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
												</xsl:choose>
												<input type="checkbox" name="action[{@id}][custom]" id="custom-{position()}" value="{.}">
													<xsl:if test="@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
											</span>
										</div>
									</xsl:for-each>
								</div>
							</div>
						</div>
					</div>
				</xsl:if>

				<xsl:if test="count(//View/actions/dashboards/dashboard) &gt; 0">
					<div class="sls-bo-form-page-section">
						<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-rights-dashboard">
							<h3>|||sls:lang:SLS_BO_USER_SECTION_RIGHTS_DASHBOARD|||</h3>
						</div>
						<div class="sls-bo-form-page-section-wrapper">
							<div class="sls-bo-form-page-section-content">
								<div class="user-rights customs">
									<xsl:for-each select="//View/actions/dashboards/dashboard">
										<div class="permissions-square-container">
											<xsl:if test="position() mod 2 = 0"><xsl:attribute name="class">permissions-square-container even</xsl:attribute></xsl:if>
											<label for="dashboard-{position()}"><xsl:value-of select="." /></label>
											<div class="line">
												<div class="dots"></div>
											</div>
											<span class="permission-square">
												<xsl:choose>
													<xsl:when test="@selected = 'true'"><xsl:attribute name="class">permission-square allowed</xsl:attribute></xsl:when>
													<xsl:when test="@selected = 'false'"><xsl:attribute name="class">permission-square forbidden</xsl:attribute></xsl:when>
												</xsl:choose>
												<input type="checkbox" name="action[{@id}][dashboard]" id="dashboard-{position()}" value="{.}">
													<xsl:if test="@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
											</span>
										</div>
									</xsl:for-each>
								</div>
							</div>
						</div>
					</div>
				</xsl:if>
				<!-- /USER RIGHTS -->

				<div class="sls-bo-form-page-bottom">
					<div class="submit-block">
						<div class="sls-bo-form-page-submit sls-bo-color">|||sls:lang:SLS_BO_USER_EDIT_SUBMIT|||</div>
					</div>
				</div>
			</form>

		</div>
		
	</xsl:template>
</xsl:stylesheet>