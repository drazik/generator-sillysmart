<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="UpdateSLS">
	
		<h1>Update SillySmart</h1>					
		<p><div style="font-weight:bold;color:red;">Don't execute this upgrade in a production environment !</div> Before updating your SillySmart, be sure to have made a back-up of your current project.</p>
		<form action="#" method="post">
			<xsl:if test="//View/stepPassword = 'no'">
				<input type="hidden" name="confirm" value="update" />
				<input type="submit" value="Update SillySmart" />
			</xsl:if>
			<xsl:if test="//View/stepPassword = 'yes'">
				<label for="login">Username :</label> <input type="text" name="login" id="login" /><br />
				<label for="login">Password :</label> <input type="password" name="password" id="password" /><br />
				<input type="hidden" name="confirm" value="update" />
				<input type="submit" value="Update SillySmart" />
			</xsl:if>
		</form>
		<xsl:if test="//View/error_server != ''">
			<div style="font-weight:bold;color:red">
				<xsl:value-of select="//View/error_server" />
			</div>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>