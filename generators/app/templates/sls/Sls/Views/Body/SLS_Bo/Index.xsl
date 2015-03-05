<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Index">
	
		<h1>Restricted Area</h1>
		<h2>Please fill your authentication informations</h2>					
		<fieldset>
			<legend>Authentication</legend>
			<xsl:if test="count(//View/errors) != 0">
				<div style="color:red;margin-bottom:20px;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" enctype="multipart/form-data" method="post" id="authentication">
				<input type="hidden" name="reload" value="true" />
				<table border="0">
					<tr>
						<td><label for="login">Username :</label></td>
						<td><input type="text" name="login" id="login" /></td>
					</tr>
					<tr>
						<td><label for="password">Password :</label></td>
						<td><input type="password" name="password" id="password" /></td>
					</tr>
				</table>
				
				<input type="submit" value="Authenticate" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>