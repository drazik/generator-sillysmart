/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.language = (document.html && document.html.lang && ['de','en','es','fr','pl'].indexOf(document.html.lang) != -1) ? document.html.lang : 'en';
	config.toolbar = 'SillySmartToolbar';
	config.toolbar_SillySmartToolbar =
	    [
	     ['Source','ShowBlocks','-','Preview'],
	     ['SelectAll','Cut','Copy','Paste','PasteFromWord','PasteText'],
	     ['Undo','Redo','-','Find','Replace','-'],
	     ['Link','Unlink','Anchor','-','HorizontalRule','SpecialChar'],
	     ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
	     ['NumberedList','BulletedList','Outdent','Indent','Blockquote'],
	     ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],	     
	     ['CreateDiv','Image','oembed','Table','PageBreak'],
	     ['Styles','Format','Font','FontSize'],
	     ['TextColor','BGColor','-'],
	     ['BidiLtr','BidiRtl','-','Smiley']
	    ];
	config.toolbar_SillySmartToolbarSmall =
		[
			['Bold','Italic','Underline'],
			['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
			['Styles','Format']
		];
	config.entities = false;
	config.entities_latin = false;
	config.removePlugins = 'elementspath';
	config.extraPlugins = 'oembed';
	config.allowedContent = true;
	config.oembed_WrapperClass = 'slsEmbedContent';
	//config.oembed_maxWidth = '640';
	//config.oembed_maxHeight = '480';
	//config.contentsCss = '/Public/Style/Css/ck.css';
	//config.colorButton_colors = '';
};
