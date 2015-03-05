/**
 * Generic class to build custom select
 * author : Kévin LANCIEN
 * date : 18/04/2012
 */

var Select = new Class({
	Implements: [Events, Options],

	options: {
		maxOptions: 12,
		scrollbar: {
			width : 10,
			offsetY : 0,
			offsetX : 2
		},
		selectId : '',
		forceOpeningTop: false
	},

	scrollbarActive : false,
	mouseScrollbar : false,
	keyword: '',
	optionsLiteral: [],

	initialize: function(select, options){
		this.setOptions(options);
		this.select = select;
		this.relayer = this.select.getParent('.select-relayer');

		this.build();
		this.handleKeys();
		this.setClosed();

		$$('body')[0].addEvent('click', function(){
			this.close();
		}.bind(this));
	},

	build: function(){
		// Select
		this.selectBox = new Element('div', {
			'class' : 'sls_custom_select',
			'id' : this.options.selectId,
			'styles' : {
				'position' : 'relative',
				'cursor' : 'pointer'
			},
			'events' : {
				'click' : function(e){
					e.stop();
					if (Browser.platform.ios || Browser.platform.android || Browser.platform.webos){
						if (document.createEvent) { // all browsers
							var e = document.createEvent("MouseEvents");
							e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
							worked = this.select.dispatchEvent(e);
						} else if (this.select.fireEvent) { // ie
							worked = this.select.fireEvent("onmousedown");
						}
					} else {
						if (this.dropdown.hasClass('closed')){
							$$('body')[0].fireEvent('click');
							this.open();
						} else
							this.close();
					}
				}.bind(this)
			}
		}).inject(this.select.getParent());

		this.realSelectContainer = new Element('div', {
			'styles' : {
				'position' : 'relative',
				'overflow' : 'hidden',
				'height' : 0,
				'width' : 0
			}
		}).wraps(this.select);

		this.selectValue = new Element('span', {
			'class' : 'sls_custom_select_value',
			'html' : (this.select.getElements('option:selected').length > 0) ? this.select.getElement('option:selected').get('html') : ''
		}).inject(this.selectBox);

		new Element('span', {
			'class' : 'sls_custom_select_arrow'
		}).inject(this.selectBox);
		// /Select

		// Dropdown
		this.dropdown = new Element('div', {
			'class' : 'sls_custom_select_dropdown closed',
			'styles' : {
				'overflow' : 'hidden',
				'position' : 'absolute',
				'height' : 0,
				'top' : this.selectBox.getCoordinates().height,
				'left' : -1,
				'z-index' : 99999,
				'box-sizing': 'border-box',
				'-moz-box-sizing': 'border-box'
			},
			'events' : {
				'click' : function(e){
					e.stop();
				}
			}
		}).inject(this.selectBox)
			.set('morph', {link:'cancel', duration:300});

		this.dropdownWrapper = new Element('div', {
			'class' : 'sls_custom_select_dropdown_wrapper'
		}).inject(this.dropdown);

		this.ulOptions = new Element('ul', {
			'class' : 'sls_custom_select_options',
			'events' : {
				'touchstart': function(e){
					this.dragYTouch = e.client.y;
				}.bind(this),
				'touchmove' : this.scrollTouch.bind(this),
				'gesturestart': function(e){
					this.dragYTouch = e.client.y;
				}.bind(this),
				'gesturchange' : this.scrollTouch.bind(this)
			}
		}).inject(this.dropdownWrapper);

		if (this.select.getElements('option').length > 0){
			this.select.getElements('option').each(function(realOption, index){
				if (realOption.get('html') != ''){
					this.optionsLiteral.push(realOption.get('html').toLowerCase());

					var option = new Element('li', {
						'class' : (realOption.get('selected')) ? 'sls_custom_select_option selected' : 'sls_custom_select_option',
						'styles' : {
							'cursor' : 'pointer'
						},
						'events' : {
							'click' : function(){
								// Close the dropdown if the select isn't multiple
								if (!this.select.get('multiple')){
									this.setClosed();
									this.ulOptions.getElements('.selected').removeClass('selected');
									option.addClass('selected');
									this.selectValue.set('html', realOption.get('html'));
									realOption.set('selected', 'selected');
								} else {
									if (option.hasClass('selected')){
										option.removeClass('selected');
										realOption.erase('selected');
									} else {
										option.addClass('selected');
										realOption.set('selected','selected');
									}
									var indexValue = 0;
									var value = '';

									this.select.getElements('option').each(function(option){
										if (option.get('selected')){
											value += (indexValue == 0) ? option.get('html') : ' / '+option.get('html');
											indexValue++;
										}
									});
									this.selectValue.set('html', value);
								}

								if (this.select.onchange)
									this.select.onchange();
								this.select.fireEvent('change');

								if (this.relayer){
									this.relayer.fireDelegatedEvent('change', this.select);
								}
							}.bind(this)
						}
					}).inject(this.ulOptions);

					var optionLabel = new Element('span', {
						'class' : 'sls_custom_select_option_label',
						'html' : realOption.get('html'),
						'styles' : {
							'white-space' : 'nowrap'
						}
					}).inject(option);

					var optionValue = new Element('span', {
						'class' : 'sls_custom_select_option_value display_none',
						'html' : realOption.get('value'),
						'styles' : {
							'display' : 'none'
						}
					}).inject(option);
				}
			}.bind(this));
		}

		this.dropdownDimensions = this.ulOptions.measure(function(){
			return this.getDimensions(true);
		});

		if (this.selectBox.getDimensions(true).width > this.dropdownDimensions.width){
			this.dropdownDimensions.width = this.selectBox.getDimensions(true).width;
			this.dropdown.setStyles({
				'width' : this.dropdownDimensions.width
			});
		}

		// Build scrollbar if too many options
		if (this.ulOptions.getElements('li').length > this.options.maxOptions){
			this.buildScrollbar();
			this.scrollbarActive = true;
		}
		// /Dropdown

		this.select.addEvent('change', function(){
			var realOptions = this.select.getElements('option');
			var fakeOptions = this.ulOptions.getElements('li');
			var value = '';
			fakeOptions.filter('.selected').removeClass('selected');

			realOptions.filter(':selected').each(function(realOption, index){
				var index = realOptions.indexOf(realOption);
				fakeOptions[index].addClass('selected');
				if (value == '')
					value = realOption.get('html');
				else
					value += ' / '+realOption.get('html');
			});

			this.selectValue.set('html', value);
		}.bind(this));
	},

	open: function(){
		var windowBottomY = window.getScrollTop()+window.getCoordinates().height;
		var dropdownY = this.dropdown.getCoordinates().top;

		if (dropdownY + this.dropdownDimensions.height > windowBottomY)
			this.dropdown.setStyles({'top' : 'auto', 'bottom' : this.selectBox.getCoordinates().height});
		else
			this.dropdown.setStyles({'bottom' : 'auto', 'top' : this.selectBox.getCoordinates().height});

		this.dropdown
			.morph({'height' : this.dropdownDimensions.height, 'border-width' : 1})    
			.addClass('open')
			.removeClass('closed');

		this.selectBox
			.addClass('open')
			.removeClass('closed');

		this.dropdownState = true;

		this.select.fireEvent('focus');
	},

	close: function(){
		this.dropdown
			.morph({'height' : 0, 'border-width' : 0})					
			.addClass('closed')
			.removeClass('open');

		this.selectBox
			.addClass('closed')
			.removeClass('open');

		if (this.scrollbarActive){
			this.ulOptions.setStyles({'margin-top' : 0});
			this.scrollbar.setStyles({'top' : this.scrollbar.retrieve('offsetY')});
		}

		this.ulOptions.getElements('li').removeClass('hover');
		this.dropdownState = false;
		this.optionHover = null;

		this.select.fireEvent('blur');
	},

	setClosed: function(){
		this.dropdown
			.setStyles({'height' : 0, 'border-width' : 0})					
			.addClass('closed')
			.removeClass('open');

		this.selectBox
			.addClass('closed')
			.removeClass('open');

		if (this.scrollbarActive){
			this.ulOptions.setStyles({'margin-top' : 0});
			this.scrollbar.setStyles({'top' : this.scrollbar.retrieve('offsetY')});
		}

		this.ulOptions.getElements('li').removeClass('hover');
		this.dropdownState = false;
		this.optionHover = null;

		this.select.fireEvent('blur');
	},

	buildScrollbar: function(){
		var optionHeight = this.dropdown.measure(function(){
			return this.getElements('li')[0].getDimensions(true).height;
		});

		var ulOptionsDimensions = this.dropdown.measure(function(){
			return this.getElement('ul').getDimensions(true);
		});

		this.dropdownDimensions.height = optionHeight * this.options.maxOptions;
		this.dropdown.setStyles({
			'padding-right' : this.options.scrollbar.width+this.options.scrollbar.offsetX
		});

		var offsetY = parseInt(this.ulOptions.getStyle('padding-top'))+parseInt(this.ulOptions.getStyle('margin-top'));
		var ratio = (this.dropdownDimensions.height / ulOptionsDimensions.height);

		this.scrollbar = new Element('div', {
			'class' : 'sls_custom_select_scrollbar',
			'styles' : {
				'width' : this.options.scrollbar.width,
				'height' : ratio * this.dropdownDimensions.height,
				'position' : 'absolute',
				'right' : this.options.scrollbar.offsetX,
				'top' : offsetY + this.options.scrollbar.offsetY
			}
		}).inject(this.dropdown)
			.store('offsetY', offsetY + this.options.scrollbar.offsetY);

		this.dropdown.addEvent('mousewheel', this.mouseScroll.bind(this));

		Element.Events.drag = {
			base: 'mousemove',
			condition: function(e){
				return this.mouseScrollbar;
			}.bind(this)
		};

		this.scrollbar.addEvents({
			'mousedown' : function(e){
				e.stop();
				this.mouseScrollbar = true;
				this.dragY = this.scrollbar.getCoordinates().top-e.page.y;
			}.bind(this),
			'mouseup' : function(){
				this.mouseScrollbar = false;
			}.bind(this),
			'touchstart': function(e){
				this.dragYTouch = e.client.y;
			}.bind(this),
			'touchmove' : this.dragTouch.bind(this),
			'gesturestart': function(e){
				this.dragYTouch = e.client.y;
			}.bind(this),
			'gesturchange' : this.dragTouch.bind(this)
		});

		window.addEvents({
			mouseup: function(){
				this.mouseScrollbar = false;
			}.bind(this),
			drag: this.drag.bind(this)
		});
	},

	mouseScroll: function(event){
		if (!event || typeOf(event) != 'domevent')
			return false;
		event.stop();

		var ulOptionsDimensions = this.dropdown.measure(function(){
				return this.getElement('ul').getDimensions(true);
			}),
			ratio = (this.dropdownDimensions.height / ulOptionsDimensions.height),
			ulMarginTop = parseInt(this.ulOptions.getStyle('margin-top'));

		if (event.wheel > 0 && ulMarginTop < 0){
			ulMarginTop += (ulMarginTop + 20 <= 0) ? 20 : Math.abs(ulMarginTop) - 20;
			this.ulOptions.setStyles({
				'margin-top' : ulMarginTop
			});
		} else if (event.wheel < 0 && (ulMarginTop + ulOptionsDimensions.height) >= this.dropdownDimensions.height) {
			ulMarginTop -= ((ulMarginTop + ulOptionsDimensions.height) - 20 >= this.dropdownDimensions.height) ? 20 : (ulMarginTop + ulOptionsDimensions.height) - this.dropdownDimensions.height;
			this.ulOptions.setStyles({
				'margin-top' : ulMarginTop
			});
		} else
			return;

		this.scrollbar.setStyles({
			'top' : ratio * Math.abs(ulMarginTop) - this.scrollbar.retrieve('offsetY')
		});
	},

	drag: function(e){
		e.stop();
		var Y = e.page.y-this.dropdown.getCoordinates().top;
		var dy = this.dragY;
		var y = Y+dy;
		var H = this.ulOptions.getCoordinates().height;
		var h = this.dropdown.getCoordinates().height;
		var hS = this.scrollbar.getCoordinates().height;
		var Hm = H-h;
		var hmS = h-hS;
		var ratio = y/hmS;

		if (y >= 0 && y <= hmS){
			this.scrollbar.setStyles({'top' : y});
			this.ulOptions.setStyles({'margin-top' : -ratio*Hm});
		}
	},

	dragTouch: function(e){
		e.stop();
		var y = parseInt(this.scrollbar.getStyle('top'))-(this.dragYTouch- e.client.y);
		var H = this.ulOptions.getCoordinates().height;
		var h = this.dropdown.getCoordinates().height;
		var hS = this.scrollbar.getCoordinates().height;
		var Hm = H-h;
		var hmS = h-hS;
		var ratio = y/hmS;
		this.dragYTouch = e.client.y;

		if (y >= 0 && y <= hmS){
			this.scrollbar.setStyles({'top' : y});
			this.ulOptions.setStyles({'margin-top' : -ratio*Hm});
		}
	},

	scrollTouch: function(e){
		e.stop();

		var ulOptionsHeight = this.dropdown.measure(function(){
			return this.getElements('ul')[0].getDimensions(true).height;
		});
		var dropdownHeight = this.dropdown.measure(function(){
			return this.getDimensions(true).height;
		});

		var ratio = (dropdownHeight / ulOptionsHeight);

		var marginTop = parseInt(this.ulOptions.getStyle('margin-top'))-(this.dragYTouch-e.client.y);
		this.ulOptions.setStyles({
			'margin-top' : marginTop
		});
		if (marginTop > 0){
			this.ulOptions.morph({'margin-top' : 0});
			if (this.scrollbar)
				this.scrollbar.morph({'top' : 0});
		} else if (marginTop < -(ulOptionsHeight-dropdownHeight)) {
			this.ulOptions.morph({'margin-top' : -(ulOptionsHeight-dropdownHeight)});
			if (this.scrollbar)
				this.scrollbar.morph({'top' : dropdownHeight-this.scrollbar.getDimensions().height});
		}

		this.scrollbar.setStyles({
			'top' : ratio * Math.abs(parseInt(this.ulOptions.getStyle('margin-top'))) - this.scrollbar.retrieve('offsetY')
		});
		this.dragYTouch = e.client.y;
	},

	updateScrollbar: function(){
		var content = {
			height 	: this.ulOptions.getCoordinates().height,
			area 	: this.ulOptions.getCoordinates().height - this.dropdown.getCoordinates().height,
			marginTop : parseInt(this.ulOptions.getStyle('margin-top'))
		};
		var wrapper = { height : this.dropdown.getCoordinates().height};
		var scrollbar = {
			height	: this.scrollbar.getCoordinates().height,
			area	: wrapper.height - this.scrollbar.getCoordinates().height
		};
		var ratio = scrollbar.area / content.area;

		this.scrollbar.setStyles({
			'top' : Math.abs(content.marginTop) * ratio
		});
	},

	handleKeys: function(){
		Element.Events.selectKeydown = {
			base: 'keydown',
			condition: function(e){
				return this.dropdownState;
			}.bind(this)
		};

		document.addEvents({
			"selectKeydown": function(e){
				e.stop();
				var options = this.ulOptions.getElements('li');
				var realOptions = this.select.getElements('option').filter(function(option){
					if (option.get('html') != '') return option;
				});

				switch (e.key){
					case 'esc' :
						this.setClosed();
						break;
					case 'enter':
						if (this.optionHover != null){
							var realOption = realOptions[this.optionHover];

							if (!this.select.get('multiple')){
								this.ulOptions.getElements('.selected').removeClass('selected');
								options[this.optionHover].removeClass('hover').addClass('selected');
								this.selectValue.set('html', realOption.get('html'));
								realOption.set('selected', 'selected');
								this.setClosed();

							} else {

								if (options[this.optionHover].hasClass('selected')){
									options[this.optionHover].removeClass('hover').removeClass('selected');
									realOption.erase('selected');

								} else {
									options[this.optionHover].removeClass('hover').addClass('selected');
									realOption.set('selected', 'selected');

								}

								var value = "";
								var indexValue = 0;
								this.select.getElements('option').each(function(option){
									if (option.get('selected')){
										value += (indexValue == 0) ? option.get('html') : ' / '+option.get('html');
										indexValue++;
									}
								});
								this.selectValue.set('html', value);
							}
						}
						break;
					case 'up' :
						if (this.optionHover == null){
							options.getLast().addClass('hover');
							this.optionHover = options.length-1;
						} else {
							options[this.optionHover].removeClass('hover');
							this.optionHover = (this.optionHover-1 < 0) ? options.length-1 : this.optionHover-1;
							options[this.optionHover].addClass('hover');
						}
						this.stayFocused(options[this.optionHover]);
						break;
					case 'down' :
						if (this.optionHover == null){
							options[0].addClass('hover');
							this.optionHover = 0;
						} else {
							options[this.optionHover].removeClass('hover');
							this.optionHover = (this.optionHover+1 >= options.length) ? 0 : this.optionHover+1;
							options[this.optionHover].addClass('hover');
						}
						this.stayFocused(options[this.optionHover]);
						break;
					default:
						if (e.key == 'space') e.key = ' ';
						this.keyword += e.key;
						this.findOptionByString();
						clearTimeout(this.timerKeydown);
						this.timerKeydown = this.clearKeyword.delay(1000, this);
						break;
				}
			}.bind(this)
		});
	},

	stayFocused: function(option){
		var li = {
			object : option,
			top : option.getCoordinates(this.ulOptions).top,
			bottom : option.getCoordinates(this.ulOptions).bottom
		};

		var conditionTop = li.top < Math.abs(parseInt(this.ulOptions.getStyle('margin-top')));
		var conditionBottom = li.bottom > (this.dropdownDimensions.height + Math.abs(parseInt(this.ulOptions.getStyle('margin-top'))));

		if (conditionTop || conditionBottom){
			if (conditionTop)
				this.ulOptions.setStyles({'margin-top' : -li.top});
			else if (conditionBottom)
				this.ulOptions.setStyles({'margin-top' : -(li.bottom - this.dropdownDimensions.height)});

			this.updateScrollbar();
		}
	},

	findOptionByString: function(){
		var regex = new RegExp('^'+this.keyword+'\.*', 'gi');

		for (var i = 0; i < this.optionsLiteral.length; i++){
			if (regex.test(this.optionsLiteral[i])){
				this.stayFocused(this.ulOptions.getElements('li')[i]);
				this.ulOptions.getElements('li.hover').removeClass('hover');
				this.ulOptions.getElements('li')[i].addClass('hover');
				this.optionHover = i;
				return true;
			}
		}
	},

	clearKeyword: function(){
		this.keyword = '';
	}
});