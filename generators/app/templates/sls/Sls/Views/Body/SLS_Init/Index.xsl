<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Index">
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<h1>Welcome on SillySmart Installation</h1>
					<h2>First Use</h2>
					<p><img src="{concat($sls_url_img_core, 'Logos/sls.jpg')}" alt="SillySmart" title="SillySmart" style="display:block;float:left;margin-right:20px;margin-bottom:20px;"/>
					You have download Sillysmart and you need now to install your web Project.<br />
					This Wizard will guide you through the installation process step-by-step.<br />
					<a href="{//Statics/Sls/Configs/action/links/link[name='DIRCHECK']/href}" title="" >To begin installation, click here</a> </p>
					
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>