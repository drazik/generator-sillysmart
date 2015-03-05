<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" xmlns:wsl="http://www.w3.org/1999/xhtml" extension-element-prefixes="dyn">
	<xsl:template name="SlsGraphBar">
		<xsl:param name="sls_graph_id" />
		<xsl:param name="sls_graph_path" />
		<xsl:param name="sls_graph_width" />

		<xsl:variable name="sls_graph_title"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_title'))" /></xsl:variable>
		<xsl:variable name="sls_graph_error"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_error'))" /></xsl:variable>
		<xsl:variable name="sls_graph_data_aggregation_function"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_aggregation_function'))" /></xsl:variable>
		<xsl:variable name="sls_graph_data_legend_x"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_legend_x'))" /></xsl:variable>
		<xsl:variable name="sls_graph_data_legend_y"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_legend_y'))" /></xsl:variable>
		<xsl:variable name="sls_graph_data_legend_stacked"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_legend_stacked'))" /></xsl:variable>
		<xsl:variable name="sls_graph_data_stacked"><xsl:value-of select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_stacked'))" /></xsl:variable>

		<div  class="graph">

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
						window.addEvent('load', function () {
							var stackedColumn = new Highcharts.Chart({
							chart: {
								renderTo: <xsl:value-of select="$sls_graph_id" />,
								type: 'column'
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
							xAxis: {
								categories: [
									<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
										'<xsl:call-template name="protectString"><xsl:with-param name="str" select="sls_graph_data_legend" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'
										<xsl:if test="position() != last()">,</xsl:if>
									</xsl:for-each>
								],
								title: {
									text: '<xsl:call-template name="protectString"><xsl:with-param name="str" select="$sls_graph_data_legend_x" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'
								}
							},
							yAxis: {
								title: {
									text: '<xsl:call-template name="protectString"><xsl:with-param name="str" select="$sls_graph_data_legend_y" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'
								}
							},
	
							legend: {
								<xsl:if test="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_stacked')) = 'false'">
									enabled : false,
								</xsl:if>
								layout : 'vertical',
								backgroundColor: 'white',
								borderColor: '#CCC',
								borderWidth: 1,
								shadow: false,
								floating : false,
								verticalAlign : 'bottom',
								align : 'right',
								title: {
									text: '<xsl:call-template name="protectString"><xsl:with-param name="str" select="$sls_graph_data_legend_stacked" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'
								}
							},
	
							tooltip: {
								formatter: function() {
									return '<b>'+ this.x +'</b><br/>'+
									<xsl:choose>
										<xsl:when test="$sls_graph_data_aggregation_function != 'avg' and $sls_graph_data_stacked != 'false'">
											this.series.name +': '+ this.y +'<br/>'
											+ 'Total: '+ this.point.stackTotal;
										</xsl:when>
										<xsl:otherwise>
											this.y
										</xsl:otherwise>
									</xsl:choose>
								}
							},
	
							plotOptions: {
								column: {
									<xsl:if test="$sls_graph_data_stacked = 'false'">
										colorByPoint: true,
									</xsl:if>
									stacking: 'normal',
									dataLabels: {
										enabled: false,
										color: 'white'
									}
								}
							},
							series: [
								<xsl:choose>
									<xsl:when test="$sls_graph_data_stacked = 'false'">
										{
											data :[
											<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
												<xsl:value-of select="sls_graph_data_value" />
												<xsl:if test="position() != last()">,</xsl:if>
											</xsl:for-each>
											]
										}
									</xsl:when>
									<xsl:otherwise>
										<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line/*/*/sls_graph_sub_data_legend[not(preceding::sls_graph_sub_data_legend/. = .)]'))">
											<xsl:variable name="legend" select="." />
											{
												name: '<xsl:choose><xsl:when test="$legend != ''"><xsl:call-template name="protectString"><xsl:with-param name="str" select="$legend" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template></xsl:when><xsl:otherwise>Unknown</xsl:otherwise></xsl:choose>',
												data : [
													<xsl:for-each select="dyn:evaluate(concat($sls_graph_path, '/sls_graph_data/sls_graph_data_line'))">
														<xsl:choose>
															<xsl:when test="count(sls_graph_sub_data/sls_graph_sub_data_line[sls_graph_sub_data_legend = $legend]) &gt; 0"><xsl:value-of select="sls_graph_sub_data/sls_graph_sub_data_line[sls_graph_sub_data_legend = $legend]/sls_graph_sub_data_value" /></xsl:when>
															<xsl:otherwise>0</xsl:otherwise>
														</xsl:choose>
														<xsl:if test="position() != last()">,</xsl:if>
													</xsl:for-each>
												]
											}
											<xsl:if test="position() != last()">,</xsl:if>
										</xsl:for-each>
									</xsl:otherwise>
								</xsl:choose>
							]
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