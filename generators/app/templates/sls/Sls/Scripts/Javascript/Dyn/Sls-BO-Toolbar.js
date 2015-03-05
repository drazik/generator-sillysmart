var Toolbar = new Class({
	Implements: [Events],

	options: {
		devSections: {
			marginSpace: 8
		}
	},

	initialize: function () {
		this.toolbar = $('sls-bo-toolbar');
		if (!this.toolbar)
			throw new Error("Toolbar: the main toolbar element is missing !");
		this.toolbarWrapper = this.toolbar.getElement('.sls-bo-toolbar-wrapper');
		this.toolbarWrapperScroll = this.toolbar.getElement('.sls-bo-toolbar-wrapper-scroll');
		this.toolbarContent = this.toolbarWrapper.getElement('.sls-bo-toolbar-content');
		this._scrollbar = new Scrollbar(this.toolbar, this.toolbarWrapper, this.toolbarWrapperScroll, this.toolbarContent, {
			offset: {
				y: {
					top: 100,
					bottom: 30
				},
				x: {
					right: 14
				}
			}
		});
		this.boActionsList = this.toolbar.getElement('.sls-bo-toolbar-sections');
		this.togglerBtn = this.toolbar.getElement('.toggler');
		this.modulesSwitchers = this.toolbar.getElements('.sls-bo-toolbar-module-switchers li');
		this.modules = this.toolbar.getElements('.sls-bo-toolbar-module');
		this.devSections = new Elements([this.toolbar]);

		if (!window.SlsView)
			window.SlsView = {
				expanded: false
			};
		this.opened = SlsView.expanded ? false : true;

		if (this.modules.filter('.sls-bo-toolbar-developer').length)
			this.initDeveloperToolbar();

		this.initEvents();

		this.checkSelected();
		this.fireEvent('updated');
	},

	initEvents: function () {
		this.boActionsList.addEvents({
			'click:relay(div.section-item, .category-title)': this.toggleMenuList.bind(this),
			'mousedown:relay(div.section-item, .category-title)': this.preventMouseSelection.bind(this),
			'click:relay(.section-filters .all)': this.showAll.bind(this),
			'click:relay(.section-filters .favorite)': this.showOnlyFavorites.bind(this),
			'click:relay(.favorite)': this.toggleLike.bind(this)
		});

		// Set all morphs of the toolbar's wrappers to have a duration of 250ms
		this.boActionsList.getElements('.categories-wrapper, .items-wrapper').each(function (wrapper) {
			wrapper.get('morph').setOptions({'duration': 250});
		});

		this.toolbarWrapper.get('morph').setOptions({'duration': 250});
		this.toolbarContent.get('morph').setOptions({'duration': 250});
		this._scrollbar.container.get('morph').setOptions({'duration': 250});

		if (this.togglerBtn) {
			this.togglerBtn.addEvents({
				'click': this.showBar.bind(this),
				'mouseenter': this.showBar.bind(this)
			});
		}

		this.toolbar.addEvent('mouseleave', this.hideBarDelayed.bind(this));
		this.toolbarWrapper.addEvent('mouseenter', this.showBar.bind(this));

		this.addEvent('updated', this._scrollbar.update.bind(this._scrollbar));
		this.toolbar.addEvent('updated', this._scrollbar.update.bind(this._scrollbar));

		if (!window._fixedToolbars) {
			this.refreshHeight();
			window.addEvent('resize', this.refreshHeight.bind(this));
			this.addEvent('updated', this.refreshHeight.bind(this));
		}

		if (this.modules.length && this.modulesSwitchers.length) {
			this.modulesSwitchers.addEvents({
				'click': this.toggleModule.bind(this),
				'mousedown': this.preventMouseSelection.bind(this)
			});
			this.toolbar.addEvent('click:relay(.sls-bo-toolbar-developer li, .sls-bo-toolbar-developer-section li)', this.toggleDevSection.bind(this));
		}

		window.addEvents({
			'XMLCopied': this.displayXMLCallback.bind(this),
			'SlsViewChanged': this.updateOpenedStatus.bind(this)
		});
	},

	updateOpenedStatus: function(expanded){
		if (this.opened && expanded)
			this.hideBar(true);
		if (expanded == this.opened)
			this.opened = !this.opened;
	},

	refreshHeight: function () {
		if (!this.modules.filter('.module-opened').length && this.isToolbarOpened())
			return this.closeInstantToolbar();

		var maxHeight = window.getHeight() - this.toolbar.getPosition().y;
		var realHeight = this.toolbarContent.measure(function () {
			return this.getComputedSize().totalHeight;
		});
		var height = (realHeight > maxHeight) ? maxHeight : 'auto';
		this.toolbar.setStyles({'height': height});
		this._scrollbar.update();
	},

	showBar: function () {
		if (this.togglingTimer)
			clearInterval(this.togglingTimer);

		if (this.opened || !SlsView.expanded)
			return false;

		this.toolbarWrapper.setStyles({
			'opacity': 0,
			'left': -50
		});
		this._scrollbar.container.setStyles({
			'opacity': 0
		});
		this.toolbar.addClass('opened');
		this.opened = true;
		this.toolbarWrapper
			.get('morph')
			.start({
				'opacity': 1,
				'left': 0
			});
		this._scrollbar.container
			.get('morph')
			.cancel()
			.start({
			'right': [50, 0],
			'opacity': 1
		});
	},

	hideBarDelayed: function () {
		this.togglingTimer = this.hideBar.delay(1000, this);
	},

	hideBar: function (force) {
		if (!this.opened || !SlsView.expanded)
			return false;

		if (this.togglingTimer)
			clearInterval(this.togglingTimer);

		this.toolbarWrapper
			.get('morph')
			.start({
				'opacity': 0,
				'left': -50
			})
			.chain(function () {
				this.toolbar.removeClass('opened');
				this.toolbarWrapper.setStyles({
					'opacity': null,
					'left': null
				});
				this.opened = false;
			}.bind(this));
		this._scrollbar.container
			.get('morph')
			.cancel()
			.start({
				'right': (force && !SlsView.expanded) ? 50 : 0,
				'opacity': 0
			})
			.chain(function () {
				this.set({
					'right': 0,
					'opacity': 1
				});
			});
	},

	showAll: function () {
		this.boActionsList.getElements('.hidden').removeClass('hidden');
		if (window._fixedToolbars)
			_fixedToolbars.fireEvent('updated');
		else
			this.fireEvent('updated');
	},

	showOnlyFavorites: function (event) {
		var section;
		if (typeOf(arguments[0]) == "domevent") {
			arguments[0].stop();
			section = event.target.getParent('li');
		} else if (typeOf(arguments[0]) == "element") {
			section = arguments[0].getParent('li');
		} else
			return false;

		var items = section.getElements('.category-item');

		items.each(function (item) {
			var li = item.getParent();
			if (item.hasClass('category-title')) {
				var subItems = li.getElements('.sls-bo-toolbar-items > li');
				var count = subItems.length;
				subItems.each(function (subItem) {
					if (!subItem.getElement('.favorite.liked')) {
						subItem.addClass('hidden');
						count--;
					}
				});
				if (count == 0)
					li.addClass('hidden');
			} else {
				if (!item.getNext('.favorite.liked'))
					li.addClass('hidden');
			}
		});

		if (window._fixedToolbars)
			_fixedToolbars.fireEvent('updated');
		else
			this.fireEvent('updated');
	},

	toggleLike: function () {
		var btnLike, post = {};
		if (arguments.length == 0)
			return false;
		else if (typeOf(arguments[0]) == "domevent")
			btnLike = arguments[0].target;

		if (!btnLike || !btnLike.hasClass('favorite'))
			return false;

		post.Like = (!btnLike.hasClass('liked')) ? "true" : "false";
		post.Db = btnLike.get('sls-db');
		post.Model = btnLike.get('sls-entity');
		post['sls-request'] = 'async';

		new Request.JSON({
			'url': urls.like,
			'data': post,
			'onSuccess': function (xhr) {
				if (xhr.status == "OK") {
					if (xhr.result == "false")
						btnLike.removeClass('liked');
					else
						btnLike.addClass('liked');
				}
			}
		}).send();
	},

	preventMouseSelection: function (event) {
		if (event || typeOf(event) == 'domevent')
			event.preventDefault();
	},

	toggleMenuList: function () {
		var trigger, menu, wrapper, content;
		if (arguments.length == 0)
			return false;
		else if (typeOf(arguments[0]) == "domevent") {
			trigger = arguments[0].target;
			arguments[0].preventDefault();
		} else if (typeOf(arguments[0]) == "element")
			trigger = arguments[0];

		if (!trigger.hasClass('section-item') && !trigger.hasClass('category-title'))
			return false;

		menu = trigger.getParent('li');
		wrapper = trigger.getNext('.categories-wrapper, .items-wrapper');
		content = wrapper.getElement('.categories-content, .items-content');

		if (!menu.hasClass('opened')) {
			wrapper
				.get('morph')
				.start({
					'height': content.getDimensions().height
				})
				.chain(function () {
					wrapper
						.setStyles({
							'height': 'auto'
						});
					if (window._fixedToolbars)
						_fixedToolbars.fireEvent('updated');
					else
						this.fireEvent('updated');
				}.bind(this));

			menu.addClass('opened');
		} else {
			wrapper
				.get('morph')
				.start({
					'height': [content.getDimensions().height, 0]
				})
				.chain(function () {
					if (window._fixedToolbars)
						_fixedToolbars.fireEvent('updated');
					else
						this.fireEvent('updated');
				}.bind(this));
			menu.removeClass('opened');
		}
	},

	checkSelected: function () {
		var selected = this.boActionsList.getElement('.selected');
		if (!selected)
			return false;

		var wrappers = selected.getParents('.categories-wrapper, .items-wrapper');
		var menus = selected.getParents('li');
		wrappers.setStyles({
			'height': 'auto'
		});
		menus.addClass('selected').addClass('opened');

		this.boActionsList.getElements('.section-filters .current').each(function (filter) {
			if (filter.hasClass('favorite'))
				this.showOnlyFavorites(filter);
			else if (filter.hasClass('all'))
				this.showAll();
		}.bind(this));
	},

	toElement: function () {
		return this.toolbar;
	},

	toggleModule: function (arg) {
		var switcher = null;
		if (typeOf(arg) == 'domevent' && (arg.target.hasClass('sls-bo-toolbar-module-switcher') || arg.target.getParent('.sls-bo-toolbar-module-switcher')))
			switcher = arg.target.hasClass('sls-bo-toolbar-module-switcher') ? arg.target : arg.target.getParent('.sls-bo-toolbar-module-switcher');
		else if (typeOf(arg) == 'element' && arg.hasClass('sls-bo-toolbar-module-switcher'))
			switcher = arg;
		else
			throw new Error("Toolbar: wrong or missing parameter !");

		var module = this.modules.filter('.' + switcher.get('class').match(/sls\-bo\-toolbar\-module\-switcher\-\w+/)[0].replace(/\-module-switcher/, ''))[0];
		if (!module)
			throw new Error("Toolbar: unknow module !");
		if (module.hasClass('module-opened'))
			this.closeModule(module, true);
		else
			this.openModule(module);
	},

	openInstantToolbar: function () {
		this.toolbar.removeClass('closed');
	},

	closeInstantToolbar: function () {
		this.toolbar.addClass('closed');
	},

	isToolbarOpened: function () {
		return !this.toolbar.hasClass('closed');
	},

	openModule: function (module) {
		if (typeOf(module) != 'element' || !module.hasClass('sls-bo-toolbar-module'))
			throw new TypeError("Toolbar: this element is not a valid module");

		if (this.modules.filter('.module-opened').length)
			this.closeModule(this.modules.filter('.module-opened')[0]);
		else
			this.openInstantToolbar();
		module.addClass('module-opened');

		this.refreshHeight();
	},

	closeModule: function (module, closeToolbar) {
		if (typeOf(module) != 'element' || !module.hasClass('sls-bo-toolbar-module'))
			throw new TypeError("Toolbar: this element is not a valid module");

		module.removeClass('module-opened');

		if (closeToolbar)
			this.closeInstantToolbar();

		if (module.hasClass('sls-bo-toolbar-developer') && this.devSections.length > 1){
			this.closeDevSection(this.devSections[1]);
			var selectedLis = this.modules.filter('.sls-bo-toolbar-developer')[0].getElements('li.selected');
			if (selectedLis.length)
				selectedLis.removeClass('selected');
		}
	},

	toggleDevSection: function (arg) {
		var toggler = null;
		if (typeOf(arg) == 'domevent' && (arg.target.tagName.toLowerCase() == 'li' || arg.target.getParent('li'))) {
			toggler = arg.target.tagName.toLowerCase() == 'li' ? arg.target : arg.target.getParent('li');
			if (!toggler.getElement('ul.sls-bo-toolbar-developer-sub-sections'))
				toggler = null;
		}
		if (!toggler)
			return;
		var devSection = toggler.getParent('.sls-bo-toolbar-developer-section') || this.toolbar;
		var devSectionIndex = (this.devSections.indexOf(devSection) != -1) ? this.devSections.indexOf(devSection) : 0;

		if (toggler.hasClass('selected')) {
			toggler.removeClass('selected');
			if (devSectionIndex + 1 < this.devSections.length)
				this.closeDevSection(this.devSections[devSectionIndex + 1]);
		} else {
			if (devSection) {
				var selectedItem = devSection.getElement('.selected');
				if (selectedItem)
					selectedItem.removeClass('selected');
			}
			toggler.addClass('selected');
			this.openDevSection(toggler);
		}
	},

	openDevSection: function (toggler) {
		if (typeOf(toggler) != 'element' || toggler.tagName.toLowerCase() != 'li')
			throw new TypeError("Toolbar: wrong or missing parameter !");

		var ul = toggler.getElement('ul.sls-bo-toolbar-developer-sub-sections');
		if (!ul)
			throw new Error("Toolbar: missing HTML structure to build the element list !");

		// Close other sections
		var togglerSection = toggler.getParent('.sls-bo-toolbar-developer-section') || this.toolbar;
		var togglerSectionIndex = togglerSection ? this.devSections.indexOf(togglerSection) : 0;
		if (this.devSections.length >= 2 && togglerSectionIndex < this.devSections.length - 1)
			this.closeDevSection(this.devSections[togglerSectionIndex + 1]);

		var value = (ul.hasClass('end-value') && ul.getElement('li').get('html').test(/^\<div/)) ? ul.getElement('li').get('html') : ul.outerHTML;
		var template = (value.test(/^\<div/)) ? this.devSectionCodeHTML : ((ul.hasClass('end-value')) ? this.devSectionValueHTML : this.devSectionHTML);
		var devSection = Elements.from(template.substitute({item: value}))[0];
		this.devSections.push(devSection);
		var previousSection = (this.devSections.indexOf(devSection) > 0) ? this.devSections[this.devSections.indexOf(devSection) - 1] : this.toolbar;

		devSection
			.setStyles({
				'left': previousSection.getPosition().x + previousSection.getComputedSize().totalWidth + this.options.devSections.marginSpace
			})
			.inject(this.toolbar);

		if (!ul.hasClass('end-value')){
			new Scrollbar(devSection,
				devSection.getElement('.sls-bo-toolbar-developer-section-wrapper'),
				devSection.getElement('.sls-bo-toolbar-developer-section-wrapper-scroll'),
				devSection.getElement('.sls-bo-toolbar-developer-section-content'),
				{
					offset: {
						y: {
							top: 35,
							bottom: 35
						},
						x: {
							right: 8
						}
					},
					position: 'right'
				});
			this.refreshDevSectionsHeight();
		}
	},

	closeDevSection: function (section) {
		if (typeOf(section) != 'element')
			throw new TypeError("Toolbar: wrong or missing parameter !");
		var sectionIndex = this.devSections.indexOf(section);
		if (sectionIndex == -1)
			throw new Error("Toolbar: section not registered in the developer sections array.");

		if (sectionIndex == 0) sectionIndex++;
		for (var i = sectionIndex; i < this.devSections.length; i++)
			this.devSections[i].destroy();
		this.devSections.splice(sectionIndex);
	},

	initDeveloperToolbar: function () {
		var initSyntaxHighlighter = function () {
			if (window.SyntaxHighlighter) {
				this.modules.filter('.sls-bo-toolbar-developer')[0].getElements('.end-value li').each(function (pre) {
					SyntaxHighlighter.highlight(pre);
				});
			} else
				initSyntaxHighlighter.delay(500);
		}.bind(this);
		initSyntaxHighlighter();

		ZeroClipboard.setMoviePath('//'+slsBuild.site.domainName+'/'+slsBuild.paths.coreJsDyn+'ZeroClipboard/ZeroClipboard.swf');
		var clip = new ZeroClipboard.Client();
		clip.setHandCursor(true);
		clip.setText(html_entity_decode(slsBuild.xml));
		clip.glue($('XML-to-clipboard'), $('XML-to-clipboard'));

		if (!('refreshDevSections' in window.retrieve('events'))){
			Element.Events.refreshDevSections = {
				base: 'resize'
			};

			window.addEvent('refreshDevSections', this.refreshDevSectionsHeight.bind(this));
		}
	},

	refreshDevSectionsHeight: function(){
		if (!this.devSections || this.devSections.length < 2)
			return false;

		var height = null,
			maxHeight = null,
			top = null,
			windowHeight = window.getHeight(),
			devSectionsLength = this.devSections.length;
		for (var i = 1; i < devSectionsLength; i++, height = null, maxHeight = null, top = null){
			if (this.devSections[i].hasClass('code-section') || this.devSections[i].hasClass('value'))
				continue;
			top = this.devSections[i].getPosition().y;
			maxHeight = windowHeight - top;
			height = this.devSections[i].getElement('.sls-bo-toolbar-developer-section-content').measure(function(){
				return this.getComputedSize().totalHeight;
			});
			this.devSections[i].setStyle('height', (height >= maxHeight) ? maxHeight : 'auto');
		}

		window.fireEvent('refreshScrollbars');
	},

	displayXMLCallback: function(){
		var XMLButton = $('XML-to-clipboard');

		if (!XMLButton)
			return false;
		var XMLButtonText = XMLButton.getElement('.text');
		var text = XMLButtonText.get('html');
		XMLButtonText.set('html', 'copied !');
		setTimeout(function(){
			XMLButtonText.set('html', text);
		}, 1000);
	},

	devSectionHTML: '<div class="sls-bo-toolbar-developer-section">' +
						'<div class="sls-bo-toolbar-developer-section-wrapper">' +
							'<div class="sls-bo-toolbar-developer-section-wrapper-scroll">' +
								'<div class="sls-bo-toolbar-developer-section-content">' +
									'{item}' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>',

	devSectionValueHTML: '<div class="sls-bo-toolbar-developer-section value">{item}</div>',

	devSectionCodeHTML: '<div class="sls-bo-toolbar-developer-section code-section">{item}</div>'
});

