/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckfinder.com/license
*/

CKFinder.customConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.skin = 'v1';
	config.language = 'fr'; // TODO SLS Lang
	config.removePlugins = 'basket';
	config.height = 598;
	config.callback = function(){
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
};
