<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoDashBoard">

		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="sls-bo-listing-title fixed-in-header">
			<h1>|||sls:lang:SLS_BO_MENU_DASHBOARD|||</h1>
		</div>

		<div class="sls-bo-dashboard main-core-content">
			<div class="action-row sls-bo-color fixed-in-header">
				<xsl:variable name="moduleGa"><xsl:choose><xsl:when test="count(//View/google_analytics/*) &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
				<xsl:variable name="moduleMetric"><xsl:choose><xsl:when test="count(//View/metrics/metric/*) &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
				<xsl:variable name="moduleMonitoring"><xsl:choose><xsl:when test="count(//View/logs/monitoring/log/*) &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
				<xsl:variable name="moduleGraph"><xsl:choose><xsl:when test="count(//View/sls_graphs/sls_graph/*) &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
				<xsl:variable name="moduleEmail"><xsl:choose><xsl:when test="count(//View/logs/email/log/*) &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></xsl:variable>
				<xsl:variable name="nbModules"><xsl:value-of select="number($moduleGa + $moduleMetric + $moduleMonitoring + $moduleGraph + $moduleEmail)" /></xsl:variable>
				<xsl:if test="$nbModules &gt; 1">
					<ul class="actions dashboard-modules">
						<xsl:if test="count(//View/google_analytics/*) &gt; 0">
							<li class="btn-dashboard-module-google-analytics" sls-setting-name="dashboard_ga" sls-setting-value="{//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_ga']}" sls-setting-value-off="hidden">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_ga'] = 'hidden'"><xsl:attribute name="sls-setting-value-off">visible</xsl:attribute></xsl:if>
								<span class="label">Google Analytics</span>
							</li>
						</xsl:if>
						<xsl:if test="count(//View/metrics/metric/*) &gt; 0">
							<li class="btn-dashboard-module-metrics" sls-setting-name="dashboard_metric" sls-setting-value="{//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_metric']}" sls-setting-value-off="hidden">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_metric'] = 'hidden'"><xsl:attribute name="sls-setting-value-off">visible</xsl:attribute></xsl:if>
								<span class="label">|||sls:lang:SLS_BO_DASHBOARD_METRICS_TITLE|||</span>
							</li>
						</xsl:if>
						<xsl:if test="count(//View/logs/monitoring/log/*) &gt; 0">
							<li class="btn-dashboard-module-monitoring-logs" sls-setting-name="dashboard_monitoring" sls-setting-value="{//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_monitoring']}" sls-setting-value-off="hidden">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_monitoring'] = 'hidden'"><xsl:attribute name="sls-setting-value-off">visible</xsl:attribute></xsl:if>
								<span class="label">|||sls:lang:SLS_BO_DASHBOARD_LOGS_MONITORING_TITLE|||</span>
							</li>
						</xsl:if>
						<xsl:if test="count(//View/sls_graphs/sls_graph/*) &gt; 0">
							<li class="btn-dashboard-module-custom-graphs" sls-setting-name="dashboard_graph" sls-setting-value="{//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_graph']}" sls-setting-value-off="hidden">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_graph'] = 'hidden'"><xsl:attribute name="sls-setting-value-off">visible</xsl:attribute></xsl:if>
								<span class="label">|||sls:lang:SLS_BO_DASHBOARD_GRAPHS_TITLE|||</span>
							</li>
						</xsl:if>
						<xsl:if test="count(//View/logs/email/log/*) &gt; 0">
							<li class="btn-dashboard-module-email-logs" sls-setting-name="dashboard_email" sls-setting-value="{//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_email']}" sls-setting-value-off="hidden">
								<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_email'] = 'hidden'"><xsl:attribute name="sls-setting-value-off">visible</xsl:attribute></xsl:if>
								<span class="label">|||sls:lang:SLS_BO_DASHBOARD_LOGS_EMAILS_TITLE|||</span>
							</li>
						</xsl:if>
						<li class="view-all">
							<span class="label">|||sls:lang:SLS_BO_DASHBOARD_VIEW_ALL|||</span>
						</li>
					</ul>
				</xsl:if>

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
			</div>

			<!-- GOOGLE ANALYTICS -->
			<xsl:if test="count(//View/google_analytics/*) &gt; 0">
				<div class="dashboard-module-google-analytics" id="sls-bo-section-ga">
					<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='dashboard_ga']]/setting) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_ga'] = 'hidden'"><xsl:attribute name="class">dashboard-module-google-analytics disabled</xsl:attribute></xsl:if>
					<div class="sls-bo-dashboard-module dashboard-module-dependent">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_SUMMARY|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id="gaSummary"></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_VISITS|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaVisits'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half border-right">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_VIEWS|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaViews'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_TIMES|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaTimes'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half border-right">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_SOURCES|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaSources'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_BROWSERS|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaBrowsers'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half border-right">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_OS|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaOs'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_CONTINENTS|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaContinents'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half border-right">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_COUNTRIES|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaCountries'></div>
						</div>
					</div>

					<div class="sls-bo-dashboard-module dashboard-module-dependent half">
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_GA_LANGUAGES|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div id='gaLanguages'></div>
						</div>
					</div>
				</div>
			</xsl:if>
			<!-- /GOOGLE ANALYTICS -->


			<!-- METRICS -->
			<xsl:if test="count(//View/metrics/metric/*) &gt; 0">
				<div class="sls-bo-dashboard-module dashboard-module-metrics" id="sls-bo-section-metric">
					<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='dashboard_metric']]/setting) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_metric'] = 'hidden'"><xsl:attribute name="class">sls-bo-dashboard-module dashboard-module-metrics disabled</xsl:attribute></xsl:if>
					<div class="sls-bo-dashboard-module-title">
						<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_METRICS_TITLE|||</h1>
					</div>
					<div class="sls-bo-dashboard-module-content">
						<div class="sls-bo-dashboard-grid">
							<xsl:for-each select="//View/metrics/metric">
								<div class="grid-cell metric">
									<div class="grid-cell-content">
										<div class="metric-title"><xsl:value-of select="title" /></div>
										<div class="metric-legend"><xsl:value-of select="legend" /></div>
										<div class="vertical-separator"></div>
										<div class="metric-value sls-bo-color-text"><xsl:value-of select="nb" /></div>
									</div>
								</div>
							</xsl:for-each>
						</div>
					</div>
				</div>
			</xsl:if>
			<!--  /METRICS -->

			<!-- LOGS -->
			<xsl:if test="count(//View/logs/*) &gt; 0">
				<xsl:if test="count(//View/logs/monitoring/log/*) &gt; 0">

					<div class="sls-bo-dashboard-module dashboard-module-monitoring-logs" id="sls-bo-section-monitoring">
						<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='dashboard_monitoring']]/setting) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_monitoring'] = 'hidden'"><xsl:attribute name="class">sls-bo-dashboard-module dashboard-module-monitoring-logs disabled</xsl:attribute></xsl:if>
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_LOGS_MONITORING_TITLE|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div class="sls-bo-dashboard-grid">
								<xsl:for-each select="//View/logs/monitoring/log">
									<div class="grid-cell log">
										<div class="grid-cell-content">
											<div class="log-date"><xsl:value-of select="date" /></div>
											<div class="log-action"><a href="{url/@absolute}" target="_blank"><xsl:value-of select="url" /></a></div>
											<div class="vertical-separator"></div>
											<div class="log-time" style="color: {color};"><xsl:value-of select="time" /></div>
										</div>
									</div>
								</xsl:for-each>
							</div>
						</div>
					</div>
				</xsl:if>
			</xsl:if>
			<!-- /LOGS -->

			<!-- SLS GRAPHS -->
			<xsl:if test="count(//View/sls_graphs/sls_graph/*) &gt; 0">
				<div class="sls-bo-dashboard-module dashboard-module-custom-graphs" id="sls-bo-section-custom-graphs">
					<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='dashboard_graph']]/setting) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_graph'] = 'hidden'"><xsl:attribute name="class">sls-bo-dashboard-module dashboard-module-custom-graphs disabled</xsl:attribute></xsl:if>
					<xsl:for-each select="//View/sls_graphs/sls_graph">
						<xsl:variable name="position" select="position()" />
						<div class="sls-bo-dashboard-module dashboard-module-dependent" id="sls-bo-section-graph-{sls_graph_id}">
							<div class="sls-bo-dashboard-module-title">
								<h1 class="sls-bo-dashboard-module-title-text"><xsl:value-of select="//View/sls_graphs/sls_graph[$position]/sls_graph_title" /></h1>
							</div>
							<div class="sls-bo-dashboard-module-content">
								<xsl:choose>
									<xsl:when test="sls_graph_type = 'pie'">
										<xsl:call-template name="SlsGraphPie">
											<xsl:with-param name="sls_graph_id">graph_<xsl:value-of select="$position" /></xsl:with-param>
											<xsl:with-param name="sls_graph_path">//View/sls_graphs/sls_graph[<xsl:value-of select="$position" />]</xsl:with-param>
										</xsl:call-template>
									</xsl:when>
									<xsl:when test="sls_graph_type = 'bar'">
										<xsl:call-template name="SlsGraphBar">
											<xsl:with-param name="sls_graph_id">graph_<xsl:value-of select="$position" /></xsl:with-param>
											<xsl:with-param name="sls_graph_path">//View/sls_graphs/sls_graph[<xsl:value-of select="$position" />]</xsl:with-param>
										</xsl:call-template>
									</xsl:when>
									<xsl:when test="sls_graph_type = 'pivot'">
										<div class="sls-bo-dashboard-listing">
											<xsl:call-template name="SlsGraphPivot">
												<xsl:with-param name="sls_graph_path">//View/sls_graphs/sls_graph[<xsl:value-of select="$position" />]</xsl:with-param>
											</xsl:call-template>
										</div>
									</xsl:when>
									<xsl:when test="sls_graph_type = 'list'">
										<xsl:call-template name="SlsGraphList">
											<xsl:with-param name="sls_graph_path">//View/sls_graphs/sls_graph[<xsl:value-of select="$position" />]</xsl:with-param>
										</xsl:call-template>
									</xsl:when>
								</xsl:choose>
							</div>
						</div>
					</xsl:for-each>
				</div>
			</xsl:if>
			<!-- /SLS GRAPHS -->

			<!-- Email listing-->
			<xsl:if test="count(//View/logs/*) &gt; 0">
				<xsl:if test="count(//View/logs/email/log/*) &gt; 0">
					<div class="sls-bo-dashboard-module dashboard-module-email-logs" id="sls-bo-section-email">
						<xsl:if test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='dashboard_email']]/setting) &gt; 0 and //Statics/Site/BoMenu/admin/settings/setting[@key='dashboard_email'] = 'hidden'"><xsl:attribute name="class">sls-bo-dashboard-module dashboard-module-email-logs disabled</xsl:attribute></xsl:if>
						<div class="sls-bo-dashboard-module-title">
							<h1 class="sls-bo-dashboard-module-title-text">|||sls:lang:SLS_BO_DASHBOARD_LOGS_EMAILS_TITLE|||</h1>
						</div>
						<div class="sls-bo-dashboard-module-content">
							<div class="sls-bo-dashboard-listing">
								<div class="sls-bo-listing-head">
									<div class="sls-bo-listing-cell">|||sls:lang:SLS_BO_DASHBOARD_LOGS_EMAILS_DATE|||</div>
									<div class="sls-bo-listing-cell">|||sls:lang:SLS_BO_DASHBOARD_LOGS_EMAILS_SUBJECT|||</div>
									<div class="sls-bo-listing-cell">|||sls:lang:SLS_BO_DASHBOARD_LOGS_EMAILS_RECIPIENT|||</div>
								</div>
								<xsl:for-each select="//View/logs/email/log">
									<div class="sls-bo-listing-row">
										<div class="sls-bo-listing-recordset">
											<div class="sls-bo-listing-cell"><div class="sls-bo-listing-cell-content"><xsl:value-of select="date" /></div></div>
											<div class="sls-bo-listing-cell"><div class="sls-bo-listing-cell-content"><xsl:value-of select="subject" /></div></div>
											<div class="sls-bo-listing-cell"><div class="sls-bo-listing-cell-content"><a href="mailto:{to}" class="sls-bo-color-text" target="_blank"><xsl:value-of select="to" /></a></div></div>
										</div>
									</div>
								</xsl:for-each>
							</div>
						</div>
					</div>
				</xsl:if>
			</xsl:if>
			<!-- Email listing-->
			
		</div>

	</xsl:template>
</xsl:stylesheet>