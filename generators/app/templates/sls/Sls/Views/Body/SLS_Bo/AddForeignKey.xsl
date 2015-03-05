<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddForeignKey">	
	
		<h1>Add a foreign key on a column of `<xsl:value-of select="//View/model/table" />` table</h1>
		<xsl:if test="count(//View/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error) = 0">						
			Please select your foreign key, the table linked to her and the column in the pk table that let to describe your pk<br />
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<select name="column">
					<xsl:for-each select="//View/model/columns/column">
						<option value="{.}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
				<select name="table" id="allTables" onchange="javascript:changeSelectBox()">
					<xsl:for-each select="//View/model/tables/table">
						<option value="{concat(db,'_',name)}"><xsl:value-of select="name" /></option>
					</xsl:for-each>
				</select>
				<xsl:for-each select="//View/model/tables/table">
					<select name="{concat(db,'_',name,'_fkLabel')}" id="{concat(db,'_',name,'_fkLabel')}">
						<xsl:attribute name="style"><xsl:if test="position() = 1">display:inline;</xsl:if><xsl:if test="position() &gt; 1">display:none;</xsl:if></xsl:attribute>
						<xsl:for-each select="columns/column">
							<option value="{.}"><xsl:value-of select="." /></option>
						</xsl:for-each>
					</select>
				</xsl:for-each>
				<span><input type="checkbox" name="fkLabel_specified_checkbox" id="fkLabel_specified_checkbox" onclick="javascript:checkSpecifiedCheckbox()" /><label for="fkLabel_specified_checkbox">or make your own pattern</label></span>
				<input type="text" name="fkLabel_specified" id="fkLabel_specified" style="display:none" />							
				<select name="multilanguage" id="multilanguage" style="display:none">
					<option value="false">No-Multilanguage</option>
					<option value="true">Multilanguage</option>
				</select>
				<select name="ondelete" id="ondelete" style="display:inline">					
					<option value="set_null">On delete - set null</option>
					<option value="cascade">On delete - cascade</option>
					<option value="no_action">On delete - no action</option>
				</select>
				<input type="submit" value="Add" />
			</form>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>