//"use strict";

var Utils = {
	getWindowHeight: function () {
		return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	},
	getWindowWidth: function() {
		return document.documentElement.clientWidth || document.body.clientWidth;
	},
	isLogged: function (callback) {
		new Request.JSON({
			url: urls.is_logged,
			onSuccess: function (xhr) {
				if (xhr.logged == 'true') {
					if (typeOf(callback) == 'function')
						callback();
				} else if (xhr.logged == 'false')
					new PopinLogin(callback);
			},
			onError: function () {
				new PopinLogin(callback);
			}
		}).send();
	},
	hex3ToHex6: function (hex3, sharp) {
		var sharp = sharp || false;
		if (typeOf(hex3) != 'string' || !hex3.test(/^\#?[a-f0-9]{3}$/i)) {
			if (hex3.test(/^\#?[a-f0-9]{6}$/i))
				return hex3;
			throw new TypeError("The argument is not a proper hexadecimal string of length : 3 ");
		}
		hex3 = new String(hex3.replace(/\#/, ''));
		var hex6 = (sharp) ? '#' : '';
		for (var i = 0; i < 3; i++)
			hex6 += hex3[i] + hex3[i];
		return hex6;
	}
};

Request.SLSJSON = new Class({
	Extends: Request.JSON,
	initialize: function (a) {
		this.parent(a);
	},
	send: function (o) {
		if (!this.logged) {
			if (o != null)
				this.requestData = o;
			return this.isLogged();
		} else if (this.requestData != null) {
			o = this.requestData;
			this.requestData = null;
		}
		this.parent(o);
	},

	isLogged: function () {
		var sendMethod = this.send.bind(this);
		jQuery.getJSON(urls.is_logged)
			.done(function (xhr) {
				if (xhr.logged != 'true')
					new PopinLogin(sendMethod);
				else if (xhr.authorized != 'true')
					_notifications.add('error', slsBuild.langs.SLS_BO_NOT_AUTHORIZED);
				else {
					this.logged = true;
					sendMethod();
				}
			}.bind(this))
			.fail(function () {
				new PopinLogin(sendMethod);
			});
	}
});

var StandardPage = new Class({
	Implements: [Events, Options],

	options: {
		minCollapseViewWidth: 1640
	},

	initialize: function () {
		this.initDefaultElements();
	},

	initDefaultElements: function () {
		// Global objects
		window.SlsView = window.SlsView || {
			expanded: $$('*[sls-setting-name="list_view"][sls-setting-value="expand"][sls-setting-selected="true"]').length > 0
		};
		if ($('sls-bo-toolbar') || $('sls-bo-actions-bar'))
			window._fixedToolbars = new FixedToolbars();

		// Properties
		this.core = $('sls-bo-core');
		this.view = $('sls-bo-view');
		this.expanded = this.core.hasClass('expanded');
		this._toolbar = $('sls-bo-toolbar') ? new Toolbar() : null;
		if (this._toolbar) SlsView.toolbar = this._toolbar;
		this._actionsBar = $('sls-bo-actions-bar') ? new ActionsBar() : null;
		if (this._actionsBar) SlsView.actionsBar = this._actionsBar;
		this._fixedHeader = new FixedHeader();

		// Settings
		this.settings = {
			'list_view': {
				'expand': $$('*[sls-setting-name="list_view"][sls-setting-value="expand"]')[0],
				'collapse': $$('*[sls-setting-name="list_view"][sls-setting-value="collapse"]')[0]
			}
		};

		// Events
		if (this.settings.list_view.expand)
			this.settings.list_view.expand.addEvent('click', this.toggleListView.bind(this));
		if (this.settings.list_view.collapse)
			this.settings.list_view.collapse.addEvent('click', this.toggleListView.bind(this));
		if (this.settings.list_view.expand && this.settings.list_view.collapse){
			window.addEvents({
				'load': this.checkWindowWidth.bind(this),
				'resize': this.checkWindowWidth.bind(this),
				'orientationchange': this.checkWindowWidth.bind(this)
			});
		}
	},

	toggleListView: function () {
		var settingBtn;
		if (arguments.length == 0)
			return false;
		else if (typeOf(arguments[0]) == "domevent") {
			arguments[0].preventDefault();
			settingBtn = (arguments[0].target.attributes['sls-setting-name']) ? arguments[0].target : arguments[0].target.getParent('*[sls-setting-name]');
		} else if (arguments.length >= 2 && typeOf(arguments[0]) == "string" && typeOf(arguments[1]) == "string" && $$('*[sls-setting-name="' + arguments[0] + '"][sls-setting-value="' + arguments[1] + '"]').length)
			settingBtn = $$('*[sls-setting-name="' + arguments[0] + '"][sls-setting-value="' + arguments[1] + '"]')[0];
		else
			return false;

		if (settingBtn.get('sls-setting-value') == "expand") {
			this.core.addClass('expanded');
			SlsView.expanded = true;
		} else if (settingBtn.get('sls-setting-value') == "collapse") {
			this.core.removeClass('expanded');
			SlsView.expanded = false;
			//this._toolbar.showBar();
			//this._actionsBar.showBar();
		}
		window.fireEvent('SlsViewChanged', SlsView.expanded);
	},

	checkWindowWidth: function(){
		if (Utils.getWindowWidth() < this.options.minCollapseViewWidth && !SlsView.expanded)
			this.toggleListView("list_view", "expand");
		if (Utils.getWindowWidth() < this.options.minCollapseViewWidth)
			this.settings.list_view.collapse.addClass('display-none');
		else if (Utils.getWindowWidth() >= this.options.minCollapseViewWidth)
			this.settings.list_view.collapse.removeClass('display-none');
	}
});

var DashBoard = new Class({
	Extends: StandardPage,

	initialize: function () {
		this.parent();
		this.dashBoard = this.view.getElement('.sls-bo-dashboard');
		if (!this.dashBoard)
			throw new Error("DashBoard: the dashboard container element is missing!");

		this.modules = this.dashBoard.getElements('.dashboard-module-google-analytics, .sls-bo-dashboard-module:not(.dashboard-module-dependent)');
		this.moduleTogglers = this.dashBoard.getElements('.actions.dashboard-modules [class^=btn]');
		this.viewAllBtn = this.dashBoard.getElement('.actions.dashboard-modules .view-all');

		this.initEvents();
		this.refreshTogglers();
	},

	initEvents: function () {
		this.moduleTogglers.addEvents({
			'click': this.toggleModule.bind(this)
		});

		if (this.viewAllBtn)
			this.viewAllBtn.addEvent('click', this.showAllModules.bind(this));

		window.addEvents({
			'onGAUnauthorized': this.handleGoogleUnauthorization.bind(this),
			'resize': this.refreshCharts.bind(this),
			'SlsViewChanged': this.refreshCharts.bind(this)
		});
	},

	handleGoogleUnauthorization: function () {
		if (window._loading)
			_notifications.destroy(_loading);

		var googleModule = this.modules.filter('.dashboard-module-google-analytics');
		var googleTogglerBtn = this.moduleTogglers.filter('.btn-dashboard-module-google-analytics');
		if (googleModule)
			googleModule.destroy();
		if (googleTogglerBtn)
			googleTogglerBtn.destroy();

		this.modules = this.dashBoard.getElements('.sls-bo-dashboard-module:not(.dashboard-module-dependent)');
		this.moduleTogglers = this.dashBoard.getElements('.actions.dashboard-modules [class^=btn]');
	},

	refreshCharts: function (event) {
		if (typeOf(event) == 'boolean')
			return window.fireEvent('resize');
		if (window.googleDash)
			googleDash.execute();
	},

	refreshTogglers: function () {
		if (this.moduleTogglers.length == false)
			return;

		this.modules.each(function (module) {
			try {
				var className = module.get('class').match(/dashboard\-module[^\s]+/)[0];
			} catch (e) {
				return (window.console) ? console.log(e) : null;
			}
			var toggler = this.moduleTogglers.filter('[class*="btn-' + className + '"]')[0];
			if (!toggler)
				throw new Error("DashBoard: the module \"" + className + "\" doesn't have a toggler button!");
			if (module.hasClass('disabled')) {
				toggler.removeClass('selected');
			} else {
				toggler.addClass('selected');
			}
		}.bind(this));

		this.refresh();
	},

	showAllModules: function () {
		var togglers = this.moduleTogglers.filter(':not(.selected)');
		if (!togglers.length)
			return;
		for (var i = 0; i < togglers.length; i++)
			this.toggleModule(togglers[i]);
	},

	toggleModule: function (arg) {
		var modules, toggler;
		if (typeOf(arg) == 'domevent' && arg.target && (arg.target.get('class').test(/btn\-/) || arg.target.getParent('[class*="btn-"]')))
			toggler = arg.target.get('class').test(/btn\-/) ? arg.target : arg.target.getParent('[class*="btn-"]');
		else if (typeOf(arg) == 'element' && arg && arg.get('class').test(/btn\-/))
			toggler = arg;
		else
			throw new Error("DashBoard: supplied arguement is not a toggler.");

		modules = this.dashBoard.getElements('.' + toggler.get('class').match(/btn\-([^\s]+)/)[1]);

		if (!modules.length)
			throw new Error("DashBoard: module element not found.");

		if (modules[0].hasClass('disabled')) {
			toggler.addClass('selected');
			modules.removeClass('disabled');
		} else if (this.modules.filter('.disabled').length < this.modules.length - 1) {
			toggler.removeClass('selected');
			modules.addClass('disabled');
		}

		this.refresh();
	},

	refresh: function () {
		if (this.moduleTogglers.length == this.moduleTogglers.filter('.selected').length)
			this.viewAllBtn.addClass('hidden');
		else
			this.viewAllBtn.removeClass('hidden');
	}
});

var Listing = new Class({
	Extends: StandardPage,

	initialize: function () {
		this.parent();
		this.listing = $$('#sls-bo-view .sls-bo-listing')[0];
		this.listingContainer = this.listing.getElement('.sls-bo-listing-container-positioning');
		if (this.listingContainer)
			this.listingSubContainer = this.listingContainer.getElement('.sls-bo-listing-container');
		this.recordsets = this.listing.getElements('.sls-bo-listing-recordset');
		this.recordsetCheckboxes = this.listing.getElements('.sls-bo-listing-recordset .checkbox');
		this.recordsetCheckboxInputs = this.listing.getElements('.sls-bo-listing-recordset .checkbox input');
		this.nbResultsByPageSelect = $$('.sls-bo-listing-params .results-by-page select').length ? $$('.sls-bo-listing-params .results-by-page select')[0] : null;
		this.togglerBtns = this.listing.getElements('.toggler-btn-radio').length ? this.listing.getElements('.toggler-btn-radio') : null;
		this.btnCheckAll = this.listing.getElement('.check-all');

		this.initBtns();
		this.initEvents();
		this.initFields();
		this.refreshCheckedRecordset();
	},

	initBtns: function () {
		this.addBtn = this.listing.getElement('.action-row .actions .add-action');
		this.editBtn = this.listing.getElement('.action-row .actions .edit-action');
		this.cloneBtn = this.listing.getElement('.action-row .actions .clone-action');
		this.deleteBtn = this.listing.getElement('.action-row .actions .delete-action');
		this.populateBtn = this.listing.getElement('.action-row .actions .populate-action');

		if (this.addBtn) {
			this.addBtn.addEvent('click', function (e) {
				e.stop();
				if (this.addBtn.getElement('a[href]'))
					window.location.href = this.addBtn.getElement('a[href]').get('href');
			}.bind(this));
		}

		if (this.editBtn) {
			this.editBtn.addEvent('click', function (e) {
				e.stop();
				if (this.editBtn.getElement('a[href]') && this.checkedRecordsets && this.checkedRecordsets.length == 1) {
					var href = this.editBtn.getElement('a[href]').get('href');
					href += ((!href.test(/\/$/)) ? '/' : '') + this.checkedRecordsets[0].getElement('input[type=checkbox]').get('value');
					window.location.href = href;
				}
			}.bind(this));
		}

		if (this.deleteBtn) {
			this.deleteBtn.addEvent('click', function (e) {
				e.stop();
				if (this.deleteBtn.getElement('a[href]') && this.checkedRecordsets && this.checkedRecordsets.length > 0) {
					var recordsets = this.checkedRecordsets;
					var ids = new Elements(recordsets.getElement('input[type=checkbox]')).get('value').join('|');
					var href = this.deleteBtn.getElement('a[href]').get('href');
					href += ((!href.test(/\/$/)) ? '/' : '') + ids;

					if (confirm(slsBuild.langs.SLS_BO_LIST_CONFIRM_DELETE)) {
						new Request.JSON({
							url: href,
							data: {'sls-request': 'async'},
							onSuccess: this.deleteRecordsets.bind(this, [recordsets, _notifications.add('loading', slsBuild.langs.SLS_BO_DELETE_LOADING)])
						}).send();
					}
				}
			}.bind(this));
		}

		if (this.cloneBtn) {
			this.cloneBtn.addEvent('click', function (e) {
				e.stop();
				var href = this.cloneBtn.getElement('a[href]').get('href');
				if (href && this.checkedRecordsets && this.checkedRecordsets.length > 0) {
					var recordsets = this.checkedRecordsets;
					var ids = new Elements(recordsets.getElement('input[type=checkbox]')).get('value').join('|');
					var loading = _notifications.add('loading', false);
					href += ((!href.test(/\/$/)) ? '/' : '') + ids;

					new Request.JSON({
						url: href,
						data: {'sls-request': 'async'},
						onSuccess: function (xhr) {
							if (xhr.status == 'OK') {
								window.location.reload();
							} else if (xhr.status == 'ERROR') {
								_notifications.add('error', xhr.result.message);
							}
							_notifications.destroy(loading);
						}
					}).send();
				}
			}.bind(this));
		}

		if (this.populateBtn) {
			this.populateBtn.addEvent('click', function (e) {
				e.stop();
				var href = this.populateBtn.getElement('a[href]').get('href');
				if (href) {
					var loading = _notifications.add('loading', false);
					new Request.JSON({
						url: href,
						data: {'sls-request': 'async'},
						onSuccess: function (xhr) {
							if (xhr.status == 'OK') {
								window.location.reload();
							} else if (xhr.status == 'ERROR') {
								_notifications.add('error', xhr.result.message);
							}
							_notifications.destroy(loading);
						}
					}).send();
				}
			}.bind(this));
		}
	},

	initEvents: function () {
		this.view.addEvent('click:relay(.sls-bo-listing-head .relative)', this.orderByColumn.bind(this));

		if (this.nbResultsByPageSelect)
			this.nbResultsByPageSelect.addEvent('change', this.setNbResultsByPage.bind(this));

		if (this.listingContainer) {
			this.listingContainer.addEvent('scroll', this.synchronizeListingHeader.bind(this));
			if (this.editBtn || this.cloneBtn || this.deleteBtn) {
				this.btnCheckAll.addEvent('change', this.checkAll.bind(this));
				this.view.addEvents({
					'change:relay(.sls-bo-listing-recordset .checkbox input)': this.toggleRecordsetCheck.bind(this),
					'click:relay(.sls-bo-listing-recordset .sls-bo-listing-cell)': this.toggleCheckbox.bind(this),
					'mousedown:relay(.sls-bo-listing-recordset .sls-bo-listing-cell)': this.preventShiftSelection.bind(this)
				});
				this.addEvent('recordsetToggled', this.handleRecordsetToggling.bind(this));
			}

			if (Browser.ie8)
				this.view.getElements('.sls-bo-listing-recordset .checkbox').removeEvents('click');
		}
	},

	initFields: function () {
		this.view.getElements('.sls-bo-listing-cell, .results-by-page').each(initField);
		if (this.listingSubContainer)
			var dotLineWidth = this.listingSubContainer.measure(function () {
				return this.getDimensions().width;
			});

		if (!Browser.ie8){
			window.addEvent('load', function () {
				this.styleDotLinenew = new Element('style', {
					'html': '#sls-bo-view .sls-bo-listing-row-separator:before {' +
						'width: ' + dotLineWidth + 'px' +
						'}'
				}).inject($$('body')[0]);
			}.bind(this));
		}
	},

	checkAll: function (arg) {
		var target;
		if (!arg)
			target = this.btnCheckAll;
		else if (typeOf(arg) == 'object' || arg.target || arg.target.hasClass('check-all'))
			target = arg.target;
		else
			return false;
		var state = target.checked;

		if (state) {
			this.recordsets.addClass('checked');
			this.recordsetCheckboxes.addClass('checked');
			this.recordsetCheckboxInputs.set('checked', true);
		} else {
			this.recordsets.removeClass('checked');
			this.recordsetCheckboxes.removeClass('checked');
			this.recordsetCheckboxInputs.set('checked', false);
		}

		if (target != this.btnCheckAll) {
			this.btnCheckAll.set('checked', state);
			this.btnCheckAll.retrieve('Checkbox').update();
		}

		this.refreshCheckedRecordset();
	},

	deleteRecordsets: function (args, xhr) {
		if (typeOf(args) != 'array')
			return false;
		var recordsets = args[0];
		var notification = args[1];
		if (typeOf(recordsets) != 'elements')
			return false;

		if (xhr.status != 'OK')
			return;

		_notifications.destroy(notification);
		_notifications.add('success', xhr.result.message);

		recordsets.each(function (recordset) {
			var row = recordset.getParent('.sls-bo-listing-row');
			var separator = row.getPrevious('.sls-bo-listing-row-separator');
			row
				.get('morph')
				.start({'opacity': 0})
				.chain(function () {
					row.destroy();
					if (separator)
						separator.destroy();
					this.refreshCheckedRecordset();
				}.bind(this));
			window.fireEvent('recordsetDeleted', {id: recordset.getElement('input[type=checkbox]').get('value')});
		}.bind(this));
	},

	toggleCheckbox: function (event) {
		if (!event || typeOf(event) != 'domevent')
			throw new Error('Listing: wrong parameter type or missing!');

		var recordset =
			(event.target.tagName.toLowerCase() != 'a' && !event.target.getParent('a') && !event.target.hasClass('checkbox') && !event.target.hasClass('drag-and-drop') && !event.target.getParent('.drag-and-drop')) ?
				((event.target.hasClass('sls-bo-listing-recordset')) ? event.target : event.target.getParent('.sls-bo-listing-recordset')) : null;

		if (recordset) {
			var checkbox = recordset.getElement('.checkbox');
			// Check recordsets in between in case of a shift click
			if (event.shift) {
				event.stop();
				if (this.lastCheckedRecordset) {
					var currentRecordsetIndex = this.recordsets.indexOf(recordset);
					var lastCheckedRecordsetIndex = this.recordsets.indexOf(this.lastCheckedRecordset);
					if (currentRecordsetIndex != -1 && lastCheckedRecordsetIndex != -1) {
						var min = Math.min(currentRecordsetIndex, lastCheckedRecordsetIndex) + 1, max = Math.max(currentRecordsetIndex, lastCheckedRecordsetIndex) - 1, cb;
						for (var i = min; i <= max; i++) {
							if (!this.recordsets[i].hasClass('checked')) {
								cb = this.recordsets[i].getElement('input[type=checkbox]').set('checked', true);
								cb.getParent('.checkbox').addClass('checked');
								this.toggleRecordsetCheck({target: cb, preventQuickEdit: true});
							}
						}
					}
				}
			}

			// Check clicked recordset
			if (checkbox){
				checkbox.fireEvent('click', {target: checkbox, stop: function () {
					return true;
				}});
				if (Browser.ie8)
					this.toggleRecordsetCheck({target: checkbox.getElement('input[type=checkbox]')});
			}
		}
	},

	preventShiftSelection: function (event) {
		if (!event || typeOf(event) != 'domevent')
			throw new Error("Listing: wrong parameter type or missing!");
		if (event.shift)
			event.preventDefault();
	},

	toggleRecordsetCheck: function () {
		var recordset, preventQuickEdit = false;
		if (typeOf(arguments[0]) == "object") {
			if (arguments[0].target.tagName == "INPUT" && arguments[0].target.type == "checkbox")
				recordset = arguments[0].target.getParent('.sls-bo-listing-recordset');
			if ('preventQuickEdit' in arguments[0] && arguments[0].preventQuickEdit)
				preventQuickEdit = true;
		}

		if (!recordset)
			return false;
		if (!recordset.hasClass('checked'))
			this.checkRecordset(recordset, preventQuickEdit);
		else
			this.uncheckRecordset(recordset);
		this.refreshCheckedRecordset();
	},

	refreshCheckedRecordset: function () {
		this.checkedRecordsets = this.listing.getElements('.sls-bo-listing-recordset').filter(function (recordset) {
			if (recordset.getElement('.sls-bo-listing-cell:first-child input[type=checkbox]:checked'))
				return recordset;
		});
		this.fireEvent('recordsetToggled');
	},

	handleRecordsetToggling: function () {
		var nbCheckedRecordset = this.checkedRecordsets.length;

		if (nbCheckedRecordset == 0) {
			if (this.editBtn)
				this.editBtn.setStyles({'display': 'none'});
			if (this.cloneBtn)
				this.cloneBtn.setStyles({'display': 'none'});
			if (this.deleteBtn)
				this.deleteBtn.setStyles({'display': 'none'});
		} else if (nbCheckedRecordset == 1) {
			if (this.editBtn)
				this.editBtn.setStyles({'display': 'inline-block'});
			if (this.cloneBtn)
				this.cloneBtn.setStyles({'display': 'inline-block'});
			if (this.deleteBtn)
				this.deleteBtn.setStyles({'display': 'inline-block'});
		} else if (nbCheckedRecordset > 1) {
			if (this.editBtn)
				this.editBtn.setStyles({'display': 'none'});
			if (this.cloneBtn)
				this.cloneBtn.setStyles({'display': 'inline-block'});
			if (this.deleteBtn)
				this.deleteBtn.setStyles({'display': 'inline-block'});
		}
	},

	checkRecordset: function (recordset, preventQuickEdit) {
		if (typeOf(recordset) != "element" || !recordset.hasClass('sls-bo-listing-recordset') || recordset.hasClass('checked'))
			return false;

		this.lastCheckedRecordset = recordset;
		recordset.addClass('checked');

		if (this._actionsBar && this._actionsBar._quickEdit && !preventQuickEdit)
			this._actionsBar._quickEdit.fireEvent('edit', {id: recordset.getElement('input[type=checkbox]').get('value')});
	},

	uncheckRecordset: function (recordset) {
		if (typeOf(recordset) != "element" || !recordset.hasClass('sls-bo-listing-recordset') || !recordset.hasClass('checked'))
			return false;

		recordset.removeClass('checked');
		if (this.lastCheckedRecordset == recordset)
			this.lastCheckedRecordset = null;
	},

	orderByColumn: function () {
		if (arguments.length == 0 || !this._actionsBar || !this._actionsBar._filters || !this._actionsBar._filters.inputOrder)
			return false;

		var column, way, columnLabel;
		if (typeOf(arguments[0]) == "domevent") {
			arguments[0].stop();
			columnLabel = (arguments[0].target.hasClass('relative')) ? arguments[0].target : arguments[0].target.getParent('.relative');
			var classAttr = columnLabel.get('class');
			if (classAttr.test(/column\_\w+/))
				column = classAttr.match(/column\_(\w+)/)[1];
			else
				return false;
		} else if (typeOf(arguments[0]) == "string" && this.core.getElements('.sls-bo-listing-head .relative').filter('.column_' + arguments[0]).length > 0) {
			columnLabel = this.core.getElements('.sls-bo-listing-head .relative').filter('.column_' + arguments[0])[0];
			column = arguments[0];
		} else
			return false;

		if (columnLabel.hasClass('asc'))
			way = "DESC";
		else
			way = "ASC";

		this._actionsBar._filters.inputOrder.set('value', column + "_" + way);
		this._actionsBar._filters.form.submit();
	},

	setNbResultsByPage: function () {
		if (!this.nbResultsByPageSelect || !this._actionsBar || !this._actionsBar._filters || !this._actionsBar._filters.inputLength)
			return false;

		var nbResultsByPage = this.nbResultsByPageSelect.options[this.nbResultsByPageSelect.selectedIndex].get('value').toInt();

		if (isNaN(nbResultsByPage))
			return false;

		this._actionsBar._filters.inputLength.set('value', nbResultsByPage);
		this._actionsBar._filters.form.submit();
	},

	synchronizeListingHeader: function () {
		if (!this._fixedHeader && !this._fixedHeader.content.getElement('.sls-bo-listing-head'))
			return false;

		this._fixedHeader.content.getElement('.sls-bo-listing-head')
			.setStyles({
				'margin-left': -this.listingContainer.getScrollLeft()
			});
	}
});

var FormPage = new Class({
	Extends: StandardPage,

	currentLang: '',

	initialize: function () {
		this.parent();
		this.formPage = this.view.getElement('.sls-bo-form-page');
		this.form = this.formPage.getElement('form[sls-validation=true]');
		this.langs = (this.formPage.getElements('.langs [sls-lang]').length && !this.formPage.hasClass('sls-bo-i18n')) ? this.formPage.getElements('.langs [sls-lang]').get('sls-lang') : null;
		if (this.langs) {
			this.langsContainers = this.formPage.getElements('.sls-bo-form-page-langs').filter(function (langsContainer) {
				if (langsContainer.getElements('.sls-bo-form-page-lang').length > 1)
					return langsContainer;
			});
			this.langSwitchers = this.formPage.getElements('.langs [sls-lang]');
			this.langPages = this.formPage.getElements('.sls-bo-form-page-langs .sls-bo-form-page-lang');
			this.currentLang = this.langPages.filter('.current')[0].get('sls-lang');
			this.langPages.each(function (page) {
				page.get('morph').setOptions({duration: 250});
			});
		}
		this.scroll = new Fx.Scroll(window);

		this.initEvents();
		this.initFields();
	},

	initEvents: function () {
		if (this.langs)
			new Elements(this.langSwitchers.getParent('li')).addEvent('click', this.switchLang.bind(this));

		this.form.addEvents({
			'error': this.onError.bind(this),
			'success': this.onSuccess.bind(this)
		});
	},

	onError: function (obj) {
		if (this.langs && typeOf(obj) == 'object' && obj.langs) {
			this.langSwitchers.each(function (langSwitcher) {
				if (obj.langs.indexOf(langSwitcher.get('sls-lang')) != -1)
					langSwitcher.addClass('error');
				else
					langSwitcher.removeClass('error');
			});
		}
	},

	onSuccess: function () {
		if (this.langs)
			this.langSwitchers.removeClass('error');
	},

	initFields: function () {
		var selector = '';
		if (this.langs)
			selector = '.sls-bo-form-page-langs:not(.sls-form-page-children-section) .sls-form-page-field';
		else
			selector = '.sls-bo-form-page-section .sls-form-page-field, .input-lang .sls-form-page-field';
		this.formPage.getElements(selector).each(initField);
		initField(this.formPage.getElement('.sls-bo-form-page-bottom select'));
		this.form.retrieve('Validation').testFormFields();
	},

	switchLang: function (arg) {
		var lang;
		if (typeOf(arg) == 'domevent' && arg.target && (arg.target.get('sls-lang') || arg.target.getParent('[sls-lang]') || (arg.target.tagName.toLowerCase() == 'li' && arg.target.getElement('[sls-lang]')))) {
			lang = (arg.target.tagName.toLowerCase() == 'li') ? arg.target.getElement('[sls-lang]').get('sls-lang') : (arg.target.get('sls-lang') ? arg.target.get('sls-lang') : arg.target.getParent('[sls-lang]').get('sls-lang'));
			arg.stop();
		} else if (typeOf(arg) == 'string')
			lang = arg;

		if (this.langs.indexOf(lang) == -1)
			throw new Error('You try to access an unknown language.');
		else if (this.currentLang == lang)
			return;

		this.langsContainers.each(function (langsContainer) {
			var langs = langsContainer.getElements('.sls-bo-form-page-lang');
			var langOut = langs.filter('[sls-lang="' + this.currentLang + '"]')[0];
			var langIn = langs.filter('[sls-lang="' + lang + '"]')[0];

			if (!langOut || !langIn)
				return;

			langOut
				.get('morph')
				.start({'opacity': 0})
				.chain(function () {
					this.set({'display': 'none'});
					langIn
						.get('morph')
						.set({
							'display': 'block',
							'opacity': 0
						})
						.start({'opacity': 1});
				});
		}.bind(this));

		this.langSwitchers.filter('[sls-lang="' + this.currentLang + '"]')[0].getParent('li').removeClass('selected');
		this.langSwitchers.filter('[sls-lang="' + lang + '"]')[0].getParent('li').addClass('selected');

		this.currentLang = lang;
	}
});

var FixedHeader = new Class({
	initialize: function () {
		this.fixedHeader = $('sls-bo-fixed-header');
		this.content = this.fixedHeader.getElement('.sls-bo-fixed-header-content');
		if (!this.fixedHeader)
			throw new Error('JS: FixedHeader<br/>An HTML is missing to fix the header.');
		this.fixedElements = $$('.fixed-in-header');
		if (!this.fixedElements.length)
			throw new Error('JS: FixedHeader<br/>The fixed header is useless without elements to fix.');

		if (!Browser.ie8)
			this.initEvents();
	},

	initEvents: function () {
		this.checkScrollBinded = this.checkScroll.bind(this);

		window.addEvent('scroll', this.checkScrollBinded);
	},

	getMarkTop: function () {
		var getTop = function () {
			return this.getCoordinates().top;
		};

		return this.fixedHeader.getParent().measure(getTop);
	},

	buildFakeListHeader: function () {
		if ($$('.sls-bo-listing-head').length == 0)
			return;
		this.listHeader = $$('.sls-bo-listing-head')[0];
		var listHeaderFake = this.listHeader.clone();
		var fakeCells = listHeaderFake.getChildren();

		this.listHeader.getChildren().each(function (cell, index) {
			fakeCells[index].set('style', 'width:' + cell.getDimensions().width + 'px !important;');
		});

		var listing = this.listHeader.getParent('.sls-bo-listing');
		if (listing.get('sls-listing-selection') == "true") {
			var input = listHeaderFake.getElement('.checkbox input');
			new Checkbox(input);
			input.addEvent('change', _listing.checkAll.pass({target: input}, _listing));
		} else {
			var selectionListingCell = listHeaderFake.getElement('.sls-bo-listing-cell:first-child');
			selectionListingCell.setStyles({
				'display': 'none',
				'visibility': 'hidden'
			});
		}

		listHeaderFake.inject(this.content);
		if (window._listing)
			_listing.synchronizeListingHeader();
	},

	checkScroll: function () {
		var windowScrollTop = window.getScrollTop();

		if (this.activated && windowScrollTop < this.getMarkTop()) {
			this.desactivate();
		} else if (!this.activated && windowScrollTop > this.getMarkTop()) {
			this.activate();
		}
	},

	activate: function () {
		this.fixedElements.each(this.pushElement.bind(this));
		if (window._listing)
			this.buildFakeListHeader();
		this.activated = true;
		this.fixedHeader.addClass('activated');
	},

	desactivate: function () {
		this.fixedElements.each(this.pullElement.bind(this));
		this.content.empty();
		this.activated = false;
		this.fixedHeader.removeClass('activated');
	},

	pushElement: function (element) {
		var mark = new Element('div.fixed-missing-mark', {
			styles: element.getStyles(['display', 'width', 'height', 'margin', 'padding', 'border'])
		});
		mark.replaces(element);
		element.store('FixedHeader-Mark', mark);
		element.inject(this.content);
	},

	pullElement: function (element) {
		element.replaces(element.retrieve('FixedHeader-Mark'));
	}
});

var FixedToolbars = new Class({
	Implements: [Events],

	activated: false,

	initialize: function () {
		this.toolBar = $('sls-bo-toolbar');
		this.actionBar = $('sls-bo-actions-bar');
		if (this.toolBar) {
			this.toolBarContent = this.toolBar.getElement('.sls-bo-toolbar-content');
			this.limitTop = this.toolBar.getCoordinates().top;
			this.toolBar.store('top', this.toolBar.getCoordinates(this.toolBar.getParent()).top);
		}
		if (this.actionBar) {
			this.actionBarContent = this.actionBar.getElement('.sls-bo-actions-bar-content');
			this.actionBar.store('top', this.actionBar.getCoordinates(this.actionBar.getParent()).top);
		}

		if (this.toolBar || this.actionBar) {
			this.update();
			this.initEvents();
		}
	},

	initEvents: function () {
		this.addEvent('updated', this.update.bind(this));

		window.addEvents({
			'scroll': this.update.bind(this),
			'resize': this.update.bind(this)
		});
	},

	update: function () {
		this.checkHeight();
		this.checkScroll();

		if (this.toolBar)
			this.toolBar.fireEvent('updated');
		if (this.actionBar)
			this.actionBar.fireEvent('updated');
	},

	checkHeight: function () {
		var sidebarTop = this.limitTop - window.getScrollTop();
		if (sidebarTop < 0)
			sidebarTop = 0;
		var maxSidebarHeight = Utils.getWindowHeight() - sidebarTop;
		var toolbarHeight;
		var actionBarHeight;

		if (SlsView.expanded) {
			if (this.toolBar) {
				toolbarHeight = this.toolBarContent.measure(function () {
					return this.getComputedSize().totalHeight;
				});
				if (toolbarHeight > maxSidebarHeight)
					toolbarHeight = maxSidebarHeight;
			}
			if (this.actionBar) {
				actionBarHeight = this.actionBarContent.measure(function () {
					return this.getComputedSize().totalHeight;
				});
				if (actionBarHeight > maxSidebarHeight)
					actionBarHeight = maxSidebarHeight;
			}
		} else {
			toolbarHeight = maxSidebarHeight;
			actionBarHeight = maxSidebarHeight;
		}

		if (this.toolBar)
			this.toolBar.setStyles({'height': toolbarHeight});
		if (this.actionBar)
			this.actionBar.setStyles({'height': actionBarHeight});
	},

	checkScroll: function () {
		var windowScrollTop = window.getScrollTop();

		if (this.activated && windowScrollTop < this.limitTop) {
			this.desactivate();
		} else if (!this.activated && windowScrollTop > this.limitTop) {
			this.activate();
		}
	},

	activate: function () {
		if (this.toolBar)
			this.toolBar.setStyles({'position': 'fixed', 'top': 0});
		if (this.actionBar)
			this.actionBar.setStyles({'position': 'fixed', 'top': 0});

		this.activated = true;
	},

	desactivate: function () {
		if (this.toolBar)
			this.toolBar.setStyles({'position': 'absolute', 'top': this.toolBar.retrieve('top')});
		if (this.actionBar)
			this.actionBar.setStyles({'position': 'absolute', 'top': this.actionBar.retrieve('top')});

		this.activated = false;
	}
});

var Gallery = new Class({
	Implements: [Options, Events],

	options: {
		selector: '.sls-bo-listing-cell img'
	},
	opened: false,

	initialize: function (options) {
		this.setOptions(options);
		this.body = $$('body')[0];

		this.build();
	},

	build: function () {
		this.gallery = Elements.from(this.HTMLGalery)[0];
		this.gallery
			.setStyles({'opacity': 0})
			.addEvents({
				'click': this.close.bind(this)
			})
			.inject(document.body);
		this.focus = this.gallery.getElement('.sls-bo-gallery-focus');

		$$('body')[0].addEvent('click:relay(' + this.options.selector + ')', this.open.bind(this));

		window.addEvents({
			'resize': function () {
				if (this.opened)
					this.close();
			}.bind(this),
			'orientationchange': function () {
				if (this.opened)
					this.close();
			}.bind(this)
		});
	},

	open: function () {
		if (!arguments.length || typeOf(arguments[0]) != 'domevent' || arguments[0].target.tagName != "IMG" || arguments[0].target.getParent('.gallery') == this.gallery)
			return false;
		arguments[0].stop();

		this.fireEvent('loading');
		this.loading = _notifications.add('loading', false);

		this.prepareImg(arguments[0].target);

		return false;
	},

	openGallery: function () {
		this.fireEvent('opening');

		this.body.setStyles({'overflow': 'hidden'});
		this.gallery
			.setStyles({'opacity': 0})
			.removeClass('disabled')
			.get('morph')
			.start({'opacity': 1})
			.chain(function () {
				this.fireEvent('opened');
				this.opened = true;
			}.bind(this));
	},

	prepareImg: function (originalImg) {
		var aParent = originalImg.getParent('a[href]');
		var imgSrc = aParent && aParent.get('href').match(/\.(\w+)$/)[1] in _mimeTypes.image ? aParent.get('href') : originalImg.get('src');
		this.focusedVisual = Elements.from('<img sls-image-src="' + imgSrc + '" sls-image-fit="visible" class="sls-image" alt="' + originalImg.title + '" title="' + originalImg.title + '"/>')[0];
		this.focusedVisual.store('Gallery:original', originalImg);

		this.focusedVisual
			.setStyles({'visibility': 'hidden'})
			.inject(this.focus);

		SlsImage.init(this.focusedVisual, function () {
			this.openGallery();
			this.morphImgIn();
		}.bind(this));
	},

	morphImgIn: function () {
		var originalImgDimensions = this.focusedVisual.retrieve('Gallery:original').getCoordinates();
		var originalImgCoordinates = this.focusedVisual.retrieve('Gallery:original').getPosition();
		var focusCoords = this.focus.getCoordinates();
		var focusedImgCoords = this.focusedVisual.getStyles(['top', 'left', 'width', 'height']);

		this.focusedVisual
			.setStyles({
				'top': originalImgCoordinates.y - focusCoords.top,
				'left': originalImgCoordinates.x - focusCoords.left,
				'width': originalImgDimensions.width,
				'height': originalImgDimensions.height,
				//'position': 'fixed',
				'visibility': 'visible',
				'opacity': 1})
			.get('morph')
			.start({
				'top': focusedImgCoords.top,
				'left': focusedImgCoords.left,
				'width': focusedImgCoords.width,
				'height': focusedImgCoords.height
			});

		_notifications.destroy(this.loading);
	},

	morphImgOut: function () {

	},

	close: function () {
		if (arguments.length && typeOf(arguments[0]) == 'domevent')
			arguments[0].stop();

		this.fireEvent('closing');

		this.gallery
			.get('morph').start({'opacity': 0})
			.chain(function () {
				this.gallery
					.addClass('disabled');
				this.fireEvent('closed');
				this.opened = false;
				this.body.setStyles({'overflow': 'visible'});
			}.bind(this));

		var originalImgDimensions = this.focusedVisual.retrieve('Gallery:original').getCoordinates();
		var originalImgCoordinates = this.focusedVisual.retrieve('Gallery:original').getPosition();
		var focusCoords = this.focus.getCoordinates();

		this.focusedVisual
			.get('morph')
			.start({
				'top': originalImgCoordinates.y - focusCoords.top,
				'left': originalImgCoordinates.x - focusCoords.left,
				'width': originalImgDimensions.width,
				'height': originalImgDimensions.height
			})
			.chain(function () {
				this.focusedVisual.destroy();
			}.bind(this));
	},

	HTMLGalery: '<div class="sls-bo-gallery disabled">' +
		'<div class="sls-bo-gallery-focus sls-image-container"></div>' +
		'</div>'
});

var Header = new Class({
	initialize: function () {
		this.header = $('sls-bo-header');
		this.langsSelect = this.header.getElement('li.langs select');
		if (this.langsSelect){
			this.currentLangElement = this.header.getElement('li.langs .current-lang');
			initField(this.langsSelect.getParent());
		}

		this.initEvents();
	},

	initEvents: function () {
		if (this.langsSelect){
			var currentLangElement = this.currentLangElement;
			this.langsSelect.addEvent('change', function () {
				Utils.isLogged(function () {
					currentLangElement.set('html', this.options[this.options.selectedIndex].get('html'));
					window.location.href = this.value;
				}.bind(this));
			});
		}
	}
});

var I18n = new Class({
	Extends: StandardPage,

	initialize: function () {
		this.parent();
		this.page = $$('.sls-bo-i18n')[0];
		this.langs = this.page.getElements('.actions.langs li a').get('sls-lang');
		this.langBtns = this.page.getElements('.actions.langs li');
		this.langsContainer = this.page.getElement('.columns-lang-container');
		this.langContainers = this.langsContainer.getElements('.column-lang-container');
		this.inputs = this.langsContainer.getElements('.input-lang');
		this.actionsBar = $('sls-bo-actions-bar');
		this.actionBarModule = this.actionsBar.getElement('.sls-bo-module-i18n');
		this.actionBarModuleContent = this.actionBarModule.getElement('.sls-bo-actions-bar-section-content');
		this.actionBarInputsContainer = this.actionBarModuleContent.getElement('.inputs');

		this.currentReferenceLang = this.langs[0];
		this.currentFocusedLang = (this.langs.length > 1) ? this.langs[1] : this.currentReferenceLang;

		this.initEvents();
		this.start();
	},

	initEvents: function () {
		if (this.langs.length > 2)
			this.page.addEvent('click:relay(.arrow)', this.onArrowClick.bind(this));
		if (this.langs.length > 1)
			this.page.addEvent('click:relay(.column-title)', this.onColumnHeaderClick.bind(this));
		if (this.langBtns.length > 2)
			this.langBtns.addEvent('click', this.onLangBtnClick.bind(this));

		this.langsContainer.addEvents({
			'focus:relay(.input-lang input, .input-lang textarea)': this.onInputFocus.bind(this),
			'keyup:relay(.input-lang input, .input-lang textarea)': this.copyInputFromCore.bind(this),
			'tab:relay(.input-lang input, .input-lang textarea)': this.checkBeforeTab.bind(this),
			'shiftTab:relay(.input-lang input, .input-lang textarea)': this.checkBeforeShiftTab.bind(this)
		});

		this.actionBarInputsContainer.addEvent('keyup:relay(input, textarea)', this.copyInputFromActionsBar.bind(this));
		this.actionBarModuleContent.addEvent('click:relay(.navigation .btn)', this.navigateFields.bind(this));
	},

	start: function () {
		this.onInputFocus(this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']')[0].getElement('.input-lang textarea, .input-lang input'));
	},

	copyInputFromCore: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;

		var coreInput = event.target;
		var actionsBarInput = this.actionBarInputsContainer.getElement('input[name="' + coreInput.name + '"], textarea[name="' + coreInput.name + '"]');
		if (actionsBarInput)
			actionsBarInput.set('value', coreInput.get('value'));
	},

	copyInputFromActionsBar: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;

		var actionsBarInput = event.target;
		var coreInput = this.langsContainer.getElement('input[name="' + actionsBarInput.name + '"], textarea[name="' + actionsBarInput.name + '"]');
		if (coreInput)
			coreInput.set('value', actionsBarInput.get('value'));
	},

	onArrowClick: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;
		event.stop();

		var arrow = event.target.hasClass('arrow') ? event.target : event.target.getParent('.arrow');
		var langToSwitchTo = "";

		if (arrow.hasClass('previous')) {
			langToSwitchTo = this.getPreviousLang();
		} else if (arrow.hasClass('next')) {
			langToSwitchTo = this.getNextLang();
		} else
			throw new Error("I18n: The navigation button is missing an html class.");

		if (langToSwitchTo)
			this.switchFocusedLang(langToSwitchTo);
	},

	onLangBtnClick: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;
		event.stop();
		var btn = event.target.tagName.toLowerCase() == 'li' ? event.target : event.target.getParent('li');
		var lang = btn.getElement('[sls-lang]').get('sls-lang');

		if ([this.currentFocusedLang, this.currentReferenceLang].indexOf(lang) == -1)
			this.switchFocusedLang(lang);
	},

	onColumnHeaderClick: function (event) {
		if (!event || typeOf(event) != 'domevent' || event.target.getParent('.label-lang-variables'))
			return false;
		event.stop();

		var langContainer = event.target.getParent('.column-lang-container');
		var inputLang = langContainer.getElement('.input-lang');
		var input = inputLang ? inputLang.getElement(' input, textarea') : false;
		if (input)
			this.onInputFocus(input);
	},

	getPreviousLang: function () {
		var lang = false;

		if (this.langs.length <= 2)
			return false;

		var currentLangIndex = this.langs.indexOf(this.currentFocusedLang);
		var previousLangIndex = currentLangIndex - 1 >= 0 ? currentLangIndex - 1 : this.langs.length - 1;
		do {
			if (this.langs[previousLangIndex] != this.currentFocusedLang && this.langs[previousLangIndex] != this.currentReferenceLang)
				lang = this.langs[previousLangIndex];
			else
				previousLangIndex = previousLangIndex - 1 >= 0 ? previousLangIndex - 1 : this.langs.length - 1;
		} while (!lang);

		return lang;
	},

	getNextLang: function () {
		var lang = false;

		if (this.langs.length <= 2)
			return false;

		var currentLangIndex = this.langs.indexOf(this.currentFocusedLang);
		var nextLangIndex = currentLangIndex + 1 < this.langs.length ? currentLangIndex + 1 : 0;
		do {
			if (this.langs[nextLangIndex] != this.currentFocusedLang && this.langs[nextLangIndex] != this.currentReferenceLang)
				lang = this.langs[nextLangIndex];
			else
				nextLangIndex = nextLangIndex + 1 < this.langs.length ? nextLangIndex + 1 : 0;
		} while (!lang);

		return lang;
	},

	onInputFocus: function (arg) {
		var input;
		if (typeOf(arg) == 'domevent')
			input = arg.target;
		else if (typeOf(arg) == 'element')
			input = arg;

		if (input && ['input', 'textarea'].indexOf(input.tagName.toLowerCase()) != -1 && !input.getParent('.input-lang.focused')) {
			var newFocusedInput = input.getParent('.input-lang');
			var langContainer = newFocusedInput.getParent('.column-lang-container');
			var lang = langContainer.get('sls-lang');

			if (lang != this.currentFocusedLang)
				this.switchWorkingLangs();

			var oldReferenceInput = this.inputs.filter('.reference')[0];
			var oldFocusedInput = this.inputs.filter('.focused')[0];
			if (oldReferenceInput) oldReferenceInput.removeClass('reference');
			if (oldFocusedInput) oldFocusedInput.removeClass('focused');

			var newFocusedInputIndex = this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']')[0].getElements('.input-lang').indexOf(newFocusedInput);
			var newReferenceInput = this.langContainers.filter('[sls-lang=' + this.currentReferenceLang + ']')[0].getElements('.input-lang')[newFocusedInputIndex];

			newFocusedInput.addClass('focused');
			newReferenceInput.addClass('reference');

			this.copyInputsIntoActionsBar();
		}
	},

	copyInputsIntoActionsBar: function () {
		var focusedInputLang = this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']')[0].getElement('.input-lang.focused');
		var id = focusedInputLang.getElement('input, textarea').get('id').replace(/\_[a-z]{2}$/i, '');
		var inputsToCopy = this.langsContainer.getElements('[id^="' + id + '"]');

		this.actionBarInputsContainer.empty();

		for (var i = 0, langsLength = this.langs.length; i < langsLength; i++)
			this.injectInputFieldInActionsBar(inputsToCopy.filter('[id$="' + this.langs[i] + '"]')[0].getParent('.input-lang'));
	},

	injectInputFieldInActionsBar: function (HTMLNode) {
		var input = HTMLNode.getElement('input, textarea'),
			objInitializer = {
				lang: input.getParent('[sls-lang]').get('sls-lang'),
				name: input.name,
				value: input.get('value')
			},
			html, formField;

		if (input.tagName.toLowerCase() == 'input')
			html = Elements.from(this.HTMLFieldInput.substitute(objInitializer) + this.HTMLFieldSeparator);
		else if (input.tagName.toLowerCase() == 'textarea')
			html = Elements.from(this.HTMLFieldTextarea.substitute(objInitializer) + this.HTMLFieldSeparator);
		else
			return false;

		formField = html[0];
		formField.getElement('input, textarea').set('value', objInitializer.value);
		if (formField.get('sls-lang') == this.currentFocusedLang)
			formField.addClass('focused');
		html.inject(this.actionBarInputsContainer);

		// Refresh the actions bar
		this.updateActionsBar();
	},

	navigateFields: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;

		var btn = event.target.hasClass('btn') ? event.target : event.target.getParent('.btn');
		if (!btn)
			throw new Error("I18n: The button element doesn't have the right HTML structure or class.");
		var currentFocusedField = this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']')[0].getElement('.input-lang.focused');
		if (btn.hasClass('previous')) {
			var inputToFocus = currentFocusedField.getPrevious('.input-lang');
			if (this.isFirstFieldOfLang(currentFocusedField))
				return false;
		} else if (btn.hasClass('next')) {
			var inputToFocus = currentFocusedField.getNext('.input-lang');
			if (this.isLastFieldOfLang(currentFocusedField))
				return false;
		} else
			throw new Error("I18n: The button element is missing one of the class: [previous/next]");

		this.onInputFocus(inputToFocus.getElement('input, textarea'));
	},

	switchWorkingLangs: function () {
		var currentReferenceLang = this.currentReferenceLang;
		this.currentReferenceLang = this.currentFocusedLang;
		this.currentFocusedLang = currentReferenceLang;

		this.langContainers.filter('[sls-lang=' + this.currentReferenceLang + ']').removeClass('focused').addClass('reference');
		this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']').removeClass('reference').addClass('focused');
	},

	switchFocusedLang: function (lang) {
		if (this.langs.indexOf(lang) == -1)
			return false;

		// Get the two lang containers
		var langOut = this.langContainers.filter('[sls-lang=' + this.currentFocusedLang + ']')[0];
		var langIn = this.langContainers.filter('[sls-lang=' + lang + ']')[0];

		// Hide the current focused lang
		langOut.removeClass('focused');
		// Show the new focused lang
		langIn
			.addClass('focused')
			.inject(langOut, 'before');
		langOut.inject(this.langsContainer, 'bottom');

		// Update the lang buttons in the top bar
		var langBtnOld = this.langBtns.filter(function (langBtn) {
			if (langBtn.getElement('[sls-lang=' + this.currentFocusedLang + ']'))
				return langBtn;
		}.bind(this))[0];
		var langBtnNew = this.langBtns.filter(function (langBtn) {
			if (langBtn.getElement('[sls-lang=' + lang + ']'))
				return langBtn;
		})[0];
		langBtnOld.removeClass('selected');
		langBtnNew.addClass('selected');

		// Set the new current focused lang
		this.currentFocusedLang = lang;

		// Unfocus the input in the current focused lang
		var oldFocusedInput = this.inputs.filter('.focused')[0];
		if (oldFocusedInput) {
			var inputIndex = langOut.getElements('.input-lang').indexOf(oldFocusedInput);
			oldFocusedInput.removeClass('focused');

			// Focus the corresponding input in the new focused lang
			if (inputIndex !== false)
				this.onInputFocus(langIn.getElements('.input-lang')[inputIndex].getElement('input, textarea'));
		}
	},

	checkBeforeTab: function (event) {
		if (typeOf(event) != 'domevent')
			throw new TypeError("I18n: wrong or missing argument !");
		var field = event.target.getParent('.input-lang');
		if (this.isLastFieldOfLang(field))
			event.stop();
	},

	checkBeforeShiftTab: function (event) {
		if (typeOf(event) != 'domevent')
			throw new TypeError("I18n: wrong or missing argument !");
		var field = event.target.getParent('.input-lang');
		if (this.isFirstFieldOfLang(field))
			event.stop();
	},

	isFirstFieldOfLang: function (field) {
		var result = false

		var nextInput = field.getPrevious('.input-lang');
		if (!nextInput) {
			_notifications.add('information', slsBuild.langs.SLS_BO_I18N_TAB_FIRST + " " + this.currentFocusedLang.toUpperCase() + " !");
			result = true;
		}

		return result;
	},

	isLastFieldOfLang: function (field) {
		var result = false

		var nextInput = field.getNext('.input-lang');
		if (!nextInput) {
			_notifications.add('information', slsBuild.langs.SLS_BO_I18N_TAB_LAST + " " + this.currentFocusedLang.toUpperCase() + " !");
			result = true;
		}

		return result;
	},

	updateActionsBar: function () {
		this.actionsBar.fireEvent('updated');
	},

	HTMLFieldInput: '<div class="sls-form-page-field" sls-lang="{lang}">' +
		'<label>{lang}</label>' +
		'<input type="text" name="{name}" value=""  sls-lang="{lang}" />' +
		'</div>',

	HTMLFieldTextarea: '<div class="sls-form-page-field" sls-lang="{lang}">' +
		'<label>{lang}</label>' +
		'<textarea name="{name}" sls-lang="{lang}" ></textarea>' +
		'</div>',

	HTMLFieldSeparator: '<hr class="sls-form-page-field-separator" />'
});

