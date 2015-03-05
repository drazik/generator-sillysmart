<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Bomenu">
		<xsl:param name="loadScript" select="'false'" />

		<xsl:if test="$loadScript = 'true'">
			<xsl:call-template name="Boheaders">
				<xsl:with-param name="lightVersion" select="'true'" />
			</xsl:call-template>
		</xsl:if>

		<div id="sls-bo-toolbar">
			<xsl:if test="$loadScript = 'true'"><xsl:attribute name="class">closed</xsl:attribute></xsl:if>
			<div class="toggler">
				<div class="picto"></div>
			</div>
			<div class="sls-bo-toolbar-wrapper">
				<div class="sls-bo-toolbar-wrapper-scroll">
					<div class="sls-bo-toolbar-content">
						<xsl:if test="count(//Statics/Site/BoMenu/*) &gt; 0">
							<div class="sls-bo-toolbar-module sls-bo-toolbar-menu">
								<div class="sls-bo-toolbar-head">
									<div class="symbol">
										<xsl:value-of select="php:functionString('substr', //Statics/Sls/Configs/site/projectName, 0, 1)" />
									</div>
									<div class="project">
										<table class="vt_centered">
											<tr><td>
												<h3 class="sls-bo-toolbar-title"><xsl:value-of select="//Statics/Sls/Configs/site/projectName" /></h3>
											</td></tr>
										</table>
									</div>
									<div class="clear"></div>
								</div>
								<ul class="sls-bo-toolbar-sections">
									<li class="dashboard">
										<a href="{//Statics/Site/BoMenu/various/dashboard}" title="Dashboard" class="section-item sls-bo-color-text-hover"><xsl:if test="//Statics/Site/BoMenu/various/dashboard/@selected='true'"><xsl:attribute name="class">section-item sls-bo-color-text-hover selected</xsl:attribute></xsl:if>|||sls:lang:SLS_BO_MENU_DASHBOARD|||</a>
									</li>
									<xsl:for-each select="//Statics/Site/BoMenu/nav/section">
										<li>
											<xsl:choose>
												<xsl:when test="count(categories/category) &gt; 0">
													<div class="section-item sls-bo-color-text-hover">
														<xsl:value-of select="title" />
														<xsl:if test="categories/category//like">
															<ul class="section-filters">
																<li class="favorite" sls-setting-name="nav_filter" sls-setting-value="like" sls-setting-selected="false" sls-setting-selected-class="current">
																	<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='nav_filter'] = 'like'">
																		<xsl:attribute name="class">favorite current</xsl:attribute>
																		<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
																	</xsl:if>
																</li>
																<li class="all" sls-setting-name="nav_filter" sls-setting-value="default" sls-setting-selected="false" sls-setting-selected-class="current">
																	<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='nav_filter'] = 'default'">
																		<xsl:attribute name="class">all current</xsl:attribute>
																		<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
																	</xsl:if>
																</li>
															</ul>
														</xsl:if>
													</div>
													<div class="categories-wrapper">
														<div class="categories-content">
															<ul class="sls-bo-toolbar-categories">
																<xsl:for-each select="categories/category[@type = 'table' or (@type = 'category' and count(items/item) &gt; 0)]">
																	<xsl:sort select="title" />
																	<xsl:choose>
																		<xsl:when test="@type = 'table'">
																			<li>
																				<xsl:if test="like"><xsl:attribute name="class">likeable</xsl:attribute></xsl:if>
																				<a href="{href}" title="" class="category-item sls-bo-color-hover">
																					<xsl:if test="selected = 'true'"><xsl:attribute name="class">category-item sls-bo-color-text-hover selected</xsl:attribute></xsl:if>
																					<xsl:value-of select="title" />
																				</a>
																				<xsl:if test="like">
																					<div class="favorite" sls-db="{db}" sls-entity="{model}">
																						<xsl:if test="like = 'true'"><xsl:attribute name="class">favorite liked</xsl:attribute></xsl:if>
																					</div>
																				</xsl:if>
																			</li>
																		</xsl:when>
																		<xsl:otherwise>
																			<li>
																				<div class="category-item category-title sls-bo-color-text-hover"><xsl:value-of select="title" /></div>
																				<div class="items-wrapper">
																					<div class="items-content">
																						<ul class="sls-bo-toolbar-items">
																							<xsl:for-each select="items/item">
																								<xsl:sort select="title" />
																								<li>
																									<xsl:if test="like"><xsl:attribute name="class">likeable</xsl:attribute></xsl:if>
																									<a href="{href}" title="" class="sls-bo-color-hover"><xsl:if test="selected = 'true'"><xsl:attribute name="class">sls-bo-color-hover selected</xsl:attribute></xsl:if><xsl:value-of select="title" /></a>
																									<xsl:if test="like">
																										<div class="favorite" sls-db="{db}" sls-entity="{model}">
																											<xsl:if test="like = 'true'"><xsl:attribute name="class">favorite liked</xsl:attribute></xsl:if>
																										</div>
																									</xsl:if>
																								</li>
																							</xsl:for-each>
																						</ul>
																					</div>
																				</div>
																			</li>
																		</xsl:otherwise>
																	</xsl:choose>
																</xsl:for-each>
															</ul>
														</div>
													</div>
												</xsl:when>
												<xsl:otherwise>
													<a href="{href}" title="{title}" class="section-item sls-bo-color-text-hover">
														<xsl:if test="selected = 'true'"><xsl:attribute name="class">section-item sls-bo-color-text-hover selected</xsl:attribute></xsl:if>
														<xsl:value-of select="title" />
													</a>
												</xsl:otherwise>
											</xsl:choose>
										</li>
									</xsl:for-each>
								</ul>
							</div>
						</xsl:if>
						<xsl:if test="php:functionString('SLS_BoRights::getAdminType') = 'developer' and $loadScript = 'true'">
							<div class="sls-bo-toolbar-module sls-bo-toolbar-developer">
								<div class="sls-bo-toolbar-head">
									<div class="symbol sls-bo-color">
										<div class="picto"></div>
									</div>
									<div class="project">
										<table class="vt_centered">
											<tr><td>
												<h3 class="sls-bo-toolbar-title">Sls Developer Toolbar</h3>
											</td></tr>
										</table>
									</div>
									<div class="clear"></div>
								</div>
								<ul class="sls-bo-toolbar-developer-sections">
									<li>
										<div id="XML-to-clipboard" class="sls-bo-color-text sls-bo-color-border sls-bo-color-hover">
											<span class="text">&lt;XML /&gt;</span>
										</div>
									</li>
									<xsl:if test="count(//Statics/Sls/Http/params/param[@method != 'SLS' and name != 'mode' and name != 'smode']) &gt; 0">
										<li>
											<div class="section-item sls-bo-color-hover">HTTP</div>
											<ul class="sls-bo-toolbar-developer-sub-sections">
												<xsl:for-each select="//Statics/Sls/Http/params/param[@method != 'SLS' and name != 'mode' and name != 'smode']" >
													<li class="sls-bo-color-hover">
														<xsl:value-of select="name" />
														<ul class="sls-bo-toolbar-developer-sub-sections end-value">
															<li class="sls-bo-color">
																<xsl:choose>
																	<xsl:when test="@type = 'string'">
																		<xsl:value-of select="value" />
																	</xsl:when>
																	<xsl:when test="@type = 'object'">
																		<pre class="brush: js">
																			<xsl:value-of select="value" />
																		</pre>
																	</xsl:when>
																	<xsl:otherwise>
																		<pre class="brush: php">
																			<xsl:value-of select="value" />
																		</pre>
																	</xsl:otherwise>
																</xsl:choose>
															</li>
														</ul>
													</li>
												</xsl:for-each>
											</ul>
										</li>
									</xsl:if>
									<li>
										<div class="section-item sls-bo-color-hover">Session</div>
										<ul class="sls-bo-toolbar-developer-sub-sections">
											<xsl:for-each select="//Statics/Sls/Session/params/param" >
												<li class="sls-bo-color-hover">
													<xsl:value-of select="name" />
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:choose>
																<xsl:when test="@type = 'string'">
																	<xsl:value-of select="value" />
																</xsl:when>
																<xsl:when test="@type = 'object'">
																	<pre class="brush: js">
																		<xsl:value-of select="value" />
																	</pre>
																</xsl:when>
																<xsl:otherwise>
																	<pre class="brush: php">
																		<xsl:value-of select="value" />
																	</pre>
																</xsl:otherwise>
															</xsl:choose>
														</li>
													</ul>
												</li>
											</xsl:for-each>
										</ul>
									</li>
									<li>
										<div class="section-item sls-bo-color-hover">Cookies</div>
										<ul class="sls-bo-toolbar-developer-sub-sections">
											<xsl:for-each select="//Statics/Sls/Cookie/item">
												<li class="sls-bo-color-hover">
													<xsl:value-of select="@name" />
													<ul class="sls-bo-toolbar-developer-sub-sections">
														<xsl:for-each select="params/param" >
															<li class="sls-bo-color-hover">
																<xsl:value-of select="name" />
																<ul class="sls-bo-toolbar-developer-sub-sections end-value">
																	<li class="sls-bo-color"><xsl:value-of select="value" /></li>
																</ul>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</xsl:for-each>
										</ul>
									</li>
									<li>
										<div class="section-item sls-bo-color-hover">Monitoring</div>
										<ul class="sls-bo-toolbar-developer-sub-sections">
											<xsl:if test="count(//View/dev_logs/flush_cache/children/child) &gt; 0">
												<li class="sls-bo-color-hover">
													Flush Cache <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/flush_cache/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<xsl:for-each select="//View/dev_logs/flush_cache/children/child">
															<li class="sls-bo-color-hover">
																<xsl:value-of select="msg" /> - <xsl:value-of select="time" />s
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/statics/children/child) &gt; 0">
												<li class="sls-bo-color-hover">
													Statics <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/statics/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections">
														<xsl:for-each select="//View/dev_logs/statics/children/child">
															<li class="sls-bo-color-hover">
																<xsl:value-of select="msg" />
																<ul class="sls-bo-toolbar-developer-sub-sections end-value">
																	<li class="sls-bo-color">
																		<xsl:value-of select="time" />s
																	</li>
																</ul>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/components/children/child) &gt; 0">
												<li class="sls-bo-color-hover">
													 Components <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/components/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections">
														<xsl:for-each select="//View/dev_logs/components/children/child">
															<li class="sls-bo-color-hover">
																<xsl:value-of select="msg" />
																<ul class="sls-bo-toolbar-developer-sub-sections end-value">
																	<li class="sls-bo-color">
																		<xsl:value-of select="time" />s
																	</li>
																</ul>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/routing/*) &gt; 0">
												<li class="sls-bo-color-hover">
													Routing <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/routing/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<pre class="brush: php">
																<xsl:value-of select="//View/dev_logs/routing/msg" />
															</pre>
														</li>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/init/*) &gt; 0">
												<li class="sls-bo-color-hover">
													Init <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/init/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:value-of select="//View/dev_logs/init/time" />s
														</li>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/action/*) &gt; 0">
												<li class="sls-bo-color-hover">
													Action <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/action/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:value-of select="//View/dev_logs/action/time" />s
														</li>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/sql/children/child) &gt; 0">
												<li class="sls-bo-color-hover">
													MySQL <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/sql/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections">
														<xsl:for-each select="//View/dev_logs/sql/children/child">
															<li class="sls-bo-color-hover">
																<xsl:choose>
																	<xsl:when test="msg/@type = 'sql'">
																		<xsl:value-of select="type" />
																		<ul class="sls-bo-toolbar-developer-sub-sections end-value">
																			<li class="sls-bo-color-hover">
																				<pre class="brush: sql">
																					<xsl:value-of select="msg" />
																				</pre>
																			</li>
																		</ul>
																	</xsl:when>
																	<xsl:otherwise>
																		<xsl:value-of select="msg" />
																		<ul class="sls-bo-toolbar-developer-sub-sections end-value">
																			<li class="sls-bo-color">
																				<xsl:value-of select="time" />s
																			</li>
																		</ul>
																	</xsl:otherwise>
																</xsl:choose>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/parsing_html/*) &gt; 0">
												<li class="sls-bo-color-hover">
													HTML Parsing <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/parsing_html/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:value-of select="//View/dev_logs/parsing_html/time" />s
														</li>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/parsing_xsl/*) &gt; 0">
												<li class="sls-bo-color-hover">
													XSL Parsing <span class="monitoring-percent"><xsl:value-of select="//View/dev_logs/parsing_xsl/percent" />%</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:value-of select="//View/dev_logs/parsing_xsl/time" />s
														</li>
													</ul>
												</li>
											</xsl:if>
											<xsl:if test="count(//View/dev_logs/render/*) &gt; 0">
												<li class="sls-bo-color-hover">
													Final Render <span class="monitoring-percent">-</span>
													<ul class="sls-bo-toolbar-developer-sub-sections end-value">
														<li class="sls-bo-color">
															<xsl:value-of select="//View/dev_logs/render/time" />s
														</li>
													</ul>
												</li>
											</xsl:if>
										</ul>
									</li>
								</ul>
							</div>
						</xsl:if>
					</div>
				</div>
			</div>
			<xsl:if test="$loadScript = 'true'">
				<ul class="sls-bo-toolbar-module-switchers">
					<xsl:if test="count(//Statics/Site/BoMenu/*) &gt; 0">
						<li class="sls-bo-toolbar-module-switcher sls-bo-toolbar-module-switcher-menu sls-bo-color"><div></div></li>
					</xsl:if>
					<xsl:if test="php:functionString('SLS_BoRights::getAdminType') = 'developer'">
						<li class="sls-bo-toolbar-module-switcher sls-bo-toolbar-module-switcher-developer sls-bo-color"><div></div></li>
					</xsl:if>
				</ul>
			</xsl:if>
		</div>

	</xsl:template>
</xsl:stylesheet>