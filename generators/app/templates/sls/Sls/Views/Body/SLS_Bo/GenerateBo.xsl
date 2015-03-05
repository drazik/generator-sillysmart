<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GenerateBo">
	
		<h1>Generate your back-offices</h1>
		<xsl:choose>
			<xsl:when test="count(//View/errors/error) &gt; 0">
				<div style="font-weight:bold;color:red">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
					<a href="{//View/url_add_controller}" title="Add your back-office controller">Add your back-office controller</a>
				</div>
			</xsl:when>
			<xsl:otherwise>
				Please choose the models you want to generate back-office :
				<form action="" method="post">
					<input type="hidden" name="reload" value="true" />
					<xsl:for-each select="//View/dbs/db[count(models/model) &gt; 0]">
						<xsl:variable name="dbAlias" select="name" />
						<fieldset>
							<legend>
								<input id="{concat($dbAlias,'_all')}" type="checkbox" onclick="checkAll('{$dbAlias}',{count(models/model)})">
									<xsl:if test="count(models/model[existed = 'false']) = 0">
										<xsl:attribute name="disabled">disabled</xsl:attribute>
									</xsl:if>
								</input>
								<label for="{concat($dbAlias,'_all')}"><xsl:value-of select="$dbAlias" /></label>
							</legend>
							<xsl:for-each select="models/model">
								<div>
									<xsl:if test="existed = 'false'">
										<input id="{concat($dbAlias,'_',position())}" type="checkbox" name="models[]" value="{concat($dbAlias,'.',name)}" /><label for="{concat($dbAlias,'_',position())}"><xsl:value-of select="name" /></label>
									</xsl:if>
									<xsl:if test="existed = 'true'">
										<input id="{concat($dbAlias,'_',position())}" type="checkbox" disabled="disabled" value="{concat($dbAlias,'.',name)}" /><label for="{concat($dbAlias,'_',position())}"><xsl:value-of select="name" /></label>
									</xsl:if>
								</div>
							</xsl:for-each>
						</fieldset>
					</xsl:for-each>
					<input type="submit" value="Generate" />
				</form>
			</xsl:otherwise>
		</xsl:choose>
				
	</xsl:template>
</xsl:stylesheet>