var ActionsBar = new Class({
	Implements: [Events],

	initialize: function () {
		this.bar = $('sls-bo-actions-bar');
		this.barWrapper = this.bar.getElement('.sls-bo-actions-bar-wrapper');
		this.barWrapperScroll = this.bar.getElement('.sls-bo-actions-bar-wrapper-scroll');
		this.barContent = this.barWrapper.getElement('.sls-bo-actions-bar-content');
		this._scrollbar = new Scrollbar(this.bar, this.barWrapper, this.barWrapperScroll, this.barContent, {
			offset: {
				y: {
					top: 100,
					bottom: 30
				},
				x: {
					left: 14
				}
			},
			position: 'left'
		});
		this.togglerBtn = this.bar.getElement('.toggler');
		this.sections = new Elements();

		if (this.bar.getElement('.sls-bo-filters')) {
			this._filters = new Filters();
			this.sections.push(this._filters.filterSection);
		}
		if (this.bar.getElement('.sls-bo-export')) {
			this._export = new Export();
			this.sections.push(this._export.exportSection);
		}
		if (this.bar.getElement('.sls-bo-uploads')) {
			this._uploads = new FilesActionsBar();
			this.sections.push(this._uploads.filesSection);
		}
		if (this.bar.getElement('.sls-bo-module-i18n'))
			this.sections.push(this.bar.getElement('.sls-bo-module-i18n'));
		if (this.bar.getElement('.sls-bo-quick-edit')) {
			this._quickEdit = new QuickEdit();
			this.sections.push(this.bar.getElement('.sls-bo-quick-edit'));
		}

		this.opened = !SlsView.expanded;

		if (this.sections.length)
			this.sectionTitles = this.sections.getElement('.sls-bo-actions-bar-section-title');

		this.initEvents();

		if (this.sections.length)
			this.toggleSection({target: this.sections[0].getElement('.sls-bo-actions-bar-section-title'), stop: function () {
			}});
	},

	initEvents: function () {
		if (this.sections.length) {
			this.sectionTitles.addEvent('click', this.toggleSection.bind(this));
			this.sections.addEvent('focus', this.focusOnSection.bind(this));
		}

		this.barWrapper.get('morph').setOptions({'duration': 250});
		this.barContent.get('morph').setOptions({'duration': 250});
		this._scrollbar.container.get('morph').setOptions({'duration': 250});

		if (this.togglerBtn) {
			this.togglerBtn.addEvents({
				'click': this.showBar.bind(this),
				'mouseenter': this.showBar.bind(this)
			});
		}

		this.bar.addEvents({
			'mouseleave': this.hideBarDelayed.bind(this),
			'updated': this._scrollbar.update.bind(this._scrollbar)
		});

		this.barWrapper.addEvent('mouseenter', this.showBar.bind(this));
		this.addEvent('updated', this._scrollbar.update.bind(this._scrollbar));
		//this._filters.addEvent('updated', this._scrollbar.update.bind(this._scrollbar));

		window.addEvents({
			'SlsViewChanged': this.updateOpenedStatus.bind(this)
		});
	},

	updateOpenedStatus: function (expanded) {
		if (this.opened && expanded)
			this.hideBar(true);
		if (expanded == this.opened)
			this.opened = !this.opened;

	},

	toggleSection: function (event) {
		if (!event || !event.stop)
			return false;
		else
			event.stop();

		var section = event.target.hasClass('sls-bo-actions-bar-section') ? event.target : event.target.getParent('.sls-bo-actions-bar-section');
		if (section.hasClass('opened'))
			this.closeSection(section);
		else {
			if (this.sections.filter('.opened').length)
				this.closeSection(this.sections.filter('.opened')[0]);
			if (!section.hasClass('sls-bo-quick-edit') || (section.hasClass('sls-bo-quick-edit') && this._quickEdit.isEnabled())) {
				this.openSection(section);
			}
		}
	},

	openSection: function (arg) {
		var section = null;
		if (typeOf(arg) == 'string')
			section = this.barContent.getElement(arg);
		else if (typeOf(arg) == 'element' && arg.hasClass('sls-bo-actions-bar-section'))
			section = arg;
		else
			throw new TypeError("Wrong or missing argument !");
		section.addClass('opened');
		section.getElement('.sls-bo-actions-bar-section-title').addClass('opened');

		_fixedToolbars.fireEvent('updated');
	},

	closeSection: function (arg) {
		var section = null;
		if (typeOf(arg) == 'string')
			section = this.barContent.getElement(arg);
		else if (typeOf(arg) == 'element' && arg.hasClass('sls-bo-actions-bar-section'))
			section = arg;
		else
			throw new TypeError("Wrong or missing argument !");
		section.removeClass('opened');
		section.getElement('.sls-bo-actions-bar-section-title').removeClass('opened');

		_fixedToolbars.fireEvent('updated');
	},

	focusOnSection: function (section) {
		if (!section || this.sections.indexOf(section) == -1)
			throw new Error("ActionsBar: The supplied argument is not an ActionsBar Section element!");

		if (!section.hasClass('opened'))
			this.toggleSection({stop: function () {
			}, target: section});
	},

	showBar: function () {
		if (this.togglingTimer)
			clearInterval(this.togglingTimer);

		if (this.opened || !SlsView.expanded)
			return false;

		this.barWrapper.setStyles({
			'opacity': 0,
			'left': 50
		});
		this._scrollbar.container.setStyles({
			'opacity': 0,
			'left': 50
		});
		this.bar.addClass('opened');
		this.opened = true;
		this.barWrapper
			.get('morph')
			.start({
				'opacity': 1,
				'left': 0
			});
		this._scrollbar.container.morph({
			'left': 0,
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

		this.barWrapper
			.get('morph')
			.start({
				'opacity': 0,
				'left': 50
			})
			.chain(function () {
				this.bar.removeClass('opened');
				this.barWrapper.setStyles({
					'opacity': null,
					'left': null
				});
				this.opened = false;
			}.bind(this));
		this._scrollbar.container
			.get('morph')
			.start({
				'left': (force && !SlsView.expanded) ? 50 : 0,
				'opacity': 0
			})
			.chain(function () {
				this.set({
					'opacity': 1,
					'left': 0
				});
			});
	},

	toElement: function () {
		return this.bar;
	}
});

var Filters = new Class({
	Implements: [Events],

	initialize: function () {
		this.filterSection = $$('.sls-bo-actions-bar-section.sls-bo-filters')[0];
		this.btnAdd = this.filterSection.getElement('.sls-bo-filter-add');

		this.form = this.filterSection.getElement('form');
		this.inputOrder = this.filterSection.getElement('#order');
		this.inputLength = this.filterSection.getElement('#length');
		this.filtersActive = this.filterSection.getElement('.sls-bo-filters-active');
		this.filtersList = this.filterSection.getElement('.sls-bo-filter-add .generic-dropdown-ac');
		this.filtersListWrapper = this.filtersList.getElement('.generic-dropdown-ac-wrapper');
		this.filtersListContent = this.filtersList.getElement('.generic-dropdown-ac-content');
		this.filtersTank = this.filterSection.getElement('.sls-bo-filters-tank');

		this.initEvents();
		this.initFields();
	},

	initEvents: function () {
		this.filtersActive.addEvents({
			'click:relay(.sls-bo-filter-title)': this.toggleFilter.bind(this),
			'click:relay(.sls-bo-filter .delete)': this.deleteFilter.bind(this)
		});

		this.filterSection.addEvent('change:relay(select)', this.adaptToSelectOption.bind(this));

		this.filtersListWrapper.get('morph').setOptions({duration: 250});
		this.btnAdd.addEvent('click', this.toggleFiltersList.bind(this));
		$$('html > body')[0].addEvent('click', this.closeFiltersList.bind(this));

		this.filtersList.addEvent('click:relay(.generic-dropdown-ac-option)', this.addFilter.bind(this));

		if (this.filtersActive.getElements('select').length)
			this.filtersActive.getElements('select').each(this.adaptToSelectOption.bind(this));
	},

	initFields: function () {
		this.filterSection.getElements('.sls-bo-filters-active .sls-bo-filter').each(initField);
	},

	update: function () {
		_fixedToolbars.fireEvent('updated');
	},

	adaptToSelectOption: function (arg) {
		var select;
		if (typeOf(arg) == 'object' && arg.target) {
			select = arg.target;
		} else if (typeOf(arg) == 'element') {
			select = arg;
		} else
			return false;

		if (!select || select.tagName != 'SELECT' || !select.getParent('.sls-bo-filter'))
			return false;

		var option = select.options[select.selectedIndex].value;
		var filter = select.getParent('.sls-bo-filter');
		var selectContainer = filter.getElement('.select.generic-select-mode');
		var input = selectContainer.getNext();

		if (!input)
			return false;

		var inputDisplay = input.retrieve('display');
		if (!inputDisplay) {
			inputDisplay = input.getStyle('display');
			input.store('display', inputDisplay);
		}

		if (option.indexOf("null") != -1)
			input.setStyles({'display': 'none'});
		else
			input.setStyles({'display': inputDisplay});

		this.update();
	},

	toggleFilter: function (event) {
		if (!event || !event.stop)
			return false;
		else
			event.stop();

		var filter = (event.target.hasClass('sls-bo-filter')) ? event.target : event.target.getParent('.sls-bo-filter');
		if (!filter)
			return false;

		filter.toggleClass('closed');
		this.update();
	},

	toggleFiltersList: function (event) {
		if (event && event.stop)
			event.stop();

		if (!this.filtersList.hasClass('opened'))
			this.openFiltersList();
		else
			this.closeFiltersList();
	},

	closeFiltersList: function () {
		this.filtersListWrapper
			.get('morph')
			.start({
				'opacity': 0,
				'height': 0
			})
			.chain(function () {
				this.filtersList
					.setStyles({
						'display': 'none'
					});
				this.update();
			}.bind(this));
		this.filtersList.removeClass('opened');
	},

	openFiltersList: function () {
		this.filtersList
			.setStyles({
				'display': 'block',
				'visibility': 'hidden'
			});

		this.filtersListWrapper
			.setStyles({
				'opacity': 0,
				'height': 0
			});

		this.filtersList
			.setStyles({
				'visibility': 'visible'
			});
		this.filtersListWrapper
			.get('morph')
			.start({
				'opacity': 1,
				'height': this.filtersListContent.getDimensions().height
			})
			.chain(function () {
				this.update();
				if (SlsView.actionsBar) SlsView.actionsBar.fireEvent('onScrollbarActivation');
			}.bind(this));
		this.filtersList.addClass('opened');
	},

	refreshFilterListSize: function () {
		this.filtersListWrapper
			.setStyles({
				'height': this.filtersListContent.getDimensions().height
			});
		this.update();
	},

	addFilter: function () {
		if (!arguments[0] || typeOf(arguments[0]) != "domevent")
			return false;

		arguments[0].stopPropagation();

		var option = (arguments[0].target.hasClass('generic-dropdown-ac-option')) ? arguments[0].target : arguments[0].target.getParent('.generic-dropdown-ac-option');
		var filterSelector = option.get('class').match(/sls\-bo\-filter\-\d+/gi)[0];

		if (!filterSelector)
			return false;

		var filter = this.filtersTank.getElement('.' + filterSelector);
		if (!filter)
			return _notifications.add('error', slsBuild.langs.SLS_BO_FILTER_UNKNOWN);

		var filterClone = filter.clone();
		filterClone.inject(this.filtersActive);
		initField(filterClone);
		var slsCustomRadios = filterClone.getElements('.radio input[type="radio"]');
		var slsCustomCheckboxes = filterClone.getElements('.checkbox input[type="checkbox"]');
		if (slsCustomRadios.length) {
			slsCustomRadios.each(function (radio) {
				var id = String.uniqueID();
				radio.set('id', id);
				radio.getParent('li').getElement('label').set('for', id);
				new Radio(radio);
			});
		}
		if (slsCustomCheckboxes.length) {
			slsCustomCheckboxes.each(function (checkbox) {
				var id = String.uniqueID();
				checkbox.set('id', id);
				checkbox.getParent('li').getElement('label').set('for', id);
				new Checkbox(checkbox);
			});
		}

		this.toggleFiltersList();
	},

	deleteFilter: function (event) {
		if (!event || !event.stop)
			return false;
		else
			event.stop();

		var filter = event.target.getParent('.sls-bo-filter');

		filter.destroy();

		this.refreshFilterListSize();
	}
});

var Export = new Class({
	initialize: function () {
		this.exportSection = $$('.sls-bo-actions-bar-section.sls-bo-export')[0];

		this.form = this.exportSection.getElement('form');
		this.submit = this.exportSection.getElement('.export-submit');

		this.initEvents();
		this.initFields();
	},

	initEvents: function () {
		this.submit.addEvent('click', this.send.bind(this));

		this.exportSection.addEvents({
			'click:relay(.sls-bo-export-field-title)': this.toggleParam.bind(this)
		});
	},

	initFields: function () {
		this.exportSection.getElements('.sls-bo-export-field').each(initField);
	},

	send: function () {
		this.form.submit();
	},

	toggleParam: function (event) {
		if (!event || !event.stop)
			return false;
		else
			event.stop();

		var filter = (event.target.hasClass('sls-bo-export-field')) ? event.target : event.target.getParent('.sls-bo-export-field');
		if (!filter)
			return false;

		filter.toggleClass('closed');
		this.update();
	},

	update: function () {
		_fixedToolbars.fireEvent('updated');
	}
});

var FilesActionsBar = new Class({
	Implements: [Events],

	nbFiles: 0,

	initialize: function () {
		this.filesSection = $$('.sls-bo-actions-bar-section.sls-bo-uploads')[0];
		this.title = this.filesSection.getElement('.sls-bo-actions-bar-section-title');
		this.displayedNbFiles = this.title.getElement('.results');
		this.content = this.filesSection.getElement('.sls-bo-actions-bar-section-content');

		this.initEvents();
	},

	initEvents: function () {
		this.addEvent('updated', this.refreshNbFiles.bind(this));
	},

	refreshNbFiles: function () {
		this.nbFiles = this.content.getElements('> .file').length;

		if (this.nbFiles == 0)
			this.displayedNbFiles.setStyle('display', 'none');
		else {
			this.displayedNbFiles
				.set('html', this.nbFiles + ' file' + ((this.nbFiles > 1) ? 's' : ''))
				.setStyle('display', 'block');
		}
	}
});

Element.implement({
	fireDelegatedEvent: function (event, target) {
		this.fireEvent(event, {
			target: target,
			stop: function () {
			},
			preventDefault: function () {
			},
			stopPropagation: function () {
			}
		});
	}
});

var Checkbox = new Class({
	initialize: function (checkbox) {
		this.checkbox = checkbox;
		this.relayer = this.checkbox.getParent('.checkbox-relayer');
		this.checkbox.store('Checkbox', this);

		this.initEvents();
		this.update();
	},

	initEvents: function () {
		var container = this.checkbox.getParent('.checkbox');
		var label = $$('label[for="' + this.checkbox.get('id') + '"]');

		this.checkbox.addEvent('change', this.update.bind(this));

		container.addEvent('click', function (e) {
			e.stop();
			this.select(container);
		}.bind(this));

		if (label) {
			label.addEvent('click', function (e) {
				if (e.target.tagName != "A") {
					e.stop();
					this.select(container);
				}
			}.bind(this));
		}
	},

	select: function (container) {
		if (this.checkbox.checked) {
			container.removeClass('checked');
			this.checkbox.checked = false;
		} else {
			if (container.getParents('.choices').length > 0 && container.getParent('.choices').get('class').test(/choices_limit_\d?/gi)) {
				var parent = container.getParent('.choices');
				var limit = parseInt(parent.get('class').match(/choices_limit_(\d?)/)[1]);
				if (limit && parent.getElements('.checkbox.checked').length == limit)
					return false;
			}

			container.addClass('checked');
			this.checkbox.checked = true;
		}

		this.update();
		this.checkbox.fireEvent('change');
		if (this.relayer) {
			this.relayer.fireDelegatedEvent('change', this.checkbox);
		}
	},

	update: function () {
		var container = this.checkbox.getParent('.checkbox');

		if (this.checkbox.checked && !container.hasClass('checked'))
			container.addClass('checked');
		else if (!this.checkbox.checked && container.hasClass('checked'))
			container.removeClass('checked');

		if (this.checkbox.get('sls-required') == 'true') {
			var checkedCheckboxes = $$('input[name="' + this.checkbox.get('name') + '"]:checked');
			var hiddenInputName = this.checkbox.get('name').replace(/\[\]$/, '');
			var hiddenInput = $$('input[name="' + hiddenInputName + '"][value=""]');
			if (checkedCheckboxes.length == 0 && hiddenInput.length == 0)
				Elements.from('<input type="hidden" name="' + hiddenInputName + '" value="" />')[0].inject(this.checkbox, 'before');
			else if (checkedCheckboxes.length > 0 && hiddenInput.length > 0)
				hiddenInput.destroy();
		}
	}
});

var QuickEdit = new Class({
	Implements: [Events],

	initialize: function () {
		this.quickEditSection = $$('.sls-bo-actions-bar-section.sls-bo-quick-edit')[0];
		this.activationBtn = $$('[sls-setting-name="quick_edit"][sls-setting-value="enabled"]')[0];

		this.form = this.quickEditSection.getElement('form');
		this.listing = $$('#sls-bo-view .sls-bo-listing').length ? $$('#sls-bo-view .sls-bo-listing')[0] : null;

		if (this.form.get('sls-validation') != 'true')
			throw new Error("QuickEdit: The js validation must be activated on the form in order to get the QuickEdit functionality!");

		this.HTMLForm = this.form.innerHTML;
		this.form.empty();

		this.initEvents();
	},

	asyncSubmit: function (event) {
		if (event && event.stop)
			event.stop();

		if (this.recordsetEditRequest && this.recordsetEditRequest.isRunning())
			return _notifications.add('warning', slsBuild.langs.SLS_BO_ASYNC_UNIQUE);

		// Make sure all input are well formed before sending asynchronously the inputs data
		this.prepareFormSubmitting();

		this.loading = _notifications.add('loading', false);
		this.recordsetEditRequest = new Request.SLSJSON({
			url: this.form.get('action').replace(/id\/$/, ''),
			data: {
				'sls-request': 'async',
				'reload-edit': 'true'
			},
			onSuccess: this.recordsetEditResponse.bind(this)
		}).send(this.form.toQueryString());
	},

	prepareFormSubmitting: function () {
		var ckeditorTextareas = this.form.getElements('textarea[sls-html=true]');

		// Copy the ckeditor content into the real textarea input
		if (ckeditorTextareas.length) {
			for (var i = 0; i < ckeditorTextareas.length; i++) {
				var ckeditorInstance = ckeditorTextareas[i].retrieve('CKEditor');
				if (ckeditorInstance) ckeditorInstance.updateElement();
			}
		}
	},

	isEnabled: function () {
		return this.activationBtn && this.activationBtn.get('sls-setting-selected') == 'true';
	},

	initEvents: function () {
		this.quickEditSection.addEvents({
			'clear': this.clearForm.bind(this)
		});

		this.addEvents({
			'edit': this.getRecordsetInfos.bind(this)
		});

		this.form.addEvents({
			'submit': this.asyncSubmit.bind(this)
		});

		window.addEvent('recordsetDeleted', this.checkRecordsetExistence.bind(this));
	},

	initFields: function () {
		this.form.getElements('.sls-form-page-field').each(initField);
	},

	checkRecordsetExistence: function (event) {
		if (!('id' in event))
			throw new TypeError("QuickEdit: recordset informations missing in event object !");

		if (this.recordsetId === null)
			return;

		if (event.id == this.recordsetId) {
			this.clearForm();
		}
	},

	recordsetEditResponse: function (xhr) {
		if (xhr.status == 'OK') {
			if ('message' in xhr)
				_notifications.add('success', xhr.message);
			this.applyChangesOnListing(xhr.result);
		} else if (xhr.status == 'ERROR') {
			if ('message' in xhr)
				_notifications.add('error', xhr.message);
			this.showErrors(xhr.result);
		}
		if (this.loading)
			_notifications.destroy(this.loading);
	},

	getRecordsetInfos: function (obj) {
		if (!this.isEnabled())
			return false;
		else if (!window.Validation)
			throw new Error("QuickEdit: The JS 'Validation' Class is required!");
		else if (typeOf(obj) != 'object' || !obj.id)
			throw new Error("QuickEdit: Supplied argument hasn't the good type!");

		if (this.recordsetInfosRequest && this.recordsetInfosRequest.isRunning())
			this.recordsetInfosRequest.cancel();

		this.loading = _notifications.add('loading', false, 'quickEdit');
		this.recordsetId = obj.id;
		this.recordsetInfosRequest = new Request.SLSJSON({
			url: this.form.get('action') + obj.id,
			data: {'sls-request': 'async'},
			onSuccess: this.setFormInputs.bind(this)
		}).send();
	},

	setRecordsetId: function (id) {
		this.recordsetId = id;
		var inputId = this.form.getElement('[name=id]');
		if (!inputId)
			inputId = new Element('input', {
				'type': 'hidden',
				'name': 'id'
			}).inject(this.form, 'top');
		inputId.set('value', this.recordsetId);
		this.form.set('sls-recordset-id', this.recordsetId);
	},

	setFormInputs: function (xhr) {
		if (xhr.status == 'OK') {
			this.resetForm();
			for (var column in xhr.result)
				this.setInputValue(column, xhr.result[column]);
		} else if (xhr.status == 'ERROR') {
			_notifications.add('error', slsBuild.langs.SLS_BO_EDIT_UNKNOWN);
		}
		if (this.loading) {
			_notifications.destroy(this.loading);
			this.loading = null;
		}

		// Refresh the form Validation
		this.setRecordsetId(this.recordsetId);
		this.form.fireEvent('reset');
		this.form.fireEvent('refresh');
		this.quickEditSection.fireEvent('focus', this.quickEditSection);
	},

	applyChangesOnListing: function (recordsetData) {
		if (!this.listing)
			return false;

		var checkbox = this.listing.getElement('.sls-bo-listing-recordset > .sls-bo-listing-cell input[type=checkbox][value="' + this.recordsetId + '"]'),
			row,
			cellContent;
		if (!checkbox)
			return false;
		row = checkbox.getParent('.sls-bo-listing-recordset');
		for (var column in recordsetData) {
			cellContent = row.getElement('.sls-bo-listing-cell[sls-table-column="' + column + '"][sls-editable=true] .sls-bo-listing-cell-content');
			if (!cellContent)
				continue;
			this.setCellContent(cellContent, recordsetData[column].join(','));
		}
	},

	setCellContent: function (cell, value) {
		if (Validation.prototype.types.url(value).status)
			value = '<a href="' + value + '" title="" target="_blank" class="sls-bo-color-text">' + value + '</a>';
		else if (Validation.prototype.types.color(value).status)
			value = '<div class="sls-bo-box-color" style="color:#000;background-color:' + (value.test(/^\#/) ? '' : '#') +value + '">#' + value + '</div>';
		else if (Validation.prototype.types.email(value).status)
			value = '<a href="mailto:' + value + '" title="" target="_blank" class="sls-bo-color-text">' + value + '</a>';
		else if (cell.getElement('.toggler-btn'))
		{
			var togglerBtn = cell.getElement('.toggler-btn');
			try {
				togglerBtn.getElement('input[value!="'+value+'"]').checked = false;
				togglerBtn.getElement('input[value="'+value+'"]').checked = true;
				togglerBtn.retrieve('TogglerBtnRadio').refreshState();
			} catch (e) {
				if (window.console)
					console.log(e);
			} finally {
				return;
			}
		}
		else
		{
			value = Validation.prototype.filters.striptags(value);
			value = value.substr(0, (value.length) > 150 ? 150 : value.length);
		}

		cell.set('html', value);
	},

	showErrors: function (recordsetData) {
		if (window.console)
			console.log("ERROR", recordsetData);
	},

	clearForm: function () {
		this.form.empty();
	},

	resetForm: function () {
		this.clearForm();
		this.form.innerHTML = this.HTMLForm;
		this.initFields();
	},

	setInputValue: function (name, values) {
		var input = this.form.getElement('[name*="[' + name + ']"]');
		if (!input)
			return false;
		var fieldObj = input.getParent('.sls-form-page-field').retrieve('ValidationParams');
		var length = values.length;
		for (var i = 0; i < length; i++) {
			try {
				Validation.prototype.setValue(fieldObj, values[i]);
			} catch (e) {
				if (window.console)
					console.log(e);
			}
		}
	}
});

var Radio = new Class({
	initialize: function (radio) {
		this.radio = radio;
		this.fakeRadio = this.radio.getParent('.radio');
		this.name = this.radio.get('name');
		this.radioContainers = $$('input[type="radio"][name="' + this.name + '"]').getParent('.radio');

		this.radio.addEvent('change', function () {
			if (this.radio.checked) {
				this.radioContainers.removeClass('checked');
				this.fakeRadio.addClass('checked');
			} else {
				this.fakeRadio.removeClass('checked');
			}
		}.bind(this));

		this.fakeRadio.addEvent('click', function () {
			if (!this.fakeRadio.hasClass('checked')) {
				this.radioContainers.removeClass('selected');
				this.fakeRadio.addClass('checked');
				this.radio.set('checked', 'checked');
			}
			this.radio.fireEvent('change');
		}.bind(this));

		if (this.radio.checked)
			this.fakeRadio.addClass('checked');

		if (this.radio.get('sls-required') == 'true' && $$('input[type="radio"][name="' + this.name + '"]:checked').length == 0) {
			var radioToCheck = $$('input[type="radio"][name="' + this.name + '"]')[0];
			radioToCheck
				.set('checked', true)
				.fireEvent('change');
		}
	}
});

var SlsSettings = new Class({
	initialize: function () {
		this.settingBtns = $$('*[sls-setting-name]');

		this.initEvents();
	},

	initEvents: function () {
		this.settingBtns.addEvent('click', this.setSetting.bind(this));

		var quickEditBtns = this.settingBtns.filter('[sls-setting-name="quick_edit"]');
		if (quickEditBtns.length) {
			quickEditBtns.addEvent('settingChanged:throttle', function () {
				if (!window._listing || !_listing._actionsBar || !_listing._actionsBar._quickEdit)
					return false;
				var enabled = _listing._actionsBar._quickEdit.isEnabled();
				if (!enabled)
					_listing._actionsBar.closeSection('.sls-bo-quick-edit');
				var text = $$('.fast-edit .state');
				if (text.length)
					text.set('html', (enabled) ? slsBuild.langs.SLS_BO_GENERIC_ENABLED : slsBuild.langs.SLS_BO_GENERIC_DISABLED);
			});
		}
	},

	setSetting: function () {
		var settingBtn;
		if (arguments.length == 0)
			return false;
		else if (typeOf(arguments[0]) == "domevent") {
			arguments[0].preventDefault();
			settingBtn = (arguments[0].target.attributes['sls-setting-name']) ? arguments[0].target : arguments[0].target.getParent('*[sls-setting-name]');
		} else if (arguments.length >= 2 && typeOf(arguments[0]) == "string" && typeOf(arguments[1]) == "string" && $$('*[sls-setting-name="' + arguments[0] + '"][sls-setting-value="' + arguments[1] + '"]').length)
			settingBtn = $$('*[sls-setting-name="' + arguments[0] + '"][sls-setting-value="' + arguments[1] + '"]')[0];
		else
			return false;

		var settingKey = settingBtn.get('sls-setting-name');
		var settingValue = settingBtn.get('sls-setting-value');
		var settingValueOff = settingBtn.get('sls-setting-value-off');

		if (!this.checkSpecific(settingKey, settingValue))
			return false;
		var loading = _notifications.add('loading', false, settingKey);

		new Request.SLSJSON({
			'url': urls.setting,
			'data': {
				'Key': settingKey,
				'Value': settingValueOff ? settingValueOff : settingValue,
				'sls-request': 'async'
			},
			'onSuccess': function (xhr) {
				if (xhr.status == "OK" && xhr.result[settingKey]) {
					var settingToUnselect = $$('*[sls-setting-name="' + settingKey + '"][sls-setting-value!="' + settingValue + '"]');
					if (settingToUnselect.length)
						settingToUnselect.set('sls-setting-selected', 'false');
					if (settingValueOff) {
						settingBtn.set('sls-setting-value', settingValueOff);
						settingBtn.set('sls-setting-value-off', settingValue);
					} else
						settingBtn.set('sls-setting-selected', 'true');
				}
				_notifications.destroy(loading);
				if (settingBtn.retrieve('events') != null)
					settingBtn.fireEvent('settingChanged');
			}
		}).send();
	},

	checkSpecific: function (key, value) {
		var allowed = true;

		if (key.indexOf('dashboard_') != -1 && value == 'visible' && $$('[sls-setting-name^="dashboard_"][sls-setting-value="visible"]').length == 1)
			allowed = false;

		return allowed;
	}
});

var getFormDataObject = function (form) {
	var values = {};
	form.getElements('input[type=text], input[type=password], input[type=hidden], input[type=checkbox]:checked, input[type=radio]:checked, select').each(function (element) {
		var results = /([\w\-]+)\[([\w\-]*)\]/gi.exec(element.get('name'));
		if (element.tagName == "SELECT")
			var value = element.getElement('option:selected').get('value');
		else
			var value = element.get('value');

		if (!!results && results.length > 0) {
			var nameArray = results[1];
			var nameVar = results[2];

			if (eval("!values." + nameArray))
				eval("values['" + nameArray + "'] = {}");

			eval("values['" + nameArray + "']['" + nameVar + "'] = value");
		} else {
			var nameVar = element.get('name');
			eval("values['" + nameVar + "'] = value");
		}
	});

	return values;
};

var getPGCD = function (a, b) {
	try {
		return (b == 0) ? a : getPGCD(b, a % b);
	} catch (e) {
		return 1;
	}
};

var _mimeTypes = {
	image: {
		'bmp': "image/bmp",
		'dib': "image/bmp",
		'gif': "image/gif",
		'jpg': "image/jpeg",
		'jpe': "image/jpeg",
		'jpeg': "image/jpeg",
		'jfif': "image/jpeg",
		'pcx': "image/pcx",
		'png': "image/png",
		'tif': "image/tiff",
		'tiff': "image/tiff",
		'ico': "image/x-icon",
		'pct': "image/x-pict"
	}
};

var Notifications = new Class({
	initialize: function () {

	},

	add: function (type, message, key) {
		if (!['success', 'warning', 'error', 'information', 'loading'].indexOf(type) == -1 || (typeOf(message) != 'string' && message !== false))
			return;
		if (type == 'loading')
			var notification = Elements.from(this.HTMLNotificationLoading)[0];
		else {
			var notification = Elements.from(this.HTMLNotification.substitute({type: type, message: message.replace(/\&lt\;/, '<').replace(/\&gt\;/, '>')}))[0];
			if (message.test(/(\n|\<br\s*\/\>)/) || message.length > 27 || type == 'loading')
				notification.addClass('big');
		}

		if (!this.container)
			this.buildContainer();
		if (type != 'loading')
			notification.notificationTimer = this.destroy.delay(4000, this, notification);
		notification
			.setStyles({'opacity': 0, 'top': -20})
			.inject(this.container, 'top')
			.morph({'opacity': 1, 'top': 0});
		if (key && typeOf(key) == "string"){
			key = "key_"+key.replace(/[^a-z]*/gi, '');
			var olderNotifications = $$('.sls-bo-notification.'+key);
			if (olderNotifications.length)
				olderNotifications.each(_notifications.destroy);
			notification.addClass(key);
		}

		return notification;
	},

	destroy: function (arg) {
		var notification;
		if (!arg)
			return false;
		else if (typeOf(arg) == 'element' && arg.hasClass('sls-bo-notification'))
			notification = arg;
		else if (typeOf(arg) == 'domevent' && (arg.target.hasClass('sls-bo-notification') || arg.target.getParent('.sls-bo-notification')))
			notification = (arg.target.hasClass('sls-bo-notification')) ? arg.target : arg.target.getParent('.sls-bo-notification');
		else
			return false;

		clearTimeout(notification.retrieve('timerDestroy'));

		notification
			.get('morph')
			.start({'opacity': 0})
			.chain(function () {
				this.start({'height': 0, 'margin-top': 0, 'padding-top': 0, 'padding-bottom': 0});
			})
			.chain(function () {
				notification.destroy();
			}.bind(this));
	},

	preventClosing: function (arg) {
		var notifications = this.container.getElements('.sls-bo-notification');
		notifications.each(function (notif) {
			clearTimeout(notif.notificationTimer);
			notif.addClass('sls-bo-notification-persistent');
		});
	},

	enableClosing: function () {
		if (!this.container)
			return false;
		var notifications = this.container.getElements('.sls-bo-notification');

		if (notifications.length) {
			notifications.each(function (notif) {
				if (notif.getElement('.sls-loading-css3'))
					return;
				notif.notificationTimer = this.destroy.delay(2000, this, notif);
				notif.removeClass('sls-bo-notification-persistent');
			}.bind(this));
		}
	},

	buildContainer: function () {
		this.container = new Element('div.sls-bo-notifications').inject(document.body);

		this.container.addEvents({
			'click:relay(.sls-bo-notification:not(.loading))': this.destroy.bind(this),
			'mouseenter:relay(.sls-bo-notification)': this.preventClosing.bind(this),
			'mouseleave': this.enableClosing.bind(this)
		});
	},

	HTMLNotification: '<div class="sls-bo-notification {type}">' +
		'<div class="picto"></div>' +
		'<div class="message">' +
		'<table class="vt_centered"><tr><td>' +
		'{message}' +
		'</td></tr></table>' +
		'</div>' +
		'</div>',

	HTMLNotificationLoading: '<div class="sls-bo-notification information loading big">' +
		'<div class="sls-loading-css3"></div>' +
		'<div class="message">' +
		'<table class="vt_centered"><tr><td>' +
		slsBuild.langs.SLS_BO_ASYNC_LOADING +
		'</td></tr></table>' +
		'</div>' +
		'</div>'
});
var _notifications = new Notifications();

var Tooltips = new Class({
	initialize: function () {

	},

	add: function (type, message, element) {
		if (['success', 'warning', 'error', 'information'].indexOf(type) == -1 || typeOf(message) != 'string' || typeOf(element) != 'element')
			return;
		var tooltip = Elements.from(this.HTMLTooltip.substitute({type: type, message: message.replace(/\&lt\;/, '<').replace(/\&gt\;/, '>')}))[0];
		var show = Tooltips.show.pass(tooltip, this);
		var hide = Tooltips.hide.pass(tooltip, this);
		var showEvent = '';
		var hideEvent = '';

		if (Tooltips.tooltips.indexOf(tooltip) == -1)
			Tooltips.tooltips.push(tooltip);

		if (element.type && (['text', 'password', 'textarea'].indexOf(element.type) != -1)) {
			showEvent = 'focus';
			hideEvent = 'blur';
			element.addEvents({
				'focus': show,
				'blur': hide
			});
		} else {
			showEvent = 'mouseenter';
			hideEvent = 'mouseleave';
			element.addEvents({
				'mouseenter': show,
				'mouseleave': hide
			});
		}

		tooltip
			.store('Sls-Tooltip', {
				type: type,
				message: message,
				referencedElement: element,
				show: {
					fn: show,
					event: showEvent
				},
				hide: {
					fn: hide,
					event: hideEvent
				}
			})
			.setStyles({
				'display': 'none',
				'opacity': 0
			});

		tooltip.inject(document.body);
		tooltip.get('morph').setOptions({duration: 250});
		//if (typeOf(document.activeElement) == 'element' && ['input','select','textarea'].indexOf(document.activeElement.tagName.toLowerCase()))
		//	element.fireEvent(showEvent);

		return tooltip;
	},

	destroy: function (tooltip) {
		if (!tooltip)
			return false;
		var SlsTooltip = tooltip.retrieve('Sls-Tooltip');
		SlsTooltip.referencedElement.removeEvent(SlsTooltip.show.event, SlsTooltip.show.fn);
		SlsTooltip.referencedElement.removeEvent(SlsTooltip.hide.event, SlsTooltip.hide.fn);
		SlsTooltip = null;
		tooltip.eliminate('Sls-Tooltip');
		Tooltips.hide(tooltip, true);
	},

	HTMLTooltip: '<div class="sls-bo-tooltip {type} rightOriented">' +
		'<div class="arrow"></div>' +
		'<div class="message">' +
		'<table class="vt_centered"><tr><td>' +
		'{message}' +
		'</td></tr></table>' +
		'</div>' +
		'</div>'
});
Tooltips.offset = {
	x: 11
};
Tooltips.tooltips = new Elements();
Tooltips.show = function (tooltip) {
	var orientation = tooltip.hasClass('leftOriented') ? 'left' : 'right';
	var referencedElement = tooltip.retrieve('Sls-Tooltip').referencedElement;
	var referencedElementCoordinates = referencedElement.getCoordinates();
	var referencedElementSizes = referencedElement.getComputedSize();
	var tooltipSizes = tooltip.measure(function () {
		return this.getComputedSize();
	});

	var styles = {
		'top': referencedElementCoordinates.top + (Math.round(referencedElementSizes.totalHeight / 2)),
		'margin-top': -Math.round((tooltipSizes.totalHeight / 2)),
		'opacity': 0,
		'display': 'block'
	};
	var morph = {
		'opacity': 1
	};
	if (orientation == 'left') {
		styles.right = window.getWidth() - referencedElementCoordinates.left + Tooltips.offset.x;
		styles.left = null;
		morph.marginRight = 0;
		morph.marginLeft = null;
	} else {
		var field = (referencedElement.hasClass('sls-form-page-field') || referencedElement.getParent('.sls-form-page-field')) ? (referencedElement.hasClass('sls-form-page-field') ? referencedElement : referencedElement.getParent('.sls-form-page-field')) : false;
		styles.left = (field && field.getElement('.sls-error-picto')) ? field.getElement('.sls-error-picto').getCoordinates().left + 2 : referencedElementCoordinates.right + Tooltips.offset.x;
		styles.right = null;
		morph.marginRight = null;
		morph.marginLeft = 0;
	}

	tooltip
		.setStyles(styles)
		.morph(morph)
		.addClass('displayed');
};
Tooltips.hide = function (tooltip, destroy) {
	var orientation = tooltip.hasClass('leftOriented') ? 'left' : 'right';

	var morph = {
		'opacity': 0
	};
	if (orientation == 'left') {
		morph.marginRight = 20;
	} else {
		morph.marginLeft = 20;
	}

	tooltip
		.get('morph')
		.start(morph)
		.chain(function () {
			this.set({'display': 'none'});
			tooltip.removeClass('displayed');
			if (destroy === true)
				tooltip.destroy();
		});
};
var _tooltips = new Tooltips();

var FieldError = new Class({
	initialize: function () {

	},

	add: function (reference, message) {
		if (!reference || !message)
			throw new Error("FieldError: Missing parameter to add an error.");

		// Get the reference element : must be always 'sls-form-page-field'
		var field = this.getField(reference);
		if (!field)
			return;
		var fieldError = field.retrieve('FieldError');
		if (fieldError) {
			if (fieldError.message != message)
				this.clear(field);
			else
				return false;
		}

		// Activate red outline
		field.addClass('sls-form-page-field-error');

		// Add error tick
		var tick = Elements.from(this.HTMLTick)[0].inject(field);

		// Get the best reference element for the tooltip
		var elementReference = field.getElement('input[type="text"]:not([sls-html-type="input_ac"]), input[type="password"], textarea[sls-html="true"]') || field.getElement('.sls-form-page-field-input');

		// Add tooltip
		if (!elementReference.getParent('[sls-tooltip=false]'))
			var tooltip = _tooltips.add('error', message, elementReference);

		field.store('FieldError', {
			tick: tick,
			message: message,
			elementReference: elementReference,
			tooltip: tooltip,
			activated: true
		});
	},

	clear: function (reference) {
		if (!reference)
			throw new Error("JS FieldError<br/>Missing parameter to remove an error.");

		var field = this.getField(reference);
		if (field === null)
			return false;
		var fieldError = field.retrieve('FieldError');
		if (!fieldError)
			return false;

		fieldError.tick.destroy();
		Tooltips.prototype.destroy(fieldError.tooltip);
		field.eliminate('FieldError');
		field.removeClass('sls-form-page-field-error');
	},

	getField: function (arg) {
		var field = null;
		if (typeOf(arg) == 'element' && (arg.hasClass('sls-form-page-field') || arg.getParent('.sls-form-page-field')))
			field = (arg.hasClass('sls-form-page-field')) ? arg : arg.getParent('.sls-form-page-field');
		else if (typeOf(arg) == 'string' && _formPage.formPage.getElement('[id^="' + arg + '"]'))
			field = _formPage.formPage.getElement('[id^="' + arg + '"]').getParent('.sls-form-page-field');

		return field;
	},

	HTMLTick: '<div class="sls-error-tooltipped">' +
		'<div class="sls-error-picto"></div>' +
		'</div>'
});
var _fieldError = new FieldError();

var FileDropZone = new Class({
	initialize: function (dropZone) {
		this.dropZone = dropZone;
		if (!this.dropZone || !this.dropZone.hasClass('sls-input-file-drop-zone'))
			throw new Error('JS FileDropZone<br/>Wrong parameters.');
		this.field = this.dropZone.getParent('.sls-form-page-field');
		this.inputFile = this.field.getElement('input[type="file"]');
		this._fileUpload = this.inputFile.retrieve('FileUpload');
		if (this._fileUpload.type == 'img')
			this._popinCrop = new PopinCrop(this.getImgLimits());
		this.btnBrowse = this.field.getElement('.sls-input-file-actions-browse');
		this.btnCrop = this.field.getElement('.sls-input-file-actions-crop');
		this.btnDelete = this.field.getElement('.sls-input-file-actions-trash');
		this.dropZone.store('FileDropZone', this);

		this.initEvents();
	},

	initEvents: function () {
		this.dropZone.addEvents({
			'updated': this.refreshState.bind(this),
			'click:relay(.sls-drop-zone-rendering)': this.showCropOnError.bind(this)
		});

		if (this._popinCrop) {
			// Open cropping popin
			this.btnCrop.addEvent('click', this._popinCrop.show.bind(this._popinCrop));

			// CallBack after cropping - retrieving img params
			this._popinCrop.addEvent('cropped', this._fileUpload.updateFormData.bind(this._fileUpload));
		}
		this.btnDelete.addEvent('click', this._fileUpload.clearDropZone.bind(this._fileUpload));
	},

	showCropOnError: function (event) {
		if (this._popinCrop && this.dropZone.getElement('.layer.sls-drop-zone-rendering') && this.dropZone.getParent('.sls-form-page-field-error'))
			this._popinCrop.show.call(this._popinCrop);
	},

	refreshState: function (event) {
		if (event.fileUploaded) {
			this.field.addClass('file-uploaded');
			if (this._fileUpload.type == 'img')
				this._popinCrop.data.imgSrc = this.dropZone.getElement('.sls-drop-zone-rendering img').get('src');
		} else {
			this.field.removeClass('file-uploaded');
			if (this._fileUpload.type == 'img')
				this._popinCrop.data.imgSrc = null;
		}
	},

	getImgLimits: function () {
		return {
			ratio: this.inputFile.get('sls-image-ratio') ? this.inputFile.get('sls-image-ratio') : null,
			minWidth: this.inputFile.get('sls-image-min-width') ? this.inputFile.get('sls-image-min-width') : null,
			minHeight: this.inputFile.get('sls-image-min-height') ? this.inputFile.get('sls-image-min-height') : null
		}
	}
});

var MultiUpload = new Class({
	Implements: [Events],

	initialize: function (container) {
		this.dropZoneContainer = container;
		this.dropZoneContainer.store('MultiUpload', this);
		this.id = 'MultiUpload-' + String.uniqueID();
		this.dropZoneContainer.set('id', this.id);
		this.dropZoneSquare = this.dropZoneContainer.getElement('.sls-form-children-drop-zone-square');
		this.input = this.dropZoneSquare.getElement('input[type="file"]');
		this.dropZone = this.dropZoneContainer.getElement('.sls-form-children-drop-zone');
		this.progressStep = this.dropZoneContainer.getElement('.progress-step');
		this.progressBar = this.progressStep.getElement('.sls-progress-bar-percentage');
		this.progressPercentage = this.progressStep.getElement('.percentage');
		this.progressNbFilesTotal = this.progressStep.getElement('.nb-files-total');
		this.progressNbFilesUploaded = this.progressStep.getElement('.nb-files-uploaded');
		var childrenSection = this.dropZoneContainer.getParent('.sls-form-page-children-section');
		if (!childrenSection || !childrenSection.retrieve('ChildrenBlocksManager')) {
			throw new Error('Js MultiUpload<br/>ChildrenBlocksManager Class is missing!');
			return;
		}
		this._childrenBlocksManager = childrenSection.retrieve('ChildrenBlocksManager');

		this.initEvents();
	},

	initEvents: function () {
		this.updateProgressBound = this.updateProgress.bind(this);
		this.addEvents({
			'updateProgress': this.updateProgressBound
		});

		this.dropZone.addEvent('dragenter', function (e) {
			e.stopPropagation();
			if (e.target == this.dropZone)
				this.dropZoneSquare.addClass('drag-over');
		}.bind(this));

		this.dropZone.addEvent('dragleave', function (e) {
			e.stopPropagation();
			if (e.target == this.dropZone)
				this.dropZoneSquare.removeClass('drag-over');
		}.bind(this));

		this.dropZone.addEvent('dragover', function (e) {
			e.preventDefault();
		}.bind(this));

		// Drop on dropzone
		this.dropZone.addEvent('drop', this.dropFiles.bind(this));

		this.input.addEvent('change', this.sendFiles.bind(this));
	},

	detectFileType: function (file) {
		var type = 'all';

		if (file.type.indexOf('image') != -1 || file.name.match(/\.(\w+)$/)[1] in _mimeTypes.image)
			type = 'img';

		return type;
	},

	// Modern Browsers drag&drop file sending method
	dropFiles: function (event) {
		event.preventDefault();
		this.dropZoneSquare.removeClass('drag-over');
		if (!window.FormData)
			return _notifications.add('warning', slsBuild.langs.SLS_BO_DRAG_AND_DROP);

		if (typeOf(event) == 'domevent') {
			event.stop();
			var files = event.event.dataTransfer.files;
			if (files.length == 0)
				return false;

			var limit = files.length > 20 ? 20 : files.length;

			this.uploads = [];
			for (var f = 0; f < limit; f++) {
				var block = this._childrenBlocksManager.addChildBlock();
				var type = this.detectFileType(files[f]);
				var input = block.getElement('input[type="file"][sls-type-extended="' + type + '"], input[type="file"][sls-type-extended="all"]');
				if (!input) {
					_notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_FILE_EXTENSION_FORBIDDEN);
					continue;
				}
				var fileUpload = input.retrieve('FileUpload');
				fileUpload.sendFile(files[f], f);
				this.uploads.push({progress: 0, size: files[f].size});
			}
			this.progressNbFilesUploaded.set('html', 0);
			this.progressNbFilesTotal.set('html', this.uploads.length);
			this.progressStep.setStyles({
				'display': 'block',
				'opacity': 1
			});

			if (files.length > limit)
				_notifications.add('warning', slsBuild.langs.SLS_BO_UPLOAD_LIMIT.replace('%s1', limit));
		}
	},

	// Modern Browsers drag&drop file sending method
	sendFiles: function (event) {
		if (!window.FormData)
			return _notifications.add('warning', slsBuild.langs.SLS_BO_DRAG_AND_DROP);

		var files = this.input.files;
		if (files.length == 0)
			return false;

		var limit = files.length > 20 ? 20 : files.length;

		this.uploads = [];
		for (var f = 0; f < limit; f++) {
			var block = this._childrenBlocksManager.addChildBlock();
			var type = this.detectFileType(files[f]);
			var input = block.getElement('input[type="file"][sls-type-extended="' + type + '"], input[type="file"][sls-type-extended="all"]');
			if (!input) {
				_notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_FILE_EXTENSION_FORBIDDEN);
				continue;
			}
			var fileUpload = input.retrieve('FileUpload');
			fileUpload.sendFile(files[f], f);
			this.uploads.push({progress: 0, size: files[f].size});
		}
		this.progressNbFilesUploaded.set('html', 0);
		this.progressNbFilesTotal.set('html', this.uploads.length);
		this.progressStep.setStyles({
			'display': 'block',
			'opacity': 1
		});

		if (files.length > limit)
			_notifications.add('warning', slsBuild.langs.SLS_BO_UPLOAD_LIMIT.replace('%s1', limit));
	},

	updateProgress: function (progress, key) {
		if (!this.uploads || !this.uploads.length)
			return false;

		this.uploads[key].progress = progress;
		var nbCompleted = this.uploads.filter(function (upload) {
			if (upload.progress == 100) return upload.progress;
		}).length;
		var totalSize = 0, loadedSize = 0, totalPercentage = 0;
		this.uploads.each(function (upload) {
			totalSize += upload.size;
			loadedSize += Math.floor((upload.progress / 100) * upload.size);
		});
		totalPercentage = Math.floor(loadedSize / totalSize * 100);

		this.progressNbFilesUploaded.set('html', nbCompleted);
		this.progressBar.setStyles({'width': totalPercentage + '%'});
		this.progressPercentage.set('html', totalPercentage + '%');

		if (this.uploads.length == nbCompleted) {
			_notifications.add('success', slsBuild.langs.SLS_BO_UPLOAD_SUCCESS);
			this.clear();
		}
	},

	clear: function () {
		this.progressStep.setStyles({
			'display': 'none',
			'opacity': 0
		});
		this.progressNbFilesUploaded.set('html', 0);
		this.progressNbFilesTotal.set('html', 0);
		this.progressBar.setStyles({'width': '0%'});
	}
});

var ChildrenBlocksManager = new Class({
	Implements: [Events],

	dropZoneContainer: null,
	blocks: [],

	initialize: function (section) {
		this.section = section;
		this.section.store('ChildrenBlocksManager', this);
		this.langs = this.section.getElements('.sls-bo-form-page-lang');
		this.addBlockBtns = this.section.getElements('.children-add-block');
		if (this.section.getElement('.sls-form-children-drop-zone-container')) {
			this.dropZoneContainer = this.section.getElement('.sls-form-children-drop-zone-container');
			this._multiUpload = new MultiUpload(this.dropZoneContainer);
		}

		this.initSkeleton();
		this.initEvents();
	},

	initSkeleton: function () {
		// Block in default lang
		var skeleton = this.section.getElement('.skeleton-child').removeClass('skeleton-child');
		if (!skeleton)
			throw new Error('JS ChildrenBlocksManager<br/>Skeleton structure for children block is missing!');

		// Block in other langs
		var nonSkeleton = this.section.getElements('.sls-bo-form-page-child:not(.skeleton-child):not(.sls-bo-form-page-child-draft)');
		if (nonSkeleton.length)
			nonSkeleton.destroy();

		// Block unsubmitted because of previous error(s)
		var draftFields = this.section.getElements('.sls-bo-form-page-child.sls-bo-form-page-child-draft .sls-form-page-field');
		if (draftFields.length)
			draftFields.each(initField);
		var drafts = this.langs.filter('[sls-default-lang=true]')[0].getElements('.sls-bo-form-page-child.sls-bo-form-page-child-draft');
		drafts.each(this.initDraftBlock.bind(this));

		this.skeleton = skeleton;
		this.skeleton.dispose();
	},

	initDraftBlock: function (block) {
		if (typeOf(block) != 'element' || !block.hasClass('sls-bo-form-page-child-draft') || !block.getParent('[sls-default-lang=true]'))
			return false;

		var index = block.getParent().getChildren('.sls-bo-form-page-child-draft').indexOf(block);
		var blockClones = new Elements;
		this.langs.filter('[sls-default-lang=false]').each(function (lang) {
			var blockClone = lang.getElements('.sls-bo-form-page-child-draft')[index];
			blockClone.store('ChildBlock', {type: 'secondary', reference: block});
			blockClones.push(blockClone);
		});
		block.store('ChildBlock', {type: 'main', clones: blockClones});
	},

	initEvents: function () {
		if (this._multiUpload) {
			this._multiUpload.addEvents({
				'uploading': this.addChildBlock.bind(this)
			});
		}

		this.section.addEvents({
			'click:relay(.child-delete)': this.removeChildBlock.bind(this)
		});
		this.addBlockBtns.addEvent('click', this.addChildBlock.bind(this));
	},

	addChildBlock: function () {
		var index = this.section.getElements('.sls-bo-form-page-lang.current .sls-bo-form-page-child').length;
		var defaultLang = this.langs.filter('[sls-default-lang=true]')[0].get('sls-lang');
		var id = String.uniqueID();
		var idReplacerRegExp = new RegExp(defaultLang + '\\_' + id);
		var block = Elements.from(
			this.skeleton.outerHTML
				.replace(/\$\$ID\$\$/g, id)
				.replace(/\$\$BLOCK\_NUMBER\$\$/g, index)
				.replace(/\$\$CHILD\_NUMBER\$\$/g, index + 1)
		)[0];

		if (this.langs.length > 1)
			var blockClones = new Elements;

		var blockTemplate = block.clone(true, true);
		this.langs.each(function (lang) {
			var langTxt = lang.get('sls-lang');
			if (lang.get('sls-default-lang') == 'true') {
				block.inject(lang.getElement('.children-add-block-container'), 'before');
				block.getElements('[name*="$$LANG$$"]').each(function (input) {
					input.set('name', input.get('name').replace(/\$\$LANG\$\$/, langTxt));
				});
				block.getElements('.sls-form-page-field').each(initField);
			} else {
				var cloneId = String.uniqueID();
				var clonedBlock = blockTemplate.clone(true, true);
				var unwantedFields = clonedBlock.getElements('.sls-form-page-field[sls-multilanguage="false"]');
				if (unwantedFields.length)
					unwantedFields.destroy();
				clonedBlock.inject(lang.getElement('.children-add-block-container'), 'before');
				blockClones.push(clonedBlock);
				clonedBlock.getElements('[name*="$$LANG$$"]').each(function (input) {
					input.set('name', input.get('name').replace(/\$\$LANG\$\$/, langTxt));
				});
				clonedBlock.getElements('[id],[for]').each(function (element) {
					var value = element.get('for') || element.get('id');
					element.set((element.get('for') ? 'for' : 'id'), value.replace(idReplacerRegExp, langTxt + '_' + cloneId));
				});
				clonedBlock.getElements('[sls-lang]').set('sls-lang', langTxt);
				clonedBlock.getElements('.sls-form-page-field').each(initField);
				clonedBlock.store('ChildBlock', {type: 'secondary', reference: block});
			}
		}.bind(this));

		block.store('ChildBlock', {type: 'main', clones: blockClones});
		this.blocks.push(block);

		return block;
	},

	removeChildBlock: function (arg) {
		var block = false;
		if (typeOf(arg) == 'element' && arg.hasClass('sls-bo-form-page-child'))
			block = arg;
		else if (typeOf(arg) == 'domevent' && (arg.target.getParent('.sls-bo-form-page-child')))
			block = arg.target.getParent('.sls-bo-form-page-child');
		var childBlockObj = block.retrieve('ChildBlock'), clones;
		if (childBlockObj.type == 'main')
			clones = childBlockObj.clones;
		else if (childBlockObj.type == 'secondary') {
			block = childBlockObj.reference;
			clones = block.retrieve('ChildBlock').clones;
		}

		if (!block) {
			throw new Error('JS ChildrenBlocksManager<br/>Cannot remove this element.');
			return;
		}
		var urlDeleteBtn = block.getElement('.child-delete[sls-async-action]');
		if (urlDeleteBtn && urlDeleteBtn.get('sls-async-action') && Validation.prototype.types.url(urlDeleteBtn.get('sls-async-action')).status) {
			new Request({
				url: urlDeleteBtn.get('sls-async-action')
			}).send();
		}

		block.destroy();
		clones.destroy();

		// update the inputs name's indexes
		this.langs.each(function (lang, langIndex) {
			var childrenBlocks = lang.getElements('.sls-bo-form-page-child');
			childrenBlocks.each(function (child, childIndex) {
				var inputs = child.getElements('.sls-form-page-field [name]');
				inputs.each(function (input) {
					input.set('name', input.get('name').replace(/\[(\d+)\]/, '[' + childIndex + ']'));
				});
			});
		});

		_notifications.add('success', slsBuild.langs.SLS_BO_BLOCK_REMOVE);
	}
});

var FileUpload = new Class({
	dropZone: {
		activated: false,
		element: null,
		caption: null,
		progress: null,
		rendering: null
	},
	uploaded: false,
	input: {
		element: null,
		name: null,
		id: null,
		required: null,
		minWidth: null,
		minHeight: null
	},
	type: 'all',
	ratio: true,

	initialize: function (input) {
		this.input.element = input;
		if (!this.input.element || this.input.element.tagName != 'INPUT' || this.input.element.type != 'file') {
			throw new Error('JS FileUpload<br/>You are trying to initialize a File Upload using an unsupported element.');
			return;
		}
		this.input.element.store('FileUpload', this);
		this.input.name = this.input.element.get('name');
		this.input.id = this.input.element.get('id');
		this.input.required = this.input.element.get('sls-required') == "true";
		this.input.element.set('name', 'upload[file]');
		this.type = ['all', 'img'].indexOf(this.input.element.get('sls-type-extended')) != -1 ? this.input.element.get('sls-type-extended') : 'all';
		if (this.input.element.get('sls-image-min-width'))
			this.input.minWidth = this.input.element.get('sls-image-min-width').toInt();
		if (this.input.element.get('sls-image-min-height'))
			this.input.minHeight = this.input.element.get('sls-image-min-height').toInt();
		if (this.input.element.get('sls-image-ratio'))
			this.input.ratio = this.input.element.get('sls-image-ratio').toFloat();
		if (this.input.element.getParent('.sls-input-file-drop-zone')) {
			this.dropZone.element = this.input.element.getParent('.sls-input-file-drop-zone');
			this.dropZone.activated = true;
		}
		if (this.input.element.getParent('.sls-form-page-children-section') && this.input.element.getParent('.sls-form-page-children-section').retrieve('ChildrenBlocksManager'))
			this._childrenBlocksManager = this.input.element.getParent('.sls-form-page-children-section').retrieve('ChildrenBlocksManager');
		if (Browser.ie8){
			this.dropZone.element.addClass('ie8');
			this.input.element.addClass('ie8');
		}

		this.initEvents();
		this.createEmptyFormData(true);
	},

	buildFormUpload: function () {
		if (this.uploaderContainer){
			this.uploaderContainer.destroy();
			this.uploaderContainer = null;
		}
		this.uploaderFormId = String.uniqueID();
		this.uploaderContainer = Elements.from(this.HTMLContainer.substitute({
			action: urls.upload,
			id: this.uploaderFormId,
			inputId: this.input.element.get('id'),
			type: this.type,
			fileUid: this.input.element.get('sls-file-uid')
		}))[0].inject(document.body);

		this.uploaderContainerForm = this.uploaderContainer.getElement('form');
	},

	createProgress: function (fileNameIn) {
		var fileName = fileNameIn || ((window.File && this.input.element.files.length > 0) ? this.input.element.files[0].name : ((this.input.element.value != '') ? this.input.element.value : 'nom inconnu'));
		this.dropZone.progress = Elements.from(this.HTMLDropZoneProgress.substitute({
			fileName: fileName
		}))[0].inject(this.dropZone.element);

		this.progressBar = this.dropZone.progress.getElement('.sls-progress-bar-percentage');
		this.progressPercentage = this.dropZone.progress.getElement('.percentage');

		if (window.slsBoSettings && slsBoSettings.apc && !slsBoSettings.apc.enabled) {
			this.progressBar.getParent('.progress').empty().addClass('loading');
			this.progressPercentage.destroy();
		}

		if (window._formPage && _formPage._actionsBar && _formPage._actionsBar._uploads) {
			this.fileInActionsBar = Elements.from(this.HTMLActionsBarUpload.substitute({
				'column-name': this.input.name.match(/\[(\w+)\]/g).getLast().replace(/(\_|\[|\])/g, ' ') + ((typeOf(this.key) == 'number') ? ' - n°' + (this.key + 1) : ''),
				'file-name': fileName
			}))[0].inject(_formPage._actionsBar._uploads.content);
			this.progressInActionsBar = this.fileInActionsBar.getElement('.sls-progress-bar-percentage');
			_formPage._actionsBar._uploads.fireEvent('updated');
			window.fireEvent('scroll');
		}
	},

	createRendering: function (thumb, force) {
		if (typeOf(thumb) != 'string')
			return false;

		var callback = function () {
			this.dropZone.rendering.getElement('.sls-loading-css3').destroy();
		}.bind(this);
		if (force) {
			callback = function () {
				this.uploaded = true;
				this.dropZone.rendering.getElement('.sls-loading-css3').destroy();
				this.dropZone.element.fireEvent('updated', {fileUploaded: this.uploaded});
			}.bind(this);
		}

		this.dropZone.rendering = Elements.from(this.HTMLDropZoneRendering.substitute({
			thumb: thumb
		}))[0].inject(this.dropZone.element);
		SlsImage.init(this.dropZone.rendering.getElement('img'), callback);
	},

	initEvents: function () {
		this.input.element.addEvents({
			'uploaded': this.receiveFile.bind(this)
		});

        if (Browser.platform == "windows" && window.File){
            this.input.element.erase("onchange");
            this.input.element.addEvent('change', function(e) {
                if (e && e.target && e.target.files && e.target.files[0])
                    this.sendFile(e.target.files[0]);
            }.bind(this));
        }

		this.dropZone.element.addEvent('dragenter', function (e) {
			e.stopPropagation();
			if (e.target == this.dropZone.element)
				this.dropZone.element.addClass('drag-over');
		}.bind(this));

		this.dropZone.element.addEvent('dragleave', function (e) {
			e.stopPropagation();
			if (e.target == this.dropZone.element && !e.target.getParent('.sls-input-file-drop-zone'))
				this.dropZone.element.removeClass('drag-over');
		}.bind(this));

		this.dropZone.element.addEvent('dragover', function (e) {
			e.preventDefault();
		}.bind(this));

		// Drop on dropzone
		this.dropZone.element.addEvents({
			'drop': this.sendFile.bind(this),
			'updated': this.fireUpdateOnInput.bind(this)
		});
	},

	fireUpdateOnInput: function () {
		this.input.element.fireEvent('updated');
	},

	receiveFile: function (data) {
		if (typeOf(data) != 'object')
			return _notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_ERROR);

		if (data.status == "OK") {
			this.data = data.data;
			if (data.thumb && data.thumb != ''){
				this.createRendering(data.thumb);
				this.dropZone.rendering.getElement('img').addEvent('loaded', this.createFormData.bind(this, data.data));
			}
			this.input.element.inject(this.dropZone.element);
			this.uploaded = true;
			this.fileName = data.data.tmp_name;
			if (this.fileInActionsBar) {
				this.fileInActionsBar.removeClass('uploading').addClass('success');
				this.fileInActionsBar.getElements('.sls-bo-color-text').removeClass('sls-bo-color-text');
				this.fileInActionsBar.getElement('.txt').set('html', slsBuild.langs.SLS_BO_UPLOAD_FINISH);
			}
		}

		this.dropZone.element.fireEvent('updated', {fileUploaded: this.uploaded});
	},

	createEmptyFormData: function (init) {
		if (init && this.dropZone.element.getElement('.sls-input-file-params input[type=hidden][value!=""][sls-input-poster!=""]')) {
			var input = this.dropZone.element.getElement('.sls-input-file-params input[type=hidden][value!=""][sls-input-poster!=""]');
			this.fileName = input.get('value');
			this.createRendering(input.get('sls-input-poster'), true);
			return this.emptyFormData = this.dropZone.element.getElement('.sls-input-file-params');
		}
		if (this.emptyFormData)
			this.emptyFormData.destroy();
		this.emptyFormData = Elements.from(this.HTMLEmptyFormData.substitute({inputName: this.input.name}))[0].inject(this.dropZone.element, 'top');
	},

	createFormData: function (data) {
		var values = data, dimensions = SlsImage.getDimensions.call(this.dropZone.rendering.getElement('img'));
		if (values === undefined){
			values = {
				name: this.fileName.match(/[^/]+$/)[0],
				tmp_name: this.fileName.replace(window.location.protocol+"//"+slsBuild.site.domainName+"/", ''),
				type: "image/"+this.fileName.match(/\.(\w+)$/)[1],
				size: this.input.element.get('sls-input-file-size'),
				error: 0
			};
		}
		values.inputName = this.input.name;
		values.x = 0;
		values.y = 0;
		values.w = (this.type == 'img') ? dimensions.width : 0;
		values.h = (this.type == 'img') ? dimensions.height : 0;

		if (this.emptyFormData) {
			this.emptyFormData.destroy();
			this.emptyFormData = null;
		}

		this.formData = Elements.from(this.HTMLFormData.substitute(values))[0].inject(this.dropZone.element, 'top');

		if (this.type == 'img'){
			if (((this.input.minWidth && this.input.minWidth > values.w) || (this.input.minHeight && this.input.minHeight > values.h))) {
				_notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_MIN_REZO + '<br/>' + (this.input.minWidth ? this.input.minWidth : 'X') + ' px * ' + (this.input.minHeight ? this.input.minHeight : 'X') + ' px.');
				return this.clearDropZone();
			} else if (this.input.ratio && (Math.round((values.w / values.h) * 100))/100 != this.input.ratio){
				_notifications.add('error', slsBuild.langs.SLS_BO_FORM_ERROR_RATIO);
				this.ratio = false;
				this.input.element.fireEvent('updated');
			}
		}
	},

	updateFormData: function (params) {
		if (!this.formData) {
			this.createFormData();
		}
		else if (typeOf(params) != 'object') {
			throw new Error('JS: FileUpload<br/>You are tying to update the file informations with wrong parameters!');
			return;
		}

		this.ratio = true;
		this.input.element.fireEvent('updated');
		if (params.img && typeOf(params.img) == 'element' && params.img.tagName.toLowerCase() == "img")
			params.img.replaces(this.dropZone.rendering.getElement('img'));
		this.formData.getElement('input[name$="[file][size][x]"]').set('value', params.x);
		this.formData.getElement('input[name$="[file][size][y]"]').set('value', params.y);
		this.formData.getElement('input[name$="[file][size][w]"]').set('value', params.w);
		this.formData.getElement('input[name$="[file][size][h]"]').set('value', params.h);
	},

	checkInputValue: function () {
		var file = false;
		if ((window.File && this.input.element.files.length > 0) || this.input.element.value != '')
			file = true;
		return file;
	},

	checkType: function (file) {
		var fileType;
		if (window.File && (this.input.element.files.length > 0 || typeOf(file) == 'object')) {
			file = (typeOf(file) == 'object') ? file : this.input.element.files[0];
			fileType = file.type != "" && file.type.match(/^(\w+)\//)[1] == 'image' ? 'img' : 'all';
		} else if (this.input.element.value != '') {
			fileType = this.input.element.value.match(/\.(\w+)$/)[1] in _mimeTypes.image ? 'img' : 'all';
		} else
			return false;

		if (this.type != 'all' && fileType != this.type)
			_notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_FILE_EXTENSION_FORBIDDEN);

		return (this.type == 'all') ? true : fileType == this.type;
	},

	submit: function (event) {
		if (!this.checkInputValue() || !this.checkType())
			return false;
		this.clearDropZone();
		this.buildFormUpload();
		this.createProgress();
		this.input.element.inject(this.uploaderContainerForm);
		if (!Browser.ie8)
			this.uploaderContainerForm.submit();

		if (window.slsBoSettings && slsBoSettings.apc && slsBoSettings.apc.enabled)
			this.checkProgress.delay(200, this);
		else
			this.undefinedLoading();
	},

	createSubmit: function(){
		this.dropZone.submit = Elements.from(this.HTMLDropZoneSubmit.substitute({
			fileName: this.fileName || '...',
			formId: this.uploaderContainerForm.get('id')
		}))[0].inject(this.dropZone.element);
		this.dropZone.submit.getElement('input[type=submit]').store('form', this.uploaderContainerForm);
	},

	sendFile: function (arg, key) {
		this.clearDropZone();
		var file;

		if (typeOf(key) == 'number')
			this.key = key;

		if (typeOf(arg) == 'domevent') {
			var event = arg;
			event.preventDefault();
			this.dropZone.element.removeClass('drag-over');
			if (!window.FormData)
				return _notifications.add('warning', slsBuild.langs.SLS_BO_DRAG_AND_DROP);
			if (event.event.dataTransfer.files.length == 0)
				return false;
			file = event.event.dataTransfer.files[0];
		} else if (typeOf(arg) == 'object') {
			file = arg;
		} else
			return false;

		if (!this.checkType(file))
			return false;

		var formData = new FormData();
		formData.append('upload[type]', (file.type.match(/^(\w+)\//)[1] == 'image') ? 'img' : 'all');
		formData.append('upload[file]', file);
		formData.append('sls-request', 'async');

		var xhr = new XMLHttpRequest();
		xhr.open("POST", urls.upload, true);
		xhr.onload = function () {
			var response = JSON.parse(xhr.responseText);
			var data = response.result;
			data.status = response.status;
			this.receiveFile(data);
		}.bind(this);

		xhr.upload.addEventListener('progress', this.checkAsyncProgress.bind(this), false);
		xhr.addEventListener('loadend', this.checkAsyncProgress.bind(this, {completed: true}));

		this.createProgress(file.name);
		xhr.send(formData);
	},

	checkAsyncProgress: function (event) {
		if (event.lengthComputable || event.completed) {
			var total, current, progress;
			if (event.completed)
				progress = 100;
			else {
				total = event.totalSize || event.total;
				current = event.loaded || event.position;
				progress = Math.floor(current / total * 100);
			}

			this.progressBar.setStyle('width', progress + '%');
			this.progressPercentage.set('html', progress + '%');
			if (this.progressInActionsBar)
				this.progressInActionsBar.setStyle('width', progress + '%');
			if (this._childrenBlocksManager)
				this._childrenBlocksManager._multiUpload.fireEvent('updateProgress', [progress, this.key]);
		} else
			this.progressPercentage.set('html', slsBuild.langs.SLS_BO_UPLOAD_PROGRESS_DISABLED);
	},

	clearDropZone: function () {
		if (this.uploaderContainer) {
			this.input.element.inject(this.dropZone.element);
			this.uploaderContainer.destroy();
			this.uploaderContainer = null;
			this.uploaderContainerForm = null;
		}
		if (this.dropZone.progress) {
			this.dropZone.progress.destroy();
			this.dropZone.progress = null;
		}
		if (this.dropZone.rendering) {
			this.dropZone.rendering.destroy();
			this.dropZone.rendering = null;
		}
		if (this.uploaded)
			this.deleteCurrentFile();
		if (this.formData) {
			this.formData.destroy();
			this.formData = null;
			this.createEmptyFormData();
		}
	},

	deleteCurrentFile: function () {
		if (!this.uploaded)
			return _notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_DELETE_NO_FILE);

		if (this.formData || this.emptyFormData) {
			if (this.fileName && this.fileName != '' && !this.input.required) {
				var params = {
					file: this.fileName
				};
				var recordsetBlock = this.input.element.getParent('[sls-recordset-id]');
				var recordsetId = (recordsetBlock) ? recordsetBlock.get('sls-recordset-id') : null;
				if (recordsetId != null) {
					var recordsetLang = recordsetBlock.get('sls-lang') || recordsetBlock.getParent('[sls-lang]') ? recordsetBlock.getParent('[sls-lang]').get('sls-lang') : null || recordsetBlock.getElement('[sls-lang]') ? recordsetBlock.getElement('[sls-lang]').get('sls-lang') : null;
					if (recordsetId !== false && recordsetId.test(/^\d+$/)) {
						params = Object.merge(params, {
							id: recordsetId.toInt(),
							db: recordsetBlock.get('sls-db') || null,
							table: recordsetBlock.get('sls-model') || null,
							column: this.input.id.replace(new RegExp("\\_" + recordsetLang + "\\_?.*$"), '')
						});
					}
				}
				new Request.JSON({
					url: urls.delete_file,
					data: params
				}).send();
			}
			this.fileName = null;
			this.uploaded = false;
			this.dropZone.element.fireEvent('updated', {fileUploaded: this.uploaded});
			if (this.fileInActionsBar) {
				this.fileInActionsBar.destroy();
				this.fileInActionsBar = null;
				this.progressInActionsBar = null;
				_formPage._actionsBar._uploads.fireEvent('updated');
			}
			if (this.emptyFormData)
				this.createEmptyFormData();
		} else
			return _notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_DELETE_ERROR);
	},

	checkProgress: function () {
		this.requestProgress = new Request.JSON({
			url: urls.upload_progress,
			data: {
				file_uid: this.input.element.get('sls-file-uid')
			},
			onSuccess: function (xhr) {
				this.timerProgress = this.checkProgress.delay(20, this);
				if (xhr.status == "OK" && xhr.result && xhr.result.percent && typeOf(xhr.result.percent) == "number") {
					this.progressBar.setStyle('width', xhr.result.percent + '%');
					this.progressPercentage.set('html', xhr.result.percent + '%');
					if (this.progressInActionsBar)
						this.progressInActionsBar.setStyle('width', xhr.result.percent + '%');
				}

				if (this.uploaded || xhr.result.percent == 100)
					clearInterval(this.timerProgress);
			}.bind(this)
		}).send();
	},

	undefinedLoading: function () {

	},

	HTMLContainer: '<div class="sls-input-file-uploader">' +
		'<form action="{action}" method="post" target="iframe_uploader_{id}" enctype="multipart/form-data" >' +
		'<input type="hidden" name="APC_UPLOAD_PROGRESS" value="{fileUid}" />' +
		'<input type="hidden" name="upload[type]" value="{type}" />' +
		'<input type="hidden" name="upload[inputId]" value="{inputId}" />' +
		'</form>' +
		'<iframe name="iframe_uploader_{id}" frameborder="0" border="0" scrolling="no" width="0" height="0" />' +
		'</div>',

	HTMLDropZoneSubmit:   '<div class="layer sls-drop-zone-submit">' +
								'<div class="file-name">{fileName}</div>' +
								'<div class="">' +
									'<input type="submit" class="sls-bo-color" onclick="this.retrieve(\'form\').submit();return false;" value="Send" />' +
								'</div>' +
							'</div>',

	HTMLDropZoneProgress: '<div class="layer sls-drop-zone-progress">' +
		'<div class="file-name">{fileName}</div>' +
		'<div class="progress">' +
		'<div class="sls-progress-bar sls-bo-color-border">' +
		'<div class="sls-progress-bar-percentage sls-bo-color"></div>' +
		'</div>' +
		'<div class="percentage sls-bo-color-text">0%</div>' +
		'</div>' +
		'</div>',

	HTMLDropZoneRendering: '<div class="layer sls-drop-zone-rendering">' +
		'<div class="sls-loading-css3 sls-bo-color"></div>' +
		'<img sls-image-src="{thumb}" sls-image-fit="visible" />' +
		'</div>',

	HTMLEmptyFormData: '<div class="sls-input-file-params">' +
		'<input type="hidden" name="{inputName}[file]" value="" />' +
		'</div>',

	HTMLFormData:   '<div class="sls-input-file-params">' +
						'<input type="hidden" name="{inputName}[file][data][name]" value="{name}" />' +
						'<input type="hidden" name="{inputName}[file][data][tmp_name]" value="{tmp_name}" />' +
						'<input type="hidden" name="{inputName}[file][data][type]" value="{type}" />' +
						'<input type="hidden" name="{inputName}[file][data][size]" value="{size}" />' +
						'<input type="hidden" name="{inputName}[file][data][error]" value="{error}" />' +
						'<input type="hidden" name="{inputName}[file][size][x]" value="{x}" />' +
						'<input type="hidden" name="{inputName}[file][size][y]" value="{y}" />' +
						'<input type="hidden" name="{inputName}[file][size][w]" value="{w}" />' +
						'<input type="hidden" name="{inputName}[file][size][h]" value="{h}" />' +
					'</div>',

	HTMLActionsBarUpload: '<div class="file uploading">' +
		'<div class="column-name">{column-name}</div>' +
		'<div class="file-name">{file-name}</div>' +
		'<div class="status">' +
		'<div class="sls-progress-bar">' +
		'<div class="sls-progress-bar-percentage sls-bo-color"></div>' +
		'</div>' +
		'<div class="picto"></div>' +
		'<span class="txt sls-bo-color-text">' + slsBuild.langs.SLS_BO_UPLOAD_PROCESSING + '</span>' +
		'</div>' +
		'</div>'
});

var Popin = new Class({
	Implements: [Events],

	container: null,
	popin: null,
	bo: null,
	initialize: function (data) {
		this.data = data || false;
		this.bo = $('sls-bo');
		this.build();
	},

	build: function () {
		this.container = Elements.from(this.HTMLTemplate)[0];
		this.background = this.container.getElement('.sls-bo-popin-background');
		this.content = this.container.getElement('.sls-bo-popin-content');
		this.popin = this.container.getElement('.sls-bo-popin');
		this.insideContent = this.popin.getElement('.sls-bo-popin-inside-content');

		this.container
			.setStyles({
				'display': 'none',
				'position': 'fixed',
				'left': 0,
				'top': 0,
				'right': 0,
				'bottom': 0
			});

		this.background
			.setStyles({
				'position': 'fixed',
				'left': 0,
				'top': 0,
				'right': 0,
				'bottom': 0
			});

		this.content
			.setStyles({
				'position': 'absolute',
				'left': 0,
				'top': 0,
				'right': 0,
				'bottom': 0
			});

		this.popin
			.setStyles({
				'position': 'absolute',
				'left': '50%',
				'top': '10%',
				'width': 'auto',
				'margin': 'auto'
			});
		if (this.data && this.data.popinClass)
			this.popin.addClass(this.data.popinClass);

		this.container.addEvent('click', function (event) {
			if ([this.container, this.content].indexOf(event.target) != -1)
				this.destroy();
		}.bind(this));

		this.fireEvent('built');
	},

	show: function () {
		if (this.opened)
			return;

		this.fireEvent('showing');

		if (!this.isInjected()) {
			if (!this.container)
				this.build();
			this.container.inject(document.body);
			this.addContent(this.data);
		}

		this.container.setStyle('display', 'block');
		this.popin
			.get('morph')
			.start({'opacity': 1})
			.chain(function () {
				this.fireEvent('shown');
			}.bind(this));
		//this.bo.addClass('blurred');

		this.opened = true;
	},

	hide: function () {
		if (!this.opened)
			return;

		this.container.setStyle('display', 'none');
		this.popin.setStyle('opacity', 1);
		//this.bo.removeClass('blurred');

		this.opened = false;

		this.fireEvent('hidden');
	},

	destroy: function () {
		this.container
			.get('morph')
			.start({'opacity': 0})
			.chain(function () {
				//this.bo.removeClass('blurred');
				this.container.destroy();
				this.container = null;
				this.background = null;
				this.content = null;
				this.popin = null;
				this.insideContent = null;
				this.opened = false;
			}.bind(this));

		this.fireEvent('destroyed');
	},

	isInjected: function () {
		return (!(!this.container || this.container.getParent('body') == null));
	},

	toElement: function () {
		return this.container;
	},

	addContent: function (contents) {
		var elements;
		if (!this.HTMLContent)
			return false;
		if (contents && typeOf(contents) == "object")
			elements = Elements.from(this.HTMLContent.substitute(contents));
		else
			elements = Elements.from(this.HTMLContent);
		elements.inject(this.insideContent);
		var imgs = this.insideContent.getElements('img');
		if (imgs.length)
			imgs.each(SlsImage.init);
	},

	HTMLTemplate: '<div class="sls-bo-popin-container">' +
		'<div class="sls-bo-popin-background"></div>' +
		'<div class="sls-bo-popin-content">' +
		'<div class="sls-bo-popin">' +
		'<div class="sls-bo-popin-inside-content"></div>' +
		'</div>' +
		'</div>' +
		'</div>'
});

var PopinLogin = new Class({
	Extends: Popin,

	initialize: function (callback) {
		this.parent();
		this.callback = callback || null;
		this.popin.addClass('sls-login');

		this.show();
		this.initEvents();
	},

	initEvents: function () {
		this.form = this.insideContent.getElement('form');
		this.form.addEvent('submit', this.submitForm.bind(this));
	},

	submitForm: function (event) {
		if (this.loginRequest && this.loginRequest.isRunning())
			return _notifications.add('warning', slsBuild.langs.SLS_BO_ASYNC_UNIQUE);

		if (this.loading == null) {
			this.form.addClass('loading');
			this.loading = new Element('div.sls-loading-css3').inject(this.form.getElement('input[type=submit]', 'before'));
		}

		this.loginRequest = new Request.JSON({
			url: urls.login,
			onSuccess: this.handleFormResponse.bind(this)
		}).send(this.form.toQueryString());

		if (event && typeOf(event) == 'domevent')
			event.stop();
		return false;
	},

	handleFormResponse: function (xhr) {
		if (xhr.status == 'OK') {
			if (typeOf(this.callback) == 'function')
				this.callback();
			this.destroy();
		} else if (xhr.status == 'ERROR') {
			_notifications.add('error', (xhr.errors.length) ? xhr.errors[0] : "Error");
		}
		if (this.loading) {
			this.loading.destroy();
			this.loading = null;
		}
		this.form.removeClass('loading');
	},

	HTMLContent: '<div class="sls-bo-popin-title-block sls-bo-color">' +
		'<p class="sls-bo-popin-title">' +
		'<strong>' + ((slsBoUser.firstname) ? slsBoUser.firstname : slsBoUser.login) + ',</strong>' +
		'<br/>' +
		html_entity_decode(slsBuild.langs.SLS_BO_KEEP_ALIVE_TITLE) +
		'</p>' +
		'<div class="sls-bo-popin-user-img">' +
		'<img class="sls-image" sls-image-fit="cover" sls-image-src="' + slsBoUser.img + '" title="" alt="" />' +
		'</div>' +
		'</div>' +
		'<div class="sls-bo-popin-content-block">' +
		'<div class="sls-bo-popin-form">' +
		'<p>' + html_entity_decode(slsBuild.langs.SLS_BO_KEEP_ALIVE_SUBTITLE) + '</p>' +
		'<form action="' + urls.login + '" method="post">' +
		'<input type="hidden" name="sls-request" value="async" />' +
		'<div class="field">' +
		'<input type="text" name="admin[login]" placeholder="' + slsBuild.langs.SLS_BO_KEEP_ALIVE_LOGIN + '" value="' + ((slsBoUser.login) ? slsBoUser.login : '') + '" />' +
		'</div>' +
		'<div class="field">' +
		'<input type="password" name="admin[password]" placeholder="' + slsBuild.langs.SLS_BO_KEEP_ALIVE_PASSWORD + '" />' +
		'</div>' +
		'<div class="sls-bo-popin-form-actions">' +
		'<input type="submit" value="' + slsBuild.langs.SLS_BO_KEEP_ALIVE_SUBMIT + '" class="sls-bo-color"/>' +
		'<a href="' + urls.forgotten_pwd + '" title="" class="forgotten" target="_blank">' + slsBuild.langs.SLS_BO_KEEP_ALIVE_FORGOTTEN + '</a>' +
		'</form>' +
		'</div>' +
		'</div>'
});

var PopinCrop = new Class({
	Extends: Popin,

	params: {
		ratio: null,
		width: {
			min: null,
			max: null
		},
		height: {
			min: null,
			max: null
		}
	},

	data: null,

	initialize: function (data) {
		if (typeOf(data) != 'object')
			data = {};
		data.popinClass = 'sls-crop';
		this.data = data;
		this.parent(this.data);

		this.initParams();

		this.addEvents({
			'showing': this.startLoading.bind(this),
			'shown': this.init.bind(this),
			'ready': this.readyState.bind(this)
		});

		this.container.removeEvents('click');
	},

	initParams: function () {
		if (this.data.ratio)
			this.params.ratio = parseFloat(this.data.ratio);
		if (this.data.minWidth)
			this.params.width.min = parseInt(this.data.minWidth);
		if (this.data.minHeight)
			this.params.height.min = parseInt(this.data.minHeight);
	},

	init: function () {
		this.preview = this.insideContent.getElement('.sls-crop-preview');
		this.previewCanvas = this.preview.getElement('canvas');
		if (Browser.ie8)
			G_vmlCanvasManager.initElement(this.previewCanvas);
		this.previewCtx = this.previewCanvas.getContext('2d');
		this.cropView = this.insideContent.getElement('.sls-crop-view');
		this.visualBackgroundContainer = this.insideContent.getElement('.sls-crop-view .visual.background');
		this.cropZone = this.insideContent.getElement('.sls-crop-view .crop-zone');
		this.croppingZone = this.cropZone.getElement('.cropping-zone');
		this.btnTopLeft = this.cropZone.getElement('.top-left');
		this.btnTopRight = this.cropZone.getElement('.top-right');
		this.btnBottomLeft = this.cropZone.getElement('.bottom-left');
		this.btnBottomRight = this.cropZone.getElement('.bottom-right');
		this.informationBlockWidth = this.insideContent.getElement('.informations .information.width .value');
		this.informationBlockHeight = this.insideContent.getElement('.informations .information.height .value');
		this.informationBlockRatio = this.insideContent.getElement('.informations .information.ratio .value');
		this.btnCancel = this.insideContent.getElement('.cancel');
		this.btnSubmit = this.insideContent.getElement('.submit');

		var HTMLImg = '<img class="sls-image" sls-image-fit="visible" sls-image-src="' + this.data.imgSrc + '" title="" alt="" />';
		this.referenceImg = Elements.from(HTMLImg)[0].inject(this.visualBackgroundContainer, 'bottom');
		this.croppedImg = Elements.from(HTMLImg)[0].inject(this.croppingZone.getElement('.cropping-zone-overflow'));

		SlsImage.init(this.referenceImg, this.fireEvent.pass('ready', this));
		SlsImage.init(this.croppedImg, function () {
			this.croppedImg.setStyles({
				'left': null,
				'top': null
			});
			this.fireEvent('ready');
		}.bind(this));

		window.addEvent('resize', this.refreshCropZone.bind(this));

		this.btnCancel.addEvent('click', this.destroy.bind(this));
		this.btnSubmit.addEvent('click', this.validateCropping.bind(this));

		// Activate the move action
		this.croppingZone.store('DragMove', new Drag.Move(this.croppingZone, {
			'container': this.cropZone,
			'style': false,
			'onStart': this.recordInitialPosition.bind(this),
			'onDrag': this.move.bind(this),
			'onComplete': this.endMoving.bind(this)
		}));

		// Activate the four resizing possibilities
		new Drag(this.croppingZone, {
			'container': this.cropZone,
			'handle': this.btnTopLeft,
			'snap': 1,
			'style': false,
			'onStart': this.recordInitialPosition.bind(this),
			'onDrag': this.resize.bind(this),
			'onComplete': this.endResizing.bind(this)
		});

		new Drag(this.croppingZone, {
			'container': this.cropZone,
			'handle': this.btnTopRight,
			'snap': 1,
			'style': false,
			'onStart': this.recordInitialPosition.bind(this),
			'onDrag': this.resize.bind(this),
			'onComplete': this.endResizing.bind(this)
		});

		new Drag(this.croppingZone, {
			'container': this.cropZone,
			'handle': this.btnBottomRight,
			'snap': 1,
			'style': false,
			'onStart': this.recordInitialPosition.bind(this),
			'onDrag': this.resize.bind(this),
			'onComplete': this.endResizing.bind(this)
		});

		new Drag(this.croppingZone, {
			'container': this.cropZone,
			'handle': this.btnBottomLeft,
			'snap': 1,
			'style': false,
			'onStart': this.recordInitialPosition.bind(this),
			'onDrag': this.resize.bind(this),
			'onComplete': this.endResizing.bind(this)
		});
		// /Activate the four resizing possibilities

		this.moveWithKeysHandler = this.moveWithKeys.bind(this);
		window.addEvent('keydown', this.moveWithKeysHandler);
	},

	readyState: function () {
		this.refreshCropZone();
		this.stopLoading();
		if (this.params.ratio)
			this.setCroppingZoneRatio();
		this.computeParams();
	},

	refreshCropZone: function () {
		var dimCropView = this.cropView.getComputedSize();
		var dimImg = this.referenceImg.getComputedSize();

		this.cropZone
			.setStyles({
				'width': dimImg.width,
				'height': dimImg.height,
				'left': (dimCropView.width - dimImg.width) / 2,
				'top': (dimCropView.height - dimImg.height) / 2
			});
		this.positionImgInCroppingZone();
		this.computeParams();
	},

	startLoading: function () {
		this.notification = _notifications.add('loading', false);
	},

	stopLoading: function () {
		if (this.notification)
			_notifications.destroy(this.notification);
	},

	setCroppingZoneRatio: function () {
		var imgDimensions = this.referenceImg.getComputedSize(),
			cropZoneDimensions = this.cropZone.getDimensions(),
			styles,
			width,
			height;
		if (imgDimensions.width / this.params.ratio <= imgDimensions.height) {
			width = imgDimensions.width;
			height = imgDimensions.width / this.params.ratio;
		} else {
			height = imgDimensions.height;
			width = imgDimensions.height * this.params.ratio;
		}

		styles = {
			top: Math.floor((cropZoneDimensions.height - height) / 2),
			bottom: Math.floor((cropZoneDimensions.height - height) / 2),
			left: Math.floor((cropZoneDimensions.width - width) / 2),
			right: Math.floor((cropZoneDimensions.width - width) / 2)
		};

		this.croppingZone.setStyles(styles);
		this.positionImgInCroppingZone();
		this.drawPreview();
	},

	recordInitialPosition: function (element, event) {
		if (this.initialPosition)
			return false;

		this.initialPosition = {
			target: event.target,
			x: event.page.x,
			y: event.page.y
		};

		if (!this.initialPosition.target.hasClass('cropping-knob')) {
			var croppingZoneCoordinates = this.croppingZone.getCoordinates();
			this.initialPosition.Dx = this.initialPosition.x - croppingZoneCoordinates.left;
			this.initialPosition.Dy = this.initialPosition.y - croppingZoneCoordinates.top;
		}

		if ([this.btnTopLeft, this.btnTopRight, this.btnBottomRight, this.btnBottomLeft].indexOf(this.initialPosition.target) != -1)
			this.initialPosition.target.addClass('sls-bo-color');
	},

	flushInitialPosition: function () {
		this.btnTopLeft.removeClass('sls-bo-color');
		this.btnTopRight.removeClass('sls-bo-color');
		this.btnBottomRight.removeClass('sls-bo-color');
		this.btnBottomLeft.removeClass('sls-bo-color');

		this.initialPosition = null;
	},

	getEventCoordinates: function (event) {
		if (typeOf(event) != 'domevent')
			return _notifications.add('error', slsBuild.langs.SLS_BO_UPLOAD_CROP_ERROR);
		var cropZoneCoords = this.cropZone.getCoordinates();
		return {
			top: event.page.y - cropZoneCoords.top,
			right: cropZoneCoords.right - event.page.x,
			bottom: cropZoneCoords.bottom - event.page.y,
			left: event.page.x - cropZoneCoords.left
		};
	},

	getCroppingZoneCoordinates: function () {
		return Object.map(this.croppingZone.getStyles(['width', 'height', 'top', 'right', 'bottom', 'left']), function (value) {
			return value.toInt()
		});
	},

	move: function (croppingZone, event) {
		if (this.initialPosition.target.hasClass('cropping-knob'))
			return false;

		var Dx = this.initialPosition.x - event.page.x;
		var Dy = this.initialPosition.y - event.page.y;
		var croppingZoneCoords = this.getCroppingZoneCoordinates();

		if (croppingZoneCoords.left - Dx < 0) {
			croppingZoneCoords.right += Math.abs(0 - croppingZoneCoords.left);
			croppingZoneCoords.left = 0;
		} else if (croppingZoneCoords.right + Dx < 0) {
			croppingZoneCoords.left += Math.abs(0 - croppingZoneCoords.right);
			croppingZoneCoords.right = 0;
		} else {
			croppingZoneCoords.left -= Dx;
			croppingZoneCoords.right += Dx;
			this.initialPosition.x = event.page.x;
		}

		if (croppingZoneCoords.top - Dy < 0) {
			croppingZoneCoords.bottom += Math.abs(0 - croppingZoneCoords.top);
			croppingZoneCoords.top = 0;
		} else if (croppingZoneCoords.bottom + Dy < 0) {
			croppingZoneCoords.top += Math.abs(0 - croppingZoneCoords.bottom);
			croppingZoneCoords.bottom = 0;
		} else {
			croppingZoneCoords.top -= Dy;
			croppingZoneCoords.bottom += Dy;
			this.initialPosition.y = event.page.y;
		}

		croppingZone.setStyles({
			top: croppingZoneCoords.top,
			right: croppingZoneCoords.right,
			bottom: croppingZoneCoords.bottom,
			left: croppingZoneCoords.left
		});

		this.computeParams();
		this.positionImgInCroppingZone();
	},

	moveWithKeys: function (event) {
		if (typeOf(event) != 'domevent')
			throw new Error("PopinCrop: missing argument!");
		if (['up', 'down', 'left', 'right'].contains(event.key)) {
			event.stop();
			var Dx, Dy, croppingZone = this.croppingZone;

			if (['left', 'right'].contains(event.key)) {
				Dy = 0;
				Dx = event.key == 'left' ? 1 : -1;
			} else if (['up', 'down'].contains(event.key)) {
				Dx = 0;
				Dy = event.key == 'up' ? 1 : -1;
			} else
				throw new Error("PopinCrop: invalid argument!");

			var croppingZoneCoords = this.getCroppingZoneCoordinates();

			if (croppingZoneCoords.left - Dx < 0) {
				croppingZoneCoords.right += Math.abs(0 - croppingZoneCoords.left);
				croppingZoneCoords.left = 0;
			} else if (croppingZoneCoords.right + Dx < 0) {
				croppingZoneCoords.left += Math.abs(0 - croppingZoneCoords.right);
				croppingZoneCoords.right = 0;
			} else {
				croppingZoneCoords.left -= Dx;
				croppingZoneCoords.right += Dx;
			}

			if (croppingZoneCoords.top - Dy < 0) {
				croppingZoneCoords.bottom += Math.abs(0 - croppingZoneCoords.top);
				croppingZoneCoords.top = 0;
			} else if (croppingZoneCoords.bottom + Dy < 0) {
				croppingZoneCoords.top += Math.abs(0 - croppingZoneCoords.bottom);
				croppingZoneCoords.bottom = 0;
			} else {
				croppingZoneCoords.top -= Dy;
				croppingZoneCoords.bottom += Dy;
			}

			croppingZone.setStyles({
				top: croppingZoneCoords.top,
				right: croppingZoneCoords.right,
				bottom: croppingZoneCoords.bottom,
				left: croppingZoneCoords.left
			});

			this.positionImgInCroppingZone();
			this.computeParams();
		}
	},

	resize: function (croppingZone, event) {
		if (!this.initialPosition.target.hasClass('cropping-knob'))
			return false;

		var styles = {};
		var imgDimensions = this.croppedImg.getDimensions();
		var croppingZoneCoordinates = this.getCroppingZoneCoordinates();
		var imgRealDimensions = SlsImage.getDimensions.call(this.croppedImg);
		var coords = this.getEventCoordinates(event);

		switch (this.initialPosition.target) {
			case this.btnTopLeft:
				styles.top = (coords.top < 0) ? 0 : coords.top;
				styles.left = (coords.left < 0) ? 0 : coords.left;
				if (this.params.ratio)
					styles.left = Math.round(imgDimensions.width - (croppingZoneCoordinates.right + ( imgDimensions.height - ( coords.top + croppingZoneCoordinates.bottom )) * this.params.ratio));
				if (styles.left < 0) {
					styles.top = croppingZoneCoordinates.top;
					styles.left = croppingZoneCoordinates.left;
				}
				break;
			case this.btnTopRight:
				styles.top = (coords.top < 0) ? 0 : coords.top;
				styles.right = (coords.right < 0) ? 0 : coords.right;
				if (this.params.ratio)
					styles.right = Math.round(imgDimensions.width - (croppingZoneCoordinates.left + ( imgDimensions.height - ( coords.top + croppingZoneCoordinates.bottom )) * this.params.ratio));
				if (styles.right < 0) {
					styles.top = croppingZoneCoordinates.top;
					styles.right = croppingZoneCoordinates.right;
				}
				break;
			case this.btnBottomRight:
				styles.bottom = (coords.bottom < 0) ? 0 : coords.bottom;
				styles.right = (coords.right < 0) ? 0 : coords.right;
				if (this.params.ratio)
					styles.right = Math.round(imgDimensions.width - (croppingZoneCoordinates.left + ( imgDimensions.height - ( coords.bottom + croppingZoneCoordinates.top )) * this.params.ratio));
				if (styles.right < 0) {
					styles.bottom = croppingZoneCoordinates.bottom;
					styles.right = croppingZoneCoordinates.right;
				}
				break;
			case this.btnBottomLeft:
				styles.bottom = (coords.bottom < 0) ? 0 : coords.bottom;
				styles.left = (coords.left < 0) ? 0 : coords.left;
				if (this.params.ratio)
					styles.left = Math.round(imgDimensions.width - (croppingZoneCoordinates.right + ( imgDimensions.height - ( coords.bottom + croppingZoneCoordinates.top )) * this.params.ratio));
				if (styles.left < 0) {
					styles.bottom = croppingZoneCoordinates.bottom;
					styles.left = croppingZoneCoordinates.left;
				}
				break;
		}
		var width = imgDimensions.width - (((styles.left) ? styles.left : croppingZoneCoordinates.left) + ((styles.right) ? styles.right : croppingZoneCoordinates.right));
		var height = imgDimensions.height - (((styles.top) ? styles.top : croppingZoneCoordinates.top) + ((styles.bottom) ? styles.bottom : croppingZoneCoordinates.bottom))
		if (width < 1 || (this.params.width.min && width < (this.params.width.min * imgDimensions.width / imgRealDimensions.width))) {
			styles.left = croppingZoneCoordinates.left;
			styles.right = croppingZoneCoordinates.right;
		}
		if (height < 1 || (this.params.height.min && height < (this.params.height.min * imgDimensions.height / imgRealDimensions.height))) {
			styles.top = croppingZoneCoordinates.top;
			styles.bottom = croppingZoneCoordinates.bottom;
		}
		if (this.params.ratio) {
			var ratio = Math.round(((imgDimensions.width - ((styles.left ? styles.left : croppingZoneCoordinates.left) + (styles.right ? styles.right : croppingZoneCoordinates.right))) / (imgDimensions.height - ((styles.top ? styles.top : croppingZoneCoordinates.top) + (styles.bottom ? styles.bottom : croppingZoneCoordinates.bottom)))) * 100) / 100;
			if (ratio != this.params.ratio)
				return false;
		}
		this.croppingZone.setStyles(styles);

		this.computeParams();
		this.positionImgInCroppingZone();
	},

	endMoving: function () {
		this.flushInitialPosition();
		this.computeParams();
	},

	endResizing: function () {
		this.flushInitialPosition();
		this.computeParams();
	},

	positionImgInCroppingZone: function () {
		var coords = Object.map(this.croppingZone.getStyles(['left', 'top']), function (item) {
			return item.toInt();
		});

		this.croppedImg.setStyles({
			'left': -coords.left,
			'top': -coords.top
		});
	},

	computeParams: function () {
		var resizedDimensions = this.referenceImg.getComputedSize();
		var realDimensions = SlsImage.getDimensions.call(this.referenceImg);
		var croppedDimensions = this.croppingZone.getCoordinates(this.croppingZone.getOffsetParent());
		var ratioWidth = resizedDimensions.width / realDimensions.width;
		var ratioHeight = resizedDimensions.height / realDimensions.height;

		var params = {
			x: Math.round(croppedDimensions.left / ratioWidth),
			y: Math.round(croppedDimensions.top / ratioHeight),
			w: Math.round(croppedDimensions.width / ratioWidth),
			h: Math.round(croppedDimensions.height / ratioHeight)
		};

		if (params.x + params.w > realDimensions.width)
			params.w = realDimensions.width - params.x;
		if (params.y + params.h > realDimensions.height)
			params.h = realDimensions.height - params.y;

		Object.merge(this.params, params);

		this.informationBlockWidth.set('html', this.params.w + " px");
		this.informationBlockHeight.set('html', this.params.h + " px");
		var pgcd = getPGCD(this.params.w, this.params.h);
		this.informationBlockRatio.set('html', (this.params.w / pgcd) + '/' + (this.params.h / pgcd) + ' ( ' + (Math.round((this.params.w / this.params.h) * 100) / 100) + ' )');

		this.drawPreview();
	},

	drawPreview: function (final) {
		if (final) {
			this.previewCanvas.width = 283;
			this.previewCanvas.height = 283;
		}
		var width = this.params.w;
		var height = this.params.h;
		var ratio = Math.max(width / this.previewCanvas.width, height / this.previewCanvas.height);
		if (ratio > 1) {
			width = Math.ceil(width / ratio);
			height = Math.ceil(height / ratio);
		}

		this.previewCtx.clearRect(0, 0, this.previewCanvas.width, this.previewCanvas.height);
		this.previewCtx.drawImage(
			this.croppedImg,
			this.params.x,
			this.params.y,
			this.params.w,
			this.params.h,
			(this.previewCanvas.width - width) / 2,
			(this.previewCanvas.height - height) / 2,
			width,
			height
		);
		if (final && this.previewCanvas.toDataURL)
			return this.previewCanvas.toDataURL();
	},

	validateCropping: function () {
		if (this.previewCanvas.toDataURL){
			this.params.img = new Element('img');
			this.params.img.set('src', this.drawPreview(true));
			this.fireEvent('cropped', this.params);
		}

		this.destroy();
	},

	destroy: function () {
		// Remove specific event from the window
		if ('keydown' in window.retrieve('events'))
			window.removeEvent('keydown', this.moveWithKeysHandler);

		// Call base class method
		this.parent();
	},

	injectImg: function (imgSrc) {
		if (this.imgPreview) {
			this.imgPreview.destroy();
			this.imgPreview = null;
		}
		if (this.imgBackground) {
			this.imgBackground.destroy();
			this.imgBackground = null;
		}
		if (this.imgCropped) {
			this.imgCropped.destroy();
			this.imgCropped = null;
		}

		this.imgPreview = Elements.from('<img class="sls-image" sls-image-src="' + imgSrc + '" />')[0];
		this.imgBackground = this.imgPreview.clone().inject(this.preview);
		this.imgCropped = this.imgPreview.clone().inject(this.visualBackgroundContainer);
		this.imgPreview.inject(this.visualCroppedContainer);
	},

	HTMLContent: '<div class="sls-bo-popin-title-block sls-bo-color">' +
		'<div class="sls-bo-popin-title">' +
		'<table class="vt_centered"><tr><td>' +
		html_entity_decode(slsBuild.langs.SLS_BO_UPLOAD_CROP_TITLE) +
		'</td></tr></table>' +
		'</div>' +
		'</div>' +
		'<div class="sls-bo-popin-content-block">' +
		'<div class="sls-crop-preview loading">' +
		'<canvas width="227" height="227"></canvas>' +
		'</div>' +
		'<div class="informations">' +
		'<div class="information width">' +
		'<div class="label">Image width</div>' +
		'<div class="value">{imgWidth} px</div>' +
		'</div>' +
		'<div class="information height">' +
		'<div class="label">Image height</div>' +
		'<div class="value">{imgHeight} px</div>' +
		'</div>' +
		'<div class="information ratio">' +
		'<div class="label">Ratio</div>' +
		'<div class="value"></div>' +
		'</div>' +
		'</div>' +
		'<div class="sls-bo-popin-form">' +
		'<div class="cancel sls-bo-color-border sls-bo-color-text sls-bo-disabled-color-hover">' + slsBuild.langs.SLS_BO_UPLOAD_CROP_CANCEL + '</div>' +
		'<div class="submit sls-bo-color sls-bo-color-border sls-bo-color-text-hover">' + slsBuild.langs.SLS_BO_UPLOAD_CROP_SUBMIT + '</div>' +
		'</div>' +
		'<div class="sls-crop-view sls-image-container loading">' +
		'<div class="visual background">' +
		'<div class="transparent-overlay"></div>' +
		//'<img class="sls-image" sls-image-fit="visible" sls-image-src="{imgSrc}" title="" alt="" />' +
		'</div>' +
		'<div class="crop-zone">' +
		'<div class="cropping-zone">' +
		'<div class="cropping-zone-overflow">' +
		//'<img class="sls-image" sls-image-fit="visible" sls-image-src="{imgSrc}" title="" alt="" />' +
		'</div>' +
		'<div class="cropping-knob top-left sls-bo-color-border"></div>' +
		'<div class="cropping-knob top-right sls-bo-color-border"></div>' +
		'<div class="cropping-knob bottom-right sls-bo-color-border"></div>' +
		'<div class="cropping-knob bottom-left sls-bo-color-border"></div>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
});

var Login = new Class({
	Implements: [Events],

	request: null,

	initialize: function () {
		this.form = $$('#Login form')[0];
		if (!this.form)
			return;
		this.inputs = this.form.getElements('input[type="text"], input[type="password"], input[type="hidden"]');
		this.submit = this.form.getElement('input[type="submit"]');

		this.titleBlock = $$('#Login .title-block')[0];
		this.contentBlock = $$('#Login .content-block')[0];
		this.userImg = this.titleBlock.getElement('.user-img');

		this.initEvents();
		this.loadBackground();
	},

	initEvents: function () {
		this.form.addEvent('submit', this.send.bind(this));
		this.form.get('morph').setOptions({duration: 250});
	},

	loadBackground: function () {
		// Home Intro picture
		var homeIntro = new Element('img[sls-image-fit=cover]');

		homeIntro.onload = function () {
			this.backgroundBlock = new Element('div', {
				'styles': {
					'position': 'absolute',
					'left': 0,
					'top': 0,
					'right': 0,
					'bottom': 0
				}
			}).inject(this.titleBlock, 'top');
			homeIntro
				.setStyles({'opacity': 0, 'position': 'relative'})
				.inject(this.backgroundBlock);
			SlsImage.acceptedAttributes.fit.apply.pass(['cover', true], homeIntro)();
			SlsImage.initAttributes.call(homeIntro);
			SlsImage.addImage.call(homeIntro);
			homeIntro
				.get('morph')
				.setOptions({'duration': 250})
				.start({'opacity': 1});

			// Loading bar
			this.loadingBar = new Element('div.sls-loading-bar').inject(this.userImg, 'before').setStyles({'background-color': '#404B57', 'bottom': -2});
			this.loadingBar.get('morph').setOptions({'unit': '%'}).start({'width': 100});
		}.bind(this);

		homeIntro.src = window.location.protocol + '//' + window.location.host + '/Public/Files/__Uploads/images/bo/default_background.jpg';
		// /Home Intro picture
	},

	showForm: function () {
		this.form
			.setStyle('opacity', 0)
			.removeClass('sls-loading')
			.get('morph')
			.start({
				'opacity': 1
			})
			.chain(function () {
				this.fireEvent('formShown');
			}.bind(this));
	},

	hideForm: function () {
		this.form
			.get('morph')
			.start({
				'opacity': 0
			})
			.chain(function () {
				this.form
					.addClass('sls-loading')
					.setStyle('opacity', 1);
				this.fireEvent('formHidden');
			}.bind(this));

		//this.inputs.each(blur);
	},

	send: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;

		event.preventDefault();

		if (this.request && this.request.isRunning())
			return _notifications.add('warning', slsBuild.langs.SLS_BO_ASYNC_UNIQUE);

		var data = getFormDataObject(this.form);
		data['sls-request'] = 'async';

		this.request = new Request.JSON({
			'data': data,
			'onRequest': this.hideForm.bind(this),
			'onSuccess': this.sendCallback.bind(this)
		}).send();
	},

	sendCallback: function (xhr) {
		if (!xhr || typeOf(xhr) != 'object')
			return false;

		if (xhr.status == 'OK') {
			if (xhr.logged == 'true') {
				if (xhr.result) {
					this.transformPage(xhr.result, xhr.forward);
				} else
					window.location.reload();
			} else if (xhr.result) {
				try {
					for (var message in xhr.result)
						_notifications.add(message, xhr.result[message]);
				} catch (e) {
					throw new Error('Ajax callback not handled.');
				}
				if (xhr.forward) {
					return (function () {
						window.location.href = xhr.forward;
					}).delay(3000, this);
				}
			}
		} else if (xhr.status == 'ERROR' && xhr.errors && xhr.errors.length) {
			for (var error in xhr.errors)
				_notifications.add('error', xhr.errors[error]);
			if (xhr.expired == 'true' && xhr.forward) {
				return (function () {
					window.location.href = xhr.forward;
				}).delay(3000, this);
			}
			this.showForm();
		}
	},

	transformPage: function (user, forward) {
		// Background destroying
		if (this.backgroundBlock) {
			this.backgroundBlock
				.get('morph')
				.start({
					'opacity': 0
				});
		}
		// /Background destroying

		// Profile picture
		var profilePicture = new Element('img');

		profilePicture.onload = function () {
			this.userImg.setStyles({'border-color': user.color});

			profilePicture
				.setStyles({'opacity': 0, 'position': 'absolute'})
				.inject(this.userImg);
			SlsImage.acceptedAttributes.fit.apply.pass(['cover', true], profilePicture)();
			SlsImage.addImage.call(profilePicture);
			profilePicture
				.get('morph')
				.setOptions({'duration': 250})
				.start({'opacity': 1});
		}.bind(this);

		profilePicture.src = user.img;
		// /Profile picture

		// Intro sentence
		var greetings = Elements.from('<ul>' +
			'<li class="greeting">Hello ' + user.firstname + ' ' + user.name + '</li>' +
			'<li class="separator"></li>' +
			'<li>' +
			'<a href="" title="">Log out</a>' +
			'</li>' +
			'</ul>');
		var title = this.titleBlock.getElement('.title');
		title
			.get('morph')
			.start({'opacity': 0})
			.chain(function () {
				greetings
					.setStyle('opacity', 0)
					.replaces(title)
					.morph({'opacity': 1});
			}.bind(this));
		// /Intro sentence

		// Loading sentence
		var loadingSentence = new Element('div.loading-sentence', {
			'text': slsBuild.langs.SLS_BO_LOGIN_LOADING,
			'styles': {
				'opacity': 0
			}
		}).replaces(this.form)
			.morph({'opacity': 1});
		// /Loading sentence

		// Loading bar
		if (!this.loadingBar) {
			this.loadingBar = new Element('div.sls-loading-bar').inject(this.userImg, 'before').setStyles({'background-color': user.color, 'bottom': -2});
		}
		this.loadingBar.setStyles({'background-color': user.color}).get('morph').setOptions({'unit': '%'}).start({'width': 100});

		this.userImg
			.morph({
				'left': [this.userImg.getCoordinates().left, 65],
				'margin-left': 0
			});
		this.contentBlock.morph({'top': 43});
		this.titleBlock
			.setStyles({
				//'bottom': 'auto',
				'height': window.getHeight() / 2
			})
			.get('morph')
			.start({
				'height': 43
			})
			.chain(function () {
				window.location.href = forward;
			});
		this.titleBlock.getElement('.padded').morph({'padding': '0 128px'});
		this.contentBlock.getElement('.padded').morph({'padding': '0 128px'});
	}
});

