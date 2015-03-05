var AutoComplete = new Class({
	Implements: [Events],

	postData: {
		Db: false, // database
		Model: false, // model
		Column: false, // model's column
        Lang: false,
        Keyword: false
	},
	label: '',
	placeholder: '',
	type: 'single', // single / multiple
	listenedKeys: ['left','up','right','down','enter','esc','tab'],
	isSetted: false,

	initialize: function(input){
		if (!input || input.tagName != "INPUT" || input.type != 'text')
			return false;
		if (input.retrieve('AutoComplete'))
			return false;
		this.input = input;

		// Store class instance in the original input
		this.input.store('AutoComplete', this);

		if (this.initAutoCompleteInput()){
			this.initEvents();
			this.retrieveInitialValue();
		}
	},

	initAutoCompleteInput: function(){
		var params = {};
		var attributes = this.input.attributes, attribute;
		var requiredParams = ["name","db","entity","column","lang","label"];

		params.name = this.input.get('name');
		this.placeholder = this.input.get('placeholder');
		for (var i = 0; i < attributes.length; i++){
			attribute = attributes[i];
			if (attribute.nodeName.indexOf('sls-') != -1){
				var key = attribute.nodeName.replace(/sls\-(ac)?\-?/, '').match(/[a-z]*/i)[0];
                if (!(key in params))
					params[key] = attribute.nodeValue;
			}
		}

		for (var i = 0; i < params.length; i++)
		{
			if (!requiredParams[i] in params)
				throw new Error("JS : AutoComplete<br/>Some required HTTP params are missing!"+"<br/>=\>"+requiredParams[i]);
		}

        this.postData.Db = params.db;
        this.postData.Model = params.entity;
        this.postData.Column = params.column;
        this.postData.Lang = params.lang;
		this.postData['sls-request'] = 'async';

		// Creates a div container for all the HTML content of the class
		this.container = new Element('div.sls-ac-container').wraps(this.input);
		new Element('div.sls-ac-arrow', {
			events: {
				'click': function(e){
					e.stop();

					if (!this.isSetted)
						this.inputAC.focus();
					else
						this.unsetValue();
				}.bind(this)
			}
		}).inject(this.container);

		// Hides the original input
		this.input.setStyles({'display': 'none'});

		// Creates a fake input for all the autocomplete-user interactions
		this.inputAC = new Element('input[type="text"][autocomplete="off"]', {
			'class': ((typeOf(this.input.get('class')) == "string") ? this.input.get('class')+" " : "")+"sls-ac-input",
			'placeholder': this.placeholder
		}).inject(this.container);

		// Instantiates a generic dropdown : used to display result options of the autocomplete
		this._dropdown = new GenericDropdown();
		this._dropdown.attachTo(this.inputAC);

        return true;
	},

	initEvents: function(){
		this.inputAC.addEvents({
			'focus': this.focused.bind(this),
			//'blur': this.blurred.bind(this),
			'keydown': this.inputKeydown.bind(this),
			'valueSetted': this.valueSetted.bind(this),
			'valueUnsetted': this.valueUnsetted.bind(this),
			'click': this.preventMousedown.bind(this)
		});

        this._dropdown.addEvents({
	        'selectOption': this.setValue.bind(this),
	        //'closeWithoutSelection': this.blurred.bind(this)
	        'open': this.setOpened.bind(this),
	        'close': this.blurred.bind(this)
        });
	},

	loading: function(state){
		if (typeOf(state) != "boolean")
			return false;
		if (state)
			this.container.addClass('loading');
		else
			this.container.removeClass('loading');
	},

	focused: function(){
		if (this.inputAC.get('value') == this.inputAC.retrieve('label')){
			this.inputAC.set('value', '');
		}
		this.search();
	},

	preventMousedown: function(event){
		if (typeOf(event) == 'domevent' && this.container.hasClass('opened')){
			event.stop();
			return false;
		}
	},

	setOpened: function(){
		this.container.addClass('opened');
	},

	blurred: function(event, force){
		this.container.removeClass('opened');
		if (!force){
			if (!event || !event.target || event.target == this.inputAC)
				return;
		}
		if (this.input.get('value') != false && this.inputAC.retrieve('label') != this.inputAC.get('value'))
			this.inputAC.set('value', this.inputAC.retrieve('label'));
	},

	inputKeydown: function(event){
		if (!event || typeOf(event) != "domevent")
			return false;

		if (this._dropdown.isOpen && this.listenedKeys.indexOf(event.key) != -1){
			event.stop();
	        if (event.key == 'enter'){
				this._dropdown.validateOption();
	        } else if (event.key == 'left' || event.key == 'up'){
				this._dropdown.selectPreviousOption();
	        } else if (event.key == 'right' || event.key == 'down'){
				this._dropdown.selectNextOption();
	        } else if (event.key == 'esc'){
		        this._dropdown.close();
		        this.inputAC.blur();
		        this.blurred(false, true);
	        }
		} else {
            if (this.timerSearch)
                clearInterval(this.timerSearch);
            this.timerSearch = this.search.delay(200, this);
        }
	},

	retrieveInitialValue: function(){
		var id = this.input.get('value');
		if (id == "")
			return false;

		this.postData.Id = id;
		delete this.postData.Keyword;

		new Request.SLSJSON({
			'url' : urls.ac,
			'data': this.postData,
			onRequest: function(){
				this.loading(true);
			}.bind(this),
			onComplete: function(xhr){
				if (xhr && xhr.status == "OK" && xhr.result.length)
					this.setValue(xhr.result[0]);
				this.loading(false);
			}.bind(this)
		}).send();
	},

	search: function(){
        this.postData.Keyword = this.inputAC.get('value');
		delete this.postData.Id;

        new Request.SLSJSON({
            'url' : urls.ac,
            'data': this.postData,
            onRequest: function(){
                this.loading(true);
            }.bind(this),
            onComplete: function(xhr){
	            if (xhr.status == "OK")
                    this._dropdown.refresh(xhr.result);
	            this.loading(false);
            }.bind(this)
        }).send();
	},

    setValue: function(data){
        if (!data || typeOf(data) != 'object')
        	throw new Error("JS : AutoComplete<br/>You are trying to set a wrong value, or your parameters are wrong.");
        this.input.set('value', data.id);
        this.inputAC.set('value', data.label);
	    this.inputAC.store('label', data.label);

	    this.inputAC.fireEvent('valueSetted', {value: data.id});
	    this.input.fireEvent('valueSetted', {value: data.id});
    },

    unsetValue: function(){
		this.inputAC.set('value', '');
	    this.input.set('value', '');

	    this.inputAC.fireEvent('valueUnsetted');
	    this.input.fireEvent('valueUnsetted');
    },

	valueSetted: function(data){
		this.isSetted = true;
		this.container.addClass('value-setted');
	},

	valueUnsetted: function(){
		this.isSetted = false;
		this.container.removeClass('value-setted');
	}
});

