<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ModelsProperties">
	
		<h1>Models Properties</h1>
		
		<ul style="position:fixed;left:840px;">
			<xsl:for-each select="//View/dbs/db">
				<li>
					<h3 style="margin-bottom:0;"><xsl:value-of select="name" /></h3>
					<ul>
						<xsl:for-each select="tables/table">
							<xsl:if test="count(columns/column) &gt; 0">
								<li>
									<a href="#{concat(../../name,'_',name)}" class="sls_smooth_scroll">
										<xsl:value-of select="name" />
									</a>
								</li>
							</xsl:if>
						</xsl:for-each>
					</ul>
				</li>
			</xsl:for-each>
		</ul>
		
		<xsl:for-each select="//View/dbs/db">
			<xsl:variable name="db" select="name" />
			<xsl:for-each select="tables/table">
				<xsl:if test="count(columns/column) &gt; 0">
					<xsl:variable name="table" select="name" />
					<h3 id="{concat($db,'_',$table)}" style="margin-bottom:0;"><xsl:value-of select="concat($db,' - ',$table)" /></h3>
					<xsl:choose>
						<xsl:when test="count(columns/column) &gt; 0">
							<table cellspacing="0" cellpadding="5" border="1" style="width:600px;">
								<tr>
									<th align="center" style="background-color:#E9E9E9;color:#000;">Column</th>
									<th align="center" style="background-color:#E9E9E9;color:#000;">Types</th>
									<th align="center" style="background-color:#E9E9E9;color:#000;">Filters</th>
									<th align="center" style="background-color:#E9E9E9;color:#000;">FKs</th>
								</tr>
								<xsl:for-each select="columns/column">
									<xsl:variable name="column" select="." />
									<tr>
										<td align="center"><xsl:value-of select="." /></td>
										<td align="center">
											<xsl:choose>
												<xsl:when test="count(../../types/type[@column=$column]) &gt; 0">
													<a href="{//View/url_type}/name/{concat($db,'_',$table)}/column/{$column}" style="color:#A2A2A2;text-decoration:none;"><xsl:value-of select="../../types/type[@column=$column]" /></a>
												</xsl:when>
												<xsl:otherwise>X</xsl:otherwise>
											</xsl:choose>
										</td>
										<td align="center">
											<xsl:choose>
												<xsl:when test="count(../../filters/filter[@column=$column]) &gt; 0">
													<a href="{//View/url_model}/name/{concat($db,'_',$table)}" style="color:#A2A2A2;text-decoration:none;"><xsl:value-of select="../../filters/filter[@column=$column]" /></a>
												</xsl:when>
												<xsl:otherwise>X</xsl:otherwise>
											</xsl:choose>
										</td>
										<td align="center">
											<xsl:choose>
												<xsl:when test="count(../../fks/fk[@column=$column]) &gt; 0">
													<a href="{//View/url_fk}/name/{concat($db,'_',$table)}/column/{$column}" style="color:#A2A2A2;text-decoration:none;"><xsl:value-of select="../../fks/fk[@column=$column]" /></a>
												</xsl:when>
												<xsl:otherwise>X</xsl:otherwise>
											</xsl:choose>
										</td>
									</tr>
								</xsl:for-each>
							</table>
						</xsl:when>
						<xsl:otherwise>
							No properties for this model.<br />
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:for-each>
		</xsl:for-each>
		
	</xsl:template>
</xsl:stylesheet>