<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Query">	

		<h1>SQL Query</h1>
		<xsl:if test="count(//View/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="//View/success != ''">
			<div style="font-weight:bold;color:green;">
				<xsl:value-of select="//View/success" />
			</div>
		</xsl:if>				
		<form method="post" action="">
			<input type="hidden" name="reload" value="true" />
			<div class="row">
				<label for="db">Database</label>
				<select name="db" id="db">
					<xsl:for-each select="//View/dbs/db">
						<option value="{.}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
			</div>
			<div class="row">
				<textarea name="query" id="query" rows="10" cols="80"><xsl:value-of select="//View/query" /></textarea>
			</div>
			<div class="row">
				<input type="submit" value="Send" />
			</div>
			<xsl:if test="count(//View/results/result) &gt; 0">
				<table cellpadding="5" cellspacing="0" border="1" width="100%">
					<tr>
						<xsl:for-each select="//View/legends/legend">
							<th style="background-color:#E9E9E9;"><xsl:value-of select="." /></th>
						</xsl:for-each>
					</tr>
					<xsl:for-each select="//View/results/result">
						<xsl:variable name="currentPosition" select="position()" />
						<tr>
							<xsl:for-each select="//View/legends/legend">
								<xsl:variable name="currentColumn" select="." />
								<td>									
									<xsl:value-of select="//View/results/result[$currentPosition]/*[name() = $currentColumn]" disable-output-escaping="yes" />
								</td>
							</xsl:for-each>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:if>			
		</form>
		
				
	</xsl:template>
</xsl:stylesheet>