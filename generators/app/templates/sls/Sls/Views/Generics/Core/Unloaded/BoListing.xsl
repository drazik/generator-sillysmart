<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" extension-element-prefixes="dyn">
	<!-- 
	 	- Function BoListing
	 	- Generic's generation of back-office listing
	 	- Don't change anything
	-->
	<xsl:template name="BoListing">
		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="sls-bo-listing-title fixed-in-header">
			<h1>
				<xsl:variable name="sentenceRecordsets"><xsl:call-template name="displayLang"><xsl:with-param name="str" select="'SLS_BO_GENERIC_RECORDSETS'" /><xsl:with-param name="escaping" select="'true'" /></xsl:call-template></xsl:variable>
				<xsl:variable name="sentenceRecordsetsPlural"><xsl:if test="//View/page/total &gt; 1">s</xsl:if></xsl:variable>
				<xsl:value-of select="//View/page/model/label" />&#160;<sub>(<xsl:value-of select="php:functionString('sprintf',$sentenceRecordsets,//View/page/total,$sentenceRecordsetsPlural)" disable-output-escaping="yes" />)</sub>
			</h1>

			<div class="sls-bo-listing-params">
				<div class="paginate">
					<xsl:value-of select="php:functionString('SLS_String::paginate',//View/page/limit/start,//View/page/limit/length,//View/page/total)" disable-output-escaping="yes" />
				</div>
				<div class="results-by-page select generic-select-small">
					<select>
						<option value="20"><xsl:if test="//View/page/limit/length = 20"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>20</option>
						<option value="50"><xsl:if test="//View/page/limit/length = 50"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>50</option>
						<option value="100"><xsl:if test="//View/page/limit/length = 100"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>100</option>
						<option value="250"><xsl:if test="//View/page/limit/length = 250"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>250</option>
						<option value="500"><xsl:if test="//View/page/limit/length = 500"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>500</option>
						<option value="1000"><xsl:if test="//View/page/limit/length = 1000"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>1000</option>
					</select>
				</div>
			</div>
		</div>

		<div class="sls-bo-listing main-core-content" sls-listing-selection="false">
			<xsl:if test="(//View/urls/edit != '' and //View/urls/edit/@authorized = 'true') or (//View/urls/delete != '' and //View/urls/delete/@authorized = 'true') or (//View/urls/clone != '' and //View/urls/clone/@authorized = 'true')"><xsl:attribute name="sls-listing-selection">true</xsl:attribute></xsl:if>
			<div class="action-row sls-bo-color fixed-in-header">
				<ul class="actions">
					<xsl:if test="//View/urls/add != '' and //View/urls/add/@authorized = 'true'">
						<li class="add add-action">
							<a href="{//View/urls/add}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_GENERIC_ADD|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//View/urls/edit != '' and //View/urls/edit/@authorized = 'true'">
						<li class="edit edit-action">
							<a href="{//View/urls/edit}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_GENERIC_EDIT|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//View/urls/clone != '' and //View/urls/clone/@authorized = 'true'">
						<li class="clone clone-action">
							<a href="{//View/urls/clone}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_GENERIC_CLONE|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//View/urls/populate != '' and //View/urls/populate/@authorized = 'true'">
						<li class="populate populate-action">
							<a href="{//View/urls/populate}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_GENERIC_POPULATE|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//View/urls/delete != '' and //View/urls/delete/@authorized = 'true'">
						<li class="delete delete-action">
							<a href="{//View/urls/delete}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_GENERIC_DELETE|||</span>
							</a>
						</li>
					</xsl:if>
				</ul>

				<a href="" title="" class="screen-layout-expand" sls-setting-name="list_view" sls-setting-value="expand" sls-setting-selected="false" sls-setting-selected-class="selected">
					<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='list_view'] = 'expand'">
						<xsl:attribute name="class">screen-layout-expand selected</xsl:attribute>
						<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
					</xsl:if>
					<span class="picto"></span>
					<span class="label">|||sls:lang:SLS_BO_GENERIC_EXPAND|||</span>
				</a>
				<a href="" title="" class="screen-layout-condense" sls-setting-name="list_view" sls-setting-value="collapse" sls-setting-selected="false" sls-setting-selected-class="selected">
					<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='list_view'] = 'collapse'">
						<xsl:attribute name="class">screen-layout-condense selected</xsl:attribute>
						<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
					</xsl:if>
					<span class="picto"></span>
					<span class="label">|||sls:lang:SLS_BO_GENERIC_COLLAPSE|||</span>
				</a>

				<xsl:if test="count(//View/columns/column[edit = 'true']) &gt; 0">
					<ul class="fast-edit">
						<li>
							|||sls:lang:SLS_BO_GENERIC_QUICK_EDIT|||
							(<span class="state"><xsl:choose><xsl:when test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='quick_edit']]/setting) = 0 or //Statics/Site/BoMenu/admin/settings/setting[@key='quick_edit'] = 'disabled'">|||sls:lang:SLS_BO_GENERIC_DISABLED|||</xsl:when><xsl:otherwise>|||sls:lang:SLS_BO_GENERIC_ENABLED|||</xsl:otherwise></xsl:choose></span>)
						</li>
						<li class="disable">
							<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='quick_edit']]/setting) = 0 or //Statics/Site/BoMenu/admin/settings/setting[@key='quick_edit'] = 'disabled'"><xsl:attribute name="class">disable selected</xsl:attribute></xsl:if>
							<a href="" title="" sls-setting-name="quick_edit" sls-setting-value="disabled" sls-setting-selected="false">
								<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='quick_edit']]/setting) = 0 or //Statics/Site/BoMenu/admin/settings/setting[@key='quick_edit'] = 'disabled'">
									<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
								</xsl:if>
								<span class="picto"></span>
							</a>
						</li>
						<li class="enable">
							<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='quick_edit'] = 'enabled'"><xsl:attribute name="class">enable selected</xsl:attribute></xsl:if>
							<a href="" title="" sls-setting-name="quick_edit" sls-setting-value="enabled" sls-setting-selected="false">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='quick_edit'] = 'enabled'">
									<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
								</xsl:if>
								<span class="picto"></span>
							</a>
						</li>
					</ul>
				</xsl:if>
			</div>

			<xsl:choose>
				<xsl:when test="count(//View/entities/entity) &gt; 0">
					<div class="sls-bo-listing-container-positioning">
						<div class="sls-bo-listing-container">
							<div class="sls-bo-listing-head">
								<xsl:if test="(//View/urls/edit != '' and //View/urls/edit/@authorized = 'true') or (//View/urls/delete != '' and //View/urls/delete/@authorized = 'true') or (//View/urls/clone != '' and //View/urls/clone/@authorized = 'true')">
									<div class="sls-bo-listing-cell sls-listing-selection-btn">
										<div class="checkbox">
											<input type="checkbox" class="check-all" name="{name(//View/entities/entity[1]/*[1])}[]" value="all" />
										</div>
									</div>
								</xsl:if>
								<xsl:for-each select="//View/columns/column[list='true']">
									<xsl:variable name="columnTable" select="table" />
									<div class="sls-bo-listing-cell">
										<div class="relative column_{name}">
											<xsl:if test="name = //View/page/order/column"><xsl:attribute name="class"><xsl:value-of select="concat('relative ordered ', php:functionString('strtolower', //View/page/order/way), ' column_', name)" /></xsl:attribute></xsl:if>
											<xsl:if test="$columnTable != //View/page/model/table">
												<span class="table">
													<xsl:for-each select="//View/page/joins/join[table=$columnTable]/labels_html/label_html">
														<span><xsl:value-of select="." /><xsl:if test="position() &gt; 1">&#160;</xsl:if></span>
													</xsl:for-each>
												</span>
												<br/>
											</xsl:if>
											<span class="column-label">
												<xsl:for-each select="labels_html/label_html">
													<span><xsl:value-of select="." /><xsl:if test="position() &gt; 1">&#160;</xsl:if></span>
												</xsl:for-each>
											</span>
										</div>
									</div>
								</xsl:for-each>
								<!--<div class="sls-bo-listing-cell drag-and-drop">
									<div class="picto drag-and-drop-picto"></div>
								</div>-->
							</div>
							<xsl:for-each select="//View/entities/entity">
								<xsl:variable name="recordset" select="position()" />
								<xsl:if test="$recordset != 1">
									<div class="sls-bo-listing-row-separator"></div>
								</xsl:if>
								<div class="sls-bo-listing-row">
									<div class="sls-bo-listing-recordset">
										<div class="sls-bo-listing-cell sls-listing-selection-btn">
											<div class="checkbox">
												<input type="checkbox" name="{//View/page/model/pk}[]" value="{//View/entities/entity[$recordset]/*[name() = //View/page/model/pk]}" />
											</div>
										</div>
										<xsl:for-each select="//View/columns/column[list='true']">
											<xsl:variable name="columnName" select="name" />
											<xsl:variable name="columnGapBefore"><xsl:choose><xsl:when test="//View/entities/entity[$recordset]/@gap &gt; 0 and $columnName=//View/columns/column[list='true' and name != 'pk_lang' and html_type='input_text'][1]/name"><xsl:value-of select="//View/entities/entity[$recordset]/@gap" /></xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
											<xsl:variable name="columnClass"><xsl:choose><xsl:when test="$columnName=//View/columns/column[list='true' and name != 'pk_lang' and html_type='input_text'][1]/name">recordset-title</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>

											<xsl:call-template name="SlsBoListingCellContent">
												<xsl:with-param name="column" select="$columnName" />
												<xsl:with-param name="value" select="//View/entities/entity[$recordset]/*[name() = $columnName]" />
												<xsl:with-param name="class" select="$columnClass" />
												<xsl:with-param name="gapBefore" select="$columnGapBefore" />
											</xsl:call-template>
										</xsl:for-each>
										<!--<div class="sls-bo-listing-cell drag-and-drop">
											<div class="picto drag-and-drop-picto"></div>
										</div>-->
									</div>
									<xsl:if test="count(entities/entity) &gt; 0">
										<xsl:call-template name="SlsBoListingRecordsets">
											<xsl:with-param name="path" select="concat('//View/entities/entity[', $recordset, ']')" />
										</xsl:call-template>
									</xsl:if>
								</div>
							</xsl:for-each>
						</div>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="listing-no-result">
						|||sls:lang:SLS_BO_LIST_RESULTS_NOTHING|||
					</div>
				</xsl:otherwise>
			</xsl:choose>

		</div>

	</xsl:template>

	<xsl:template name="SlsBoListingRecordsets">
		<xsl:param name="path" />

		<xsl:for-each select="dyn:evaluate(concat($path, '/entities/entity'))" >
			<xsl:variable name="recordset" select="position()" />
			<div class="sls-bo-listing-row-child-separator"></div>
			<div class="sls-bo-listing-recordset sls-bo-listing-recordset-child">
				<div class="sls-bo-listing-cell">
					<div class="checkbox">
						<input type="checkbox" name="{//View/page/model/pk}[]" value="{dyn:evaluate(concat($path, '/entities/entity'))[$recordset]/*[name() = //View/page/model/pk]}" />
					</div>
				</div>
				<xsl:for-each select="//View/columns/column[list='true']">
					<xsl:variable name="columnName" select="name" />
					<xsl:variable name="columnGapBefore"><xsl:choose><xsl:when test="dyn:evaluate(concat($path, '/entities/entity'))[$recordset]/@gap &gt; 0 and $columnName=//View/columns/column[list='true' and name != 'pk_lang' and html_type='input_text'][1]/name"><xsl:value-of select="dyn:evaluate(concat($path, '/entities/entity'))[$recordset]/@gap" /></xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
					<xsl:variable name="columnClass"><xsl:choose><xsl:when test="$columnName=//View/columns/column[list='true' and name != 'pk_lang' and html_type='input_text'][1]/name">recordset-title</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
					<xsl:call-template name="SlsBoListingCellContent">
						<xsl:with-param name="column" select="$columnName" />
						<xsl:with-param name="value" select="dyn:evaluate(concat($path, '/entities/entity'))[$recordset]/*[name() = $columnName]" />
						<xsl:with-param name="class" select="$columnClass" />
						<xsl:with-param name="gapBefore" select="$columnGapBefore" />
					</xsl:call-template>
				</xsl:for-each>
				<!--<div class="sls-bo-listing-cell drag-and-drop">
					<div class="picto drag-and-drop-picto"></div>
				</div>-->
			</div>

			<xsl:if test="count(entities/entity) &gt; 0">
				<xsl:call-template name="SlsBoListingRecordsets">
					<xsl:with-param name="path" select="concat($path, '/entities/entity')" />
				</xsl:call-template>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="SlsBoListingCellContent">
		<xsl:param name="column" />
		<xsl:param name="value" />
		<xsl:param name="class" />
		<xsl:param name="gapBefore" />
		<xsl:variable name="columnType" select="//View/columns/column[name = $column]/html_type" />
		<div class="sls-bo-listing-cell" sls-table-column="{$column}" sls-editable="{edit}">
			<xsl:if test="$class != ''"><xsl:attribute name="class">sls-bo-listing-cell <xsl:value-of select="$class" /></xsl:attribute></xsl:if>
			<div class="sls-bo-listing-cell-content">
				<xsl:if test="$gapBefore != ''"><span class="sls-bo-listing-cell-content-before"><xsl:value-of select="php:functionString('str_repeat','—',$gapBefore)" />&#160;</span></xsl:if>
				<xsl:value-of select="$value" disable-output-escaping="yes" />
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>