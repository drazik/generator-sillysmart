<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LogsMonitoring">
	
		<div class="stat_monitoring">
			<h1>Logs > Monitoring > View the '<xsl:value-of select="//View/batchs/date" />' log</h1><br />
			<xsl:if test="count(//View/batchs/batch) &gt; 0">
				<xsl:for-each select="//View/batchs/batch">
					<h2 class="title">
						<a href="" onclick="displayDetail({position()});return false;"><xsl:value-of select="infos/name" /> in <xsl:value-of select="infos/times/total" />s</a>
					</h2>
					<div class="detail" id="{concat('detail_',position())}" style="display:none;">
						<div class="lines">
							<xsl:for-each select="lines/line">											
								<div class="line">
									<div class="info">
										<span class="hour"><xsl:value-of select="time" /></span>
										<span class="message"><xsl:value-of select="msg" /></span>
										<span class="type">[<xsl:value-of select="type" />]</span>
									</div>
									<span class="time"><xsl:value-of select="duration" /> s</span>
									<xsl:if test="more != ''">
										<div class="more">
											<div>
												<xsl:value-of select="more" disable-output-escaping="yes" />
											</div>
										</div>
									</xsl:if>
								</div>
							</xsl:for-each>
						</div>
						<div class="graph">
							<xsl:variable name="nbRatios" select="count(ratios/ratio)" />
							<xsl:variable name="width" select="'300'" />
							<xsl:variable name="height" select="'300'" />
							<xsl:variable name="gchart">http://chart.apis.google.com/chart?chf=bg,lg,0,EFEFEF,0,BBBBBB,1&amp;chs=<xsl:value-of select="$width" />x<xsl:value-of select="$height" />&amp;chma=100,100&amp;cht=pc&amp;chdlp=b&amp;chp=0&amp;chtt=Pie+Chart&amp;chd=t:<xsl:for-each select="ratios/ratio"><xsl:value-of select="degree" /><xsl:if test="position() &lt; $nbRatios">,</xsl:if></xsl:for-each>&amp;chdl=<xsl:for-each select="ratios/ratio"><xsl:value-of select="concat(label,' (',duration,'s)')" /><xsl:if test="position() &lt; $nbRatios">|</xsl:if></xsl:for-each>&amp;chl=<xsl:for-each select="ratios/ratio"><xsl:value-of select="label" /><xsl:if test="position() &lt; $nbRatios">|</xsl:if></xsl:for-each></xsl:variable>
							<a href="{php:functionString('str_replace',concat('chs=',$width,'x',$height),'chs=540x540',$gchart)}" title="View larger" target="_blank"><img src="{$gchart}" alt="Chart" title="Chart" /></a>
						</div>
					</div>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="count(//View/batchs/batch) = 0">
				Sorry, it doesn't have any logs for this date.
			</xsl:if>
		</div>
				
	</xsl:template>
</xsl:stylesheet>