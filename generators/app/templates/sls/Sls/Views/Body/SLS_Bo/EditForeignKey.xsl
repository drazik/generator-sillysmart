<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditForeignKey">	
	
		<h1>Edit a foreign key on a column of `<xsl:value-of select="//View/model/table" />` table</h1>
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
						<option value="{.}">
							<xsl:if test=". = //View/model/current_values/columnFk"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							<xsl:value-of select="." />
						</option>
					</xsl:for-each>
				</select>
				<select name="table" id="allTables" onchange="javascript:changeSelectBox()">
					<xsl:for-each select="//View/model/tables/table">
						<option value="{concat(db,'_',name)}">
							<xsl:if test="concat(db,'_',name) = //View/model/current_values/tablePk"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							<xsl:value-of select="name" />
						</option>
					</xsl:for-each>
				</select>
				<xsl:for-each select="//View/model/tables/table">
					<select name="{concat(db,'_',name,'_fkLabel')}" id="{concat(db,'_',name,'_fkLabel')}">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="concat(db,'_',name) = //View/model/current_values/tablePk and //View/model/current_values/specific_pattern = 'false'">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<xsl:for-each select="columns/column">
							<option value="{.}">
								<xsl:if test=". = //View/model/current_values/labelPk"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								<xsl:value-of select="." />
							</option>
						</xsl:for-each>
					</select>
				</xsl:for-each>
				<span><input type="checkbox" name="fkLabel_specified_checkbox" id="fkLabel_specified_checkbox" onclick="javascript:checkSpecifiedCheckbox()"><xsl:if test="//View/model/current_values/specific_pattern = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input><label for="fkLabel_specified_checkbox">or make your own pattern</label></span>
				<input type="text" name="fkLabel_specified" id="fkLabel_specified">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/model/current_values/specific_pattern = 'true'">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<xsl:if test="//View/model/current_values/specific_pattern = 'true'"><xsl:attribute name="value"><xsl:value-of select="//View/model/current_values/labelPk" /></xsl:attribute></xsl:if>
				</input>							
				<select name="multilanguage" id="multilanguage" style="display:none">
					<option value="false">No-Multilanguage</option>
					<option value="true">Multilanguage</option>
				</select>
				<select name="ondelete" id="ondelete" style="display:inline">					
					<option value="set_null">
						<xsl:if test="//View/model/current_values/ondelete = 'set_null'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						On delete - set null
					</option>
					<option value="cascade">
						<xsl:if test="//View/model/current_values/ondelete = 'cascade'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						On delete - cascade						
					</option>
					<option value="no_action">
						<xsl:if test="//View/model/current_values/ondelete = 'no_action' or //View/model/current_values/ondelete = '' or count(//View/model/current_values/ondelete) = 0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						On delete - no action
					</option>
				</select>
				<input type="submit" value="Update" />
			</form>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>