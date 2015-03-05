<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Updates">
	
		<h1>SillySmart Update Manager</h1>					
		<xsl:if test="//View/error_server != ''">
			<div style="font-weight:bold;color:red">
				<xsl:value-of select="//View/error_server" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error_server) = 0">
			<fieldset style="float:left;">
				<legend>Current SillySmart Release</legend>							
				<span style="margin-left:60px;">Version: <xsl:value-of select="//View/current_release/version" /></span>
			</fieldset>
			<fieldset style="float:left;margin-left:20px;">
				<legend>Your SillySmart Release</legend>
				<span>
					<xsl:attribute name="style"><xsl:if test="//View/up_to_date = 'true'">margin-left:50px;color:green;</xsl:if><xsl:if test="//View/up_to_date = 'false'">margin-left:50px;color:red;</xsl:if></xsl:attribute>
					Version: <xsl:value-of select="//View/current_version" />
				</span>
			</fieldset>
			
			<xsl:if test="//View/up_to_date = 'true'">
				<div style="clear:both;font-weight:bold;color:green;">
					Congratulations, your SillySmart is already up to date !
				</div>
			</xsl:if>
			<xsl:if test="//View/up_to_date = 'false'">
				<div style="clear:both;font-weight:bold;color:red;">
					Your SillySmart is deprecated, perform an <a href="{//View/url_update}" title="Update SillySmart">update</a> (authentication &amp; confirmation need).
				</div>
			</xsl:if>
			
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>