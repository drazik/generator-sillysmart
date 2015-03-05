<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" xmlns:wsl="http://www.w3.org/1999/xhtml" extension-element-prefixes="dyn">
	<xsl:template name="SlsGraphPie">
		<xsl:param name="sls_graph_id" />
		<xsl:param name="sls_graph_path" />
		<xsl:param name="sls_graph_width" />

		<xsl:variable name="sls_graph_title"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_title'))" /></xsl:variable>
		<xsl:variable name="sls_graph_error"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_error'))" /></xsl:variable>

		<div class="graph">

			<xsl:choose>
				<xsl:when test="count(dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))) &gt; 0">
					<div id="{$sls_graph_id}"></div>
	
					<script type="text/javascript">
						if (typeof Highcharts == "undefined"){
							document.write(unescape("%3Cscript src='<xsl:value-of select="$sls_url_js_core_dyn" />highcharts/adapters/mootools-adapter.js'type='text/javascript'%3E%3C/script%3E"));
							document.write(unescape("%3Cscript src='<xsl:value-of select="$sls_url_js_core_dyn" />highcharts/highcharts.js'type='text/javascript'%3E%3C/script%3E"));
						}
					</script>
					<script type="text/javascript">
						window.addEvent('domready', function () {
	
							var pieGradient = new Highcharts.Chart({
								chart: {
									plotBackgroundColor: null,
									plotBorderWidth: null,
									plotShadow: false,
									renderTo: <xsl:value-of select="$sls_graph_id" />
									<xsl:if test="$sls_graph_width != ''">
										,width: <xsl:value-of select="$sls_graph_width" />
									</xsl:if>
								},
								credits: {
									enabled: false
								},
								colors:[
									<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_colors/sls_graph_color'))">
										'<xsl:value-of select="." />'
										<xsl:if test="position() != last()">,</xsl:if>
									</xsl:for-each>
								],
								title: {
									text: null //'<xsl:value-of select="$sls_graph_title" />'
								},
								plotOptions: {
									pie: {
										allowPointSelect: true,
										cursor: 'pointer',
										dataLabels: {
											enabled: true,
											formatter: function() {
												return '<b>' + this.point.name + '</b>: ' + this.percentage.toFixed(2) + '%';
											}
										}
									}
								},
								series: [{
									type: 'pie',
									name: 'Total',
									data: [
										<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
											['<xsl:choose><xsl:when test="sls_graph_data_legend != ''"><xsl:call-template name="protectString"><xsl:with-param name="str" select="sls_graph_data_legend" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template></xsl:when><xsl:otherwise>Unknown</xsl:otherwise></xsl:choose>', <xsl:value-of select="sls_graph_data_count" />]
											<xsl:if test="position() != last()">,</xsl:if>
										</xsl:for-each>
									]
								}]
							});
						});
					</script>
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