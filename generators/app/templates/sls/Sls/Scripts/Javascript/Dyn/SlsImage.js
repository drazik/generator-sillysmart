var SlsImage = new Class({
	Implements: [Options, Events],
	options:{
		'parentClass': 'loading'
	},
	initialize: function(options){
		this.setOptions(options);

		var images = $$('.sls-image');
		if (images.length)
			images.each(SlsImage.init);

		window.addEvents({
			'resize': SlsImage.resize,
			'load': SlsImage.resize
		});
	}
});

SlsImage.init = function(img, callback){
	if (!SlsImage.isImage(img))
		return false;

	SlsImage.prepareLoad.call(img);
	SlsImage.initAttributes.call(img);
	SlsImage.getDimensions.call(img, callback);
	SlsImage.startLoading.call(img);
	SlsImage.addImage.call(img);

	return img;
};

SlsImage.images = new Elements();

SlsImage.prepareLoad = function(){
	var src;

	if (this.attributes.src){
		src = this.get('src');
		this.removeAttribute('src');
	}
	if (this.attributes["sls-image-src"]){
		src = this.attributes["sls-image-src"].nodeValue;
		this.removeAttribute('sls-image-src');
	}
	if (src != '' && this.get('atl') != ''){
		this.store('SlsImageAlt', this.get('alt'));
		this.removeAttribute('alt');
	}

	this.setStyles({'opacity': 0, 'visibility': 'hidden'});
	this.set('src', src)
		.set('sls-image-state', 'loading');
};

SlsImage.loaded = function(callback){
	this.eliminate('SlsImageLoading');
	this.setStyles({'visibility': 'visible'});
	SlsImage.applyAttributes.call(this);
	SlsImage.stopLoading.call(this);
	this.morph({'opacity': 1});
	this.fireEvent('loaded', SlsImage.getDimensions.call(this));

	if (this.retrieve('SlsImageAlt') != '')
		this.set('alt', this.retrieve('SlsImageAlt'));

	this.set('sls-image-state', 'loaded');

	if (typeOf(callback) == 'function')
		callback();
};

SlsImage.getContainer = function(){
	var container = this.retrieve('SlsImageContainer');
	if (!container){
		container = this.getParent('.sls-image-container') || this.getParent();
		container.setStyles({'position': (container.getStyle('position').indexOf('static') == -1) ? container.getStyle('position') : "relative", 'overflow': 'hidden'});
		this.store('SlsImageContainer', container);
	}
	return container;
};

SlsImage.acceptedAttributes = {
	// parent / none / visible / contain / cover / width / height
	'fit': {
		'test': function(attribute){
			return attribute.test(/^(none|visible|contain|cover|width|height)$/);
		},
		'init': function(value){
			this.setStyle('position',(this.getStyle('position') != "static") ? this.getStyle('position') : 'relative');
			SlsImage.addAttribute.call(this, {
				'fit': value
			});
		},
		apply: function(value, forceLoop){
			var dimensions = SlsImage.getDimensions.call(this),
				width = "auto",
				height = "auto",
				ratio,
				containerDimensions = SlsImage.getContainer.call(this).measure(function(){
					return (this.getStyle('width').indexOf('%') != -1) ? this.getCoordinates() : this.getComputedSize();
				});

			if (!dimensions){
				if (forceLoop)
					SlsImage.acceptedAttributes.fit.apply.pass([value, forceLoop], this).delay(50, this);
				return false;
			}

			switch (value){
				case 'visible':
					width = dimensions.width;
					height = dimensions.height;
					ratio = Math.max(dimensions.width / containerDimensions.width, dimensions.height / containerDimensions.height);
					if (ratio > 1){
						width = Math.ceil(dimensions.width / ratio);
						height = Math.ceil(dimensions.height / ratio);
					}
					break;
				case 'contain':
					ratio = Math.max(dimensions.width / containerDimensions.width, dimensions.height / containerDimensions.height);
					width = Math.ceil(dimensions.width / ratio);
					height = Math.ceil(dimensions.height / ratio);
					break;
				case 'cover':
					ratio = Math.min(dimensions.width / containerDimensions.width, dimensions.height / containerDimensions.height);
					width = Math.ceil(dimensions.width / ratio);
					height = Math.ceil(dimensions.height / ratio);
					break;
				case 'width':
					width = "100%";
					break;
				case 'height':
					height = "100%";
					break;
			}

			this.setStyles({
				'width': width,
				'height': height
			});

			if (['cover','contain','visible'].indexOf(value) == -1){
				if (SlsImage.getAttributes.call(this).align)
					SlsImage.acceptedAttributes.align.apply.call(this, [SlsImage.getAttributes.call(this).align, true]);
			} else {
				this.setStyles({
					'top': (containerDimensions.height - height) / 2,
					'left': (containerDimensions.width - width) / 2
				});
			}
		}
	},
	// top / right / bottom / left / XXXpx / XXX% => "bottom" | "left|top" | "top|120px"
	'align': {
		'test': function(attribute){
			return attribute.test(/^(left|center|right|\d+(px|\%){1}){1}\|?(top|center|bottom|\d+(px|\%){1})?$/);
		},
		'init': function(value){
			this.setStyle('position',(this.getStyle('position') != "static") ? this.getStyle('position') : 'relative');
			var left = "auto", top = "auto", right = "auto", bottom = "auto", values = value.split('|');
			switch (values[0]){
				case 'left':
					left = 0;
					break;
				case 'center':
					left = "50%";
					break;
				case 'right':
					right = 0;
					break;
				default:
					left = values[0];
					break;
			}
			if (values[1]){
				switch (values[1]){
					case 'top':
						top = 0;
						break;
					case 'center':
						top = "50%";
						break;
					case 'bottom':
						bottom = 0;
						break;
					default:
						top = values[1];
						break;
				}
			}

			SlsImage.addAttribute.call(this, {
				'align': {
					'left': left,
					'top': top,
					'right': right,
					'bottom': bottom
				}
			});
		},
		apply: function(value, force){
			if (!force)
				return true;
		}
	}
};