var Scrollbar = new Class({
	Implements: [Options],

	options: {
		offset: {
			y: {
				top: 0,
				bottom: 0
			},
			x: {
				left: 0,
				right: 0
			}
		},
		position: 'right' // left / right
	},

	cssSelector: 'sls-bo-',
	mouseActive: false,

	initialize: function (block, wrapper, wrapperScroll, content, options) {
		this.setOptions(options);
		this.block = block;
		this.wrapper = wrapper;
		this.wrapperScroll = wrapperScroll;
		this.content = content;

		this.build();
		this.initEvents();

		if (Scrollbar.instances)
			Scrollbar.instances.push(this);
	},

	build: function () {
		this.container = new Element('div.' + this.cssSelector + 'scrollbar').inject(this.block);
		var containerStyles = {
			'position': 'absolute',
			'overflow': 'hidden'
		};

		if (this.options.position == 'left')
			containerStyles.left = 0;
		else if (this.options.position == 'right' || ['right', 'left'].indexOf(this.options.position) == -1)
			containerStyles.right = 0;
		containerStyles.top = 0 + (typeOf(this.options.offset.y.top) == 'number') ? this.options.offset.y.top : 0;
		containerStyles.bottom = 0 + (typeOf(this.options.offset.y.bottom) == 'number') ? this.options.offset.y.bottom : 0;
		containerStyles.marginLeft = (typeOf(this.options.offset.x.left) == 'number') ? this.options.offset.x.left : 0;
		containerStyles.marginRight = (typeOf(this.options.offset.x.right) == 'number') ? this.options.offset.x.right : 0;
		this.container.setStyles(containerStyles);
		this.knob = new Element('div.' + this.cssSelector + 'scrollbar-knob', {
			'styles': {
				'position': 'absolute',
				'top': 0,
				'height': 0
			}
		}).inject(this.container);

		this.update();
	},

	initEvents: function () {
		this.block.addEvent('mousewheel', this.scroll.bind(this));
		this.wrapperScroll.addEvent('scroll', this.update.bind(this));

		Element.Events.drag = {
			base: 'mousemove',
			condition: function (e) {
				return this.mouseActive;
			}.bind(this)
		};

		this.knob.addEvents({
			'mousedown': function (e) {
				e.stop();
				this.mouseActive = true;
				this.dragY = this.knob.getCoordinates().top - e.page.y;
			}.bind(this)
		});

		window.addEvents({
			mouseup: function () {
				this.mouseActive = false;
			}.bind(this),
			drag: this.drag.bind(this)
		});

		this.block.addEvents({
			'onScrollbarActivation': this.activateScrollbar.bind(this),
			'onScrollbarDeactivation': this.deactivateScrollbar.bind(this)
		});

		if (!('refreshScrollbars' in window.retrieve('events'))){
			Element.Events.refreshScrollbars = {
				base: 'resize',
				condition: function () {
					return !!Scrollbar.instances.length;
				}
			};

			window.addEvent('refreshScrollbars', Scrollbar.refreshAll);
		}
	},

	drag: function (e) {
		e.stop();
		var Y = e.page.y - this.container.getCoordinates().top;
		var dy = this.dragY;
		var y = Y + dy;
		var blockScrollable = this.dimensions.content - this.dimensions.wrapper;
		var scrollbarScrollable = this.dimensions.container - this.dimensions.knob;
		var ratio = y / scrollbarScrollable;

		if (y >= 0 && y <= scrollbarScrollable) {
			this.knob.setStyles({'top': y});
			this.wrapperScroll.scrollTo(0, ratio * blockScrollable);
			this.update();
		}
	},

	scroll: function (event) {
		if (this.activated)
			event.stop();
		else
			return;

		var scroll = this.wrapperScroll.getScrollTop();
		if (event.wheel == 1) { // Scroll up
			scroll -= 100;
		} else { // Scroll down
			scroll += 100;
		}

		this.wrapperScroll.scrollTo(0, scroll);
		this.update();
	},

	deactivateScrollbar: function () {
		this.container
			.get('morph')
			.start({
				'opacity': 0
			})
			.chain(function () {
				this.container.setStyle('visibility', 'hidden');
			}.bind(this));
		try {
			this.wrapperScroll.setStyle('overflowY', 'visible');
		} catch (e){

		}

		this.knob.setStyles({
			'height': this.dimensions.container,
			'top': 0
		});

		this.activated = false;
	},

	activateScrollbar: function () {
		this.container
			.get('morph')
			.start({
				'opacity': 1
			})
			.chain(function () {
				this.container.setStyle('visibility', 'visible');
			}.bind(this));
		try {
			this.wrapperScroll.setStyle('overflowY', 'scroll');
		} catch (e){

		}

		this.activated = true;
	},

	update: function () {
		this.dimensions = {
			'container': this.block.measure(function () {
				return this.container.getComputedSize().totalHeight;
			}.bind(this)),
			'block': this.block.getComputedSize().totalHeight,
			'wrapper': this.wrapper.getComputedSize().totalHeight,
			'content': this.block.measure(function () {
				return this.content.getComputedSize().totalHeight;
			}.bind(this))
		};
		this.dimensions.scrollable = this.wrapperScroll.getScrollHeight() - this.dimensions.wrapper;

		this.dimensions.ratio = {};
		this.dimensions.ratio.height = (this.dimensions.content == 0) ? 1 : this.dimensions.wrapper / this.dimensions.content;
		this.dimensions.ratio.top = (this.dimensions.scrollable == 0) ? 0 : this.wrapperScroll.getScrollTop() / this.dimensions.scrollable;
		this.dimensions.knob = this.dimensions.container * this.dimensions.ratio.height;
		this.dimensions.scrollable2 = this.dimensions.container - this.dimensions.knob;

		if (this.dimensions.ratio.height >= 1)
			this.deactivateScrollbar();
		else
			this.activateScrollbar();

		this.knob.setStyles({
			'height': Math.round(this.dimensions.knob),
			'top': this.dimensions.scrollable2 * this.dimensions.ratio.top
		});
	},

	toElement: function () {
		return this.knob;
	}
});
Scrollbar.instances = [];
Scrollbar.refreshAll = function () {
	if (!Scrollbar.instances.length)
		return false;
	for (var i = 0; i < Scrollbar.instances.length; i++)
		Scrollbar.instances[i].update();
};

