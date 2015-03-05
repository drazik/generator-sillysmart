<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Compressor">	
	
		<h1>JS &amp; Css Compressor / Uncompressor</h1>
				
		<form method="post" action="">
			<xsl:choose>
				<xsl:when test="//View/current_step = '0'">
					<input type="hidden" name="reload_type" value="true" />
					<label for="file_type">File types</label>
					<select name="type" id="file_type">
						<option value="both">JS &amp; CSS</option>
						<option value="js">JS</option>
						<option value="css">CSS</option>
					</select>					
					<input type="submit" value="Ok" />
				</xsl:when>
				<xsl:when test="//View/current_step = '1'">
					<input type="hidden" name="reload_files" value="true" />
					Please choose files you want to compress:<br />
					<xsl:if test="count(//View/files/js/file) = 0 and count(//View/files/css/file) = 0">
						<div style="margin:10px;padding:10px;border:1px dotted #FF0000;background-color:#FFEBE8;color:#000;">
							Hummm, it's very strange :/ it's seems you haven't any JS and CSS files.							
						</div>
						<div style="margin:10px;padding:10px;border:1px dotted #E2C822;background-color:#FFF9D7;color:#000;">
							<u>Please check the following paths :</u>
							<ul>
								<li><xsl:value-of select="//Statics/Sls/Configs/paths/css" /></li>
								<li><xsl:value-of select="//Statics/Sls/Configs/paths/jsDyn" /></li>
								<li><xsl:value-of select="//Statics/Sls/Configs/paths/jsStatics" /></li>
							</ul>
						</div>
					</xsl:if>
					<xsl:if test="count(//View/files/js/file) &gt; 0">
						<fieldset style="float:left;">
							<legend>JS files</legend>
							<xsl:for-each select="//View/files/js/file">						
								<input type="checkbox" name="file_compress_{.}" value="{.}" id="{.}" checked="checked" /><label for="{.}"><xsl:value-of select="php:functionString('SLS_String::substrAfterLastDelimiter',.,'/')" /></label><br />
							</xsl:for-each>
						</fieldset>
					</xsl:if>
					<xsl:if test="count(//View/files/css/file) &gt; 0">
						<fieldset style="float:left;margin-left:10px;">
							<legend>CSS files</legend>
							<xsl:for-each select="//View/files/css/file">						
								<input type="checkbox" name="file_compress_{.}" value="{.}" id="{.}" checked="checked" /><label for="{.}"><xsl:value-of select="php:functionString('SLS_String::substrAfterLastDelimiter',.,'/')" /></label><br />
							</xsl:for-each>
						</fieldset>
					</xsl:if>
					<div style="clear:both;float:none;display:block;width:100%;line-height:1px;height:1px;"></div>
					<select name="compress" id="compress">
						<option value="compress">Compress</option>
						<option value="uncompress">Uncompress</option>
					</select>
					<input type="submit" value="Ok" />
				</xsl:when>
				<xsl:when test="//View/current_step = '2'">
					<xsl:if test="count(//View/errors/error) &gt; 0">
						<div style="margin:10px 10px 10px 0;padding:10px;border:1px dotted #FF0000;background-color:#FFEBE8;color:#000;">
							The following files haven't been <xsl:choose><xsl:when test="//View/compress = 'compress'">compressed</xsl:when><xsl:otherwise>uncompressed</xsl:otherwise></xsl:choose>.
						</div>
						<div style="margin:10px 10px 10px 0;padding:10px;border:1px dotted #E2C822;background-color:#FFF9D7;color:#000;">
							<u>Please check the following files :</u>
							<ul>
								<xsl:for-each select="//View/errors/error">
									<li>
										<xsl:value-of select="." />
									</li>
								</xsl:for-each>
							</ul>
						</div>
					</xsl:if>
					<xsl:if test="count(//View/successes/success) &gt; 0">
						<div style="margin:10px 10px 10px 0;padding:10px;border:1px dotted #008000;background-color:#EFFFF9;color:#000;">
							The following files have been successfully <xsl:choose><xsl:when test="//View/compress = 'compress'">compressed</xsl:when><xsl:otherwise>uncompressed</xsl:otherwise></xsl:choose> :
						</div>
						<table border="1" cellpadding="5" cellspacing="0">
							<tr style="background-color:#E9E9E9;color:#000;">
								<th>File</th>
								<th>Old Size</th>
								<th>New Size</th>
								<th><xsl:choose><xsl:when test="//View/compress = 'compress'">Compress</xsl:when><xsl:otherwise>Uncompress</xsl:otherwise></xsl:choose> Ratio</th>
							</tr>
							<xsl:for-each select="//View/successes/success">
								<tr>
									<td><a href="{concat($sls_url_domain,'/',file)}" target="_blank"><em><xsl:value-of select="file" /></em></a></td>
									<td align="right"><xsl:value-of select="old_size" /></td>
									<td align="right"><xsl:value-of select="new_size" /></td>
									<td align="right">
										<xsl:attribute name="style">color:<xsl:choose><xsl:when test="ratio = 0">#A2A2A2</xsl:when><xsl:when test="ratio &gt; 0">green</xsl:when><xsl:otherwise>red</xsl:otherwise></xsl:choose></xsl:attribute>
										<xsl:value-of select="ratio" />%
									</td>
								</tr>
							</xsl:for-each>
							<tr style="background-color:#E9E9E9;color:#000;">
								<th>Total</th>
								<th align="right"><xsl:value-of select="//View/successes/total/old_size" /></th>
								<th align="right"><xsl:value-of select="//View/successes/total/new_size" /></th>
								<th align="right">
									<xsl:attribute name="style">color:<xsl:choose><xsl:when test="//View/successes/total/ratio = 0">#A2A2A2</xsl:when><xsl:when test="//View/successes/total/ratio &gt; 0">green</xsl:when><xsl:otherwise>red</xsl:otherwise></xsl:choose></xsl:attribute>
									<xsl:value-of select="//View/successes/total/ratio" />%
								</th>
							</tr>
						</table>
					</xsl:if>
				</xsl:when>
			</xsl:choose>			
		</form>
				
	</xsl:template>
</xsl:stylesheet>