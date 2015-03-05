<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderEditType">
		<script type="text/javascript">
			<xsl:text disable-output-escaping="yes"><![CDATA[
			function checkType()
			{
				if (document.getElementById('type').value == 'file')
				{
					document.getElementById('complexity').style.display = 'none';
					document.getElementById('complexity_min').style.display = 'none';
					document.getElementById('num').style.display = 'none';
					document.getElementById('ip').style.display = 'none';
					document.getElementById('file').style.display = 'inline';					
					checkFile();
					checkThumb();
				}
				else if (document.getElementById('type').value == 'ip')
				{
					document.getElementById('complexity').style.display = 'none';
					document.getElementById('complexity_min').style.display = 'none';
					document.getElementById('num').style.display = 'none';
					document.getElementById('file').style.display = 'none';		
					document.getElementById('rules').style.display = 'none';			
					document.getElementById('thumb_check').style.display = 'none';
					document.getElementById('thumbs').style.display = 'none';
					document.getElementById('ip').style.display = 'inline';
				}
				else if (document.getElementById('type').value == 'num')
				{					
					document.getElementById('complexity').style.display = 'none';
					document.getElementById('complexity_min').style.display = 'none';
					document.getElementById('num').style.display = 'inline';
					document.getElementById('file').style.display = 'none';			
					document.getElementById('rules').style.display = 'none';		
					document.getElementById('thumb_check').style.display = 'none';
					document.getElementById('thumbs').style.display = 'none';
					document.getElementById('ip').style.display = 'none';
				}
				else if (document.getElementById('type').value == 'complexity')
				{					
					document.getElementById('complexity').style.display = 'inline';
					document.getElementById('complexity_min').style.display = 'inline';
					document.getElementById('num').style.display = 'none';
					document.getElementById('file').style.display = 'none';
					document.getElementById('rules').style.display = 'none';					
					document.getElementById('thumb_check').style.display = 'none';
					document.getElementById('thumbs').style.display = 'none';
					document.getElementById('ip').style.display = 'none';
				}
				else
				{
					document.getElementById('complexity').style.display = 'none';
					document.getElementById('complexity_min').style.display = 'none';
					document.getElementById('num').style.display = 'none';
					document.getElementById('file').style.display = 'none';
					document.getElementById('rules').style.display = 'none';
					document.getElementById('thumb_check').style.display = 'none';
					document.getElementById('thumbs').style.display = 'none';
					document.getElementById('ip').style.display = 'none';
				}
			}
			function checkFile()
			{
				if (document.getElementById('file').value == 'img')
				{
					document.getElementById('rules').style.display = 'block';
					document.getElementById('thumb_check').style.display = 'inline';
					checkThumb();
				}
				else
				{
					document.getElementById('rules').style.display = 'none';
					document.getElementById('thumb_check').style.display = 'none';
					document.getElementById('thumbs').style.display = 'none';	
				}
			}
			function checkThumb()
			{
				if (document.getElementById('file_thumb').checked)
				{
					document.getElementById('thumbs').style.display = 'block';
				}
				else
				{
					document.getElementById('thumbs').style.display = 'none';
				}
			}
			function addThumb(id)
			{
				document.getElementById('thumb'+id).style.display = 'block';
				resetLinksMore(id);
			}
			function resetLinksMore(id)
			{
				for(var i=0 ; i<id ; i++)
				{
					document.getElementById('more'+i).style.display = 'none';
				}
			}
			]]></xsl:text>
		</script>
	</xsl:template>
</xsl:stylesheet>