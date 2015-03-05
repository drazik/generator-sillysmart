<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GenerateModels">
	
		<h1>Generate your Models</h1>
		<xsl:if test="count(//View/dbs/db/tables/table) = 0">
			Your database is empty.
		</xsl:if>
		<xsl:if test="count(//View/dbs/db/tables/table) &gt; 0">
			Please choose the models you want to generate :<br />						
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<xsl:for-each select="//View/dbs/db">
					<xsl:variable name="dbAlias" select="name" />
					<fieldset>
						<legend>										
							<input id="{concat($dbAlias,'_all')}" type="checkbox" onclick="checkAll('{$dbAlias}',{count(tables/table)})">
								<xsl:if test="count(tables/table[existed = 'false']) = 0">
									<xsl:attribute name="disabled">disabled</xsl:attribute>
								</xsl:if>
							</input>
							<label for="{concat($dbAlias,'_all')}"><xsl:value-of select="$dbAlias" /></label>	
						</legend>
						
						<xsl:for-each select="tables/table">
							<div>
								<xsl:if test="existed = 'false'">
									<input id="{concat($dbAlias,'_',position())}" type="checkbox" name="tables[]" value="{concat($dbAlias,'.',name)}" /><label for="{concat($dbAlias,'_',position())}"><xsl:value-of select="name" /></label>
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
		</xsl:if>
		<xsl:if test="count(//View/errors/error) &gt;0">
			<div style="font-weight:bold;color:red;">
				<u>Errors occured during the generation of some models :</u><br />
				<xsl:for-each select="//View/errors/error">
					Table `<xsl:value-of select="table" />` (db `<xsl:value-of select="db" />`)
					<ul style="margin-left:20px;">
						<xsl:for-each select="columns/column">
							<li>The column `<xsl:value-of select="old" />` contains php special chars, you must rename it `<xsl:value-of select="new" />`</li>
						</xsl:for-each>
					</ul>
				</xsl:for-each>
			</div>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>