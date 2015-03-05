<!--
   - Global template for your application
   - Don't change anything between marked delimiter |||dtd:tagName|||
   - Beyond you can add additional headers or/and xhtml structure
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" omit-xml-declaration="yes" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" indent="yes" encoding="|||sls:getCharset|||" />

	<!-- Variable Builder -->
	|||sls:buildUrlVars|||
	<!-- /Variable Builder -->

	<!-- Generic include -->
	|||sls:includeActionFileBody|||
	|||sls:includeActionFileHeader|||
	|||sls:includeStaticsFiles|||
	<!-- /Generic include -->

	<xsl:template match="root">
		<html xml:lang="|||sls:getLanguage|||" lang="|||sls:getLanguage|||">
			<head>				

				<!-- Generic headers loading -->
				|||sls:loadCoreHeaders|||
				<xsl:call-template name="Boheaders" />
				|||sls:loadActionFileHeader|||
				<!-- /Generic headers loading -->

			</head>
			<body>

				<div id="sls-bo">
					<!-- HEADER BAR -->
					<div id="sls-bo-header">
						<div class="central">
							<div class="user">
								<xsl:if test="//Statics/Site/BoMenu/admin/img != ''">
									<div class="visual sls-bo-color sls-bo-color-border">
										<a class="sls-image-container" href="" title="">
											<img class="sls-image" sls-image-fit="cover" sls-image-src="{//Statics/Site/BoMenu/admin/img}" title="{concat(//Statics/Site/BoMenu/admin/firstname, ' ', //Statics/Site/BoMenu/admin/name)}" alt="" />
										</a>
									</div>
								</xsl:if>
								<ul>
									<li class="greeting">
										|||sls:lang:SLS_BO_NAV_WELCOME|||&#160;<xsl:value-of select="concat(//Statics/Site/BoMenu/admin/firstname, ' ', //Statics/Site/BoMenu/admin/name)" />
									</li>
									<li class="separator"></li>
									<li>
										<a href="{//Statics/Site/BoMenu/various/logout}" title="">|||sls:lang:SLS_BO_NAV_LOGOUT|||</a>
									</li>
								</ul>
								<xsl:if test="php:functionString('SLS_BoRights::getAdminType') = 'admin'">
									<ul class="other-actions">
										<li><a href="{//Statics/Site/BoMenu/various/renew_pwd}" title="">|||sls:lang:SLS_BO_NAV_CHANGE_PWD|||</a></li>
									</ul>
								</xsl:if>
							</div>
							<ul class="forwards-actions">
								<li class="see_website">
									<a href="{concat(//Statics/Sls/Configs/site/protocol, '://' ,//Statics/Sls/Configs/site/domainName)}" title="">
										<span class="picto"></span>
										<span class="label">|||sls:lang:SLS_BO_NAV_VIEW_WEBSITE|||</span>
									</a>
								</li>
								<xsl:if test="count(//Statics/Sls/Configs/site/langs/name) &gt; 1">
									<li class="langs">
										<span class="picto"></span>
										<div class="select">
											<select name="site-lang">
												<xsl:for-each select="//Statics/Sls/Configs/site/langs/name[. != //Statics/Sls/Session/params/param[name='current_lang']/value]">
													<option value="{concat(//Statics/Site/BoMenu/various/switch_lang, .)}"><xsl:value-of select="." /></option>
												</xsl:for-each>
											</select>
											<div class="current-lang"><xsl:value-of select="//Statics/Sls/Session/params/param[name='current_lang']/value" /></div>
										</div>
									</li>
								</xsl:if>
							</ul>
						</div>
					</div>
					<!-- /HEADER BAR -->

					<!-- CORE : CENTER OF THE PAGE -->
					<div id="sls-bo-core" class="central">
						<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='list_view'] = 'expand'"><xsl:attribute name="class">central expanded</xsl:attribute></xsl:if>
						
						<!-- SLS TOOLBAR : LEFT SIDEBAR -->
						<xsl:call-template name="Bomenu" />
						<!-- /SLS TOOLBAR : LEFT SIDEBAR -->

						<!-- VIEW : ACTION -->
						<div id="sls-bo-view" class="checkbox-relayer">

							<!-- Generic bodies loading -->
							|||sls:loadActionFileBody|||
							|||sls:loadCoreBody|||
							<!-- /Generic bodies loading -->
							
						</div>
						<!-- /VIEW : ACTION -->

						<!-- ACTIONS BAR : RIGHT SIDEBAR -->
						<xsl:call-template name="Boactionsbar" />
						<!-- /ACTIONS BAR : RIGHT SIDEBAR -->

						<div class="clear"></div>
					</div>
					<!-- /CORE : CENTER OF THE PAGE -->
				</div>

			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>




