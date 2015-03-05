<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="LoadGenericJavascript">
		
		<!-- slsBuild Object -->
		<script type="text/javascript">
			var slsBuild = {<xsl:for-each select="//Statics/Sls/Configs/*"><xsl:variable name="firstPos" select="position()" /><xsl:variable name="nodeName" select="name()" /><xsl:if test="count(*[@js='true'])!=0">'<xsl:call-template name="protectString"><xsl:with-param name="str" select="$nodeName" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>' :{<xsl:variable name="nbItems" select="count(*[@js='true'])" /><xsl:for-each select="*[@js='true']"><xsl:variable name="childName" select="name()" />'<xsl:call-template name="protectString"><xsl:with-param name="str" select="$childName" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>':'<xsl:call-template name="protectString"><xsl:with-param name="str" select="." /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'<xsl:if test="$nbItems != position()">,</xsl:if></xsl:for-each>},</xsl:if></xsl:for-each>'langs' : {<xsl:for-each select="//Statics/Sls/Langs/js/sentence">'<xsl:call-template name="protectString"><xsl:with-param name="str" select="name" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>' : '<xsl:call-template name="protectString"><xsl:with-param name="str" select="value" /><xsl:with-param name="type">'</xsl:with-param></xsl:call-template>'<xsl:if test="position() != count(//Statics/Sls/Langs/js/sentence)">,</xsl:if></xsl:for-each>}};
		</script>
		<!-- /slsBuild Object -->
		
		<!-- Statics JS on 'user' side -->
		<xsl:if test="count(//Statics/Site/BoMenu/*) = 0 and //Statics/Sls/Configs/site/defaultLoadStaticsJavascript = 1 and //Statics/Sls/Session/params/param[name='current_side']/value = 'user'">
			<xsl:for-each select="//Statics/Sls/JsStatics/filesStatics/file">
				<xsl:variable name="index" select="position()" />
				<script type="text/javascript" src="{//Statics/Sls/JsStatics/filesStatics/file[$index]}"></script>				
			</xsl:for-each>			
		</xsl:if>
		<!-- /Statics JS on 'user' side -->
		
		<!-- Dyn JS on both side -->
		<xsl:if test="//Statics/Sls/Configs/site/defaultLoadDynsJavascript = 1">	
			<xsl:for-each select="//Statics/Sls/JsStatics/filesDyn/file">
				<xsl:variable name="index" select="position()" />
				<script type="text/javascript" src="{//Statics/Sls/JsStatics/filesDyn/file[$index]}"></script>				
			</xsl:for-each>
		</xsl:if>
		<!-- /Dyn JS on both side -->

		<!-- IE6 Toolbar -->
		<xsl:if test="//Statics/Sls/Configs/site/defaultLoadIE6Javascript = 1">
			<!-- // Uncompressed JavaScript
			ie6_popup = '<div style="background-color: #FEEFDA; border: 1px solid #F7941D; width: 98%; height: 80px; margin-top: 5px; position: absolute; right: 1%; left: 1%; top: 3px; bottom: 3px; font-family: courier new; font-weight: bold;">
							<div style="width: 700px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;">
								<div style="width: 75px; float: left;">
									<img src="'+ie6_url+'ie6_warning.png" alt="Warning!"/>
								</div>
								<div style="width: 275px; float: left; font-family: Arial, sans-serif;">
									<div style="font-size: 14px; font-weight: bold; margin-top: 12px;">
										You are using an outdated browser
									</div>
									<div style="font-size: 12px; margin-top: 6px; line-height: 12px;">
										For a better experience using this site, please upgrade to a modern web browser.
									</div>
								</div>
								<div style="width: 75px; float: left;">
									<a href="http://www.firefox.com" target="_blank" title="Get Mozilla Firefox">
										<img src="'+ie6_url+'ie6_firefox.png" style="border: none;" alt="Get Mozilla Firefox" title="Get Mozilla Firefox" />
									</a>
								</div>
								<div style="width: 75px; float: left;">
									<a href="http://www.browserforthebetter.com/download.html" target="_blank" title="Get Microsoft Internet Explorer">
										<img src="'+ie6_url+'ie6_ie.png" style="border: none;" alt="Get Microsoft Internet Explorer" title="Get Microsoft Internet Explorer" />
									</a>
								</div>
								<div style="width: 75px; float: left;">
									<a href="http://www.google.com/chrome" target="_blank" title="Get Google Chrome">
										<img src="'+ie6_url+'ie6_chrome.png" style="border: none;" alt="Get Google Chrome" title="Get Google Chrome" />
									</a>
								</div>
								<div style="width: 75px; float: left;">
									<a href="http://www.apple.com/safari/download/" target="_blank" title="Get Apple Safari">
										<img src="'+ie6_url+'ie6_safari.png" style="border: none;" alt="Get Apple Safari" title="Get Apple Safari" />
									</a>
								</div>
								<div style="float: left;">
									<a href="http://www.opera.com/browser/" target="_blank" title="Get Opera">
										<img src="'+ie6_url+'ie6_opera.png" style="border: none;" alt="Get Opera" title="Get Opera" />
									</a>
								</div>
							</div>
							<a href="#" onclick="javascript:this.parentNode.parentNode.style.display=\'none\'; return false;" style="float: right;margin: 3px 3px 0 0; position: absolute; top: 0; right: 0;" title="Close this notice">
								<img src="'+ie6_url+'ie6_close.png" style="border: none;" alt="Close this notice" title="Close this notice" />
							</a>
						</div>';
			-->
			<xsl:comment>[IF lt IE 7]&gt;
				&lt;script type="text/javascript"&gt;
				window.onload = function(){
				ie6_url = '<xsl:value-of select="$sls_url_img_core" />IE6/';
				ie6_popup = '&lt;div style="background-color: #FEEFDA; border: 1px solid #F7941D; width: 98%; height: 80px; margin-top: 5px; position: absolute; right: 1%; left: 1%; top: 3px; bottom: 3px; font-family: courier new; font-weight: bold;"&gt;&lt;div style="width: 700px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;"&gt;&lt;div style="width: 75px; float: left;"&gt;&lt;img src="'+ie6_url+'ie6_warning.png" alt="Warning!"/&gt;&lt;/div&gt;&lt;div style="width: 275px; float: left; font-family: Arial, sans-serif;"&gt;&lt;div style="font-size: 14px; font-weight: bold; margin-top: 12px;"&gt;You are using an outdated browser&lt;/div&gt;&lt;div style="font-size: 12px; margin-top: 6px; line-height: 12px;"&gt;For a better experience using this site, please upgrade to a modern web browser.&lt;/div&gt;&lt;/div&gt;&lt;div style="width: 75px; float: left;"&gt;&lt;a href="http://www.firefox.com" target="_blank" title="Get Mozilla Firefox"&gt;&lt;img src="'+ie6_url+'ie6_firefox.png" style="border: none;" alt="Get Mozilla Firefox" title="Get Mozilla Firefox" /&gt;&lt;/a&gt;&lt;/div&gt;&lt;div style="width: 75px; float: left;"&gt;&lt;a href="http://www.browserforthebetter.com/download.html" target="_blank" title="Get Microsoft Internet Explorer"&gt;&lt;img src="'+ie6_url+'ie6_ie.png" style="border: none;" alt="Get Microsoft Internet Explorer" title="Get Microsoft Internet Explorer" /&gt;&lt;/a&gt;&lt;/div&gt;&lt;div style="width: 75px; float: left;"&gt;&lt;a href="http://www.google.com/chrome" target="_blank" title="Get Google Chrome"&gt;&lt;img src="'+ie6_url+'ie6_chrome.png" style="border: none;" alt="Get Google Chrome" title="Get Google Chrome" /&gt;&lt;/a&gt;&lt;/div&gt;&lt;div style="width: 75px; float: left;"&gt;&lt;a href="http://www.apple.com/safari/download/" target="_blank" title="Get Apple Safari"&gt;&lt;img src="'+ie6_url+'ie6_safari.png" style="border: none;" alt="Get Apple Safari" title="Get Apple Safari" /&gt;&lt;/a&gt;&lt;/div&gt;&lt;div style="float: left;"&gt;&lt;a href="http://www.opera.com/browser/" target="_blank" title="Get Opera"&gt;&lt;img src="'+ie6_url+'ie6_opera.png" style="border: none;" alt="Get Opera" title="Get Opera" /&gt;&lt;/a&gt;&lt;/div&gt;&lt;/div&gt;&lt;a href="#" onclick="javascript:this.parentNode.parentNode.style.display=\'none\'; return false;" style="float: right;margin: 3px 3px 0 0; position: absolute; top: 0; right: 0;" title="Close this notice"&gt;&lt;img src="'+ie6_url+'ie6_close.png" style="border: none;" alt="Close this notice" title="Close this notice" /&gt;&lt;/a&gt;&lt;/div&gt;';
				sls_dynamic_ie6 = document.createElement("div");sls_dynamic_ie6.setAttribute("id","sls_dynamic_ie6");sls_dynamic_ie6.style.background = '#FEEFDA';sls_dynamic_ie6.style.textAlign = 'center';sls_dynamic_ie6.style.clear = 'both';sls_dynamic_ie6.style.height = '100px';sls_dynamic_ie6.style.position = 'absolute';sls_dynamic_ie6.style.width = '100%';sls_dynamic_ie6.style.top = '0px';sls_dynamic_ie6.style.bottom = 'auto';sls_dynamic_ie6.style.left = '0px';sls_dynamic_ie6.innerHTML = ie6_popup;document.getElementsByTagName("body")[0].appendChild(sls_dynamic_ie6);
				window.onscroll = function() {
				document.getElementById('sls_dynamic_ie6').style.top = document.documentElement.scrollTop + 'px';
				}
				};
				&lt;/script&gt;
				&lt;![endif]</xsl:comment>
		</xsl:if>
		<!-- /IE6 Toolbar -->
		<xsl:comment>[IF lt IE 9]&gt;
			&lt;script type="text/javascript" src="<xsl:value-of select="concat($sls_url_js_core_dyn, 'html5shiv.js')" />"&gt;&lt;/script&gt;
		&lt;![endif]</xsl:comment>
		<xsl:comment>[IF lt IE 10]&gt;
			&lt;script type="text/javascript" src="<xsl:value-of select="concat($sls_url_js_core_dyn, 'placeholders.js')" />"&gt;&lt;/script&gt;
		&lt;![endif]</xsl:comment>
		
		<!-- Developer Toolbar & Bo Menu-->
		<xsl:if test="php:functionString('SLS_BoRights::isLogged') and php:functionString('SLS_Dtd::boExists') and count(//Statics/Site/BoMenu/*) = 0 and //Statics/Sls/Session/params/param[name = 'current_side']/value = 'user'">
			<xsl:comment>[IF lt IE 9]&gt;
				&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'Fonts.css')" />" /&gt;
				&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'themes/blue.css')" />" /&gt;
				&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'SlsBOToolbar.css')" />" /&gt;
				&lt;link rel="stylesheet" type="text/css" href="<xsl:value-of select="concat($sls_url_css_core, 'highlight.css')" />" /&gt;
			&lt;![endif]</xsl:comment>
			<script type="text/javascript">
				<xsl:text disable-output-escaping="yes"><![CDATA[
				var SlsBoMenu = {
					generatingActionUrl: '|||sls:urlBoMenu|||'.replace(/^[^\/]*\:/, ''),
					request: function(){
						SlsBoMenu.toolbarContainer = new Element('div').inject($$('body')[0]);
						new Request.HTML({ url: SlsBoMenu.generatingActionUrl, append: $$('body')[0], onSuccess: SlsBoMenu.receiveHTML }).send();
					},
					receiveHTML: function(responseTree, responseElements, responseHTML, responseJavaScript){
						window.addEvent('frontToolbarReady', function(){ new Toolbar(); });
						new Element('script', { 'async': true, 'src': '//'+slsBuild.site.domainName+'/'+slsBuild.paths.coreJsDyn+'Sls-BO-Toolbar.js' }).inject(SlsBoMenu.toolbarContainer);
						new Element('script', { 'async': true, 'src': '//'+slsBuild.site.domainName+'/'+slsBuild.paths.coreJsDyn+'highlight.js' }).inject(SlsBoMenu.toolbarContainer);
					}
				};
				if (!window.MooTools){
					var script = document.createElement('script'); script.async = true;
					if (window.addEventListener)
						window.addEventListener('load', SlsBoMenu.request);
					else if (window.attachEvent)
						window.attachEvent('onload', SlsBoMenu.request);
					document.getElementsByTagName('head')[0].appendChild(script);
					script.src = '//'+slsBuild.site.domainName+'/'+slsBuild.paths.coreJsDyn+'mootools-core-and-more-1.5.0.js';
				} else
					window.addEvent('domready', SlsBoMenu.request);
				]]></xsl:text>
			</script>
		</xsl:if>
		<!-- /Developer Toolbar & Bo Menu-->
		
	</xsl:template>
</xsl:stylesheet>