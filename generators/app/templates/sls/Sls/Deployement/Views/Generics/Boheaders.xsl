<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Boheaders">
		<xsl:param name="lightVersion" select="'false'" />

		<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'Fonts.css')}" />
		<xsl:variable name="boColor"><xsl:choose><xsl:when test="count(//Statics/Site/BoMenu/admin/settings[setting[@key='color']]/*) &gt; 0"><xsl:value-of select="//Statics/Site/BoMenu/admin/settings/setting[@key='color']" /></xsl:when><xsl:otherwise>pink</xsl:otherwise></xsl:choose></xsl:variable>
		<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'themes/',$boColor,'.css')}" />

		<xsl:choose>
			<xsl:when test="$lightVersion = 'false'">
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'SlsBO.css')}" />
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'SlsBOToolbar.css')}" />
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'DatePicker.css')}" />
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'colorpicker.css')}" />
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_scripts, 'ckeditor/ckeditor.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_scripts, 'ckfinder/ckfinder.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'mootools-core-and-more-1.5.0.js'))}"></script>
				<xsl:comment>[IF lt IE 9]&gt;
					&lt;script type="text/javascript" src="<xsl:value-of select="php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'excanvas.compiled.js'))" />"&gt;&lt;/script&gt;
				&lt;![endif]</xsl:comment>

				<!-- external scripts -->
				<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
				<xsl:if test="count(//columns/column[((edit and edit='true') or not(edit)) and specific_type = 'address']) &gt; 0">
					<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&amp;sensor=true"></script>
				</xsl:if>
				<!-- /external scripts -->

				<script type="text/javascript">
					if (window.jQuery)
						jQuery.noConflict();
					<xsl:if test="count(//Statics/Site/BoMenu/various/*) &gt; 0">
						window.urls = {
						<xsl:for-each select="//Statics/Site/BoMenu/various/*">
							'<xsl:value-of select="name(.)" />': '<xsl:value-of select="." />'<xsl:if test="position() != last()">,</xsl:if>
						</xsl:for-each>
						};
					</xsl:if>
					window.slsBoUser = {
						'img': '<xsl:value-of select="//Statics/Site/BoMenu/admin/img" />',
						'login': '<xsl:value-of select="//Statics/Site/BoMenu/admin/login" />',
						'lastname': '<xsl:value-of select="//Statics/Site/BoMenu/admin/name" />',
						'firstname': '<xsl:value-of select="//Statics/Site/BoMenu/admin/firstname" />',
						'type': '<xsl:value-of select="//Statics/Site/BoMenu/admin/type" />'
					};
					window.slsBoSettings = window.slsBoSettings || {};
					slsBoSettings.apc = {
						'enabled': <xsl:value-of select="//Statics/Site/BoMenu/server/apc_upload" />,
						'uploadKey': "<xsl:value-of select="//Statics/Site/BoMenu/server/apc_upload_key" />",
						'uploadMaxSize': <xsl:value-of select="//Statics/Site/BoMenu/server/upload_max_size" />
					};
					window.addEvent('scriptsReady', function(){
					<xsl:if test="count(//View/notifications/notification) &gt; 0">
						<xsl:for-each select="//View/notifications/notification">
							_notifications.add('<xsl:value-of select="@type" />', '<xsl:value-of select="." />');
						</xsl:for-each>
					</xsl:if>
					<xsl:for-each select="//View/page//errors/error">
						_fieldError.add('<xsl:value-of select="concat(../../name, '_', @lang)" />', "<xsl:value-of select="." />");
					</xsl:for-each>
					});
				</script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'Select.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'colorpicker.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'Sls-BO-Toolbar.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'Sls-BO.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'SlsImage.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'DatePicker.js'))}"></script>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile', concat($sls_url_js_core_dyn, 'AutoComplete.js'))}"></script>
			</xsl:when>
			<xsl:otherwise>
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'SlsBOToolbar.css')}" />
				<link rel="stylesheet" type="text/css" href="{concat($sls_url_css_core, 'highlight.css')}" />
			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>
</xsl:stylesheet>