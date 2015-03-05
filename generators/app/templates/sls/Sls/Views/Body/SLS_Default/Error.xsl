<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Error">
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
			<xsl:variable name="sls_Login">
				<xsl:for-each select="//Statics/Sls/Session/params/param">
					<xsl:if test="php:functionString('SLS_String::startsWith',name,'SLS_SESSION_USER')">
						<xsl:value-of select="value" />
					</xsl:if>
				</xsl:for-each>
			</xsl:variable>
			<xsl:if test="//Statics/Sls/Http/params/param[name='genericsmode']/value != 'Index'">
				<div class="logout">you are logged in as <span><xsl:value-of select="$sls_Login" /></span> - <a id="logout_link" href="{concat('http://',//Statics/Sls/Configs/site/domainName,'/',//Statics/Sls/Http/params/param[name='mode']/value,'/Logout.sls')}" title="Logout">Logout</a></div>
			</xsl:if>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<h1>Sorry, the wanted action doesn't exist</h1>
					<xsl:if test="count(//actions/action) &gt; 0">
						<h2>You maybe search :</h2>
						<ul>
							<!-- Don't change anything below -->
							<xsl:for-each select="//actions/action">
								<li><a href="{href}" title="{label}"><xsl:value-of select="label" /></a></li>
							</xsl:for-each>
							<!-- /Don't change anything below -->
						</ul>
					</xsl:if>
					
					
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>