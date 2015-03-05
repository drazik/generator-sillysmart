<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="JSSettings">
	
		<h1>Edit Your Settings</h1>
		<h2>Javascript / Ajax Settings</h2>
		<fieldset>
			<legend>Javascript Settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" name="" enctype="multipart/form-data" method="post">
				<table border="0">
					<tr>
						<td>Do you want to default load Statics JavaScripts ?</td>
						<td>
							<input type="radio" name="statics" value="1" id="statics-1">
								<xsl:if test="//View/current_values/statics = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="statics-1">yes</label>
							<input type="radio" name="statics" value="0" id="statics-0">
								<xsl:if test="//View/current_values/statics = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="statics-0">no</label>
						</td>
					</tr>
					<tr style="display:none;">
						<td>Do you want to default load Additionnal JavaScripts ?</td>
						<td>
							<input type="radio" name="dyns" value="1" id="dyns-1">
								<xsl:if test="//View/current_values/dyns = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="dyns-1">yes</label>
							<input type="radio" name="dyns" value="0" id="dyns-0">
								<xsl:if test="//View/current_values/dyns = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="dyns-0">no</label>
						</td>
					</tr>
					<tr style="display:none;">
						<td>Do you want to enable the Automatic JavaScript Variables Builder</td>
						<td>
							<input type="radio" name="vars" value="1" id="vars-1">
								<xsl:if test="//View/current_values/vars = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="vars-1">yes</label>
							<input type="radio" name="vars" value="0" id="vars-0">
								<xsl:if test="//View/current_values/vars = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="vars-0">no</label>
						</td>
					</tr>
					<tr style="display:none;">
						<td>Do you want to enable JavaScript Multilanguage (need Automatic JavaScript Variables Builder enabled) ?</td>
						<td>
							<input type="radio" name="langs" value="1" id="langs-1">
								<xsl:if test="//View/current_values/langs = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="langs-1">yes</label>
							<input type="radio" name="langs" value="0" id="langs-0">
								<xsl:if test="//View/current_values/langs = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="langs-0">no</label>
						</td>
					</tr>
					<tr>
						<td>Do you want to enable JavaScript IE6 Toolbar (message for users using deprecated IE6) ?</td>
						<td>
							<input type="radio" name="ie6" value="1" id="ie6-1">
								<xsl:if test="//View/current_values/ie6 = 1">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="ie6-1">yes</label>
							<input type="radio" name="ie6" value="0" id="ie6-0">
								<xsl:if test="//View/current_values/ie6 = 0">	
									<xsl:attribute name="checked" select="'checked'" />
								</xsl:if>
							</input>
							<label for="ie6-0">no</label>
						</td>
					</tr>
				</table>							
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Confirm Changes" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>