var TogglerBtnRadio = new Class({
	initialize: function (togglerBtn) {
		this.toggler = togglerBtn;
		this.radioOff = this.toggler.getElement('input[type=radio][sls-toggler-state=off]');
		this.radioOn = this.toggler.getElement('input[type=radio][sls-toggler-state=on]');
		this.radios = new Elements([this.radioOn, this.radioOff]);
		this.url = this.toggler.get('sls-toggler-url') && Validation.prototype.types.url(this.toggler.get('sls-toggler-url')).status ? this.toggler.get('sls-toggler-url') : null;

		if (!this.radioOff || !this.radioOn)
			throw new Error('Toggler: one or two of the required radio input are missing!');

		this.toggler.store('TogglerBtnRadio', this);

		if (this.toggler.get('sls-toggler-activated') != 'false')
			this.initEvents();
		this.refreshState();
	},

	initEvents: function () {
		if (this.toggler.hasClass('read-only'))
			return;

		this.toggler.addEvent('click', this.switchState.bind(this));
	},

	refreshState: function () {
		if (this.radioOn.checked) {
			this.toggler
				.removeClass('disabled')
				.addClass('enabled');
		} else {
			this.toggler
				.removeClass('enabled')
				.addClass('disabled');
		}
	},

	switchState: function () {
		if (arguments.length && typeOf(arguments[0]) == 'domevent')
			arguments[0].stop();
		this.radios.filter(':not(:checked)')[0].checked = true;

		if (this.url)
			this.sendTogglingRequest();
		else
			this.refreshState();
	},

	sendTogglingRequest: function () {
		if (this.request && this.request.isRunning())
			return _notifications.add('warning', slsBuild.langs.SLS_BO_ASYNC_UNIQUE);

		this.notification = _notifications.add('loading', false);
		this.request = new Request.JSON({
			url: this.url + ((!this.url.test(/\/$/)) ? '/' : '') + this.radios.filter(':checked')[0].value,
			data: {'sls-request': 'async'},
			onSuccess: this.handleTogglingResponse.bind(this)
		}).send();
	},

	handleTogglingResponse: function (xhr) {
		if (xhr) {
			if (xhr.status == "OK") {
				this.refreshState();
				if (this.toggler.get('sls-toggler-notification') != "" && this.toggler.get('sls-toggler-notification') in slsBuild.langs)
					_notifications.add('success', slsBuild.langs[this.toggler.get('sls-toggler-notification')]);
			} else
				_notifications.add('error', slsBuild.langs.SLS_BO_ASYNC_ERROR);
		} else
			throw new Error("TogglerBtnRadio: The xhr call didn't get a proper response.");

		if (this.notification) {
			_notifications.destroy(this.notification);
			this.notification = null;
		}
	}
});

