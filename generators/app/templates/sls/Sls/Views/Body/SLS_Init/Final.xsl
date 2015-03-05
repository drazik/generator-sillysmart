<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Final">
	
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<h1>Installation</h1>
					<h2>Congratulations</h2>
					<p>You have successfully installed SillySmart Framework !<br />
					<a href="{$sls_url_domain}" title="Home Page">Access to your HomePage now !</a><br />
					<a href="{//Statics/Sls/Configs/action/links/link[name='BACKOFFICE']/href}" title="Back Office">Access to the SillySmart BackOffice</a>
					</p>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>