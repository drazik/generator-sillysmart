<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ResetSLS">
	
		<h1>Reset the SillySmart Installation</h1>
		<h2>The reset will destroy all your site Data</h2>
		<div style="font-weight:bold;color:red;margin:20px 0;">You are ready to reset SillySmart Installation. The reset will destroy all your existing datas. Make sure you want to do this.</div>
		<form action="#" method="post" enctype="multipart/form-data">
			<xsl:if test="//View/stepPassword = 'no'">
				<input type="hidden" name="confirm" value="reset" />
				<input type="submit" value="Reset SillySmart Installation" />
			</xsl:if>
			<xsl:if test="//View/stepPassword = 'yes'">
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
				<input type="hidden" name="confirm" value="reset" />
				<input type="submit" value="Reset SillySmart Installation" />
			</xsl:if>
		</form>
		
	</xsl:template>
</xsl:stylesheet>