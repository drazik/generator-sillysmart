<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ReportingBoView">
	
		<div class="reporting_graph">
			<div class="head">
				<h1>Graph #<xsl:value-of select="//View/sls_graph/sls_graph_id" /> - <xsl:value-of select="//View/sls_graph/sls_graph_title" /></h1>
				<div style="margin-top:5px;">
					<a href="{//View/url_report}" title="Back to list"><img src="{concat($sls_url_img_core_icons,'txt16.png')}" title="Back to list" alt="Back to list" style="border:0" align="absmiddle" />&#160;Back to list</a> | <a href="{//View/url_edit}" title="Modify"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Modify" alt="Modify" style="border:0" align="absmiddle" />&#160;Edit this report</a> | <xsl:choose><xsl:when test="//View/sls_graph/sls_graph_visible = 'yes'"><a href="{//View/url_status}" class="report on" title="Disable"></a></xsl:when><xsl:otherwise><a href="{//View/url_status}" class="report off" title="Enable"></a></xsl:otherwise></xsl:choose> | <a href="#" onclick="confirmDelete('{//View/url_delete}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />&#160;Delete this report</a>
				</div> 
				<div class="clear"></div>
			</div>

			<div class="content_background lite_grey padded">
				<h3>Render</h3>
				<xsl:choose>
					<xsl:when test="//View/sls_graph/sls_graph_type = 'pie'">
						<div id="graph_pie_1" class="graph"></div>
						<xsl:call-template name="SlsGraphPie">
							<xsl:with-param name="sls_graph_id">graph</xsl:with-param>
							<xsl:with-param name="sls_graph_path">//View/sls_graph</xsl:with-param>
							<xsl:with-param name="sls_graph_width">1180</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="//View/sls_graph/sls_graph_type = 'bar'">
						<xsl:call-template name="SlsGraphBar">
							<xsl:with-param name="sls_graph_id">graph</xsl:with-param>
							<xsl:with-param name="sls_graph_path">//View/sls_graph</xsl:with-param>
							<xsl:with-param name="sls_graph_width">1180</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="//View/sls_graph/sls_graph_type = 'pivot'">
						<xsl:call-template name="SlsGraphPivot">
							<xsl:with-param name="sls_graph_path">//View/sls_graph</xsl:with-param>
							<xsl:with-param name="sls_graph_from">sls</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="//View/sls_graph/sls_graph_type = 'list'">
						<xsl:call-template name="SlsGraphList">
							<xsl:with-param name="sls_graph_path">//View/sls_graph</xsl:with-param>
							<xsl:with-param name="sls_graph_from">sls</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
				</xsl:choose>
			
				<h3>Query</h3>
				<div class="req">
					<textarea cols="165" rows="20" style="font-size:12px;" onclick="this.select();"><xsl:value-of select="//View/sls_graph/sls_graph_query" disable-output-escaping="yes" /></textarea>
				</div>				
			</div>
		</div>
				
	</xsl:template>
</xsl:stylesheet>