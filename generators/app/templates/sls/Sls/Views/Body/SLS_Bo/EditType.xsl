<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditType">	
	 
		<h1>Edit the specific type `<xsl:value-of select="//View/model/column" />` of `<xsl:value-of select="//View/model/table" />` table</h1>
		<xsl:if test="count(//View/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error) = 0">
			Please affect the specific type to your column :<br />
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<xsl:value-of select="//View/model/column" />: 
				<select name="type" id="type" onchange="checkType()">
					<option value="address">
						<xsl:if test="//View/model/type = 'address'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Address
					</option>
					<option value="color">
						<xsl:if test="//View/model/type = 'color'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Color
					</option>
					<option value="email">
						<xsl:if test="//View/model/type = 'email'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Email
					</option>
					<option value="file">
						<xsl:if test="php:functionString('SLS_String::startsWith',//View/model/type,'file')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						File
					</option>
					<option value="ip">
						<xsl:if test="php:functionString('SLS_String::startsWith',//View/model/type,'ip')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						IP address
					</option>
					<option value="position">
						<xsl:if test="//View/model/type = 'position'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Position
					</option>
					<option value="uniqid">
						<xsl:if test="//View/model/type = 'uniqid'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Uniqid
					</option>
					<option value="url">
						<xsl:if test="//View/model/type = 'url'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						URL
					</option>
					<option value="num">									
						<xsl:if test="php:functionString('SLS_String::startsWith',//View/model/type,'num_')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Numeric
					</option>
					<option value="complexity">									
						<xsl:if test="//View/model/type = 'complexity'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Complexity
					</option>						
				</select>
				<select name="complexity[]" id="complexity" style="display:none;margin-right:2px;" multiple="multiple" size="4">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/model/type = 'complexity'">display:inline;margin-right:2px;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<option value="lc">
						<xsl:if test="php:functionString('SLS_String::contains',//View/model/rules,'lc')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Lower
					</option>
					<option value="uc">
						<xsl:if test="php:functionString('SLS_String::contains',//View/model/rules,'uc')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Uppercase
					</option>
					<option value="digit">
						<xsl:if test="php:functionString('SLS_String::contains',//View/model/rules,'digit')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Digit
					</option>
					<option value="wild">
						<xsl:if test="php:functionString('SLS_String::contains',//View/model/rules,'wild')"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Special char
					</option>
				</select>
				<span id="complexity_min" style="display:none">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/model/type = 'complexity'">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<input type="text" name="complexity_min" id="complexity_min" value="{php:functionString('SLS_String::substrAfterFirstDelimiter',//View/model/rules,'min')}" style="width:20px" /><label for="complexity_min">minimum chars</label>&#160;
				</span>
				<select name="num" id="num" style="display:none">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="php:functionString('SLS_String::startsWith',//View/model/type,'num_')">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<option value="all">
						<xsl:if test="//View/model/type = 'num_all'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						All
					</option>
					<option value="gt">
						<xsl:if test="//View/model/type = 'num_gt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Greater than 0
					</option>
					<option value="gte">
						<xsl:if test="//View/model/type = 'num_gte'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Greater or equal 0
					</option>
					<option value="lt">
						<xsl:if test="//View/model/type = 'num_lt'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Lower than 0
					</option>
					<option value="lte">
						<xsl:if test="//View/model/type = 'num_lte'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Lower or equal 0
					</option>
				</select>
				<select name="file" id="file" onchange="checkFile()">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="php:functionString('SLS_String::startsWith',//View/model/type,'file')">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<option value="all">
						<xsl:if test="//View/model/type = 'file_all'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						All
					</option>
					<xsl:if test="//View/plugin_img = 'true'">
						<option value="img">
							<xsl:if test="//View/model/type = 'file_img'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
							Image
						</option>
					</xsl:if>
				</select>
				<select name="ip" id="ip" style="display:none">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="php:functionString('SLS_String::startsWith',//View/model/type,'ip')">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<option value="both">
						<xsl:if test="//View/model/type = 'ip_both'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Both
					</option>
					<option value="v4">
						<xsl:if test="//View/model/type = 'ip_v4'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						V4
					</option>
					<option value="v6">
						<xsl:if test="//View/model/type = 'ip_v6'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						V6
					</option>
				</select>
				<select name="multilanguage" id="multilanguage" style="display:none">								
					<option value="false">
						<xsl:if test="//View/model/multilanguage = 'false'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						No-Multilanguage
					</option>
					<option value="true">
						<xsl:if test="//View/model/multilanguage = 'true'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Multilanguage
					</option>
				</select>
				<span id="thumb_check">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/model/type = 'file_img'">display:inline;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<input type="checkbox" id="file_thumb" name="file_thumb" onchange="checkThumb()">
						<xsl:if test="count(//View/model/thumbs/thumb) &gt; 0"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
					</input>
					<label for="file_thumb">Create Thumbs ?</label>
				</span>
				<fieldset id="rules">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="//View/model/type = 'file_img'">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<legend>Settings :</legend>
					<div style="display:block;">
					<label for="ratio">Ratio <img src="{concat($sls_url_img_core_icons,'help16.png')}" align="absmiddle" style="cursor:pointer;padding:0 2px;" title="float (width / height)" alt="float (width / height)" /></label>
					<input type="text" name="imgSettings[ratio]" id="ratio" value="{php:functionString('SLS_String::substrBeforeFirstDelimiter',//View/model/rules,'|')}" />
					<label for="min-width">Min-Width</label>
					<input type="text" name="imgSettings[min-width]" id="min-width" value="{php:functionString('SLS_String::substrBeforeFirstDelimiter',php:functionString('SLS_String::substrAfterFirstDelimiter',//View/model/rules,'|'),'|')}" />
					<label for="min-height">Min-Height</label>
					<input type="text" name="imgSettings[min-height]" id="min-height" value="{php:functionString('SLS_String::substrAfterLastDelimiter',//View/model/rules,'|')}" />
					</div>
				</fieldset>
				<fieldset id="thumbs">
					<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 0">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
					<legend>Thumb(s) :</legend>
					<div id="thumb0">
						<xsl:attribute name="style">display:block;</xsl:attribute>	
						<label><strong>Thumb 0: </strong></label>
						<label for="width0">Width </label><input type="text" id="width0" name="width0" value="{//View/model/thumbs/thumb[1]/width}" />
						<label for="height0"> Height </label><input type="text" id="height0" name="height0" value="{//View/model/thumbs/thumb[1]/height}" />
						<label for="suffix0"> Suffix </label><input type="text" id="suffix0" name="suffix0" value="{//View/model/thumbs/thumb[1]/suffix}" />
						<a href="#" id="more0" onclick="addThumb(1)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb1">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 1">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 1: </strong></label>
						<label for="width1">Width </label><input type="text" id="width1" name="width1" value="{//View/model/thumbs/thumb[2]/width}" />
						<label for="height1"> Height </label><input type="text" id="height1" name="height1" value="{//View/model/thumbs/thumb[2]/height}" />
						<label for="suffix1"> Suffix </label><input type="text" id="suffix1" name="suffix1" value="{//View/model/thumbs/thumb[2]/suffix}" />
						<a href="#" id="more1" onclick="addThumb(2)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb2">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 2">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 2: </strong></label>
						<label for="width2">Width </label><input type="text" id="width2" name="width2" value="{//View/model/thumbs/thumb[3]/width}" />
						<label for="height2"> Height </label><input type="text" id="height2" name="height2" value="{//View/model/thumbs/thumb[3]/height}" />
						<label for="suffix2"> Suffix </label><input type="text" id="suffix2" name="suffix2" value="{//View/model/thumbs/thumb[3]/suffix}" />
						<a href="#" id="more2" onclick="addThumb(3)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb3">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 3">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 3: </strong></label>
						<label for="width3">Width </label><input type="text" id="width3" name="width3" value="{//View/model/thumbs/thumb[4]/width}" />
						<label for="height3"> Height </label><input type="text" id="height3" name="height3" value="{//View/model/thumbs/thumb[4]/height}" />
						<label for="suffix3"> Suffix </label><input type="text" id="suffix3" name="suffix3" value="{//View/model/thumbs/thumb[4]/suffix}" />
						<a href="#" id="more3" onclick="addThumb(4)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb4">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 4">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 4: </strong></label>
						<label for="width4">Width </label><input type="text" id="width4" name="width4" value="{//View/model/thumbs/thumb[5]/width}" />
						<label for="height4"> Height </label><input type="text" id="height4" name="height4" value="{//View/model/thumbs/thumb[5]/height}" />
						<label for="suffix4"> Suffix </label><input type="text" id="suffix4" name="suffix4" value="{//View/model/thumbs/thumb[5]/suffix}" />
						<a href="#" id="more4" onclick="addThumb(5)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb5">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 5">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 5: </strong></label>
						<label for="width5">Width </label><input type="text" id="width5" name="width5" value="{//View/model/thumbs/thumb[6]/width}" />
						<label for="height5"> Height </label><input type="text" id="height5" name="height5" value="{//View/model/thumbs/thumb[6]/height}" />
						<label for="suffix5"> Suffix </label><input type="text" id="suffix5" name="suffix5" value="{//View/model/thumbs/thumb[6]/suffix}" />
						<a href="#" id="more5" onclick="addThumb(6)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb6">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 6">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 6: </strong></label>
						<label for="width6">Width </label><input type="text" id="width6" name="width6" value="{//View/model/thumbs/thumb[7]/width}" />
						<label for="height6"> Height </label><input type="text" id="height6" name="height6" value="{//View/model/thumbs/thumb[7]/height}" />
						<label for="suffix6"> Suffix </label><input type="text" id="suffix6" name="suffix6" value="{//View/model/thumbs/thumb[7]/suffix}" />
						<a href="#" id="more6" onclick="addThumb(7)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb7">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 7">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 7: </strong></label>
						<label for="width7">Width </label><input type="text" id="width7" name="width7" value="{//View/model/thumbs/thumb[8]/width}" />
						<label for="height7"> Height </label><input type="text" id="height7" name="height7" value="{//View/model/thumbs/thumb[8]/height}" />
						<label for="suffix7"> Suffix </label><input type="text" id="suffix7" name="suffix7" value="{//View/model/thumbs/thumb[8]/suffix}" />
						<a href="#" id="more7" onclick="addThumb(8)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb8">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 8">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 8: </strong></label>
						<label for="width8">Width </label><input type="text" id="width8" name="width8" value="{//View/model/thumbs/thumb[9]/width}" />
						<label for="height8"> Height </label><input type="text" id="height8" name="height8" value="{//View/model/thumbs/thumb[9]/height}" />
						<label for="suffix8"> Suffix </label><input type="text" id="suffix8" name="suffix8" value="{//View/model/thumbs/thumb[9]/suffix}" />
						<a href="#" id="more8" onclick="addThumb(9)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb9">
						<xsl:attribute name="style"><xsl:choose><xsl:when test="count(//View/model/thumbs/thumb) &gt; 9">display:block;</xsl:when><xsl:otherwise>display:none;</xsl:otherwise></xsl:choose></xsl:attribute>
						<label><strong>Thumb 9: </strong></label>
						<label for="width9">Width </label><input type="text" id="width9" name="width9" value="{//View/model/thumbs/thumb[10]/width}" />
						<label for="height9"> Height </label><input type="text" id="height9" name="height9" value="{//View/model/thumbs/thumb[10]/height}" />
						<label for="suffix9"> Suffix </label><input type="text" id="suffix9" name="suffix9" value="{//View/model/thumbs/thumb[10]/suffix}" />
					</div>
				</fieldset>
											
				<input type="submit" value="Edit" />
			</form>
			<xsl:if test="//View/plugin_img = 'false'">
				<div>
					<u>Warning:</u> SLS_Image's plugin not found. <a href="{//View/plugin_url}" title="Plugins">Download it</a> if you want to add an image type on a specific column of your model.
				</div>
			</xsl:if>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>