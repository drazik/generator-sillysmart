<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" extension-element-prefixes="dyn">
	<!--
	 	- Function BoAdd
	 	- Generic's generation of back-office adding
	 	- Don't change anything
	-->
	<xsl:template name="BoAdd">
		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_GENERIC_ADD_RECORDSET|||</span><a href="{//View/page/urls/list}" title=""><xsl:value-of select="//View/page/model/label" /></a></h1>
		</div>

		<div class="sls-bo-form-page main-core-content">
			<xsl:if test="count(//Statics/Sls/Configs/site/langs/name) &gt; 1 and (count(//View/page/columns/column[multilanguage = 'true']) &gt; 0 or count(//View/page/children/child/columns/column[multilanguage = 'true']) &gt; 0)">
				<div class="action-row sls-bo-color fixed-in-header">
					<ul class="actions langs">
						<li><span>|||sls:lang:SLS_BO_GENERIC_LANG|||</span></li>
						<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
							<li>
								<xsl:if test=". = //Statics/Sls/Configs/site/defaultLang"><xsl:attribute name="class">selected</xsl:attribute></xsl:if>
								<a href="" title="" sls-lang="{.}">
									<span class="label"><xsl:value-of select="." /></span>
								</a>
							</li>
						</xsl:for-each>
					</ul>
				</div>
			</xsl:if>

			<form action="" method="post" sls-validation="true">
				<input type="hidden" name="reload-add" value="true" />
				<div class="sls-bo-form-page-langs">
					<xsl:variable name="db" select="//View/page/model/db" />
					<xsl:variable name="defaultLang" select="//Statics/Sls/Configs/site/defaultLang" />
					<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
						<xsl:variable name="lang" select="." />
						<xsl:variable name="isDefaultLang" select="$lang = $defaultLang" />
						<div class="sls-bo-form-page-lang"  sls-lang="{$lang}">
							<xsl:if test="position() = 1"><xsl:attribute name="class">sls-bo-form-page-lang current</xsl:attribute></xsl:if>
							
							<!-- FORM SECTION : REGULAR FORM ELEMENTS -->
							<xsl:if test="count(//View/page/columns/column[html_type != 'input_textarea' and html_type != 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]) &gt; 0 or count(//View/page/bearers/column) &gt; 0">
								<div class="sls-bo-form-page-section form-type-regulars" sls-db="{//View/page/model/db}" sls-model="{//View/page/model/table}">
									<xsl:variable name="sectionErrorNb" select="count(//View/page/columns/column[html_type != 'input_textarea' and html_type != 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]/errors/error[@lang = $lang]) + count(//View/page/bearers/column[(multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true']/errors/error[@lang = $lang])" />
									<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-basic"><h3>|||sls:lang:SLS_BO_GENERIC_SECTION_MODEL_BASE|||</h3></div>
									<xsl:if test="$sectionErrorNb &gt; 0">
										<div class="sls-bo-form-page-section-error-bar">
											<p>
												<xsl:variable name="sentenceError"><xsl:call-template name="displayLang"><xsl:with-param name="str" select="'SLS_BO_GENERIC_SECTION_MODEL_ERROR'" /><xsl:with-param name="escaping" select="'true'" /></xsl:call-template></xsl:variable>
												<xsl:variable name="sentenceErrorPlural"><xsl:if test="$sectionErrorNb &gt; 1">s</xsl:if></xsl:variable>
												<xsl:value-of select="php:functionString('sprintf',$sentenceError,$sectionErrorNb,$sentenceErrorPlural)" disable-output-escaping="yes" />
											</p>
										</div>
									</xsl:if>
									<div class="sls-bo-form-page-section-wrapper">
										<div class="sls-bo-form-page-section-content">
											<xsl:for-each select="//View/page/columns/column[html_type != 'input_textarea' and html_type != 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]">
												<xsl:variable name="table" select="table" />
												<xsl:variable name="fieldId" select="concat(name, '_', $lang)" />
												<xsl:variable name="fieldName" select="concat($table, '[properties][', $lang, '][', name, ']')" />
												<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="count(values/value[@lang = $lang]) &gt; 0"><xsl:value-of select="values/value[@lang = $lang]" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>
												<div class="sls-form-page-field">
													<div class="sls-form-page-field-label">
														<label for="{$fieldId}">
															<xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if>
														</label>
													</div>
													<div class="sls-form-page-field-input">
														<xsl:choose>
															<xsl:when test="html_type = 'input_ac'">
																<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-ac-db="{ac_db}" sls-ac-entity="{ac_entity}" sls-ac-column="{ac_column}" sls-ac-label="{ac_label}" sls-ac-multiple="{ac_multiple}" sls-label="{ac_label}" sls-required="{required}" placeholder="{ac_label}" value="{$fieldValue}" />
															</xsl:when>
															<xsl:when test="html_type = 'input_radio'">
																<xsl:choose>
																	<xsl:when test="(count(choices/choice) &lt;= 4 and required = 'true') or (count(choices/choice) &lt;= 3 and required = 'false')">
																		<xsl:if test="required = 'false'">
																			<div class="choice">
																				<span class="radio">
																					<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', (count(choices/choice) + 1))}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="{../../required}" value="">
																						<xsl:if test="$fieldName = ''or count(../../values/value[@lang = $lang]) = 0"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																					</input>
																				</span>
																				<label for="{concat($fieldId, '_', (count(choices/choice) + 1))}">|||sls:lang:SLS_BO_GENERIC_ENUM_NULLABLE|||</label>
																			</div>
																		</xsl:if>
																		<xsl:for-each select="choices/choice">
																			<div class="choice">
																				<span class="radio">
																					<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', position())}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{.}">
																						<xsl:if test=". = $fieldValue">
																							<xsl:attribute name="checked">checked</xsl:attribute>
																						</xsl:if>
																					</input>
																				</span>
																				<label for="{concat($fieldId, '_', position())}"><xsl:value-of select="." /></label>
																			</div>
																		</xsl:for-each>
																	</xsl:when>
																	<xsl:otherwise>
																		<div class="select generic-select-mode">
																			<select name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="select" sls-native-type="{native_type}" sls-required="{required}">
																				<xsl:if test="required = 'false'">
																					<option value="">-</option>
																				</xsl:if>
																				<xsl:for-each select="choices/choice">
																					<option value="{.}">
																						<xsl:if test="$fieldValue = .">
																							<xsl:attribute name="selected">selected</xsl:attribute>
																						</xsl:if>
																						<xsl:value-of select="." />
																					</option>
																				</xsl:for-each>
																			</select>
																		</div>
																	</xsl:otherwise>
																</xsl:choose>
															</xsl:when>
															<xsl:when test="html_type = 'input_checkbox'">
																<xsl:for-each select="choices/choice">
																	<xsl:variable name="choiceValue" select="." />
																	<div class="choice">
																		<span class="checkbox">
																			<input type="checkbox" name="{$fieldName}[]" id="{concat($fieldId, '_', position())}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{.}" sls-required="{../../required}" >
																				<xsl:if test="$fieldValue = . or count(../../values/value[@lang = $lang and . = $choiceValue]) &gt; 0">
																					<xsl:attribute name="checked">checked</xsl:attribute>
																				</xsl:if>
																			</input>
																		</span>
																		<label for="{concat($fieldId, '_', position())}"><xsl:value-of select="." /></label>
																	</div>
																</xsl:for-each>
															</xsl:when>
															<xsl:when test="html_type = 'input_year' or html_type='input_time' or html_type='input_date' or html_type='input_datetime' or html_type='input_timestamp'">
																<div class="datepicker">
																	<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{php:functionString('SLS_String::substrAfterFirstDelimiter',html_type,'input_')}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="{required}" value="{$fieldValue}" />
																</div>
															</xsl:when>
															<xsl:when test="html_type = 'input_password'">
																<input type="password" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_CHOOSE_PASSWORD|||" sls-required="{required}" value="{$fieldValue}" />
															</xsl:when>
															<xsl:when test="specific_type = 'color'">
																<div class="sls-bo-colorpicker">
																	<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-specific-type="{specific_type}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="{required}" value="{$fieldValue}" />
																</div>
															</xsl:when>
															<xsl:otherwise>
																<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{$fieldValue}" />
															</xsl:otherwise>
														</xsl:choose>
													</div>
												</div>
											</xsl:for-each>
											<xsl:for-each select="//View/page/bearers/column[(multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true']">
												<xsl:variable name="table" select="table" />
												<xsl:variable name="fieldId" select="concat(name, '_', $lang)" />
												<xsl:variable name="fieldName" select="concat(//View/page/model/table, '[bearers][', $table, '][]')" />
												<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="value != ''"><xsl:value-of select="value" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>
												<div class="sls-form-page-field">
													<div class="sls-form-page-field-label top">
														<label for="{$fieldId}">
															<xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if>
														</label>
													</div>
													<div class="sls-form-page-field-input">
														<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-ac-db="{ac_db}" sls-ac-entity="{ac_entity}" sls-ac-column="{ac_column}" sls-ac-label="{ac_label}" sls-ac-multiple="{ac_multiple}" sls-label="{ac_label}" placeholder="{ac_label}" value="{$fieldValue}" />
														<xsl:if test="count(values/value) &gt; 0">
															<div class="sls-ac-values-container">
																<xsl:for-each select="values/value">
																	<div class="sls-ac-multi-value sls-bo-color-text-hover sls-bo-color-border-hover">
																		<div class="sls-ac-multi-value-content">
																			<div class="sls-ac-multi-value-label"><xsl:value-of select="@label" /></div>
																			<input type="hidden" name="{$fieldName}" value="{.}" />
																		</div>
																	</div>
																</xsl:for-each>
															</div>
														</xsl:if>
													</div>
												</div>
											</xsl:for-each>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /FORM SECTION : REGULAR FORM ELEMENTS -->

							<!-- FORM SECTION : UPLOADLE FORM ELEMENTS -->
							<xsl:if test="count(//View/page/columns/column[html_type = 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]) &gt; 0">
								<div class="sls-bo-form-page-section form-type-uploadables">
									<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-download">
										<h3>|||sls:lang:SLS_BO_GENERIC_SECTION_MODEL_FILE|||</h3>
									</div>
									<div class="sls-bo-form-page-section-wrapper">
										<div class="sls-bo-form-page-section-content">
											<xsl:for-each select="//View/page/columns/column[html_type = 'input_file' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]">
												<xsl:variable name="table" select="table" />
												<xsl:variable name="fieldId" select="concat(name, '_', $lang)" />
												<xsl:variable name="fieldName" select="concat($table, '[properties][', $lang, '][', name, ']')" />
												<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="count(values/value[@lang = $lang]) &gt; 0"><xsl:value-of select="values/value[@lang = $lang]" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>
												<div class="sls-form-page-field sls-form-page-field-uploadable">
													<div class="sls-form-page-field-label">
														<label for="{$fieldId}">
															<xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if>
														</label>
													</div>
													<div class="sls-form-page-field-input sls-input-file sls-input-type-extended-{specific_type_extended}">
														<div class="sls-input-file-actions-bar">
															<p class="sls-input-file-description">
																<xsl:choose>
																	<xsl:when test="specific_type_extended = 'img'">
																		<xsl:choose>
																			<xsl:when test="image_min_width != '*' or image_min_height != '*'">
																				<xsl:variable name="sentenceSize"><xsl:call-template name="displayLang"><xsl:with-param name="str" select="'SLS_BO_GENERIC_FILE_IMAGE_SIZE'" /><xsl:with-param name="escaping" select="'true'" /></xsl:call-template></xsl:variable>
																				|||sls:lang:SLS_BO_GENERIC_FILE_IMAGE|||&#160;:&#160;<xsl:value-of select="php:functionString('sprintf',$sentenceSize,image_min_width,image_min_height)" disable-output-escaping="yes" />
																			</xsl:when>
																			<xsl:otherwise>|||sls:lang:SLS_BO_GENERIC_FILE_IMAGE|||</xsl:otherwise>
																		</xsl:choose>
																	</xsl:when>
																	<xsl:when test="specific_type_extended = 'all'">|||sls:lang:SLS_BO_GENERIC_FILE_ALL|||</xsl:when>
																</xsl:choose>
															</p>
															<ul class="sls-input-file-actions">
																<li class="sls-input-file-actions-browse" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('{$fieldId}').click();"><span class="text">|||sls:lang:SLS_BO_GENERIC_PHOTO_BROWSE|||</span></li>
																<li class="sls-input-file-actions-crop"></li>
																<li class="sls-input-file-actions-trash"></li>
															</ul>
														</div>
														<div class="sls-input-file-drop-zone" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('{$fieldId}').click();">
															<xsl:if test="$fieldValue != ''">
																<div class="sls-input-file-params">
																	<input type="hidden" name="{$fieldName}[file]" value="{$fieldValue}">
																		<xsl:choose>
																			<xsl:when test="values/value[@lang = $lang]/@mime != ''"><xsl:attribute name="sls-input-poster"><xsl:value-of select="values/value[@lang = $lang]/@mime" /></xsl:attribute></xsl:when>
																			<xsl:otherwise><xsl:attribute name="sls-input-poster"><xsl:value-of select="$fieldValue" /></xsl:attribute></xsl:otherwise>
																		</xsl:choose>
																	</input>
																</div>
															</xsl:if>
															<input type="file" name="{$fieldName}" id="{$fieldId}" sls-input-file-value="{$fieldValue}" sls-type="{specific_type}" sls-type-extended="{specific_type_extended}" sls-file-uid="{file_uid}" sls-required="{required}" onchange="if (this.retrieve('FileUpload')) FileUpload.prototype.submit.call(this.retrieve('FileUpload')); if (Browser.ie8)this.parentNode.submit();">
																<xsl:if test="specific_type_extended = 'img'">
																	<xsl:if test="image_min_width != '*'"><xsl:attribute name="sls-image-min-width"><xsl:value-of select="image_min_width" /></xsl:attribute></xsl:if>
																	<xsl:if test="image_min_height != '*'"><xsl:attribute name="sls-image-min-height"><xsl:value-of select="image_min_height" /></xsl:attribute></xsl:if>
																	<xsl:if test="image_ratio != '*'"><xsl:attribute name="sls-image-ratio"><xsl:value-of select="image_ratio" /></xsl:attribute></xsl:if>
																</xsl:if>
															</input>
															<div class="layer sls-drop-zone-caption">
																<xsl:choose>
																	<xsl:when test="specific_type_extended = 'img'"><p>|||sls:lang:SLS_BO_GENERIC_PHOTO_DRAG|||</p></xsl:when>
																	<xsl:when test="specific_type_extended = 'all'"><p>|||sls:lang:SLS_BO_GENERIC_FILE_DRAG|||</p></xsl:when>
																</xsl:choose>
															</div>
														</div>
													</div>
												</div>
											</xsl:for-each>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /FORM SECTION : UPLOADLE FORM ELEMENTS -->

							<xsl:for-each select="//View/page/columns/column[html_type = 'input_textarea' and ((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]">
								<xsl:variable name="table" select="table" />
								<xsl:variable name="fieldId" select="concat(name, '_', $lang)" />
								<xsl:variable name="fieldName" select="concat($table, '[properties][', $lang, '][', name, ']')" />
								<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="count(values/value[@lang = $lang]) &gt; 0"><xsl:value-of select="values/value[@lang = $lang]" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>

								<!-- FORM SECTION : LONG TEXT FORM ELEMENTS -->
								<div class="sls-bo-form-page-section form-type-single">
									<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-textarea-{name}">
										<h3><xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if></h3>
									</div>
									<div class="sls-bo-form-page-section-wrapper">
										<div class="sls-bo-form-page-section-content">
											<div class="sls-form-page-field">
												<div class="sls-form-page-field-input">
													<textarea name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html="{html}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" cols="" rows="5">
														<xsl:value-of select="$fieldValue" />
													</textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- /FORM SECTION : LONG TEXT FORM ELEMENTS -->

							</xsl:for-each>
						</div>
					</xsl:for-each>
				</div>

				<!-- FORM SECTION : CHILDREN ELEMENTS -->
				<xsl:for-each select="//View/page/children/child[urls/add/@authorized = 'true']">
					<xsl:variable name="db" select="model/db" />
					<xsl:variable name="defaultLang" select="//Statics/Sls/Configs/site/defaultLang" />
					<xsl:variable name="childPosition" select="position()" />
					<div class="sls-bo-form-page-langs sls-form-page-children-section">
						<div class="sls-bo-form-page-section-title-bar" id="sls-bo-section-children-{model/table}">
							<h3>|||sls:lang:SLS_BO_GENERIC_SECTION_MODEL_CHILDREN|||&#160;<span><xsl:value-of select="//View/page/children/child[$childPosition]/model/label" /></span></h3>
						</div>
						<xsl:if test="columns/column[html_type = 'input_file']">
							<div class="sls-form-children-drop-zone-container">
								<div class="drop-step">
									<div class="drop-zone-indications">
										<table class="hr_centered vt_centered">
											<tr>
												<td>|||sls:lang:SLS_BO_GENERIC_PHOTO_DRAG_ALL|||</td>
											</tr>
										</table>
									</div>
									<div class="sls-form-children-drop-zone-square" onclick="this.getElement('input').click();">
										<table class="hr_centered vt_centered">
											<tr>
												<td>
													<div class="picto"></div>
													<div class="text">|||sls:lang:SLS_BO_GENERIC_PHOTO_DRAG_ALL_HERE|||</div>
												</td>
											</tr>
										</table>
										<input type="file" multiple="multiple" />
										<div class="sls-form-children-drop-zone"></div>
									</div>
								</div>
								<div class="progress-step disabled">
									<table class="hr_centered vt_centered">
										<tr>
											<td>
												<div class="progress-indications">
													<strong>|||sls:lang:SLS_BO_GENERIC_UPLOAD_PROGRESS|||</strong>
													<br />
													<span class="nb-files-uploaded"></span>/<span class="nb-files-total"></span>
												</div>
												<div class="progress-bar-container">
													<div class="sls-progress-bar">
														<div class="sls-progress-bar-percentage sls-bo-color"></div>
													</div>
													<div class="percentage sls-bo-color-text">0%</div>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</xsl:if>
						<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
							<xsl:variable name="lang" select="." />
							<xsl:variable name="isDefaultLang" select="$lang = $defaultLang" />
							<xsl:if test="$isDefaultLang or (not($isDefaultLang) and count(//View/page/children/child[$childPosition]/columns/column[multilanguage = 'true']) &gt; 0)">
								<div class="sls-bo-form-page-lang"  sls-lang="{$lang}" sls-default-lang="{$isDefaultLang}">
									<xsl:if test="position() = 1"><xsl:attribute name="class">sls-bo-form-page-lang current</xsl:attribute></xsl:if>
									<xsl:if test="count(//View/page/children/child[$childPosition]/columns/column[((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]) &gt; 0">
										<xsl:call-template name="childBlockAdd">
											<xsl:with-param name="xPath" select="concat('//View/page/children/child[', $childPosition, ']')" />
											<xsl:with-param name="isDefaultLang" select="$isDefaultLang" />
											<xsl:with-param name="lang" select="$lang" />
										</xsl:call-template>
										<xsl:if test="//View/page/children/child[$childPosition]/model/nbChildren &gt; 0">
											<xsl:call-template name="childBlockAdd">
												<xsl:with-param name="xPath" select="concat('//View/page/children/child[', $childPosition, ']')" />
												<xsl:with-param name="isDefaultLang" select="$isDefaultLang" />
												<xsl:with-param name="lang" select="$lang" />
												<xsl:with-param name="recordPosition" select="1" />
												<xsl:with-param name="nbRecords" select="//View/page/children/child[$childPosition]/model/nbChildren" />
											</xsl:call-template>
										</xsl:if>
									</xsl:if>
									<div class="children-add-block-container">
										<div class="children-add-block">
											<table class="hr_centered vt_centered">
												<tr>
													<td>
														<div class="plus">+</div>
														|||sls:lang:SLS_BO_GENERIC_SECTION_MODEL_CHILDREN_ADD|||
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</xsl:if>
						</xsl:for-each>
					</div>
				</xsl:for-each>
				<!-- /FORM SECTION : CHILDREN ELEMENTS -->

				<div class="sls-bo-form-page-bottom">
					<div class="submit-block">
						<div class="submit-block-left">
							<div class="sls-bo-form-page-submit sls-bo-color">|||sls:lang:SLS_BO_GENERIC_SUBMIT_ADD|||</div>
						</div>
						<div class="submit-block-right">
							<div class="select generic-select-mode sls-bo-color-border sls-bo-color-text">
							<xsl:value-of select="//View/page/urls/url/edit/@authorized" />
								<select name="redirect">
									<option value="list">
										<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='add_callback']]/setting) = 0 or //Statics/Site/BoMenu/admin/settings/setting[@key='add_callback'] = 'list'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										|||sls:lang:SLS_BO_GENERIC_SUBMIT_FORWARD_LIST|||
									</option>
									<xsl:if test="//View/page/urls/edit/@authorized = 'true' and //View/page/urls/edit != ''">
										<option value="edit">
											<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='add_callback'] = 'edit'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
											|||sls:lang:SLS_BO_GENERIC_SUBMIT_FORWARD_EDIT|||
										</option>
									</xsl:if>
									<option value="add">
										<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='add_callback'] = 'add'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
										|||sls:lang:SLS_BO_GENERIC_SUBMIT_FORWARD_ADD|||
									</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>

	</xsl:template>

	<xsl:template name="childBlockAdd">
		<xsl:param name="xPath" />
		<xsl:param name="lang" />
		<xsl:param name="isDefaultLang" />
		<xsl:param name="recordPosition" select="false()" />
		<xsl:param name="nbRecords" select="false()" />

		<div class="sls-bo-form-page-section sls-bo-form-page-child" sls-db="{dyn:evaluate($xPath)/model/db}" sls-model="{dyn:evaluate($xPath)/model/table}">
			<xsl:if test="$isDefaultLang and $nbRecords = false()"><xsl:attribute name="class">sls-bo-form-page-section sls-bo-form-page-child skeleton-child</xsl:attribute></xsl:if>
			<xsl:if test="$nbRecords != false()"><xsl:attribute name="class">sls-bo-form-page-section sls-bo-form-page-child sls-bo-form-page-child-draft</xsl:attribute></xsl:if>
			<xsl:variable name="sectionErrorNb" select="count(dyn:evaluate($xPath)/columns/column[((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]/errors/error[@lang = $lang])" />
			<xsl:variable name="attId"><xsl:choose><xsl:when test="$nbRecords != false()"><xsl:value-of select="number($recordPosition)-1" /></xsl:when><xsl:otherwise>$$ID$$</xsl:otherwise></xsl:choose></xsl:variable>
			<xsl:variable name="attBlockNumber"><xsl:choose><xsl:when test="$nbRecords != false()"><xsl:value-of select="$attId" /></xsl:when><xsl:otherwise>$$BLOCK_NUMBER$$</xsl:otherwise></xsl:choose></xsl:variable>
			<xsl:variable name="attLang"><xsl:choose><xsl:when test="$nbRecords != false()"><xsl:value-of select="$lang" /></xsl:when><xsl:otherwise>$$LANG$$</xsl:otherwise></xsl:choose></xsl:variable>
			<div class="sls-bo-form-page-section-wrapper">
				<div class="sls-bo-form-page-section-content">
					<h4 class="child-block-label-numbered"><xsl:value-of select="dyn:evaluate($xPath)/model/label" /> - n°<xsl:choose><xsl:when test="$nbRecords != false()"><xsl:value-of select="$recordPosition" /></xsl:when><xsl:otherwise>$$CHILD_NUMBER$$</xsl:otherwise></xsl:choose></h4>
					<xsl:for-each select="dyn:evaluate($xPath)/columns/column[((multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true')]">
						<xsl:variable name="table" select="table" />
						<xsl:variable name="fieldId" select="concat(name, '_', $lang, '_', $attId)" />
						<xsl:variable name="fieldName" select="concat(//View/page/model/table, '[children][', $table, '][', $attBlockNumber, '][properties][', $attLang,'][', name, ']')" />
						<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="count(values/record[$recordPosition]/value[@lang = $lang]) &gt; 0"><xsl:value-of select="values/record[$recordPosition]/value[@lang = $lang]" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>
						<div class="sls-form-page-field" sls-multilanguage="{multilanguage}">
							<xsl:if test="html_type = 'input_file'"><xsl:attribute name="class">sls-form-page-field sls-form-page-field-uploadable</xsl:attribute></xsl:if>
							<div class="sls-form-page-field-label">
								<label for="{$fieldId}">
									<xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if>
								</label>
							</div>
							<div class="sls-form-page-field-input">
								<xsl:choose>
									<xsl:when test="html_type = 'input_ac'">
										<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-ac-db="{ac_db}" sls-ac-entity="{ac_entity}" sls-ac-column="{ac_column}" sls-ac-label="{ac_label}" sls-ac-multiple="{ac_multiple}" sls-label="{ac_label}" sls-required="{required}" placeholder="{ac_label}" value="{$fieldValue}" />
									</xsl:when>
									<xsl:when test="html_type = 'input_radio'">
										<xsl:choose>
											<xsl:when test="(count(choices/choice) &lt;= 4 and required = 'true') or (count(choices/choice) &lt;= 3 and required = 'false')">
												<xsl:if test="required = 'false'">
													<div class="choice">
														<span class="radio">
															<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', (count(choices/choice) + 1))}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="">
																<xsl:if test="$fieldName = ''or count(../../values/record[$recordPosition]/value[@lang = $lang]) = 0"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
															</input>
														</span>
														<label for="{concat($fieldId, '_', (count(choices/choice) + 1))}">|||sls:lang:SLS_BO_GENERIC_ENUM_NULLABLE|||</label>
													</div>
												</xsl:if>
												<xsl:for-each select="choices/choice">
													<div class="choice">
														<span class="radio">
															<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', position())}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{.}">
																<xsl:if test=". = $fieldValue">
																	<xsl:attribute name="checked">checked</xsl:attribute>
																</xsl:if>
															</input>
														</span>
														<label for="{concat($fieldId, '_', position())}"><xsl:value-of select="." /></label>
													</div>
												</xsl:for-each>
											</xsl:when>
											<xsl:otherwise>
												<div class="select generic-select-mode">
													<select name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="select" sls-native-type="{native_type}" sls-required="{required}">
														<xsl:if test="required = 'false'">
															<option value="">-</option>
														</xsl:if>
														<xsl:for-each select="choices/choice">
															<option value="{.}">
																<xsl:if test="$fieldValue = .">
																	<xsl:attribute name="selected">selected</xsl:attribute>
																</xsl:if>
																<xsl:value-of select="." />
															</option>
														</xsl:for-each>
													</select>
												</div>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:when>
									<xsl:when test="html_type = 'input_checkbox'">
										<xsl:for-each select="choices/choice">
											<xsl:variable name="choiceValue" select="." />
											<div class="choice">
												<span class="checkbox">
													<input type="checkbox" name="{$fieldName}[]" id="{concat($fieldId, '_', position())}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{.}">
														<xsl:if test="$fieldValue = . or count(../../values/record[$recordPosition]/value[@lang = $lang and . = $choiceValue]) &gt; 0">
															<xsl:attribute name="checked">checked</xsl:attribute>
														</xsl:if>
													</input>
												</span>
												<label for="{concat($fieldId, '_', position())}"><xsl:value-of select="." /></label>
											</div>
										</xsl:for-each>
									</xsl:when>
									<xsl:when test="html_type = 'input_year' or html_type='input_time' or html_type='input_date' or html_type='input_datetime' or html_type='input_timestamp'">
										<div class="datepicker">
											<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{php:functionString('SLS_String::substrAfterFirstDelimiter',html_type,'input_')}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{$fieldValue}" />
										</div>
									</xsl:when>
									<xsl:when test="specific_type = 'color'">
										<div class="sls-bo-colorpicker">
											<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-specific-type="{specific_type}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" sls-required="{required}" value="{$fieldValue}" />
										</div>
									</xsl:when>
									<xsl:when test="html_type = 'input_file'">
										<div class="sls-form-page-field-input sls-input-file sls-input-type-extended-{specific_type_extended}">
											<div class="sls-input-file-actions-bar">
												<p class="sls-input-file-description">
													<xsl:choose>
														<xsl:when test="specific_type_extended = 'img'">
															<xsl:choose>
																<xsl:when test="image_min_width != '*' or image_min_height != '*'">
																	<xsl:variable name="sentenceSize"><xsl:call-template name="displayLang"><xsl:with-param name="str" select="'SLS_BO_GENERIC_FILE_IMAGE_SIZE'" /><xsl:with-param name="escaping" select="'true'" /></xsl:call-template></xsl:variable>
																	|||sls:lang:SLS_BO_GENERIC_FILE_IMAGE|||&#160;:&#160;<xsl:value-of select="php:functionString('sprintf',$sentenceSize,image_min_width,image_min_height)" disable-output-escaping="yes" />
																</xsl:when>
																<xsl:otherwise>|||sls:lang:SLS_BO_GENERIC_FILE_IMAGE|||</xsl:otherwise>
															</xsl:choose>
														</xsl:when>
														<xsl:when test="specific_type_extended = 'all'">|||sls:lang:SLS_BO_GENERIC_FILE_ALL|||</xsl:when>
													</xsl:choose>
												</p>
												<ul class="sls-input-file-actions">
													<li class="sls-input-file-actions-browse" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('{$fieldId}').click();"><span class="text">|||sls:lang:SLS_BO_GENERIC_PHOTO_BROWSE|||</span></li>
													<li class="sls-input-file-actions-crop"></li>
													<li class="sls-input-file-actions-trash"></li>
												</ul>
											</div>
											<div class="sls-input-file-drop-zone" onclick="var rendering = this.getParent('.sls-input-file').getElement('.sls-drop-zone-rendering'); if (!Browser.ie8 &amp;&amp; ((rendering &amp;&amp; !this.getParent('.sls-form-page-field-error')) || !rendering))$('{$fieldId}').click();">
												<xsl:if test="$fieldValue != ''">
													<div class="sls-input-file-params">
														<input type="hidden" name="{$fieldName}[file]" value="{$fieldValue}">
															<xsl:choose>
																<xsl:when test="values/record[$recordPosition]/value[@lang = $lang]/@mime != ''"><xsl:attribute name="sls-input-poster"><xsl:value-of select="values/record[$recordPosition]/value[@lang = $lang]/@mime" /></xsl:attribute></xsl:when>
																<xsl:otherwise><xsl:attribute name="sls-input-poster"><xsl:value-of select="$fieldValue" /></xsl:attribute></xsl:otherwise>
															</xsl:choose>
														</input>
													</div>
												</xsl:if>
												<input type="file" name="{$fieldName}" id="{$fieldId}" sls-input-file-value="{$fieldValue}" sls-type="{specific_type}" sls-type-extended="{specific_type_extended}" sls-file-uid="{file_uid}" sls-required="{required}" onchange="if (this.retrieve('FileUpload')) FileUpload.prototype.submit.call(this.retrieve('FileUpload')); if (Browser.ie8)this.parentNode.submit();">
													<xsl:if test="specific_type_extended = 'img'">
														<xsl:if test="image_min_width != '*'"><xsl:attribute name="sls-image-min-width"><xsl:value-of select="image_min_width" /></xsl:attribute></xsl:if>
														<xsl:if test="image_min_height != '*'"><xsl:attribute name="sls-image-min-height"><xsl:value-of select="image_min_height" /></xsl:attribute></xsl:if>
														<xsl:if test="image_ratio != '*'"><xsl:attribute name="sls-image-ratio"><xsl:value-of select="image_ratio" /></xsl:attribute></xsl:if>
													</xsl:if>
												</input>
												<div class="layer sls-drop-zone-caption">
													<xsl:choose>
														<xsl:when test="specific_type_extended = 'img'"><p>|||sls:lang:SLS_BO_GENERIC_PHOTO_DRAG|||</p></xsl:when>
														<xsl:when test="specific_type_extended = 'all'"><p>|||sls:lang:SLS_BO_GENERIC_FILE_DRAG|||</p></xsl:when>
													</xsl:choose>
												</div>
											</div>
										</div>
									</xsl:when>
									<xsl:when test="html_type = 'input_textarea'">
										<textarea name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html="{html}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" cols="" rows="5">
											<xsl:value-of select="$fieldValue" />
										</textarea>
									</xsl:when>
									<xsl:when test="html_type = 'input_password'">
										<input type="password" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_CHOOSE_PASSWORD|||" sls-required="{required}" value="{$fieldValue}" />
									</xsl:when>
									<xsl:otherwise>
										<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{$fieldValue}" />
									</xsl:otherwise>
								</xsl:choose>
							</div>
						</div>
					</xsl:for-each>
					<xsl:for-each select="dyn:evaluate($xPath)/bearers/column[(multilanguage = 'false' and $isDefaultLang) or multilanguage = 'true']">
						<xsl:variable name="table" select="table" />
						<xsl:variable name="fieldId" select="concat(name, '_', $lang, $attId)" />
						<xsl:variable name="fieldName" select="concat(//View/page/model/table, '[children][', dyn:evaluate($xPath)/model/table, '][', $attBlockNumber, '][bearers][', $table, '][]')" />
						<xsl:variable name="fieldValue"><xsl:choose><xsl:when test="value != ''"><xsl:value-of select="value" /></xsl:when><xsl:otherwise><xsl:value-of select="default" /></xsl:otherwise></xsl:choose></xsl:variable>
						<div class="sls-form-page-field" sls-multilanguage="false">
							<div class="sls-form-page-field-label top">
								<label for="{$fieldId}">
									<xsl:value-of select="label" />&#160;<xsl:if test="required = 'true'"><sup class="required">*</sup></xsl:if>
								</label>
							</div>
							<div class="sls-form-page-field-input">
								<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-ac-db="{ac_db}" sls-ac-entity="{ac_entity}" sls-ac-column="{ac_column}" sls-ac-label="{ac_label}" sls-ac-multiple="{ac_multiple}" sls-label="{ac_label}" placeholder="{ac_label}" value="{$fieldValue}" />
								<xsl:if test="count(values/record[$recordPosition]/value) &gt; 0">
									<div class="sls-ac-values-container">
										<xsl:for-each select="values/record[$recordPosition]/value">
											<div class="sls-ac-multi-value sls-bo-color-text-hover sls-bo-color-border-hover">
												<div class="sls-ac-multi-value-content">
													<div class="sls-ac-multi-value-label"><xsl:value-of select="@label" /></div>
													<input type="hidden" name="{$fieldName}" value="{.}" />
												</div>
											</div>
										</xsl:for-each>
									</div>
								</xsl:if>
							</div>
						</div>
					</xsl:for-each>
				</div>
				<div class="border bottom sls-bo-color-hover sls-bo-color-trigger">
					<div class="child-delete">
						<span class="text">|||sls:lang:SLS_BO_GENERIC_SECTION_MODEL_CHILDREN_DELETE|||</span>
						<span class="picto"></span>
					</div>
				</div>
				<div class="border top sls-bo-color-prevDependent-hover"></div>
				<div class="border right sls-bo-color-prevDependent-hover"></div>
				<div class="border left sls-bo-color-prevDependent-hover"></div>
			</div>
		</div>

		<xsl:if test="$recordPosition &lt; $nbRecords">
			<xsl:call-template name="childBlockAdd">
				<xsl:with-param name="xPath" select="$xPath" />
				<xsl:with-param name="lang" select="$lang" />
				<xsl:with-param name="isDefaultLang" select="$isDefaultLang" />
				<xsl:with-param name="recordPosition" select="$recordPosition+1" />
				<xsl:with-param name="nbRecords" select="$nbRecords" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>