var MultiAutoComplete = new Class({
	Extends: AutoComplete,

	valuesContainer: null,

	initialize: function(input){
		this.parent(input);

		this.inputName = this.input.get('name');
		this.input.erase('name');

		this.valuesContainer = (this.input.getParent('.sls-form-page-field') && this.input.getParent('.sls-form-page-field').getElement('.sls-ac-values-container')) ? this.input.getParent('.sls-form-page-field').getElement('.sls-ac-values-container') : null;
		if (this.valuesContainer)
			this.initValuesContainerEvents();
	},

	buildValuesContainer: function(){
		if (this.valuesContainer)
			return;

		this.valuesContainer = new Element('div.sls-ac-values-container').inject(this.container, 'after');
		this.initValuesContainerEvents();
	},

	initValuesContainerEvents: function(){
		this.valuesContainer.addEvent('click:relay(.sls-ac-multi-value)', this.removeValue.bind(this));
	},

	destroyValuesContainer: function(){
		if (!this.valuesContainer)
			return;

		this.valuesContainer.destroy();
		this.valuesContainer = null;
	},

	addValue: function(data){
		if (!data || typeOf(data) != 'object')
			throw new Error("JS : AutoComplete<br/>You are trying to set a wrong value, or your parameters are wrong.");

		if ($$('[name="'+this.inputName+'"][value="'+data.id+'"]').length)
			return _notifications.add('warning', slsBuild.langs.SLS_BO_AUTOCOMPLETE_UNIQUE);

		data.inputName = this.inputName;
		var valueHTML = Elements.from(this.HTMLValue.substitute(data));

		this.injectValue(valueHTML);
	},

	removeValue: function(arg){
		var value = false;
		if (typeOf(arg) == "domevent" && arg.target && (arg.target.hasClass('sls-ac-multi-value') || arg.target.getParent('.sls-ac-multi-value'))){
			value = arg.target.hasClass('sls-ac-multi-value') ? arg.target : arg.target.getParent('.sls-ac-multi-value');
		} else if (typeOf() == "element" && arg.hasClass('sls-ac-multi-value')) {
			value = arg;
		}

		if (!value)
			return false;

		if (window._notifications)
			_notifications.add('information', slsBuild.langs.SLS_BO_AUTOCOMPLETE_REMOVE+'<br/>'+value.getElement('.sls-ac-multi-value-label').get('html'));

		value.destroy();
		if (this.valuesContainer.getChildren().length == 0){
			this.valuesContainer.destroy();
			this.valuesContainer = null;
		}
	},

	injectValue: function(value){
		if (!this.valuesContainer)
			this.buildValuesContainer();

		value.inject(this.valuesContainer);
	},

	setValue: function(data){
		if (!data || typeOf(data) != 'object')
			throw new Error("JS : AutoComplete<br/>You are trying to set a wrong value, or your parameters are wrong.");
		this.addValue(data);
		this.inputAC.set('value', '');
	},

	retrieveInitialValue: function(){

	},

	HTMLValue:  '<div class="sls-ac-multi-value sls-bo-color-text-hover sls-bo-color-border-hover">' +
					'<div class="sls-ac-multi-value-content">' +
						'<div class="sls-ac-multi-value-label">{label}</div>' +
						'<input type="hidden" name="{inputName}" value="{id}" />' +
					'</div>' +
				'</div>'
});

