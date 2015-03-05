<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="DeleteDataBase">
	
		<h1>Delete the database '<xsl:value-of select="//View/database" />'</h1>
		<xsl:if test="//View/db_exists = 'true'">
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<xsl:if test="count(//View/errors/error) = 0 and count(//View/models/model) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					Warning, if you delete this database, you'll lose all these files :<br />
				</div>
			</xsl:if>
			
			<xsl:for-each select="//View/models/model">
				<fieldset style="float:left;margin:5px 5px 5px 0;">
					<legend>'<xsl:value-of select="label" />' Model</legend>
					<u>Model files :</u><br />
					* <xsl:value-of select="concat(file,'.model.php')" /><br />
					* <xsl:value-of select="concat(file,'.sql.php')" /><br />
					<xsl:if test="count(bos/bo) &gt; 0">
						<fieldset>
							<legend>Back-Office files</legend>
							<xsl:for-each select="bos/bo">
								* Action '<i><xsl:value-of select="label" /></i>' (<xsl:value-of select="file" />)<br />
							</xsl:for-each>
						</fieldset>
					</xsl:if>
				</fieldset>
			</xsl:for-each>
			<form method="post" action="" style="display:block;clear:both;">
				<input type="hidden" name="reload" value="true" />
				If you want to confirm, please type your login and password and press 'Confirm': 
				<input type="text" name="login" />&#160;<input type="password" name="password" />
				<input type="submit" value="Confirm" />
			</form>
			<xsl:if test="count(//View/incorrect_account) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					Error, wrong account.
				</div>
			</xsl:if>
		</xsl:if>
		<xsl:if test="count(//View/db_exists) = 0">
			<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
				Sorry, this database can't be found.
			</div>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>