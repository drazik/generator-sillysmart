<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<!-- 
	 	- Function displayLang
	 	- Affiche une variable de lang
	 	- @param: string $str la variable de lang à afficher
	 	- @param: string $escaping if set to yes, it will add disable-output-escaping="yes"
	-->
	<xsl:template name="displayLang">
		<xsl:param name="str" />
		<xsl:param name="escaping" />		
		
		<xsl:choose>
			<xsl:when test="$escaping = 'yes'">
				<xsl:value-of select="php:functionString('SLS_Dtd::displayLang',$str)" disable-output-escaping="yes"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="php:functionString('SLS_Dtd::displayLang',$str)" />	
			</xsl:otherwise>		
		</xsl:choose>
		
	</xsl:template>
	
	<!-- 
	 	- Function protectString
	 	- Protect anything 
	 	- @param: string $str 
	-->
	<xsl:template name="protectString">
		<xsl:param name="str" />
		<xsl:param name="type" />
		<xsl:call-template name="replace-string">
			<xsl:with-param name="text" select="$str" />
			<xsl:with-param name="replace" select="$type" />
			<xsl:with-param name="with" select="concat('\', $type)" />
		</xsl:call-template>
	</xsl:template>
	
	<!--
		- Function Replace-string
		- Permit to replace a string by another
		- @param: string $text: String where you want to replace
		- @param: string $replace: String to Search
		- @param: string $with: String Replacement
	-->
	<xsl:template name="replace-string">
	    <xsl:param name="text"/>
	    <xsl:param name="replace"/>
	    <xsl:param name="with"/>
	    <xsl:choose>
	      <xsl:when test="contains($text,$replace)">
	        <xsl:value-of select="substring-before($text,$replace)"/>
	        <xsl:value-of select="$with"/>
	        <xsl:call-template name="replace-string">
	          <xsl:with-param name="text" select="substring-after($text,$replace)"/>
	          <xsl:with-param name="replace" select="$replace"/>
	          <xsl:with-param name="with" select="$with"/>
	        </xsl:call-template>
	      </xsl:when>
	      <xsl:otherwise>
	        <xsl:value-of select="$text"/>
	      </xsl:otherwise>
	    </xsl:choose>
	</xsl:template>
	
	<xsl:template name="printLink">
		<xsl:param name="codeName" />
		<xsl:param name="class" />
		<xsl:param name="id" />
		<xsl:param name="target" />
		<xsl:variable name="quote">'</xsl:variable>
		
		<a href="{//Statics/Sls/Configs/action/links/link[name=$codeName]/href}">
			<xsl:attribute name="title"><xsl:call-template name="displayLang"><xsl:with-param name="str" select="concat('LINK_REGISTRED_TITLE_', $codeName)" /></xsl:call-template></xsl:attribute>
			<xsl:if test="$class != ''">
				<xsl:attribute name="class"><xsl:value-of select="$class" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="$id != ''">
				<xsl:attribute name="id"><xsl:value-of select="$id" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="$target != ''">
				<xsl:attribute name="target"><xsl:value-of select="$target" /></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="displayLang"><xsl:with-param name="str" select="concat('LINK_REGISTRED_XHTML_', $codeName)" /></xsl:call-template>
		</a>
	</xsl:template>	
	
	<xsl:template name="include">
		<xsl:param name="sls_var" />
		<xsl:param name="file" />
		
		<xsl:choose>
			<xsl:when test="php:functionString('SLS_String::contains',$file,'css')">
				<link rel="stylesheet" type="text/css" href="{php:functionString('SLS_String::callCachingFile',concat($sls_var,$file))}" />
			</xsl:when>
			<xsl:otherwise>
				<script type="text/javascript" src="{php:functionString('SLS_String::callCachingFile',concat($sls_var,$file))}"></script>
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>
	
</xsl:stylesheet>