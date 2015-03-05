<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Boactionsbar">

		<xsl:if test="//View/page/wheres or (count(//View/columns/column) &gt; 0 and count(//View/entities/entity) &gt; 0 and //View/urls/edit/@authorized = 'true') or (count(//columns/column[html_type = 'input_file']) &gt; 0 and count(//View/entities/entity) = 0) or count(//View/sentences/sentence) &gt; 0">
			<div id="sls-bo-actions-bar">
				<div class="toggler">
					<div class="picto"></div>
				</div>
				<div class="sls-bo-actions-bar-wrapper">
					<div class="sls-bo-actions-bar-wrapper-scroll">
						<div class="sls-bo-actions-bar-content">

							<!-- FILTERS -->
							<xsl:if test="//View/page/wheres">
								<div class="sls-bo-actions-bar-section sls-bo-filters select-relayer">
									<div class="sls-bo-actions-bar-section-title">
										<div class="title-container">
											<table class="vt_centered">
												<tr><td>
													<h3 class="title">|||sls:lang:SLS_BO_SIDEBAR_SEARCH|||</h3>
												</td></tr>
											</table>
										</div>
										<div class="picto sls-bo-disabled-color sls-bo-color-opened">
											<div class="icon"></div>
										</div>
									</div>
									<div class="sls-bo-actions-bar-section-wrapper">
										<div class="sls-bo-actions-bar-section-content">
											<form method="post" action="{//View/urls/list}">
												<input type="hidden" name="Order" id="order" value="{concat(//View/page/order/column,'_',//View/page/order/way)}" />
												<input type="hidden" name="Length" id="length" value="{//View/page/limit/length}" />
												<input type="hidden" name="reload-filters" id="reload-filters" value="true" />
												<h4 class="sls-bo-actions-bar-section-subtitle">|||sls:lang:SLS_BO_SIDEBAR_FILTERS|||</h4>
												<div class="sls-bo-filters-active">
													<xsl:for-each select="//View/page/wheres/where">
														<xsl:variable name="column" select="column" />
														<div class="sls-bo-filter">
															<div class="delete"></div>
															<h5 class="sls-bo-filter-title">
																<xsl:value-of select="//View/columns/column[name = $column]/label" />
															</h5>
															<div class="sls-bo-filter-wrapper">
																<div class="sls-bo-filter-content">
																	<xsl:if test="//View/columns/column[name = $column]/html_type != 'input_ac' and //View/columns/column[name = $column]/html_type != 'input_radio' and //View/columns/column[name = $column]/html_type != 'input_checkbox'">
																		<div class="select generic-select-mode">
																			<select name="filters[{table}][{$column}][mode][]">
																				<option value="like">
																					<xsl:if test="mode = 'like'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_like'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="notlike">
																					<xsl:if test="mode = 'notlike'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_notlike'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="beginwith">
																					<xsl:if test="mode = 'beginwith'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_beginwith'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="endwith">
																					<xsl:if test="mode = 'endwith'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_endwith'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="equal">
																					<xsl:if test="mode = 'equal'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_equal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="notequal">
																					<xsl:if test="mode = 'notequal'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_notequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="in">
																					<xsl:if test="mode = 'in'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_in'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="lt">
																					<xsl:if test="mode = 'lt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_lowerthan'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="le">
																					<xsl:if test="mode = 'le'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_lowerthanorequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="ge">
																					<xsl:if test="mode = 'ge'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_greaterthanorequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="gt">
																					<xsl:if test="mode = 'gt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_greaterthan'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="null">
																					<xsl:if test="mode = 'null'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_isnull'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																				<option value="notnull">
																					<xsl:if test="mode = 'notnull'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																					<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_isnotnull'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																				</option>
																			</select>
																		</div>
																	</xsl:if>
																	<xsl:choose>
																		<xsl:when test="//View/columns/column[name = $column]/html_type = 'input_ac'">
																			<input type="text" name="filters[{table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-ac-db="{//View/columns/column[name = $column]/db}" sls-ac-entity="{//View/columns/column[name = $column]/ac_entity}" sls-ac-column="{$column}" sls-ac-label="{//View/columns/column[name = $column]/ac_label}" sls-ac-multiple="false" sls-label="{//View/columns/column[name = $column]/label}" placeholder="{//View/columns/column[name = $column]/ac_label}" value="{values/value}" />
																		</xsl:when>
																		<xsl:when test="//View/columns/column[name = $column]/html_type = 'input_radio'">
																			<ul>
																				<xsl:for-each select="//View/columns/column[name = $column]/choices/choice">
																					<xsl:variable name="labelFor" select="php:functionString('uniqid')" />
																					<li>
																						<label for="{$labelFor}"><xsl:value-of select="." /></label>
																						<span class="radio">
																							<xsl:variable name="value" select="." />
																							<input type="radio" id="{$labelFor}" name="filters[{../../table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{label}" value="{.}">
																								<xsl:if test="count(//View/page/wheres/where[column = $column]/values[value = $value]) &gt; 0">
																									<xsl:attribute name="checked">checked</xsl:attribute>
																								</xsl:if>
																							</input>
																						</span>
																					</li>
																				</xsl:for-each>
																			</ul>
																		</xsl:when>
																		<xsl:when test="//View/columns/column[name = $column]/html_type = 'input_checkbox'">
																			<ul>
																				<xsl:for-each select="//View/columns/column[name = $column]/choices/choice">
																					<xsl:variable name="labelFor" select="php:functionString('uniqid')" />
																					<li>
																						<label for="{$labelFor}"><xsl:value-of select="." /></label>
																						<span class="checkbox">
																							<xsl:variable name="value" select="." />
																							<input type="checkbox" id="{$labelFor}" name="filters[{../../table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{label}" value="{.}">
																								<xsl:if test="count(//View/page/wheres/where[column = $column]/values[value = $value]) &gt; 0">
																									<xsl:attribute name="checked">checked</xsl:attribute>
																								</xsl:if>
																							</input>
																						</span>
																					</li>
																				</xsl:for-each>
																			</ul>
																		</xsl:when>
																		<xsl:when test="//View/columns/column[name = $column]/html_type = 'input_year' or //View/columns/column[name = $column]/html_type='input_time' or //View/columns/column[name = $column]/html_type='input_date' or //View/columns/column[name = $column]/html_type='input_datetime' or //View/columns/column[name = $column]/html_type='input_timestamp'">
																			<div class="datepicker">
																				<input type="text" name="filters[{table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="{php:functionString('SLS_String::substrAfterFirstDelimiter',//View/columns/column[name = $column]/html_type,'input_')}" sls-label="{//View/columns/column[name = $column]/label}" placeholder="{//View/columns/column[name = $column]/label}" value="{values/value}" />
																			</div>
																		</xsl:when>
																		<xsl:when test="//View/columns/column[name = $column]/specific_type = 'color'">
																			<div class="sls-bo-colorpicker">
																				<input type="text" name="filters[{table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-specific-type="{//View/columns/column[name = $column]/specific_type}" sls-label="{//View/columns/column[name = $column]/label}" placeholder="{//View/columns/column[name = $column]/label}" value="{values/value}" />
																			</div>
																		</xsl:when>
																		<xsl:otherwise>
																			<input type="text" name="filters[{table}][{$column}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{//View/columns/column[name = $column]/label}" placeholder="{//View/columns/column[name = $column]/label}" value="{values/value}" />
																		</xsl:otherwise>
																	</xsl:choose>
																</div>
															</div>
														</div>
													</xsl:for-each>
												</div>
												<div class="sls-bo-filter-add sls-bo-color-text generic-dropdown-anchor sls-bo-color-pseudo-before">
													<span class="title">|||sls:lang:SLS_BO_SIDEBAR_FILTERS_ADD|||</span>
													<div class="generic-dropdown-ac">
														<div class="generic-dropdown-ac-wrapper">
															<div class="generic-dropdown-ac-content">
																<ul class="generic-dropdown-ac-options">
																	<xsl:for-each select="//View/columns/column[filter='true']">
																		<li class="generic-dropdown-ac-option sls-bo-color-hover sls-bo-filter-{position()}"><xsl:value-of select="label" /></li>
																	</xsl:for-each>
																</ul>
															</div>
														</div>
													</div>
												</div>
												<input type="submit" class="sls-bo-color" value="|||sls:lang:SLS_BO_SIDEBAR_SEARCH_SUBMIT|||" />
											</form>
											<div class="sls-bo-filters-tank">
												<xsl:for-each select="//View/columns/column[filter='true']">
													<div class="sls-bo-filter sls-bo-filter-{position()}">
														<div class="delete"></div>
														<h5 class="sls-bo-filter-title">
															<xsl:value-of select="label" />
														</h5>
														<div class="sls-bo-filter-wrapper">
															<div class="sls-bo-filter-content">
																<xsl:if test="html_type != 'input_ac' and html_type != 'input_radio' and html_type != 'input_checkbox'">
																	<div class="select generic-select-mode">
																		<select name="filters[{table}][{name}][mode][]">
																			<option value="like">
																				<xsl:if test="mode = 'like'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_like'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="notlike">
																				<xsl:if test="mode = 'notlike'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_notlike'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="beginwith">
																				<xsl:if test="mode = 'beginwith'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_beginwith'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="endwith">
																				<xsl:if test="mode = 'endwith'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_endwith'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="equal">
																				<xsl:if test="mode = 'equal'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_equal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="notequal">
																				<xsl:if test="mode = 'notequal'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_notequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="in">
																				<xsl:if test="mode = 'in'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_in'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="lt">
																				<xsl:if test="mode = 'lt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_lowerthan'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="le">
																				<xsl:if test="mode = 'le'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_lowerthanorequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="ge">
																				<xsl:if test="mode = 'ge'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_greaterthanorequal'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="gt">
																				<xsl:if test="mode = 'gt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_greaterthan'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="null">
																				<xsl:if test="mode = 'null'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_isnull'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																			<option value="notnull">
																				<xsl:if test="mode = 'notnull'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
																				<xsl:call-template name="displayLang"><xsl:with-param name="str" select="'sls_bo_list_filter_isnotnull'" /><xsl:with-param name="escaping" select="'yes'" /></xsl:call-template>
																			</option>
																		</select>
																	</div>
																</xsl:if>
																<xsl:choose>
																	<xsl:when test="html_type = 'input_ac'">
																		<input type="text" name="filters[{table}][{name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-ac-db="{db}" sls-ac-entity="{ac_entity}" sls-ac-column="{name}" sls-ac-label="{ac_label}" sls-ac-multiple="false" sls-label="{label}" placeholder="{ac_label}" value="" />
																	</xsl:when>
																	<xsl:when test="html_type = 'input_radio'">
																		<xsl:choose>
																			<xsl:when test="count(choices/choice) &lt;= 4">
																				<ul>
																					<xsl:for-each select="choices/choice">
																						<li>
																							<label for=""><xsl:value-of select="." /></label>
																							<span class="radio">
																								<input type="radio" name="filters[{../../table}][{../../name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{label}" value="{.}" />
																							</span>
																						</li>
																					</xsl:for-each>
																				</ul>
																			</xsl:when>
																			<xsl:otherwise>
																				<div class="select generic-select-mode">
																					<select name="filters[{../../table}][{../../name}][values]" sls-lang="|||sls:getLanguage|||" sls-html-type="select" sls-native-type="{native_type}" >
																						<xsl:for-each select="choices/choice">
																							<option value="{.}"><xsl:value-of select="." /></option>
																						</xsl:for-each>
																					</select>
																				</div>
																			</xsl:otherwise>
																		</xsl:choose>
																	</xsl:when>
																	<xsl:when test="html_type = 'input_checkbox'">
																		<ul>
																			<xsl:for-each select="choices/choice">
																				<li>
																					<label for=""><xsl:value-of select="." /></label>
																					<span class="checkbox">
																						<input type="checkbox" name="filters[{../../table}][{../../name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{label}" value="{.}" />
																					</span>
																				</li>
																			</xsl:for-each>
																		</ul>
																	</xsl:when>
																	<xsl:when test="html_type = 'input_year' or html_type='input_time' or html_type='input_date' or html_type='input_datetime' or html_type='input_timestamp'">
																		<div class="datepicker">
																			<input type="text" name="filters[{table}][{name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="{php:functionString('SLS_String::substrAfterFirstDelimiter',html_type,'input_')}" sls-label="{label}" placeholder="{label}" value="" />
																		</div>
																	</xsl:when>
																	<xsl:when test="specific_type = 'color'">
																		<div class="sls-bo-colorpicker">
																			<input type="text" name="filters[{table}][{name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-specific-type="{specific_type}" sls-label="{label}" placeholder="{label}" value="" />
																		</div>
																	</xsl:when>
																	<xsl:otherwise>
																		<input type="text" name="filters[{table}][{name}][values][]" sls-lang="|||sls:getLanguage|||" sls-native-type="string" sls-label="{label}" placeholder="{label}" value="" />
																	</xsl:otherwise>
																</xsl:choose>
															</div>
														</div>
													</div>
												</xsl:for-each>
											</div>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /FILTERS -->

							<!-- QUICK EDIT -->
							<xsl:if test="count(//View/columns/column) &gt; 0 and count(//View/entities/entity) &gt; 0 and //View/urls/edit/@authorized = 'true'">
								<div class="sls-bo-actions-bar-section sls-bo-quick-edit">
									<div class="sls-bo-actions-bar-section-title">
										<div class="title-container">
											<table class="vt_centered">
												<tr><td>
													<h3 class="title">|||sls:lang:SLS_BO_SIDEBAR_QUICK_EDIT|||</h3>
													<div class="results sls-bo-color-text"></div>
												</td></tr>
											</table>
										</div>
										<div class="picto sls-bo-disabled-color sls-bo-color-opened">
											<div class="icon"></div>
										</div>
									</div>
									<div class="sls-bo-actions-bar-section-wrapper">
										<div class="sls-bo-actions-bar-section-content sls-bo-form-page">
											<form action="{//View/urls/edit}" method="post" enctype="multipart/form-data" sls-validation="true" sls-tooltip="false" sls-lang="{//Statics/Sls/Session/params/param[name='current_lang']/value}" sls-db="{//View/page/model/db}" sls-model="{//View/page/model/table}" sls-recordset-id="">
												<input type="hidden" name="id" />
												<input type="hidden" name="reload-edit" value="true" />
												<input type="hidden" name="sls-request" value="async" />
												<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
													<xsl:if test="(. != //Statics/Sls/Configs/site/defaultLang and . != //Statics/Sls/Session/params/param[name='current_lang']/value) or (. != //Statics/Sls/Session/params/param[name='current_lang']/value and //Statics/Sls/Configs/site/defaultLang != //Statics/Sls/Session/params/param[name='current_lang']/value and count(//View/columns/column[edit='true' and pk = 'false' and html_type != 'input_file' and multilanguage = 'true']) = 0) or (//Statics/Sls/Configs/site/defaultLang = //Statics/Sls/Session/params/param[name='current_lang']/value and count(//View/columns/column[edit='true' and pk = 'false' and html_type != 'input_file' and multilanguage = 'false']) = 0)">
														<input type="hidden" name="{//View/page/model/table}[properties][{.}]" value="" />
													</xsl:if>
												</xsl:for-each>
												<div class="sls-bo-form-page-content">
													<xsl:for-each select="//View/columns/column[edit='true' and pk = 'false' and html_type != 'input_file']">
														<xsl:variable name="lang"><xsl:choose><xsl:when test="multilanguage = 'true'"><xsl:value-of select="//Statics/Sls/Session/params/param[name='current_lang']/value" /></xsl:when><xsl:otherwise><xsl:value-of select="//Statics/Sls/Configs/site/langs/name" /></xsl:otherwise></xsl:choose></xsl:variable>
														<xsl:variable name="table" select="table" />
														<xsl:variable name="fieldId" select="concat(name, '_', $lang)" />
														<xsl:variable name="fieldName" select="concat($table, '[properties][', $lang, '][', name, ']')" />
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
																		<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{native_type}" sls-ac-db="{ac_db}" sls-ac-entity="{ac_entity}" sls-ac-column="{ac_column}" sls-ac-label="{ac_label}" sls-ac-multiple="{ac_multiple}" sls-label="{ac_label}" sls-required="{required}" placeholder="{ac_label}" value="" />
																	</xsl:when>
																	<xsl:when test="html_type = 'input_radio'">
																		<xsl:choose>
																			<xsl:when test="(count(choices/choice) &lt;= 4 and required = 'true') or (count(choices/choice) &lt;= 3 and required = 'false')">
																				<xsl:if test="required = 'false'">
																					<div class="choice">
																						<span class="radio">
																							<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', (count(choices/choice) + 1))}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="">
																								<xsl:if test="$fieldName = '' or count(../../values/value[@lang = $lang]) = 0"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																							</input>
																						</span>
																						<label for="{concat($fieldId, '_', (count(choices/choice) + 1))}">Unspecified</label>
																					</div>
																				</xsl:if>
																				<xsl:for-each select="choices/choice">
																					<div class="choice">
																						<span class="radio">
																							<input type="radio" name="{$fieldName}" id="{concat($fieldId, '_', position())}" sls-lang="{$lang}" sls-html-type="{../../html_type}" sls-native-type="{../../native_type}" sls-label="{../../label}" sls-required="{../../required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" value="{.}" />
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
																							<option value="{.}"><xsl:value-of select="." /></option>
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
																						<xsl:if test="count(../../values/value[@lang = $lang and . = $choiceValue]) &gt; 0">
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
																			<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{php:functionString('SLS_String::substrAfterFirstDelimiter',html_type,'input_')}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" />
																		</div>
																	</xsl:when>
																	<xsl:when test="specific_type = 'color'">
																		<div class="sls-bo-colorpicker">
																			<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-native-type="{html_type}" sls-specific-type="{specific_type}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" />
																		</div>
																	</xsl:when>
																	<xsl:when test="html_type = 'input_textarea'">
																		<textarea name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html="{html}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" cols="" rows="5"></textarea>
																	</xsl:when>
																	<xsl:when test="html_type = 'input_password'">
																		<input type="password" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" placeholder="|||sls:lang:SLS_BO_GENERIC_CHOOSE_PASSWORD|||" value="" />
																	</xsl:when>
																	<xsl:otherwise>
																		<input type="text" name="{$fieldName}" id="{$fieldId}" sls-lang="{$lang}" sls-html-type="{html_type}" sls-specific-type="{specific_type}" sls-specific-type-extended="{specific_type_extended}" sls-filters="{filters}" sls-native-type="{native_type}" sls-unique="{unique}" sls-label="{label}" sls-required="{required}" placeholder="|||sls:lang:SLS_BO_GENERIC_FILL_FIELD|||" />
																	</xsl:otherwise>
																</xsl:choose>
															</div>
														</div>
													</xsl:for-each>
												</div>
												<div class="sls-bo-form-page-bottom">
													<div class="submit-block">
														<div class="sls-bo-form-page-submit sls-bo-color">|||sls:lang:SLS_BO_SIDEBAR_QUICK_EDIT_SUBMIT|||</div>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /QUICK EDIT -->

							<!-- EXPORT -->
							<xsl:if test="//View/page/wheres">
								<div class="sls-bo-actions-bar-section sls-bo-export">
									<div class="sls-bo-actions-bar-section-title">
										<div class="title-container">
											<table class="vt_centered">
												<tr><td>
													<h3 class="title">|||sls:lang:SLS_BO_SIDEBAR_EXPORT|||</h3>
												</td></tr>
											</table>
										</div>
										<div class="picto sls-bo-disabled-color sls-bo-color-opened">
											<div class="icon"></div>
										</div>
									</div>
									<div class="sls-bo-actions-bar-section-wrapper">
										<div class="sls-bo-actions-bar-section-content">
											<form method="post" action="{//Statics/Site/BoMenu/various/export}">
												<input type="hidden" name="model[db]" value="{//View/page/model/db}" />
												<input type="hidden" name="model[table]" value="{//View/page/model/table}" />
												<!-- FILTERS WHERES -->
												<xsl:for-each select="//View/page/wheres/where">
													<xsl:variable name="column" select="column" />
													<xsl:variable name="table" select="table" />
													<input type="hidden" name="filters[{$table}][{$column}][mode][]" value="{mode}" />
													<xsl:for-each select="values/value">
														<input type="hidden" name="filters[{$table}][{$column}][values][]" value="{.}" />
													</xsl:for-each>
												</xsl:for-each>
												<!-- /FILTERS WHERES -->
												<xsl:for-each select="//View/page/joins/join">
													<input type="hidden" name="joins[]" value="{table}" />
												</xsl:for-each>
												<div class="sls-bo-export-field">
													<h5 class="sls-bo-export-field-title">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_FORMAT|||</h5>
													<div class="sls-bo-export-field-wrapper">
														<div class="sls-bo-export-field-content">
															<ul>
																<li>
																	<label for="sls_bo_export_excel">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_FORMAT_XLS|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_excel" name="options[format]" value="excel">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) = 0 or (count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_format'] = 'excel')"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_html">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_FORMAT_HTML|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_html" name="options[format]" value="html">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_format'] = 'html'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_csv">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_FORMAT_CSV|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_csv" name="options[format]" value="csv">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_format'] = 'csv'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_txt">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_FORMAT_TXT|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_txt" name="options[format]" value="txt">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_format'] = 'txt'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
															</ul>
														</div>
													</div>
												</div>
												<div class="sls-bo-export-field">
													<h5 class="sls-bo-export-field-title">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_COLUMNS|||</h5>
													<div class="sls-bo-export-field-wrapper">
														<div class="sls-bo-export-field-content">
															<ul>
																<li>
																	<label for="sls_bo_export_all_column_true">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_COLUMNS_TRUE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_all_column_true" name="options[all_column]" value="true">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) = 0 or (count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_all_column'] = 'true')"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_all_column_false">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_COLUMNS_FALSE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_all_column_false" name="options[all_column]" value="false">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_all_column'] = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
															</ul>
														</div>
													</div>
												</div>
												<div class="sls-bo-export-field">
													<h5 class="sls-bo-export-field-title">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LINES|||</h5>
													<div class="sls-bo-export-field-wrapper">
														<div class="sls-bo-export-field-content">
															<ul>
																<li>
																	<label for="sls_bo_export_all_table_true">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LINES_TRUE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_all_table_true" name="options[all_table]" value="true">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) = 0 or (count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_all_table'] = 'true')"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_all_table_false">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LINES_FALSE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_all_table_false" name="options[all_table]" value="false">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_all_table'] = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
															</ul>
														</div>
													</div>
												</div>
												<div class="sls-bo-export-field">
													<h5 class="sls-bo-export-field-title">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LEGEND|||</h5>
													<div class="sls-bo-export-field-wrapper">
														<div class="sls-bo-export-field-content">
															<ul>
																<li>
																	<label for="sls_bo_export_display_legend_true">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LEGEND_TRUE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_display_legend_true" name="options[display_legend]" value="true">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) = 0 or (count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_display_legend'] = 'true')"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
																<li>
																	<label for="sls_bo_export_display_legend_false">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_LEGEND_FALSE|||</label>
																	<span class="radio">
																		<input type="radio" id="sls_bo_export_display_legend_false" name="options[display_legend]" value="false">
																			<xsl:if test="count(//Statics/Site/BoMenu/admin/settings/*) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='export_display_legend'] = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
																		</input>
																	</span>
																</li>
															</ul>
														</div>
													</div>
												</div>
												<div class="export-submit sls-bo-color">|||sls:lang:SLS_BO_SIDEBAR_EXPORT_SUBMIT|||</div>
											</form>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /EXPORT -->

							<!-- FILES -->
							<xsl:if test="count(//columns/column[html_type = 'input_file']) &gt; 0 and count(//View/entities/entity) = 0">
								<div class="sls-bo-actions-bar-section sls-bo-uploads">
									<div class="sls-bo-actions-bar-section-title">
										<div class="title-container">
											<table class="vt_centered">
												<tr><td>
													<h3 class="title">|||sls:lang:SLS_BO_SIDEBAR_UPLOAD|||</h3>
													<div class="results sls-bo-color-text"></div>
												</td></tr>
											</table>
										</div>
										<div class="picto sls-bo-disabled-color sls-bo-color-opened">
											<div class="icon"></div>
										</div>
									</div>
									<div class="sls-bo-actions-bar-section-wrapper">
										<div class="sls-bo-actions-bar-section-content">

										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /FILES -->

							<!-- TRANSLATIONS -->
							<xsl:if test="count(//View/sentences/sentence) &gt; 0">
								<div class="sls-bo-actions-bar-section sls-bo-module-i18n">
									<div class="sls-bo-actions-bar-section-title">
										<div class="title-container">
											<table class="vt_centered">
												<tr><td>
													<h3 class="title">|||sls:lang:SLS_BO_SIDEBAR_I18N|||</h3>
													<div class="results sls-bo-color-text"></div>
												</td></tr>
											</table>
										</div>
										<div class="picto sls-bo-disabled-color sls-bo-color-opened">
											<div class="icon"></div>
										</div>
									</div>
									<div class="sls-bo-actions-bar-section-wrapper">
										<div class="sls-bo-actions-bar-section-content">
											<div class="inputs"></div>
											<div class="navigation">
												<div class="btn sls-bo-color previous">|||sls:lang:SLS_BO_SIDEBAR_I18N_PREV|||</div>
												<div class="btn sls-bo-color next">|||sls:lang:SLS_BO_SIDEBAR_I18N_NEXT|||</div>
											</div>
										</div>
									</div>
								</div>
							</xsl:if>
							<!-- /TRANSLATIONS -->

						</div>
					</div>
				</div>
			</div>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>