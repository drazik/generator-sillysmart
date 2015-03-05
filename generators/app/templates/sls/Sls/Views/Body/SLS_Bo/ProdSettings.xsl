<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ProdSettings">

		<h1>Edit Your Settings</h1>
		<h2>Production Settings</h2>
		<fieldset>
			<legend>Production Settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" name="" enctype="multipart/form-data" method="post">
				<table>
					<tr>
						<td>
							<label for="prod-0">Mode</label>
						</td>
						<td>
							<input type="radio" name="prod" value="1" id="prod-1">
								<xsl:if test="//View/current_values/prod = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="prod-1">Production</label>
						</td>
						<td>
							<input type="radio" name="prod" value="0" id="prod-0">
								<xsl:if test="//View/current_values/prod = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="prod-0">Development</label>
						</td>
					</tr>
					<tr>
						<td>
							<label for="cache-0">Cache</label>
							<a href="#" onclick="confirmFlush('{//View/url_flush_cache}');return false;"><img src="{concat($sls_url_img_core_buttons,'cache_flush.png')}" title="Flush full cache" alt="Flush full cache" style="border:0;position:relative;top:2px;left:2px;" /></a>
						</td>
						<td>
							<input type="radio" name="cache" value="1" id="cache-1">
								<xsl:if test="//View/current_values/cache = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="cache-1">Enabled</label>
						</td>
						<td>
							<input type="radio" name="cache" value="0" id="cache-0">
								<xsl:if test="//View/current_values/cache = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="cache-0">Disabled</label>
						</td>						
					</tr>
					<tr>
						<td>
							<label for="maintenance-0">Maintenance</label>
						</td>
						<td>
							<input type="radio" name="maintenance" value="1" id="maintenance-1">
								<xsl:if test="//View/current_values/maintenance = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="maintenance-1">Enabled</label>
						</td>
						<td>
							<input type="radio" name="maintenance" value="0" id="maintenance-0">
								<xsl:if test="//View/current_values/maintenance = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="maintenance-0">Disabled</label>
						</td>
					</tr>					
					<tr>
						<td>
							<label for="monitoring-0">Monitoring</label>
						</td>
						<td>
							<input type="radio" name="monitoring" value="1" id="monitoring-1">
								<xsl:if test="//View/current_values/monitoring = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="monitoring-1">Enabled</label>
						</td>
						<td>
							<input type="radio" name="monitoring" value="0" id="monitoring-0">
								<xsl:if test="//View/current_values/monitoring = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="monitoring-0">Disabled</label>
						</td>
					</tr>
				</table>
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Confirm Changes" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>