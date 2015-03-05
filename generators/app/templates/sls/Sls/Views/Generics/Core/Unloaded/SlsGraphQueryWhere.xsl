<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" xmlns:wsl="http://www.w3.org/1999/xhtml" extension-element-prefixes="dyn">
	<xsl:template name="SlsGraphQueryWhere">
		<xsl:param name="sls_graph_query_where_path" />
		<xsl:param name="sls_graph_query_where_tree" />
		<xsl:param name="sls_graph_query_where_dom_id" />

		<div class="sls_graph_query_where">
			<xsl:variable name="sls_graph_query_where_num"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_num'))" /></xsl:variable>
			<xsl:variable name="sls_graph_query_where_condition"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_condition'))" /></xsl:variable>
			<xsl:variable name="sls_graph_query_where_type"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_type'))" /></xsl:variable>
			<xsl:variable name="sls_graph_query_where_column"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_column'))" /></xsl:variable>
			<xsl:variable name="sls_graph_query_where_operator"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_operator'))" /></xsl:variable>
			<xsl:variable name="sls_graph_query_where_value"><xsl:value-of select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_value'))" /></xsl:variable>

			<xsl:if test="$sls_graph_query_where_dom_id != ''">
				<xsl:attribute name="id"><xsl:value-of select="$sls_graph_query_where_dom_id" /></xsl:attribute>
			</xsl:if>

			<xsl:choose>
				<!-- query where example -->
				<xsl:when test="$sls_graph_query_where_dom_id = 'sls_graph_query_where_example'">
					<div class="condition">{CONDITION}</div>
					<div class="delete">X</div>
					<div class="clear"></div>

					<input type="hidden" class="sls_graph_query_where_tree" value="{{PATH}}[{{NUM}}]" />
					<input type="hidden" value="{{TYPE}}" name="{{PATH}}[{{NUM}}][sls_graph_query_where_type]" class="sls_graph_query_where_type" />
					<input type="hidden" value="{{CONDITION}}" name="{{PATH}}[{{NUM}}][sls_graph_query_where_condition]" class="sls_graph_query_where_condition" />

					<div class="line">
						<div class="field field_columns">
							<select name="{{PATH}}[{{NUM}}][sls_graph_query_where_column]" class="columns"></select>
						</div>

						<div class="field">
							<select name="{{PATH}}[{{NUM}}][sls_graph_query_where_operator]" class="operators">
								<option value=""></option>
								<xsl:for-each select="//View/sls_graph_query_operators/sls_graph_query_operator">
									<option value="{sls_graph_query_operator_value}">
										<xsl:value-of select="sls_graph_query_operator_label" />
									</option>
								</xsl:for-each>
							</select>
						</div>

						<div class="field">
							<input type="text" value="" name="{{PATH}}[{{NUM}}][sls_graph_query_where_value]" class="sls_graph_query_where_value" />
						</div>
					</div>
				</xsl:when>
				<!-- /query where example -->
				
				<!-- query where-->
				<xsl:otherwise>
					<div class="condition"><xsl:value-of select="$sls_graph_query_where_condition"></xsl:value-of></div>
					<xsl:if test="$sls_graph_query_where_dom_id != 'sls_graph_query_where_root' and $sls_graph_query_where_type = 'clause'">
						<div class="delete">X</div>
					</xsl:if>

					<div class="clear"></div>

					<input type="hidden" class="sls_graph_query_where_tree" value="{$sls_graph_query_where_tree}" />
					<input type="hidden" value="{$sls_graph_query_where_type}" name="{$sls_graph_query_where_tree}[sls_graph_query_where_type]" class="sls_graph_query_where_type" />
					<input type="hidden" value="{$sls_graph_query_where_condition}" name="{$sls_graph_query_where_tree}[sls_graph_query_where_condition]" class="sls_graph_query_where_condition" />

					<xsl:if test="$sls_graph_query_where_type = 'clause'">
						<div class="line">
							<div class="field field_columns hide">
								<xsl:if test="count(//View/errors/error[@column = 'sls_graph_query_where_column' and @num = $sls_graph_query_where_num]) &gt; 0">
									<xsl:attribute name="class">field field_columns error</xsl:attribute>
								</xsl:if>
								<select name="{$sls_graph_query_where_tree}[sls_graph_query_where_column]" class="columns">
									<option value="{$sls_graph_query_where_column}"></option>
								</select>
							</div>

							<div class="field">
								<xsl:if test="count(//View/errors/error[@column = 'sls_graph_query_where_operator' and @num = $sls_graph_query_where_num]) &gt; 0">
									<xsl:attribute name="class">field error</xsl:attribute>
								</xsl:if>
								<select name="{$sls_graph_query_where_tree}[sls_graph_query_where_operator]" class="operators">
									<option value=""></option>
									<xsl:for-each select="//View/sls_graph_query_operators/sls_graph_query_operator">
										<option value="{sls_graph_query_operator_value}">
											<xsl:if test="sls_graph_query_operator_value = $sls_graph_query_where_operator"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
											<xsl:value-of select="sls_graph_query_operator_label" />
										</option>
									</xsl:for-each>
								</select>
							</div>

							<div class="field">
								<xsl:choose>
									<xsl:when test="count(//View/errors/error[@column = 'sls_graph_query_where_value' and @num = $sls_graph_query_where_num]) &gt; 0">
										<xsl:attribute name="class">field error</xsl:attribute>
									</xsl:when>
									<xsl:when test="$sls_graph_query_where_operator = 'null' or $sls_graph_query_where_operator = 'notnull'">
										<xsl:attribute name="class">field hide</xsl:attribute>
									</xsl:when>
								</xsl:choose>

								<input type="text" value="{$sls_graph_query_where_value}" name="{$sls_graph_query_where_tree}[sls_graph_query_where_value]" class="sls_graph_query_where_value" />
							</div>
						</div>
					</xsl:if>
				</xsl:otherwise>
				<!-- /query where-->
			</xsl:choose>

			<div class="clear" />

			<xsl:if test="$sls_graph_query_where_dom_id = 'sls_graph_query_where_example' or  $sls_graph_query_where_type = 'group'">
				<div class="sls_graph_query_where_children">
					<xsl:if test="count(dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_children/sls_graph_query_where'))) &gt; 0">
						<xsl:for-each select="dyn:evaluate(concat($sls_graph_query_where_path, '/sls_graph_query_where_children/sls_graph_query_where'))">
							<xsl:call-template name="SlsGraphQueryWhere">
								<xsl:with-param name="sls_graph_query_where_path"><xsl:value-of select="concat($sls_graph_query_where_path, '/sls_graph_query_where_children/sls_graph_query_where[', position(),']')" /></xsl:with-param>
								<xsl:with-param name="sls_graph_query_where_tree"><xsl:value-of select="concat($sls_graph_query_where_tree, '[sls_graph_query_where_children][', position()-1, ']')" /></xsl:with-param>
								<xsl:with-param name="sls_graph_query_where_dom_id"></xsl:with-param>
							</xsl:call-template>
						</xsl:for-each>
					</xsl:if>
				</div>

				<div class="actions">
					<button class="and_group">AND ()</button>
					<button class="or_group">OR ()</button>
					<button class="and_clause">AND clause</button>
					<button class="or_clause">OR clause</button>

					<xsl:if test="$sls_graph_query_where_dom_id != 'sls_graph_query_where_root'">
						<div class="delete">X</div>
					</xsl:if>
				</div>
			</xsl:if>

			<div class="clear"></div>
		</div>

	</xsl:template>
</xsl:stylesheet>