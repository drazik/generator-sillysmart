<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderBoDashBoard">
		<xsl:if test="count(//View/google_analytics/*) &gt; 0">
			<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'gadash-2.0.js'))}"></script>
			<script type="text/javascript">
				window.addEvent('domready', function(){
					/**
					 * Src:
					 * http://analytics-api-samples.googlecode.com/svn/trunk/src/reporting/javascript/ez-ga-dash/docs/user-documentation.html#configure
					 * https://developers.google.com/analytics/devguides/reporting/core/v3/reference
					 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
					 */
					// Conf
					gadash.init({
						apiKey: '<xsl:value-of select="//View/google_analytics/apiKey" />',
						clientId: '<xsl:value-of select="//View/google_analytics/clientId" />'
					});
					// Core chart
					//var tableIdControl = gadash.getTextInputControl('tableId', 'query.ids');

					// Colors
					var colors = [<xsl:for-each select="//Statics/Site/BoMenu/admin/colors/color">'<xsl:value-of select="@hexa" />'<xsl:if test="position() != last()">, </xsl:if></xsl:for-each>];

					// Graphs
					var gaSummary = gadash.getCoreChart().setConfig({
						elementId: 'gaSummary',
						query: {
							lastNdays: 30,
							metrics: 'ga:visitors,ga:visits,ga:percentNewVisits,ga:pageviews,ga:pageviewsPerVisit,ga:bounces,ga:avgTimeOnSite,ga:avgTimeOnPage',
						},
						chartOptions: {
							width: '100%'
						}
					});

					var gaVisits = gadash.getCoreChart().setConfig({
						elementId: 'gaVisits',
						type: 'ColumnChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits,ga:newVisits',
							dimensions: 'ga:date',
							sort: 'ga:date'
						},
						chartOptions: {
							height: 400,
							width: '100%',
							isStacked: true,
							legend: {
						        position: 'bottom',
						        alignment: 'center'
							},
							vAxis:{
						        title: 'Nb visits'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '10%',
							    left: '5%'
							}
						}
					});

					var gaViews = gadash.getCoreChart().setConfig({
						elementId: 'gaViews',
						type: 'LineChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:pageviews,ga:bounces',
							dimensions: 'ga:date',
							sort: 'ga:date'
						},
						chartOptions: {
							height: 400,
							width: '100%',
							legend: {
						        position: 'bottom',
						        alignment: 'center'
							},
							vAxis:{
						        title: 'Nb views / bounces'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '10%',
							    left: '5%'
							}
						}
					});

					var gaTimes = gadash.getCoreChart().setConfig({
						elementId: 'gaTimes',
						type: 'LineChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:timeOnSite,ga:avgTimeOnSite',
							dimensions: 'ga:date',
							sort: 'ga:date'
						},
						chartOptions: {
							height: 400,
							width: '100%',
							legend: {
						        position: 'bottom',
						        alignment: 'center'
							},
							vAxis:{
						        title: 'Total time / Average time on site'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '10%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaSources = gadash.getCoreChart().setConfig({
						elementId: 'gaSources',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:source',
							sort: '-ga:visits',
							maxResults: 10
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'Source traffic'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaBrowsers = gadash.getCoreChart().setConfig({
						elementId: 'gaBrowsers',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:browser',
							sort: '-ga:visits',
							maxResults: 10
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'Browser share'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaOs = gadash.getCoreChart().setConfig({
						elementId: 'gaOs',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:operatingSystem',
							sort: '-ga:visits',
							maxResults: 10
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'OS share'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaContinents = gadash.getCoreChart().setConfig({
						elementId: 'gaContinents',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:continent',
							sort: '-ga:visits',
							maxResults: 10
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'Continents'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaCountries = gadash.getCoreChart().setConfig({
						elementId: 'gaCountries',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:country',
							sort: '-ga:visits',
							maxResults: 15
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'Countries'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					colors = shuffle(colors);
					var gaLanguages = gadash.getCoreChart().setConfig({
						elementId: 'gaLanguages',
						type: 'PieChart',
						query: {
							lastNdays: 30,
							metrics: 'ga:visits',
							dimensions: 'ga:language',
							sort: '-ga:visits',
							maxResults: 15
						},
						chartOptions: {
							height: 400,
							width: '30%',
							legend: {
						        position: 'top',
						        alignment: 'center',
						        maxLines: 4
							},
							vAxis:{
						        title: 'Languages'
							},
							colors: shuffle(colors),
							chartArea: {
							    width:'95%',
							    top: '28%',
							    left: '5%'
							}
						}
					});

					// All graphs
					var dash = gadash.getGaComponent([
						new gadash.GaControl({
							'id': '',
							'configObjKey': 'query.ids',
							'getValue': function(){ return '<xsl:value-of select="//View/google_analytics/accountId" />';}
						}), //tableIdControl,
					    gaSummary,
					    gaVisits,
					    gaViews,
					    gaTimes,
					    gaSources,
					    gaBrowsers,
					    gaOs,
					    gaContinents,
					    gaCountries,
					    gaLanguages
					]);

					// Render
					function renderGraph() {
				        dash.execute();
						window.googleDash = dash;
						  // $('sls-dashboard-google-analytics').style.display = 'block';
					 }
					window._loading = _notifications.add('loading', false);

					window.addEvents({
						'onGAAuthorized': function(){
							renderGraph();
							if (_loading)
								_notifications.destroy(_loading);
						}
					});
				});
				 
				 // Array shuffle
				function shuffle(o){
					o = o.slice();
					for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
					return o;
				};
			</script>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>