var PermissionSquare = new Class({
	initialize: function (square) {
		this.square = square;
		this.squareContainer = this.square.getParent();
		this.checkbox = this.square.getElement('input[type=checkbox]');

		if (!this.checkbox)
			throw new Error('PermissionSquare: one checkbox input is missing!');

		this.square.store('PermissionSquare', this);

		if (!this.square.hasClass('idle'))
			this.initEvents();
		this.refreshState();
	},

	initEvents: function () {
		this.squareContainer.addEvents({
			'click': this.switchState.bind(this),
			'mousedown': this.preventUserSelection.bind(this)
		});
		this.checkbox.addEvent('switched', this.refreshState.bind(this));
	},

	preventUserSelection: function (event) {
		if (event && typeOf(event) == 'domevent')
			event.preventDefault();
	},

	refreshState: function () {
		if (this.square.hasClass('idle'))
			return false;

		if (this.checkbox.checked) {
			this.square
				.removeClass('forbidden')
				.addClass('allowed');
		} else {
			this.square
				.removeClass('allowed')
				.addClass('forbidden');
		}
	},

	switchState: function () {
		if (arguments.length && typeOf(arguments[0]) == 'domevent')
			arguments[0].stop();
		this.checkbox.checked = !this.checkbox.checked;
		this.refreshState();
		this.checkbox.fireEvent('changed');
	}
});