var UniqueField = new Class({
	unique: true,

	initialize: function(input){
		this.input = input;
		this.input.store('UniqueField', this);

		if (!this.input || this.input.tagName != "INPUT" || this.input.type != 'text')
			throw new Error("JS: UniqueField <br/>You are trying to instantiate a unique field type with wrong settings!");

		this.postData = {
			Id: (this.input.getParent('[sls-recordset-id]')) ? this.input.getParent('[sls-recordset-id]').get('sls-recordset-id').toInt() : false,
			Db: this.input.getParent('[sls-db]').get('sls-db'),
			Model: this.input.getParent('[sls-model]').get('sls-model'),
			Lang: this.input.get('sls-lang'),
			Column: this.input.get('name').match(/\[(\w+)\]$/i)[1]
		};

		this.initEvents();
		this.checkValueAvailability();
	},

	initEvents: function(){
		this.input.addEvent('keyup:pause(200)', this.checkValueAvailability.bind(this));
	},

	checkValueAvailability: function(){
		if (this.request && this.request.isRunning())
			this.request.cancel();

		this.postData.Value = this.input.get('value');

		if (this.input.getParent('[sls-recordset-id]') && this.input.getParent('[sls-recordset-id]').get('sls-recordset-id').toInt() != this.postData.Id)
			this.postData.Id = this.input.getParent('[sls-recordset-id]').get('sls-recordset-id').toInt();

		this.request = new Request.SLSJSON({
			url: urls.unique,
			data: this.postData,
			onSuccess: this.handleAvailabilityResponse.bind(this)
		}).send();
	},

	handleAvailabilityResponse: function(xhr){
		if (xhr.status == 'OK'){
			this.unique = xhr.result.unique;
			this.input.fireEvent('change');
		}
	}
});

