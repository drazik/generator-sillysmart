<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="DirRights">
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<div id="breadcrumbs"><span class="focus">Directories Rigths</span> <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Authentication <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Global Settings <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						International <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						DataBase <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Mails</div>
					<h1>Installation</h1>
					<h2>Check Directories Rights</h2>
					<fieldset class="sls_init_form">
						<legend>Directories...</legend>
						<ul class="ul_table" id="directoriesList">
							<li style="margin-bottom:10px;"><span class="sls_init_icon">Writable</span><span class="sls_init_icon">Readable</span><span class="sls_init_label">Directory</span></li>
							<xsl:for-each select="//View/directories/directory">
								<li>
									<xsl:attribute name="class">
										<xsl:if test="(position() mod 2) = 0">
											mod1
										</xsl:if>
										<xsl:if test="(position() mod 2) != 0">
											mod2
										</xsl:if>
									</xsl:attribute>
									<span class="sls_init_icon">
										<img>
											<xsl:if test="writable = 1">
												<xsl:attribute name="src"> 
													<xsl:value-of select="concat($sls_url_img_core_icons, 'tick.png')" />
												</xsl:attribute>
												<xsl:attribute name="alt">This directory is writable</xsl:attribute>
												<xsl:attribute name="title">This directory is writable</xsl:attribute>
											</xsl:if>
											<xsl:if test="writable = 0">
												<xsl:attribute name="src">
													<xsl:value-of select="concat($sls_url_img_core_icons, 'cross.png')" />
												</xsl:attribute>
												<xsl:attribute name="alt">This directory is not writable</xsl:attribute>
												<xsl:attribute name="title">This directory is not writable</xsl:attribute>
											</xsl:if>
										</img>
									</span>
									<span class="sls_init_icon">
										<img>
											<xsl:if test="readable = 1">
												<xsl:attribute name="src">
													<xsl:value-of select="concat($sls_url_img_core_icons, 'tick.png')" />
												</xsl:attribute>
												<xsl:attribute name="alt">This directory is readable</xsl:attribute>
												<xsl:attribute name="title">This directory is readable</xsl:attribute>
											</xsl:if>
											<xsl:if test="readable = 0">
												<xsl:attribute name="src">
													<xsl:value-of select="concat($sls_url_img_core_icons, 'cross.png')" />
												</xsl:attribute>
												<xsl:attribute name="alt">This directory is not readable</xsl:attribute>
												<xsl:attribute name="title">This directory is not readable</xsl:attribute>
											</xsl:if>
										</img>
									</span>
									<span class="sls_init_label">
										<xsl:value-of select="path" />
									</span>
								</li>
							</xsl:for-each>
						</ul>
						<div id="buttons_panel">
							<xsl:if test="count(//View/directories/directory[readable = 0]) &gt; 0 or count(//View/directories/directory[writable = 0]) &gt; 0">
								<a href="" title="Refresh" class="refresh">Refresh</a>
							</xsl:if>
							<xsl:if test="count(//View/directories/directory[readable = 0]) = 0 and count(//View/directories/directory[writable = 0]) = 0">
								<a href="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}" title="Next" class="next">Next</a>
							</xsl:if>
						</div>
					</fieldset>
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>