var UserRights = new Class({
	initialize: function (section) {
		this.section = section;
		if (!this.section.hasClass('user-rights'))
			throw new Error('UserRights: HTML structure is not valid!');

		this.columnsCheckAll = this.section.getElements('th .permission-square');
		this.rowsCheckAll = this.section.getElements('td:first-child .permission-square');
		this.rightCheckBoxes = this.section.getElements('td:not(:first-child) .permission-square');

		if (this.rightCheckBoxes.length) {
			this.rightCheckBoxes.each(function (square) {
				new PermissionSquare(square);
			});
		}

		this.initEvents();
		this.updateAll();
	},

	initEvents: function () {
		this.columnsCheckAll.getParent().addEvent('click', this.checkAllColumn.bind(this));
		this.rowsCheckAll.getParent().addEvent('click', this.checkAllRow.bind(this));
		new Elements(this.rightCheckBoxes.getElements('input[type=checkbox]')).addEvent('changed', this.updateAll.bind(this));
	},

	updateRows: function () {
		this.rowsCheckAll.each(function (rowCheckAll) {
			var squares = rowCheckAll.getParent('tr').getElements('td:not(:first-child) .permission-square');
			var squaresAllowed = squares.filter('.allowed');
			var squaresForbidden = squares.filter('.forbidden');
			var squaresIdle = squares.filter('.idle');

			if ((squaresAllowed.length + squaresIdle.length) == squares.length)
				this.setAllowedSquare(rowCheckAll);
			else if (squaresAllowed.length > 0 && squaresForbidden.length > 0)
				this.setMixedSquare(rowCheckAll);
			else
				this.setForbiddenSquare(rowCheckAll);
		}.bind(this));
	},

	updateColumns: function () {
		this.columnsCheckAll.each(function (columnCheckAll) {
			var squares = new Elements(this.section.getElements('td input[id^="' + columnCheckAll.get('id').match(/(\w+\-)/g)[0] + '"]').getParent('.permission-square'));
			var squaresAllowed = squares.filter('.allowed');
			var squaresForbidden = squares.filter('.forbidden');
			var squaresIdle = squares.filter('.idle');

			if ((squaresAllowed.length + squaresIdle.length) == squares.length)
				this.setAllowedSquare(columnCheckAll);
			else if (squaresAllowed.length > 0 && squaresForbidden.length > 0)
				this.setMixedSquare(columnCheckAll);
			else
				this.setForbiddenSquare(columnCheckAll);
		}.bind(this));
	},

	updateAll: function () {
		this.updateRows();
		this.updateColumns();
	},

	checkAllColumn: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;
		var square = event.target.tagName.toLowerCase() == 'th' ? event.target : event.target.getParent('th');
		var id = square.getElement('.permission-square').get('id').match(/(\w+\-)/)[0];
		var squares = this.section.getElements('td .permission-square:not(.idle) input[id^="' + id + '"]');
		var allChecked = squares.filter(':checked').length == squares.length;
		if (allChecked)
			squares.set('checked', false);
		else
			squares.set('checked', true);
		squares.fireEvent('switched');
		this.updateAll();
	},

	checkAllRow: function (event) {
		if (!event || typeOf(event) != 'domevent')
			return false;
		var square = event.target.tagName.toLowerCase() == 'td' ? event.target : event.target.getParent('td');
		var squares = square.getParent('tr').getElements('td:not(:first-child) .permission-square:not(.idle) input');
		var allChecked = squares.filter(':checked').length == squares.length;
		if (allChecked)
			squares.set('checked', false);
		else
			squares.set('checked', true);
		squares.fireEvent('switched');
		this.updateAll();
	},

	setAllowedSquare: function (square) {
		square
			.removeClass('forbidden').removeClass('mixed').addClass('allowed');
	},

	setForbiddenSquare: function (square) {
		square
			.removeClass('allowed').removeClass('mixed').addClass('forbidden');
	},

	setMixedSquare: function (square) {
		square
			.removeClass('allowed').removeClass('forbidden').addClass('mixed');
	}
});

