<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoProjectSettings">

		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_PROJECT_SETTINGS_TITLE|||</span></h1>
		</div>

		<div class="main-core-content sls-bo-page-user-edition">
			<xsl:if test="count(//View/settings/setting) &gt; 0"><xsl:attribute name="class">sls-bo-form-page main-core-content sls-bo-page-user-edition</xsl:attribute></xsl:if>
			<xsl:choose>
				<xsl:when test="count(//View/settings/setting) &gt; 0">
					<form action="" method="post" sls-validation="true" sls-lang="{//Statics/Sls/Session/params/param[name='current_lang']/value}">
						<input type="hidden" name="reload-edit" value="true" />
						<div class="sls-bo-form-page-section">
							<div class="sls-bo-form-page-section-wrapper">
								<div class="sls-bo-form-page-section-content">
									<xsl:for-each select="//View/settings/setting">
										<div class="sls-form-page-field">
											<div class="sls-form-page-field-label">
												<label for="{key}"><xsl:value-of select="key" /></label>
											</div>
											<div class="sls-form-page-field-input">
												<input type="text" name="settings[{xpath}]" id="{key}" value="{value}" sls-required="true" sls-native-type="string"/>
											</div>
										</div>
									</xsl:for-each>
								</div>
							</div>
						</div>
						<div class="sls-bo-form-page-bottom">
							<div class="submit-block">
								<div class="sls-bo-form-page-submit sls-bo-color">|||sls:lang:SLS_BO_PROJECT_SETTINGS_SUBMIT|||</div>
							</div>
						</div>
					</form>
				</xsl:when>
				<xsl:otherwise>
					<div class="listing-no-result">
						|||sls:lang:SLS_BO_PROJECT_SETTINGS_NOTHING|||
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		
	</xsl:template>
</xsl:stylesheet>