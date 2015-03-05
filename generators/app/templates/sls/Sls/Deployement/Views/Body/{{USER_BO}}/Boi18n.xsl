<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Boi18n">

		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="sls-bo-listing-title fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_I18N_TITLE|||</span>&#160;|||sls:lang:SLS_BO_I18N_SUBTITLE_<xsl:value-of select="//View/file/type" />|||<xsl:if test="//View/file/type != 'SITE'">&#160;« <xsl:value-of select="//View/file/name" /> »</xsl:if></h1>
		</div>

		<div class="sls-bo-form-page main-core-content sls-bo-i18n">
			<xsl:if test="count(//Statics/Sls/Configs/site/langs/name) &gt; 1">
				<div class="action-row sls-bo-color fixed-in-header">
					<ul class="actions langs">
						<li><span>|||sls:lang:SLS_BO_GENERIC_LANG|||</span></li>
						<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
							<li>
								<xsl:if test="position() &lt; 3"><xsl:attribute name="class">selected</xsl:attribute></xsl:if>
								<a href="" title="" sls-lang="{.}">
									<span class="label"><xsl:value-of select="." /></span>
								</a>
							</li>
						</xsl:for-each>
					</ul>
				</div>
			</xsl:if>

			<form method="post" action="" sls-validation="true">
				<input type="hidden" name="reload-i18n" value="true" />

				<div class="label-lang-variables">
					<div class="column-header">
						<div class="column-title">|||sls:lang:SLS_BO_SIDEBAR_I18N|||</div>
					</div>
					<xsl:for-each select="//View/sentences/sentence">
						<div class="label-lang-variable">
							<xsl:choose>
								<xsl:when test="dom = 'input'"><xsl:attribute name="class">label-lang-variable small</xsl:attribute></xsl:when>
								<xsl:otherwise><xsl:attribute name="class">label-lang-variable big</xsl:attribute></xsl:otherwise>
							</xsl:choose>
							<label for="{title}_{//Statics/Sls/Configs/site/defaultLang}"><xsl:value-of select="title" /></label>
						</div>
					</xsl:for-each>
				</div>

				<div class="columns-lang-container">
					<xsl:for-each select="//Statics/Sls/Configs/site/langs/name">
						<xsl:variable name="lang" select="." />
						<div class="column-lang-container" sls-lang="{$lang}">
							<xsl:if test="position() = 1"><xsl:attribute name="class">column-lang-container reference</xsl:attribute></xsl:if>
							<xsl:if test="position() = 2"><xsl:attribute name="class">column-lang-container focused</xsl:attribute></xsl:if>
							<div class="column-header">
								<xsl:if test="count(//Statics/Sls/Configs/site/langs/name) &gt; 2">
									<div class="arrow previous"></div>
									<div class="arrow next"></div>
								</xsl:if>
								<div class="column-title"><xsl:value-of select="$lang" /></div>
							</div>
							<xsl:for-each select="//View/sentences/sentence">
								<div class="input-lang">
									<div class="input-lang-label"><xsl:value-of select="$lang" /></div>
									<div class="sls-form-page-field">
										<xsl:choose>
											<xsl:when test="dom = 'input'">
												<input class="input" type="text" id="{title}_{$lang}" name="translations[{$lang}][{type}][{title}]" value="{langs/translation[@lang=$lang]}" sls-required="true">
													<xsl:if test="count(errors/error[@lang=$lang]) &gt; 0"><xsl:attribute name="style">border:1px solid red;</xsl:attribute></xsl:if>
												</input>
											</xsl:when>
											<xsl:otherwise>
												<textarea class="input" id="{title}_{$lang}" name="translations[{$lang}][{type}][{title}]" sls-html="false" sls-required="true">
													<xsl:if test="count(errors/error[@lang=$lang]) &gt; 0"><xsl:attribute name="style">border:1px solid red;</xsl:attribute></xsl:if>
													<xsl:value-of select="php:functionString('SLS_String::br2nl',langs/translation[@lang=$lang])" />
												</textarea>
											</xsl:otherwise>
										</xsl:choose>
									</div>
								</div>
							</xsl:for-each>
						</div>
					</xsl:for-each>
				</div>

				<div class="sls-bo-form-page-bottom">
					<div class="submit-block">
						<div class="sls-bo-form-page-submit sls-bo-color">Update</div>
					</div>
				</div>

			</form>
		</div>
		
	</xsl:template>
</xsl:stylesheet>