var ColorsMosaic = new Class({
	initialize: function (colorMosaic) {
		this.colorMosaic = colorMosaic;
		if (!this.colorMosaic.hasClass('colors-mosaic') && !this.colorMosaic.getElements('.color').length)
			return false;
		this.colors = this.colorMosaic.getElements('.color');
		this.radios = new Elements(this.colors.getElement('input[type=radio]'));

		this.initEvents();
	},

	initEvents: function () {
		this.colors.addEvent('click', this.selectColor.bind(this));
		this.radios.addEvent('updated', this.updateColor.bind(this));
	},

	selectColor: function () {
		var colorBlock = null, radio, selectedRadio;

		if (!arguments.length)
			throw new Error("ColorsMosaic: Missing parameter!");
		else if (typeOf(arguments[0]) == 'domevent')
			colorBlock = arguments[0].target.hasClass('color') ? arguments[0].target : arguments[0].target.getParent('.color');
		else if (typeOf(arguments[0]) == 'element')
			colorBlock = arguments[0];

		if (!colorBlock)
			throw new Error("ColorsMosaic: Wrong parameter type! Accepted: [event|element]");

		selectedRadio = this.radios.filter(':checked')[0];
		radio = colorBlock.getElement('input[type=radio]');
		radio.set('checked', true);
		radio.fireEvent('updated', radio);
		selectedRadio.fireEvent('updated', selectedRadio);
	},

	updateColor: function (radio) {
		if (!radio)
			throw new Error("ColorsMosaic: Input radio element missing!");
		var colorBlock = radio.getParent('.color');
		if (radio.checked)
			colorBlock.addClass('selected');
		else
			colorBlock.removeClass('selected');
	}
});

