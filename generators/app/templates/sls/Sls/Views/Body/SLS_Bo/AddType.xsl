<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddType">	
	
		<h1>Add a specific type on a column of `<xsl:value-of select="//View/model/table" />` table</h1>
		<xsl:if test="count(//View/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error) = 0">
			Please affect the specific type to your column :<br />
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<select name="column">
					<xsl:for-each select="//View/model/columns/column">
						<option value="{.}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
				<select name="type" id="type" onchange="checkType()">
					<option value="address">Address</option>
					<option value="color">Color</option>
					<option value="email">Email</option>
					<option value="file">File</option>
					<option value="ip">IP address</option>
					<option value="position">Position</option>
					<option value="uniqid">Uniqid</option>
					<option value="url">URL</option>
					<option value="num">Numeric</option>
					<option value="complexity">Complexity</option>
				</select>
				<select name="complexity[]" id="complexity" style="display:none;margin-right:2px;" multiple="multiple" size="4">
					<option value="lc">Lower</option>
					<option value="uc">Uppercase</option>
					<option value="digit">Digit</option>
					<option value="wild">Special char</option>
				</select>
				<span id="complexity_min" style="display:none"><input type="text" name="complexity_min" id="complexity_min" value="7" style="width:20px" /><label for="complexity_min">minimum chars</label>&#160;</span>
				<select name="num" id="num" style="display:none">
					<option value="all">All</option>
					<option value="gt">Greater than 0</option>
					<option value="gte">Greater or equal 0</option>
					<option value="lt">Lower than 0</option>
					<option value="lte">Lower or equal 0</option>
				</select>
				<select name="file" id="file" style="display:none" onchange="checkFile()">
					<option value="all">All</option>
					<xsl:if test="//View/plugin_img = 'true'">
						<option value="img">Image</option>
					</xsl:if>
				</select>
				<select name="ip" id="ip" style="display:none">
					<option value="both">Both</option>
					<option value="v4">V4</option>
					<option value="v6">V6</option>
				</select>
				<select name="multilanguage" id="multilanguage" style="display:none">
					<option value="false">No-Multilanguage</option>
					<option value="true">Multilanguage</option>
				</select>
				<span id="thumb_check" style="display:none;"><input type="checkbox" id="file_thumb" name="file_thumb" onchange="checkThumb()" /><label for="file_thumb">Create Thumbs ?</label></span>
				<fieldset id="rules" style="display:none;">
					<legend>Settings :</legend>
					<div style="display:block;">
					<label for="ratio">Ratio <img src="{concat($sls_url_img_core_icons,'help16.png')}" align="absmiddle" style="cursor:pointer;padding:0 2px;" title="float (width / height)" alt="float (width / height)" /></label>
					<input type="text" name="imgSettings[ratio]" id="ratio" value="*" />
					<label for="min-width">Min-Width</label>
					<input type="text" name="imgSettings[min-width]" id="min-width" value="*" />
					<label for="min-height">Min-Height</label>
					<input type="text" name="imgSettings[min-height]" id="min-height" value="*" />
					</div>
				</fieldset>
				<fieldset id="thumbs" style="display:none;">
					<legend>Thumb(s) :</legend>
					<div id="thumb0" style="display:block;">
						<label><strong>Thumb 0: </strong></label>
						<label for="width0">Width </label><input type="text" id="width0" name="width0" />
						<label for="height0"> Height </label><input type="text" id="height0" name="height0" />
						<label for="suffix0"> Suffix </label><input type="text" id="suffix0" name="suffix0" />
						<a href="#" id="more0" onclick="addThumb(1)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb1" style="display:none;">
						<label><strong>Thumb 1: </strong></label>
						<label for="width1">Width </label><input type="text" id="width1" name="width1" />
						<label for="height1"> Height </label><input type="text" id="height1" name="height1" />
						<label for="suffix1"> Suffix </label><input type="text" id="suffix1" name="suffix1" />
						<a href="#" id="more1" onclick="addThumb(2)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb2" style="display:none;">
						<label><strong>Thumb 2: </strong></label>
						<label for="width2">Width </label><input type="text" id="width2" name="width2" />
						<label for="height2"> Height </label><input type="text" id="height2" name="height2" />
						<label for="suffix2"> Suffix </label><input type="text" id="suffix2" name="suffix2" />
						<a href="#" id="more2" onclick="addThumb(3)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb3" style="display:none;">
						<label><strong>Thumb 3: </strong></label>
						<label for="width3">Width </label><input type="text" id="width3" name="width3" />
						<label for="height3"> Height </label><input type="text" id="height3" name="height3" />
						<label for="suffix3"> Suffix </label><input type="text" id="suffix3" name="suffix3" />
						<a href="#" id="more3" onclick="addThumb(4)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb4" style="display:none;">
						<label><strong>Thumb 4: </strong></label>
						<label for="width4">Width </label><input type="text" id="width4" name="width4" />
						<label for="height4"> Height </label><input type="text" id="height4" name="height4" />
						<label for="suffix4"> Suffix </label><input type="text" id="suffix4" name="suffix4" />
						<a href="#" id="more4" onclick="addThumb(5)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb5" style="display:none;">
						<label><strong>Thumb 5: </strong></label>
						<label for="width5">Width </label><input type="text" id="width5" name="width5" />
						<label for="height5"> Height </label><input type="text" id="height5" name="height5" />
						<label for="suffix5"> Suffix </label><input type="text" id="suffix5" name="suffix5" />
						<a href="#" id="more5" onclick="addThumb(6)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb6" style="display:none;">
						<label><strong>Thumb 6: </strong></label>
						<label for="width6">Width </label><input type="text" id="width6" name="width6" />
						<label for="height6"> Height </label><input type="text" id="height6" name="height6" />
						<label for="suffix6"> Suffix </label><input type="text" id="suffix6" name="suffix6" />
						<a href="#" id="more6" onclick="addThumb(7)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb7" style="display:none;">
						<label><strong>Thumb 7: </strong></label>
						<label for="width7">Width </label><input type="text" id="width7" name="width7" />
						<label for="height7"> Height </label><input type="text" id="height7" name="height7" />
						<label for="suffix7"> Suffix </label><input type="text" id="suffix7" name="suffix7" />
						<a href="#" id="more7" onclick="addThumb(8)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb8" style="display:none;">
						<label><strong>Thumb 8: </strong></label>
						<label for="width8">Width </label><input type="text" id="width8" name="width8" />
						<label for="height8"> Height </label><input type="text" id="height8" name="height8" />
						<label for="suffix8"> Suffix </label><input type="text" id="suffix8" name="suffix8" />
						<a href="#" id="more8" onclick="addThumb(9)" style="display:inline;"> Add thumb</a>
					</div>
					<div id="thumb9" style="display:none;">
						<label><strong>Thumb 9: </strong></label>
						<label for="width9">Width </label><input type="text" id="width9" name="width9" />
						<label for="height9"> Height </label><input type="text" id="height9" name="height9" />
						<label for="suffix9"> Suffix </label><input type="text" id="suffix9" name="suffix9" />
					</div>
				</fieldset>
											
				<input type="submit" value="Add" />
			</form>
			<xsl:if test="//View/plugin_img = 'false'">
				<div>
					<u>Warning:</u> SLS_Image's plugin not found. <a href="{//View/plugin_url}" title="Plugins">Download it</a> if you want to add an image type on a specific column of your model.
				</div>
			</xsl:if>
		</xsl:if>
				
	</xsl:template>
</xsl:stylesheet>