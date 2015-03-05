<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderLogsApp">
	<style type="text/css">
		div.mainTitle {
			display:block;
			font-family:Verdana;
			font-size:18px;
			font-weight:bold;
			margin-bottom:10px;
		}
		div.mainTitle div {
			display:block;
			font-family:Verdana;
			font-size:12px;
			font-style: italic;
			color:#000080;
		}
		div.stackTrace {
			display:block;
			margin: 0 40px;
			padding: 10px;
			border:1px solid #000;
		}
		div.stackTrace div {
			display:block;
			font-family:Verdana;
			font-size:12px;
			padding-top:2px;
		}
		div.stackTrace div span{
			display:block;
			margin-left:10px;
			font-family:Verdana;
			font-size:12px;
			font-style: italic;
			color:gray;
		}		
	</style>
	</xsl:template>
</xsl:stylesheet>