var Validation = new Class({
	field: {
		requiredParams: ['state', 'form', 'instance', 'type', 'required', 'lang', 'unique', 'inputs']
	},

	initialize: function (form) {
		if (!form || typeOf(form) != 'element')
			throw new Error('JS Validation<br/>Unable to initiate the Form Validation!');
		this.form = form;
		this.form.store('Validation', this);
		this.submitBtn = this.form.getElement('.sls-bo-form-page-submit');
		this.fields = [];

		this.initFormEvents();
	},

	addField: function (fieldObj) {
		if (typeOf(fieldObj) != 'object')
			throw new Error("The needed parameter doesn't have the right type! Required [Object object] - Given: " + typeOf(field));

		var missingParams = [];
		for (var i = 0; i < this.field.requiredParams.length; i++) {
			if (!(this.field.requiredParams[i] in fieldObj))
				missingParams.push(this.field.requiredParams[i]);
		}
		if (missingParams.length)
			throw new Error("Those params are missing from the initializing object: " + missingParams);

		if (this.fields.indexOf(fieldObj) == -1)
			this.fields.push(fieldObj);
		else
			throw new Error("This field has already been initialized for the form validation.");

		this.initFieldEvents(fieldObj);
	},

	initFormEvents: function () {
		var formEvents = this.form.retrieve('events');
		if (!formEvents || !('refresh' in formEvents))
			this.form.addEvent('refresh', this.testFormFields.bind(this));

		if (!formEvents || !('click' in formEvents))
			this.submitBtn.addEvent('click', this.submitForm.bind(this));

		if (!formEvents || !('reset' in formEvents))
			this.form.addEvent('reset', this.reset.bind(this));

		if (!('submitOnKeyEnter' in window.retrieve('events')))
			window.addEvent('submitOnKeyEnter', this.submitOnKeyEnter.bind(this));
	},

	reset: function () {
		this.submitBtn = this.form.getElement('.sls-bo-form-page-submit');
		this.initFormEvents();
	},

	testFormFields: function () {
		this.checkForExistentFields();
		var fieldObjs = this.fields.each(this.dispatch.bind(this));
		var fieldsWithError = fieldObjs.filter(function (fieldObj) {
			if (fieldObj.state == 'error')
				return fieldObj;
		});
		var readyToSubmit = fieldsWithError.length == 0;

		if (readyToSubmit) {
			this.form.fireEvent('success');
			this.submitBtn.addClass('sls-bo-color').removeClass('sls-bo-disabled');
		} else {
			var langs = [];
			fieldsWithError.each(function (field) {
				var lang = field.inputs[0].getParent('[sls-lang]').get('sls-lang');
				if (langs.indexOf(lang) == -1)
					langs.push(lang);
			});
			this.form.fireEvent('error', {langs: langs});
			this.submitBtn.removeClass('sls-bo-color').addClass('sls-bo-disabled');
		}

		return {
			readyToSubmit: readyToSubmit,
			fieldsWidthError: fieldsWithError
		};
	},

	checkForExistentFields: function () {
		var container = this.form;
		this.fields = this.fields.filter(function (field) {
			if (container.contains(field.inputs[0]))
				return field;
		});
	},

	submitForm: function () {
		var formStatus = this.testFormFields();

		if (formStatus.readyToSubmit) {
			if (this.form.retrieve('events') && 'submit' in this.form.retrieve('events'))
				this.form.fireEvent('submit');
			else {
				Utils.isLogged(function () {
					this.form.submit();
				}.bind(this));
			}
		} else
			_notifications.add('error', slsBuild.langs.SLS_BO_FORM_ERROR_SUBMIT);
	},

	submitOnKeyEnter: function () {
		if (this.form.getElement('input:focus'))
			this.submitForm();
	},

	initFieldEvents: function (fieldObj) {
		var events = {};
		var handler = this.dispatch.bind(this, fieldObj, this.testFormFields.bind(this));
		switch (fieldObj.type) {
			case 'ckeditor' :
			case 'select'   :
			case 'radio'    :
			case 'checkbox' :
			case 'datepicker':
			case 'address':
				events.change = handler;
				break;
			case 'colorpicker':
				events.blur = handler;
				events.keyup = handler;
				events.updated = handler;
				break;
			case 'file':
				events.updated = handler;
				break;
			case 'autocomplete':
				events.valueSetted = handler;
				events.valueUnsetted = handler;
				break;
			default:
				events.blur = handler;
				events.keyup = handler;
		}

		fieldObj.inputs.addEvents(events);
	},

	dispatch: function (fieldObj, callback) {
		if (typeOf(fieldObj) != 'object')
			throw new Error("Validation: no Object passed as parameter.");

		var action = {
			status: 'empty',
			message: null
		};

		if (this.getValue(fieldObj)) {
			// Apply all filters binded to the field
			if (fieldObj.filters !== null)
				this.applyFilters(fieldObj);

			// Check constraints on the field if any
			if (fieldObj.specificType !== null)
				action = this.checkSpecificType(fieldObj);
		}

		if (action.status != 'error') {
			// Required field
			if (fieldObj.required)
				action = this.isNotEmpty(fieldObj);
			// None required field
			else if (!fieldObj.required) {
				action = this.isNotEmpty(fieldObj);
				action.status = (action.status != 'success') ? 'empty' : action.status;
			}
		}

		if (action.status != 'error' && fieldObj.unique) {
			action.status = fieldObj.inputs[0].retrieve('UniqueField').unique ? 'success' : 'error';
			if (action.status == 'error')
				action.message = 'ERROR_UNIQUE_TYPE';
		}
		if (action.status == 'error' && fieldObj.type == 'file' && !fieldObj.inputs[0].retrieve('FileUpload').ratio)
			action.message = slsBuild.langs.SLS_BO_FORM_ERROR_RATIO;

		this.refreshFieldStatus(fieldObj, action);

		if (typeOf(callback) == 'function')
			callback();

		return fieldObj;
	},

	refreshFieldStatus: function (fieldObj, action) {
		if (action.status == 'success') {
			this.setFieldSuccess(fieldObj);
		} else if (action.status == 'error') {
			this.setFieldError(fieldObj, action.message);
		} else
			this.clearField(fieldObj);
	},

	applyFilters: function (fieldObj) {
		if (fieldObj.filters === null)
			throw new Error("Validation: This field doesn't have any filters.");

		for (var i = 0; i < fieldObj.filters.length; i++) {
			this.applyFilter(fieldObj, fieldObj.filters[i]);
		}
	},

	applyFilter: function (fieldObj, filter) {
		if (!(filter in Validation.prototype.filters))
			throw new Error('Validation: The filter "' + Validation.prototype.filters.striptags(filter) + '" doesn\'t exist.');
		this.setValue(fieldObj, this.filters[filter](this.getValue(fieldObj), fieldObj.nativeType));
	},

	checkSpecificType: function (fieldObj) {
		if (fieldObj.specificType === null)
			throw new Error("Validation: This field doesn't have any specific type.");
		else if (!(fieldObj.specificType in Validation.prototype.types))
			throw new Error("Validation: This specific type is not supported.");

		var result = this.types[fieldObj.specificType](this.getValue(fieldObj), fieldObj.nativeType, fieldObj.specificTypeExtended);
		result.status = result.status ? 'success' : 'error';

		return result;
	},

	isNotEmpty: function (fieldObj) {
		if (fieldObj.required === null)
			throw new Error("Validation: This field is not required.");

		var status = null, message = null, value = this.getValue(fieldObj);
		if (['string', 'boolean'].indexOf(typeOf(value)) != -1) {
			status = (value) ? 'success' : 'error';
		} else
			throw new Error('Validation: (function) getValue returned an unexpected type.');

		if (status == 'error')
			message = slsBuild.langs.SLS_BO_FORM_ERROR_REQUIRED;

		return {
			status: status,
			message: message
		};
	},

	getValue: function (fieldObj) {
		var value = null;

		switch (fieldObj.type) {
			case 'ckeditor' :
				value = fieldObj.inputs[0].retrieve('CKEditor').getData();
				break;
			case 'select'   :
				value = !!fieldObj.field.getElement(':selected[value!=""]');
				break;
			case 'radio'    :
			case 'checkbox' :
				value = !!fieldObj.field.getElement(':checked[value!=""]');
				break;
			case 'autocomplete':
				value = !!fieldObj.inputs.filter('[sls-html-type="input_ac"]')[0].get('value');
				break;
			case 'file':
				value = fieldObj.inputs[0].retrieve('FileUpload').uploaded && fieldObj.inputs[0].retrieve('FileUpload').ratio;
				break;
			/*case 'text'         :
			 case 'password'     :
			 case 'textarea'     :
			 case 'datepicker'   :*/
			default:
				value = fieldObj.inputs[0].get('value');
		}

		return value;
	},

	setValue: function (fieldObj, value) {
		switch (fieldObj.type) {
			case 'checkbox':
			case 'radio':
				var input = fieldObj.inputs.filter('[value="' + value + '"]')[0];
				if (input) {
					input.set('checked', true);
					input.fireEvent('change');
					var togglerBtn = input.getParent('.toggler-btn');
					if (togglerBtn && togglerBtn.retrieve('TogglerBtnRadio'))
						togglerBtn.retrieve('TogglerBtnRadio').refreshState();
				}
				break;
			case 'ckeditor':
				fieldObj.inputs[0].retrieve('CKEditor').setData(value);
				break;
			case 'select':
				var select = fieldObj.inputs[0];
				var option = select.getElement('option[value="' + value + '"]');
				if (option) {
					select.selectedIndex = select.getElements('option').indexOf(option);
					select.fireEvent('change');
				}
				break;
			/*case 'text':
			 case 'password':
			 case 'textarea':
			 case 'datepicker':*/
			default:
				fieldObj.inputs[0].set('value', value);
				if (fieldObj.unique)
					fieldObj.inputs[0].fireEvent('keyup');
		}
	},

	setFieldSuccess: function (fieldObj) {
		fieldObj.instance.unsetFieldError(fieldObj);
		fieldObj.instance.fields[fieldObj.instance.fields.indexOf(fieldObj)].state = 'success';
		if (!fieldObj.field.hasClass('sls-form-page-success')) {
			fieldObj.field.addClass('sls-form-page-success');
			new Element('div.sls-success-picto').inject(fieldObj.field);
		}
	},

	unsetFieldSuccess: function (fieldObj) {
		fieldObj.field.removeClass('sls-form-page-success');
		var successPicto = fieldObj.field.getElement('.sls-success-picto');
		if (successPicto)
			successPicto.destroy();
	},

	setFieldError: function (fieldObj, message) {
		fieldObj.instance.unsetFieldSuccess(fieldObj);
		_fieldError.add(fieldObj.field, message);
		fieldObj.instance.fields[fieldObj.instance.fields.indexOf(fieldObj)].state = 'error';
	},

	unsetFieldError: function (fieldObj) {
		_fieldError.clear(fieldObj.field);
	},

	clearField: function (fieldObj) {
		fieldObj.instance.unsetFieldError(fieldObj);
		fieldObj.instance.unsetFieldSuccess(fieldObj);
	},

	getFilters: function (element) {
		var serializedFilters = element.get('sls-filters');
		if (!serializedFilters)
			return null;
		return serializedFilters.split('|');
	},

	initField: function (field) {
		if (['input', 'textarea', 'select'].indexOf(field.tagName.toLowerCase()) != -1)
			field = field.getParent();

		var form = (field.tagName == 'FORM') ? field : field.getParent('form');
		if (!field.hasClass('sls-form-page-field') && field.getParent('.sls-form-page-field'))
			field = field.getParent('.sls-form-page-field');
		if (!field)
			return false;
		if (field.retrieve('ValidationParams') && field.retrieve('ValidationParams').state == 'initiated')
			throw new Error('Validation: This field has already been initialized!');
		var inputs = field.getElements('input[type!=hidden], textarea, select');
		if (!inputs.length)
			return false;
		var inputsInfos = inputs.map(Validation.prototype.getInputInfos);
		var formValidationInstance = (form && form.retrieve('Validation')) ? form.retrieve('Validation') : null;

		var validationParams = {
			field: field,
			state: 'initiated',
			form: form || null,
			instance: formValidationInstance,
			type: inputsInfos[0].type,
			required: inputsInfos[0].required,
			lang: inputsInfos[0].lang,
			unique: inputsInfos[0].unique,
			inputs: inputs,
			filters: Validation.prototype.getFilters(inputs[0]),
			nativeType: (inputs[0].get('sls-native-type')) ? inputs[0].get('sls-native-type') : null,
			specificType: (inputs[0].get('sls-specific-type')) ? inputs[0].get('sls-specific-type') : null,
			specificTypeExtended: (inputs[0].get('sls-specific-type-extended')) ? ((inputs[0].get('sls-specific-type-extended').indexOf('|') != -1) ? inputs[0].get('sls-specific-type-extended').split('|') : inputs[0].get('sls-specific-type-extended')) : null
		};
		//if (validationParams.specificType == null && ['int','float'].indexOf(validationParams.nativeType) != -1)
		//	validationParams.specificType = 'numeric';
		field.store('ValidationParams', validationParams);
		if (formValidationInstance)
			formValidationInstance.addField(validationParams);
	},

	getInputInfos: function (input) {
		var tag = input.tagName.toLowerCase(), html, type;
		switch (tag) {
			case 'input':
				if (['checkbox', 'radio', 'file'].indexOf(input.type) != -1)
					type = input.type;
				else if ('sls-native-type' in input.attributes && ['year', 'time', 'date', 'datetime', 'timestamp'].indexOf(input.attributes['sls-native-type'].nodeValue) != -1)
					type = 'datepicker';
				else if ('sls-ac-db' in input.attributes && 'sls-ac-entity' in input.attributes && 'sls-ac-column' in input.attributes && 'sls-ac-multiple' in input.attributes)
					type = (input.attributes['sls-ac-multiple'] == 'true') ? 'multiautocomplete' : 'autocomplete';
				else if ('sls-specific-type' in input.attributes && input.get('sls-specific-type') == 'color')
					type = 'colorpicker';
				else if ('sls-specific-type' in input.attributes && input.get('sls-specific-type') == 'address')
					type = 'address';
				else
					type = 'text';
				break;
			case 'textarea':
				type = input.get('sls-html') == 'true' ? 'ckeditor' : tag;
				break;
			default:
				type = tag;
				break;
		}

		return {
			type: type,
			required: 'sls-required' in input.attributes && input.attributes['sls-required'].nodeValue == 'true',
			unique: 'sls-unique' in input.attributes && input.attributes['sls-unique'].nodeValue == 'true',
			lang: ('sls-lang' in input.attributes && input.attributes['sls-lang'] != '') ? input.attributes['sls-lang'].nodeValue : false
		};
	},

	types: {
		email: function (value) {
			var status, message = null;

			status = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i.test(value);

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_EMAIL_TYPE
			};
		},

		color: function (value) {
			var status, message = null;

			status = /^#?(([a-fA-F0-9]){3}){1,2}$/.test(value);

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_COLOR_TYPE
			};
		},

		ip: function (value, nativeType, specificTypeExtended) {
			var status = true, message = null;

			if (typeOf(specificTypeExtended) == 'string') {
				switch (specificTypeExtended) {
					case 'v4':
						return Validation.prototype.types.ip_v4(value);
					case 'v6':
						return Validation.prototype.types.ip_v6(value);
					case 'both':
						status = (Validation.prototype.types.ip_v4(value) || Validation.prototype.types.ip_v6(value));
				}
			}

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_IP_BOTH_TYPE
			};
		},

		ip_v4: function (value) {
			var status, message = null;

			status = /^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/.test(value);

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_IPV4_TYPE
			};
		},

		ip_v6: function (value) {
			var status, message = null;

			status = /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(value);

			return {
				status: status,
				message: status ? null : slsBuilds.langs.SLS_BO_FORM_ERROR_IPV6_TYPE
			};
		},

		uniqid: function (value) {
			var status, message = null;

			status = /^[a-z0-9]*$/.test(value);

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_UNIQID_TYPE
			};
		},

		url: function (value) {
			var status, message = null;

			status = /^(http|ftp|https):\/\/[\w-]+(\.?[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?$/i.test(value);

			return {
				status: status,
				message: status ? null : slsBuild.langs.SLS_BO_FORM_ERROR_URL_TYPE
			};
		},

		numeric: function (value, nativeType, specificTypeExtended) {
			var status, message = null, testValue = Math.abs(value);

			switch (nativeType) {
				case 'int':
					status = value.test(/^-?[0-9]+$/);
					if (!status)
						message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_INT;
					break;
				case 'float':
					status = value.test(/^-?[0-9]+(\.[0-9]+)?$/);
					if (!status)
						message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_FLOAT;
					break;
			}

			if (['lt', 'gt', 'lte', 'gte', 'all'].indexOf(specificTypeExtended) != -1 && (status || !nativeType)) {
				if (!value.test(/^-?[0-9]+$/) && !value.test(/^-?[0-9]+(\.[0-9]+)?$/)) {
					status = false;
					message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE;
				}
				else {
					switch (specificTypeExtended) {
						case 'lt':
							status = value < 0;
							if (!status)
								message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_LOWER_THAN_ZERO;
							break;
						case 'lte':
							status = value <= 0;
							if (!status)
								message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_LOWER_OR_EQUAL_ZERO;
							break;
						case 'gt':
							status = value > 0;
							if (!status)
								message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_GREATER_THAN_ZERO;
							break;
						case 'gte':
							status = value >= 0;
							if (!status)
								message = slsBuild.langs.SLS_BO_FORM_ERROR_NUMERIC_TYPE_GREATER_OR_EQUAL_ZERO;
							break;
						case 'all':
							status = true;
							break;
					}
				}
			}

			return {
				status: status,
				message: message
			};
		},

		complexity: function (value, nativeType, specificTypeExtended) {
			var status = true, message = null;

			if (specificTypeExtended) {
				if (typeOf(specificTypeExtended) == 'string')
					specificTypeExtended = [specificTypeExtended];
				else if (typeOf(specificTypeExtended) != 'array')
					throw new Error("Validation: this complexity type is not supported or doesn't exist.");

				var specificTypeExtendedLength = specificTypeExtended.length;
				for (var i = 0; i < specificTypeExtendedLength; i++) {
					switch (specificTypeExtended[i]) {
						case 'lc':
							status = status === true && value.test(/[a-z]+/g);
							break;
						case 'uc':
							status = status === true && value.test(/[A-Z]+/g);
							break;
						case 'digit':
							status = status === true && value.test(/\d+/g);
							break;
						case 'wild':
							status = status === true && value.test(/[^A-Za-z0-9]+/g);
							break;
					}
				}
			}

			if (!status)
				message = slsBuild.langs.SLS_BO_FORM_ERROR_COMPLEXITY_TYPE;

			return {
				status: status,
				message: message
			};
		},

		position: function (value) {
			return Validation.prototype.types.numeric(value, 'int', 'gt');
		},

		address: function (value) {
			return {
				status: value != "",
				message: slsBuild.langs.SLS_BO_FORM_ERROR_REQUIRED
			};
		}
	},

	filters: {
		striptags: function (value) {
			return value.replace(/(<([^>]+)>)/ig, "");
		},

		lower: function (value) {
			return value.toLowerCase();
		},

		lcfirst: function (value) {
			return value.charAt(0).toLowerCase() + value.slice(1);
		},

		upper: function (value) {
			return value.toUpperCase();
		},

		ucfirst: function (value) {
			return value.charAt(0).toUpperCase() + value.slice(1);
		},

		alpha: function (value) {
			return value.replace(/[^A-Za-z]/g, '');
		},

		alnum: function (value) {
			return value.replace(/[^A-Za-z0-9]/g, '');
		},

		numeric: function (value, nativeType) {
			value = value.replace(/[^\d\-\.]/g, '');
			if (nativeType == 'float') {
				var splitted = value.split('.');
				if (splitted.length > 2) {
					value = '';
					for (var i = 0; i < splitted.length; i++)
						value += ((i == splitted.length - 1) ? '.' : '') + splitted[i];
					oç
				}
			} else
				value = value.replace(/\./g, '');

			return value;
		},

		ucwords: function (value) {
			return (value + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
				return $1.toUpperCase();
			});
		},

		trim: function (value) {
			return value.replace(/^\s+/, "").replace(/\s+$/, "");
		},

		ltrim: function (value) {
			return value.replace(/^\s+/, "");
		},

		rtrim: function (value) {
			return value.replace(/\s+$/, "");
		},

		nospace: function (value) {
			return value.replace(/\s/g, '');
		},

		sanitize: function (value) {
			return value;
		}
	}
});