var GenericDropdown = new Class({
	Implements: [Events, Options],

	options: {
		optionClass: 'sls-bo-color-hover'
	},

	initialize: function(options){
		this.HTMLOption = this.HTMLOption.replace(/\{optionClass\}/, this.options.optionClass);
        this.requiredOptionParams = this.HTMLOption.match(/\{\w+\}/gi).map(function(param){
            return param.replace(/\{/, '').replace(/\}/, '');
        });
		this.build();
	},

	build: function(){
		this.dropdown = Elements.from(this.HTMLBasePattern)[0];
		this.wrapper = this.dropdown.getElement('.generic-dropdown-ac-wrapper');
		this.content = this.wrapper.getElement('.generic-dropdown-ac-content');

        this.content.addEvents({
	        'click:relay(.generic-dropdown-ac-option)': this.clickOption.bind(this),
	        'mouseenter:relay(.generic-dropdown-ac-option)': this.selectOption.bind(this)
        });

		document.body.addEvent('click', this.close.bind(this));
	},

    clickOption: function(event){
        if (!event || typeOf(event) != 'domevent')
            return false;

        var option = event.target.hasClass('generic-dropdown-ac-option') ? event.target : (event.target.getParent('.generic-dropdown-ac-option') ? event.target.getParent('.generic-dropdown-ac-option') : false);

        if (option){
            this.fireEvent('selectOption', this.getOptionParams(option));
            this.close();
        }
    },

	getOptions: function(){
		var options = this.content.getElements('.generic-dropdown-ac-option');

		return (options.length) ? options : false;
	},

	selectNextOption: function(){
		var options = this.getOptions();
		var nbOption = (options) ? options.length : 0;

		if (!nbOption)
			return false;

		var selectedOption = this.getSelectedOption();
		if (!selectedOption)
			this.selectOption(0);
		else {
			var index = options.indexOf(selectedOption);
			var nextIndex = (index+1 < nbOption) ? index+1 : 0;
			this.selectOption(nextIndex);
		}
	},

	selectPreviousOption: function(){
		var options = this.getOptions();
		var nbOption = (options) ? options.length : 0;

		if (!nbOption)
			return false;

		var selectedOption = this.getSelectedOption();
		if (!selectedOption)
			this.selectOption(nbOption-1);
		else {
			var index = options.indexOf(selectedOption);
			var previousIndex = (index-1 >= 0) ? index-1 : nbOption-1;
			this.selectOption(previousIndex);
		}
	},

	selectOption: function(arg){
		var option;
		if (typeOf(arg) == 'domevent')
			option = (arg.target.hasClass('generic-dropdown-ac-option')) ? arg.target : arg.target.getParent('.generic-dropdown-ac-option');
		else if (typeOf(arg) == 'element' && arg.hasClass('generic-dropdown-ac-option'))
			option = arg;
		else if (typeOf(arg) == 'number'){
			var options = this.getOptions();
			option = (options) ? this.getOptions()[arg] : false;
			options = null;
		} else
			return false;
		if (!option)
			return false;

		var lastSelectedOption = this.getSelectedOption();
		if (lastSelectedOption)
			lastSelectedOption.removeClass('selected');
		option.addClass('selected');
	},

	validateOption: function(){
		var selectedOption = this.getSelectedOption();
		if (selectedOption){
			this.fireEvent('selectOption', this.getOptionParams(selectedOption));
			this.close();
		}
	},

    getSelectedOption: function(){
        var selectedOption = this.content.getElement('.generic-dropdown-ac-option.selected');

        return selectedOption ? selectedOption : false;
    },

    getOptionParams: function(option){
        if (typeOf(option) != 'element' || !option.hasClass('generic-dropdown-ac-option'))
        	throw new Error("JS : GenericDropdown<br/>This is not a valid option element.");

        var params = option.getElements('.generic-dropdown-ac-option-params > *');

        if (params.length)
            return {
                id: params.filter('.param-id')[0].get('html').toInt(),
                label: params.filter('.param-label')[0].get('html')
            };
        else
            return false;
    },

	attachTo: function(anchor, wraps){
        if (typeOf(anchor) != 'element')
            return false;

        if (wraps){
            var anchorParent = new Element('div.generic-dropdown-ac-container');
            anchorParent.wraps(anchor);
        }
        this.dropdown.inject(anchor, 'after');
        this.dropdown.store('anchor', anchor);
	},

    refresh: function(options){
        this.emptyOptions();

        if (typeOf(options) != 'array')
        	throw new Error("JS : GenericDropdown<br/>This parameter has to be an array.");
        else if (options.length == 0)
            this.emptyOptionMessage();
        else
            this.injectOptions(options);
    },

	injectOptions: function(options){
		this.emptyOptions();

		if (typeOf(options) != 'array')
			throw new Error("JS : GenericDropdown<br/>This parameter has to be an array.");
		else if (options.length == 0)
			throw new Error("JS : GenericDropdown<br/>This is not a valid option element.");

        for (var i = 0; i < options.length; i++){
            if (typeOf(options[i]) == 'object')
                this.buildOption(options[i]);
        }
        this.open();
	},

	buildOption: function(option){
		if (typeOf(option) != 'object')
			throw new Error("JS : GenericDropdown<br/>This parameter has to be an array.");
        for (var i = 0; i < this.requiredOptionParams.length; i++){
            if (!(this.requiredOptionParams[i] in option))
            	throw new Error("JS : GenericDropdown<br/>Some required HTTP params are missing!"+"<br/>=\> "+this.requiredOptionParams[i]);
        }
        Elements.from(this.HTMLOption.substitute(option))[0].inject(this.content);
	},

	loading: function(state){
		if (state){
			this.emptyOptions();
			this.open();
			Elements.from(this.HTMLLoading)[0].inject(this.content);
		} else {
			var loadingOption = this.content.getElement('.generic-dropdown-ac-option.loading');
			if (loadingOption)
				loadingOption.destroy();
		}
	},

	emptyOptions: function(){
        this.content.empty();
	},

    emptyOptionMessage: function(){
        var emptyOption = Elements.from(this.HTMLEmptyOption.substitute({'optionClass': this.options.optionClass}))[0];
        emptyOption.inject(this.content);
    },

	open: function(){
        this.dropdown
            .setStyles({
                'display': 'block'
            });

		this.fireEvent('open');

		this.isOpen = true;
	},

	close: function(){
		if (arguments.length > 0 &&
			typeOf(arguments[0]) == "domevent" &&
			(arguments[0].target.hasClass('generic-dropdown-ac') ||
			arguments[0].target.getParent('.generic-dropdown-ac'))){
			arguments[0].stop();
		}
		this.fireEvent('close', {target: (arguments.length && arguments[0].target) ? arguments[0].target : false});

        this.emptyOptions();
        this.dropdown
            .setStyles({
                'display': 'none'
            });

		this.isOpen = false;
	},

	HTMLBasePattern:    '<div class="generic-dropdown-ac">' +
							'<div class="generic-dropdown-ac-wrapper">' +
								'<div class="generic-dropdown-ac-content">' +
									'<ul class="generic-dropdown-ac-options"></ul>' +
								'</div>' +
							'</div>' +
						'</div>',

	HTMLOption: '<div class="generic-dropdown-ac-option {optionClass}">' +
                    '{label}' +
                    '<div class="generic-dropdown-ac-option-params">' +
                        '<div class="generic-dropdown-ac-option-param param-id">{id}</div>' +
						'<div class="generic-dropdown-ac-option-param param-label">{label}</div>' +
                    '</div>' +
                '</div>',

	HTMLEmptyOption: '<div class="generic-dropdown-ac-option {optionClass}">'+slsBuild.langs.SLS_BO_SELECT_NO_RESULT+'</div>',

	HTMLLoading: '<div class="generic-dropdown-ac-option loading"></div>'
});