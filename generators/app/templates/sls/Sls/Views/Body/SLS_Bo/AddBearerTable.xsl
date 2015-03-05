<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddBearerTable">	
	
		<h1>Specify a data bearer table</h1>
		<xsl:if test="count(//View/error) &gt; 0 or count(//View/fks/fk) != 2">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error) = 0 and count(//View/fks/fk) = 2">
			The model <strong>`<u><xsl:value-of select="//View/model/class" /></u>`</strong> is a <i>data bearer</i> between <strong>`<xsl:value-of select="//View/fks/fk[1]/class" />`</strong> and <strong>`<xsl:value-of select="//View/fks/fk[2]/class" />`</strong> ?<br /><br />						 
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<label for="target_table">Please specify <u>on which</u> model will you manage the model `<xsl:value-of select="//View/model/class" />` : </label>
				<select name="target_table"	id="target_table">
					<xsl:for-each select="//View/fks/fk">
						<option value="{class}">
							<xsl:value-of select="class" />
						</option>
					</xsl:for-each>
				</select>										
				<input type="submit" value="Valid" />
			</form>						
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>