window.fireEvent('frontToolbarReady');


function html_entity_decode(string, quote_style) {
	var hash_map = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();
	if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
		return false;
	}
	hash_map['&'] = '&amp;';
	for (symbol in hash_map) {
		entity = hash_map[symbol];
		tmp_str = tmp_str.split(entity).join(symbol);
	}
	tmp_str = tmp_str.split('&#039;').join("'");
	return tmp_str;
}
function get_html_translation_table(table, quote_style) {
	var entities = {}, hash_map = {}, decimal = 0, symbol = '';
	var constMappingTable = {}, constMappingQuoteStyle = {};
	var useTable = {}, useQuoteStyle = {};
	constMappingTable[0] = 'HTML_SPECIALCHARS';
	constMappingTable[1] = 'HTML_ENTITIES';
	constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
	constMappingQuoteStyle[2] = 'ENT_COMPAT';
	constMappingQuoteStyle[3] = 'ENT_QUOTES';
	useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
	useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';
	if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
		throw new Error("Table: " + useTable + ' not supported');
	}
	entities['38'] = '&amp;';
	if (useTable === 'HTML_ENTITIES') {
		entities['160'] = '&nbsp;';
		entities['161'] = '&iexcl;';
		entities['162'] = '&cent;';
		entities['163'] = '&pound;';
		entities['164'] = '&curren;';
		entities['165'] = '&yen;';
		entities['166'] = '&brvbar;';
		entities['167'] = '&sect;';
		entities['168'] = '&uml;';
		entities['169'] = '&copy;';
		entities['170'] = '&ordf;';
		entities['171'] = '&laquo;';
		entities['172'] = '&not;';
		entities['173'] = '&shy;';
		entities['174'] = '&reg;';
		entities['175'] = '&macr;';
		entities['176'] = '&deg;';
		entities['177'] = '&plusmn;';
		entities['178'] = '&sup2;';
		entities['179'] = '&sup3;';
		entities['180'] = '&acute;';
		entities['181'] = '&micro;';
		entities['182'] = '&para;';
		entities['183'] = '&middot;';
		entities['184'] = '&cedil;';
		entities['185'] = '&sup1;';
		entities['186'] = '&ordm;';
		entities['187'] = '&raquo;';
		entities['188'] = '&frac14;';
		entities['189'] = '&frac12;';
		entities['190'] = '&frac34;';
		entities['191'] = '&iquest;';
		entities['192'] = '&Agrave;';
		entities['193'] = '&Aacute;';
		entities['194'] = '&Acirc;';
		entities['195'] = '&Atilde;';
		entities['196'] = '&Auml;';
		entities['197'] = '&Aring;';
		entities['198'] = '&AElig;';
		entities['199'] = '&Ccedil;';
		entities['200'] = '&Egrave;';
		entities['201'] = '&Eacute;';
		entities['202'] = '&Ecirc;';
		entities['203'] = '&Euml;';
		entities['204'] = '&Igrave;';
		entities['205'] = '&Iacute;';
		entities['206'] = '&Icirc;';
		entities['207'] = '&Iuml;';
		entities['208'] = '&ETH;';
		entities['209'] = '&Ntilde;';
		entities['210'] = '&Ograve;';
		entities['211'] = '&Oacute;';
		entities['212'] = '&Ocirc;';
		entities['213'] = '&Otilde;';
		entities['214'] = '&Ouml;';
		entities['215'] = '&times;';
		entities['216'] = '&Oslash;';
		entities['217'] = '&Ugrave;';
		entities['218'] = '&Uacute;';
		entities['219'] = '&Ucirc;';
		entities['220'] = '&Uuml;';
		entities['221'] = '&Yacute;';
		entities['222'] = '&THORN;';
		entities['223'] = '&szlig;';
		entities['224'] = '&agrave;';
		entities['225'] = '&aacute;';
		entities['226'] = '&acirc;';
		entities['227'] = '&atilde;';
		entities['228'] = '&auml;';
		entities['229'] = '&aring;';
		entities['230'] = '&aelig;';
		entities['231'] = '&ccedil;';
		entities['232'] = '&egrave;';
		entities['233'] = '&eacute;';
		entities['234'] = '&ecirc;';
		entities['235'] = '&euml;';
		entities['236'] = '&igrave;';
		entities['237'] = '&iacute;';
		entities['238'] = '&icirc;';
		entities['239'] = '&iuml;';
		entities['240'] = '&eth;';
		entities['241'] = '&ntilde;';
		entities['242'] = '&ograve;';
		entities['243'] = '&oacute;';
		entities['244'] = '&ocirc;';
		entities['245'] = '&otilde;';
		entities['246'] = '&ouml;';
		entities['247'] = '&divide;';
		entities['248'] = '&oslash;';
		entities['249'] = '&ugrave;';
		entities['250'] = '&uacute;';
		entities['251'] = '&ucirc;';
		entities['252'] = '&uuml;';
		entities['253'] = '&yacute;';
		entities['254'] = '&thorn;';
		entities['255'] = '&yuml;';
	}
	if (useQuoteStyle !== 'ENT_NOQUOTES') {
		entities['34'] = '&quot;';
	}
	if (useQuoteStyle === 'ENT_QUOTES') {
		entities['39'] = '&#39;';
	}
	entities['60'] = '&lt;';
	entities['62'] = '&gt;';
	for (decimal in entities) {
		symbol = String.fromCharCode(decimal);
		hash_map[symbol] = entities[decimal];
	}
	return hash_map;
};