SlsImage.initAttributes = function(){
	for (var i = 0; i < this.attributes.length; i++){
		nodeName = this.attributes[i].nodeName;
		if (nodeName.indexOf("sls-image-") == -1)
			continue;
		nodeName = nodeName.replace("sls-image-", "");
		if (SlsImage.acceptedAttributes[nodeName] && SlsImage.acceptedAttributes[nodeName].test(this.attributes[i].nodeValue)){
			SlsImage.acceptedAttributes[nodeName].init.call(this, this.attributes[i].nodeValue);
		}
	}
};

SlsImage.addAttribute = function(attribute){
	var attributes = this.retrieve('SlsImageAttributes');
	if (typeOf(attributes) != "array")
		attributes = [];
	if (attribute.fit)
		attributes.unshift(attribute);
	else
		attributes.push(attribute);

	this.store('SlsImageAttributes', attributes);

	return this;
};

SlsImage.getAttributes = function(){
	return this.retrieve('SlsImageAttributes');
};

SlsImage.applyAttributes = function(img){
	img = img || this;
	var attributes = SlsImage.getAttributes.call(img);
	if (!attributes)
		return false;
	for (var i = 0; i < attributes.length; i++){
		for (var key in attributes[i]){
			if (SlsImage.acceptedAttributes[key].apply)
				SlsImage.acceptedAttributes[key].apply.call(img, attributes[i][key]);
		}
	}
};

SlsImage.startLoading = function(){
	SlsImage.getContainer.call(this).addClass('loading');
};

SlsImage.stopLoading = function(){
	SlsImage.getContainer.call(this).removeClass('loading');
};

SlsImage.getDimensions = function(callback){
	if (!SlsImage.isImage(this) || this.retrieve('SlsImageLoading') === true)
		return false;

	var dimensions = this.retrieve('SlsImageDimensions');
	if (!dimensions || !dimensions.width || !dimensions.height) {
		this.store('SlsImageLoading', true);
		var img = new Element('img');

		img.onload = function(){
			this.store('SlsImageDimensions', this.measure(function(){
				return {
					'width': (this.offsetWidth) ? this.offsetWidth : (this.width) ? this.width : ((this.get('width')) ? this.get('width').toInt() : ((this.clientWidth) ? this.clientWidth : false)),
					'height': (this.offsetHeight) ? this.offsetHeight : (this.height) ? this.height : ((this.get('height')) ? this.get('height').toInt() : ((this.clientHeight) ? this.clientHeight : false))
				}
			}.bind(this)));
			SlsImage.loaded.call(this, callback);
		}.pass(img, this);

		img.onerror = function(){
			console.log("ERREUR !!!");
		};

		img.src = this.get('src');
		return false;
	}
	return dimensions;
};

SlsImage.addImage = function(){
	if (SlsImage.images.indexOf(this) == -1)
		SlsImage.images.push(this);
};

SlsImage.isImage = function(element){
	return (typeOf(element) != "element" || element.tagName != "IMG") ? false : true;
};

SlsImage.resize = function(){
	SlsImage.images.each(SlsImage.applyAttributes);
};

window.addEvent('domready', function(){
	if (!!window.MooTools)
		new SlsImage();
});