if (!window.html_entity_decode){
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
}




window.addEvent('domready', function () {
	if (!Element.NativeEvents.dragenter || !Element.NativeEvents.dragover || !Element.NativeEvents.drop)
		Object.merge(Element.NativeEvents, {dragenter: 2, dragleave: 2, dragover: 2, drop: 2});

	Element.Events.submitOnKeyEnter = {
		base: 'keyup',
		condition: function (e) {
			return e.key == 'enter';
		}.bind(this)
	};

	Element.Events.tab = {
		base: 'keydown',
		condition: function (e) {
			return !e.shift && e.key == 'tab';
		}.bind(this)
	};

	Element.Events.shiftTab = {
		base: 'keydown',
		condition: function (e) {
			return e.shift && e.key == 'tab';
		}.bind(this)
	};

	if ($$('form[sls-validation=true]').length) {
		$$('form[sls-validation=true]').each(function (form) {
			new Validation(form);
		});
	}
	if ($('sls-bo-header'))
		window._header = new Header();

	/*
	 Page Type
	 */
	if ($$('#sls-bo-view .sls-bo-dashboard').length)
		new DashBoard();
	else if ($$('#sls-bo-view .sls-bo-listing').length)
		window._listing = new Listing();
	else if ($$('.sls-bo-i18n').length)
		new I18n();
	else if ($$('#sls-bo-view .sls-bo-form-page').length)
		window._formPage = new FormPage();
	else if ($('Login'))
		new Login();
	else
		new StandardPage();
	/*
	 Page Type
	 */

	if ($$('#sls-bo-view .sls-bo-page-user-edition .user-rights').length) {
		$$('#sls-bo-view .sls-bo-page-user-edition .user-rights').each(function (section) {
			new UserRights(section);
		});
	}

	if ($$('.sls-form-page-children-section').length) {
		$$('.sls-form-page-children-section').each(function (section) {
			new ChildrenBlocksManager(section);
		});
	}

	if ($$('*[sls-setting-name]').length)
		new SlsSettings();

	// Statics
	new Gallery();
	// /Statics

	if (window.CKFinder)
		CKFinder.setupCKEditor( null, { basePath : window.location.protocol+"//"+slsBuild.site.domainName+'/'+slsBuild.paths.scripts+'ckfinder/'} ) ;

	window.fireEvent('scriptsReady');
});

function initField(field) {
	if (!field) {
		//throw new Error('Validation: this object if not a valid html element.');
		return false;
	}
	if (['input', 'textarea', 'select'].indexOf(field.tagName.toLowerCase()) != -1)
		field = field.getParent();

	if (field.getParent('.skeleton-child'))
		return false;

	if (field.getElements('.radio input[type="radio"]').length) {
		field.getElements('.radio input[type="radio"]').each(function (radio) {
			new Radio(radio);
		});
	}
	if (field.getElements('.checkbox input[type="checkbox"]').length) {
		field.getElements('.checkbox input[type="checkbox"]').each(function (checkbox) {
			new Checkbox(checkbox);
		});
	}
	if (field.getElements('select').length > 0) {
		field.getElements('select').each(function (select) {
			if (select.getParent('.select'))
				new Select(select, {scrollbar: {offsetX: 0}});
		});
	}
	if (field.getElements('.sls-bo-colorpicker input[sls-specific-type=color]').length) {
		field.getElements('.sls-bo-colorpicker input[sls-specific-type=color]').each(function (input) {
			jQuery(input).ColorPicker({
				onChange: function (hsb, hex, rgb) {
					input.set('value', hex);
					input.fireEvent('updated', {value: hex});
				},
				onShow: function () {
					if (Validation.prototype.types.color(input.get('value')).status)
						jQuery(input).ColorPickerSetColor(Utils.hex3ToHex6(input.get('value')).replace(/\#/, ''));
				}
			});
			input.addEvent('keyup', function () {
				if (Validation.prototype.types.color(input.get('value')).status)
					jQuery(input).ColorPickerSetColor(Utils.hex3ToHex6(input.get('value')).replace(/\#/, ''));
			});
		});
	}
	if (field.getElements('input[sls-native-type="year"],input[sls-native-type="time"],input[sls-native-type="date"],input[sls-native-type="datetime"],input[sls-native-type="timestamp"]').length) {
		field.getElements('input[sls-native-type="year"],input[sls-native-type="time"],input[sls-native-type="date"],input[sls-native-type="datetime"],input[sls-native-type="timestamp"]').each(function (input) {
			var options = {};
			switch (input.get('sls-native-type')) {
				case 'year':
					options.pickOnly = 'years';
					options.format = '%Y';
					break;
				case 'time':
					options.pickOnly = 'time';
					options.format = '%H:%M:%S';
					break;
				case 'date':
					options.format = '%Y-%m-%d';
					break;
				case 'datetime':
					options.timePicker = 'true';
					options.format = '%Y-%m-%d %H:%M:%S';
					break;
				case 'timestamp':
					options.format = '%s';
					break;
			}
			var datePicker = new DatePicker(input, options);
		});
	}
	if (field.getElements('input[sls-ac-db][sls-ac-entity][sls-ac-column][sls-ac-multiple="false"]').length) {
		field.getElements('input[sls-ac-db][sls-ac-entity][sls-ac-column][sls-ac-multiple="false"]').each(function (input) {
			new AutoComplete(input);
		});
	}
	if (field.getElements('input[sls-ac-db][sls-ac-entity][sls-ac-column][sls-ac-multiple="true"]').length) {
		field.getElements('input[sls-ac-db][sls-ac-entity][sls-ac-column][sls-ac-multiple="true"]').each(function (input) {
			new MultiAutoComplete(input);
		});
	}
	if (field.getElements('input[sls-unique="true"]').length) {
		field.getElements('input[sls-unique="true"]').each(function (input) {
			new UniqueField(input);
		});
	}
	if (field.getElements('input[sls-specific-type="address"]').length) {
		field.getElements('input[sls-specific-type="address"]').each(function (input) {
			input.store('GoogleAddressAutocomplete', new google.maps.places.Autocomplete(input));
		});
	}
	if (field.getElements('.sls-input-file-drop-zone input[type="file"]').length) {
		field.getElements('.sls-input-file-drop-zone input[type="file"]').each(function (input) {
			new FileUpload(input);
		});
	}
	if (field.getElements('.sls-input-file-drop-zone').length) {
		field.getElements('.sls-input-file-drop-zone').each(function (dropZone) {
			new FileDropZone(dropZone);
		});
	}
	if (field.getElements('textarea[sls-html="true"]').length) {
		field.getElements('textarea[sls-html="true"]').each(function (textarea) {
			if (textarea.retrieve('CKEditor'))
				return;
			var ckeditor, config = {
				on: {
					change: function () {
						textarea.fireEvent('change');
					}
				}
			};
			if (textarea.getParent('.sls-bo-form-page-child') || textarea.getParent('.sls-bo-quick-edit'))
				config.toolbar = 'SillySmartToolbarSmall';
			ckeditor = CKEDITOR.replace(textarea, config);
//			if (window.CKFinder)
//				CKFinder.setupCKEditor(ckeditor, '/ckfinder/');
			textarea.store('CKEditor', ckeditor);
		});
	}
	if (field.getElements('.colors-mosaic').length) {
		field.getElements('.colors-mosaic').each(function (colorMosaic) {
			new ColorsMosaic(colorMosaic);
		});
	}
	if (field.getElements('.toggler-btn-radio input[type=radio]').length == 2)
		new TogglerBtnRadio(field.getElement('.toggler-btn-radio'));

	if (window.Validation) {
		try {
			Validation.prototype.initField(field);
		} catch (e) {
			if (window.console)
				console.log(e.stack);
		}
	}
}