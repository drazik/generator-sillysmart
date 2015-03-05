<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" xmlns:wsl="http://www.w3.org/1999/xhtml" extension-element-prefixes="dyn">
	<xsl:template name="SlsGraphPivot">
		<xsl:param name="sls_graph_path" />
		<xsl:param name="sls_graph_from" />
		<xsl:variable name="sls_graph_title"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_title'))" /></xsl:variable>
		<xsl:variable name="sls_graph_error"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_error'))" /></xsl:variable>

		<div class="graph graph_pivot">

			<xsl:choose>
				<xsl:when test="count(dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))) &gt; 0">
					<xsl:choose>
						<xsl:when test="$sls_graph_from = 'sls'">
							<table>
								<thead>
									<tr>
										<th></th>
										<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line[1]/sls_graph_sub_data/sls_graph_sub_data_line'))">
											<th>
												<xsl:value-of select="sls_graph_sub_data_legend" />
											</th>
										</xsl:for-each>
									</tr>
								</thead>
								<tbody>
									<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
										<tr>
											<xsl:attribute name="class">
												<xsl:choose><xsl:when test="position() mod 2 = 1">odd</xsl:when><xsl:otherwise>even</xsl:otherwise></xsl:choose>
											</xsl:attribute>
											<td><xsl:value-of select="sls_graph_data_legend" /></td>
											<xsl:for-each select="sls_graph_sub_data/sls_graph_sub_data_line">
												<td>
													<xsl:value-of select="sls_graph_sub_data_value" />
												</td>
											</xsl:for-each>
										</tr>
									</xsl:for-each>
								</tbody>
							</table>
						</xsl:when>
						<xsl:otherwise>
							<div class="sls-bo-dashboard-listing">
								<div class="sls-bo-listing-head">
									<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line[1]/sls_graph_sub_data/sls_graph_sub_data_line'))">
										<div class="sls-bo-listing-cell">
											<xsl:value-of select="sls_graph_sub_data_legend" disable-output-escaping="yes" />
										</div>
									</xsl:for-each>
								</div>
								<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
									<div class="sls-bo-listing-row">
										<div class="sls-bo-listing-recordset">
											<xsl:for-each select="sls_graph_sub_data/sls_graph_sub_data_line">
												<div class="sls-bo-listing-cell">
													<div class="sls-bo-listing-cell-content">
														<xsl:value-of select="sls_graph_sub_data_value" />
													</div>
												</div>
											</xsl:for-each>
										</div>
									</div>
								</xsl:for-each>
							</div>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="$sls_graph_error != ''">
					<div class="graph_error">|||sls:lang:SLS_BO_DASHBOARD_GRAPHS_ERROR|||</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="graph_no_data">|||sls:lang:SLS_BO_DASHBOARD_GRAPHS_NO_DATA|||</div>
				</xsl:otherwise>
			</xsl:choose>
			
		</div>
	</xsl:template>
</xsl:stylesheet>