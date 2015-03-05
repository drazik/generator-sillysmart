<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="BoFileUpload">

		<div id="sls-bo-fixed-header">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_FILE_UPLOAD_TITLE|||</span></h1>
		</div>

		<div class="main-core-content">
			<xsl:variable name="boColor"><xsl:choose><xsl:when test="count(//Statics/Site/BoMenu/admin/settings/setting[@key='color']) &gt; 0"><xsl:value-of select="//Statics/Site/BoMenu/admin/colors/color[. = //Statics/Site/BoMenu/admin/settings/setting[@key='color']]/@hexa" /></xsl:when><xsl:otherwise><xsl:value-of select="//Statics/Site/BoMenu/admin/colors/color/@hexa" /></xsl:otherwise></xsl:choose></xsl:variable>
			<style data-type="sls-style">
				#toolbar_view .cke_toolbox,
				#panel_view .panel_widget .buttons input {
					background-color: <xsl:value-of select="$boColor" /> !important;
				}
				#folders_view .folder_tree li a:hover,
				#folders_view .folder_tree li a:focus,
				#folders_view .folder_tree li a:active {
					color: <xsl:value-of select="$boColor" /> !important;
				}
			</style>
			<script type="text/javascript" src="{concat($sls_url_domain,'/Public/Scripts/','ckfinder/ckfinder.js')}"></script>
			<script type="text/javascript">
				<!--var finder = new CKFinder();
				finder.basePath = '<xsl:value-of select="concat($sls_url_domain,'/Public/Scripts/','ckeditor/')" />';
				finder.create();-->
				var finder = CKFinder.create({basePath: '<xsl:value-of select="concat($sls_url_domain,'/Public/Scripts/','ckeditor/')" />', width: '100%', height: 598, callback: injectStyleInCKFinder});
				function injectStyleInCKFinder(){
				var ckIFrame = $$('iframe[name=CKFinder]');
				var customStyle = $$('style[data-type="sls-style"]');
				if (ckIFrame.length){
				ckIFrame = ckIFrame[0];
				customStyle = customStyle[0];
				var document = (ckIFrame.contentWindow || ckIFrame.contentDocument);
				if (document.document)
				document = document.document;
				if (document.head){
				var style = document.createElement('style');
				style.innerHTML = customStyle.get('html').trim();
				document.head.appendChild(style);
				}
				}
				};
			</script>
		</div>

	</xsl:template>
</xsl:stylesheet>