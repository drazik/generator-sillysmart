<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderBoi18n">
		<script type="text/javascript">
			<!-- http://api.yandex.com/translate/doc/dg/reference/translate.xml -->
			<!--
			window.addEvent('domready', function(){
				$$('form .input').each(function(element, index){
					element.addEvent('click', function(e){
						var myJSONP = new Request.JSONP({
						    url: 'https://translate.yandex.net/api/v1.5/tr.json/translate',
						    callbackKey: 'translateAnswer',
						    data: {
						        key: 'trnsl.1.1.20140217T095645Z.db4b4de2fa56d5b8.b19c558908bb499e1ea0fb38be6713e1f8aa8231',
						        lang: 'fr-en',
						        text: element.get('value')
						    },
						    onComplete: function(xhr){
						        
						    }
						}).send();
					});
				});
				
				function translateAnswer(response){
					var xhr = JSON.parse(response);
					console.log(xhr);
				};
			});
			-->
			
			<!-- 
				 Others without API Key
				 http://glosbe.com/gapi/translate?from=fr&dest=en&format=json&phrase=Un%20prix%20international%20voulu%20par%20LVMH%20pour%20r%C3%A9v%C3%A9ler%20et%20soutenir%20les%20jeunes%20cr%C3%A9ateurs%20de%20mode.&pretty=true&tm=false
				 http://mymemory.translated.net/api/get?q=Un%20prix%20international%20voulu%20par%20LVMH%20pour%20r%C3%A9v%C3%A9ler%20et%20soutenir%20les%20jeunes%20cr%C3%A9ateurs%20de%20mode.&langpair=fr|en
			-->
		</script>	
	</xsl:template>
</xsl:stylesheet>