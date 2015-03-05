window.addEvent('domready', function(){
    new ReportingForm();
});

var ReportingForm = new Class({
	initialize: function () {
		if($$('.reporting_form').length == 0)
			return;

		this.form = $$('.reporting_form')[0];
		this.sections = this.form.getElements('.sections .section');
		this.selectGraphType = this.form.getElement('#sls_graph_type');
		this.selectQueryTable = this.form.getElement('#sls_graph_query_table');
		this.requestGetFields = null;
		this.selectsAggregation = this.form.getElements('select.aggregation');
		this.selectFieldsCurrent = Array();

		this.build();
	},

	build: function(){
		this.selectGraphType.addEvent('change', function(){
			var value = this.selectGraphType.get('value');
			this.showSection(value);
			this.resetSelectedFields();
		}.bind(this));

		this.selectQueryTable.addEvent('change', function(){
			this.hideSelectFields();
			this.resetListModule();
			this.getFieldsMulti();
			this.updateModuleList();
		}.bind(this));

		this.selectsAggregation.each(function(selectAggregation){
			selectAggregation.addEvent('change', function(){
				this.updateAggregationField(selectAggregation);
			}.bind(this));
		}.bind(this));

		this.buildWhere();
		this.buildListModule();

		this.getFieldsMulti();

		this.form.getElements('select, input').each(function(field){
			field.addEvent('change', function(){
				field.getParent('.field').removeClass('error');
			});
		});
	},

	resetListModule: function(){
		this.originalFields.getElements('li').destroy();
		this.selectedFields.empty();
	},

	resetSelectedFields: function(){
		this.selectedFields.empty();
	},

	buildListModule: function(){
		this.content = $$('#reporting_add')[0];
        this.firstLoader = this.content.getElement('span.first_loader');
		this.originalFields = this.content.getElement('ul.original_fields');
		this.selectedFields = this.content.getElement('ul.selected_fields');
		this.actions = this.content.getElements('div.actions a');
		this.indexSelectFields = this.selectedFields.getElements('> li').length;
        var liSelected = this.selectedFields.getElements('li');

        if(liSelected.length > 0){
            liSelected.addEvent('click', this.fieldSelector.bind(this));
        }

		this.actions.each(function(a){
			a.addEvent('click', this.moveFields.bind(this));
		}.bind(this));

		this.updateModuleList();
	},

	updateModuleList: function(tableNameVar, li){
		var tableName;
		(tableNameVar === undefined) ? tableName = this.selectQueryTable.get('value') : tableName = tableNameVar;

		if(tableName != ''){
			if(this.requestGetFields)
				this.requestGetFields.cancel();

            if(li){
	            var a = li.getElement('a.fk_table');
	            a.set('html', '');
	            a.addClass('loading');
            }
			else{
	            this.firstLoader.addClass('active');
            }

			this.requestGetFields = new Request.JSON({
				url : _urls.reporting_getfields,
				data: {
					'table_name' : tableName
				},
				method: 'post',
				onComplete: function(xhr){
                    if(li){
                        a.removeClass('loading');
                        a.set('html', '[-]');
                    }else
                        this.firstLoader.removeClass('active');
					this.addSelectFields(xhr.fields, li);
					this.requestGetFields = null;
				}.bind(this)
			}).send();
		}
	},

	moveFields: function(e) {
		e.stop();
		var a = e.target;
		if (a.hasClass('push')){
			var container = this.selectedFields;
			var fields = this.originalFields.getElements('li.selected');

			this.actionField('push', fields, container);

			fields.addClass('hidden');
		}
		else if (a.hasClass('shift')){
			var container = this.originalFields;
			var fields = this.selectedFields.getElements('li.selected');

			this.actionField('shift', fields, container);

			fields.removeClass('selected');
		}
		return false;
	},

	actionField: function(actionType, fields, container) {
		fields.each(function(field, i){
			if(actionType == 'push'){
				// if field = childField
				if(field.getParent('ul').hasClass('parent')){
					var parentContainer = field.getParent('ul');
					// while parent -> add parentName -> currentName
					while (parentContainer.hasClass('parent')){
						var liParent = parentContainer.getParent('li');
						var liParentName = liParent.get('html').split('<input')[0];

						field.set('html', liParentName+' / '+field.get('html'));
						parentContainer = parentContainer.getParent('ul');
					}
				}

				// hide a
				if(field.getElement('a'))
					field.getElement('a').addClass('hidden');

				fields.removeClass('selected');

				// inject clone + addEvent
				var fieldCloned = field.clone();
                fieldCloned.getElements('input.value').each(function(input){
                    input.set('name', input.get('name').replace('tmp','sls_graph_query').substitute({INDEX : this.indexSelectFields}));
                }.bind(this));

				var input = new Element('input', {
					'type' : 'hidden',
					'name' : 'sls_graph_query[sls_graph_query_column]['+(this.indexSelectFields++)+'][sls_graph_query_column_label]',
					'class' : 'column label',
					'value' : field.get('html').split('<input')[0]
				}).inject(fieldCloned);

                fieldCloned.inject(container);
				var aFieldInjected = fieldCloned.getElements('a');

                fieldCloned.addEvent('click', this.fieldSelector.bind(this));
				if(aFieldInjected.length > 0)
					aFieldInjected.addEvent('click', this.expandFks.bind(this));

                fieldCloned.store('original', field);
			}
			else if(actionType == 'shift'){
				this.originalFields.getElements('li').each(function(oField, i){
					if(oField.hasClass('hidden') && (oField == field.retrieve('original') || oField.getElement('input.column').get('value') == field.getElement('input.column').get('value'))){
						if(oField.getElement('a'))
							oField.getElement('a').removeClass('hidden');

						var splittedHtml = oField.get('html').split('&nbsp;/&nbsp;');
						var htmlLength =  splittedHtml.length;
						oField.set('html', splittedHtml[htmlLength-1]);

						oField.removeClass('hidden');
						field.destroy();
					}else{
                        field.destroy();
                    }
				});
			}

			this.getFieldsMulti();
		}.bind(this));
	},

	addSelectFields: function(fields, li) {
		// if li == undefined, adding fields else adding childrens fields from fk
		// here setting the last container where everything is inject & isChild used underneath
		if(li === undefined){
            var isChild = false;
			var fieldsContainer = this.originalFields;
		}
		else{
            var isChild = true;
			var ul = new Element('ul', {
				'class' : 'parent'
			});
			// ul children is added to the parent's li
			ul.inject(li);

			var fieldsContainer = ul;
		}

		fields.each(function(field, i){
			// Vars
			var fieldName = field.field_name;
			var fieldComment = field.field_label;
			var fieldTable = field.field_table;
			var pkTable = field.field_tablePk;

			// li
			var li = new Element('li', {
				'html' : fieldComment,
				'events' : {
					'click': this.fieldSelector.bind(this)
				}
			});

			// all li need those inputs
			var inputTable = new Element('input', {
				'type' : 'hidden',
				'name' : 'tmp[sls_graph_query_column][{INDEX}][sls_graph_query_column_value]',
                'class' : 'column value',
				'value' : fieldTable+'.'+fieldName
			});

			inputTable.inject(li);
			// /all li need those inputs

			// if field is ForeignKey
			if(field.field_isFk){
                var tableName = pkTable;

                var inputTableJoin = new Element('input', {
                    'type' : 'hidden',
                    'name' : 'input_table_join',
                    'class' : 'pk_table',
                    'value' : tableName
                });

                // Element for expand fks
                var a = new Element('a', {
                    'class' : 'fk_table open',
                    'html' : ' [+]',
                    'events' : {
                        'click': this.expandFks.bind(this)
                    }
                });
                // /// Element for expand fks

                inputTableJoin.inject(li);
                a.inject(li);
			}
			// /if field is ForeignKey

            if(isChild){
                var inputTableValue = ul.getParent('li').getElement('input.column').get('value');
                inputTable.set('value', inputTableValue+'|'+fieldTable+'.'+fieldName);
            }

			li.inject(fieldsContainer);

        }.bind(this));

        this.checkSelectedFields();
	},

	expandFks: function(e) {
		e.stop();

		var a = e.target;
		var liParent = a.getParent('li');

		if(a.hasClass('open')){
			this.toggleOpenner('open', a);
			var ulParent = a.getParent().getElement('ul.parent.hidden');
			// if Parent already exist, return = no AJAX
			if(ulParent){
				ulParent.removeClass('hidden');
				return false;
			}
		}
		else if(a.hasClass('close')){
			if(this.requestGetFields && this.requestGetFields.isRunning())
				this.requestGetFields.cancel();

			this.toggleOpenner('close', a);
			a.getSiblings('ul.parent').addClass('hidden');
			return false;
		}

		var pkTable = a.getParent().getElement('input.pk_table').get('value');
		this.updateModuleList(pkTable.toLowerCase(), liParent);
	},

	fieldSelector: function(e) {
		e.stop();
		var li = e.target;

		if(li.hasClass('fk_table')){
			this.expandFks(e);
			return;
		}

		if(li.getChildren('ul.parent').length > 0 || !li.getChildren('ul.parent').hasClass('hidden'))
			if(li.getElement('a').hasClass('close'))
				return;

		(li.hasClass('selected')) ? li.removeClass('selected') : li.addClass('selected');
	},

	toggleOpenner: function(mode, a) {
		var classToRemove;
		var classToAdd;
		var html;
		var liContainer = a.getParent('li');

		if (mode == 'open'){
			if(liContainer.hasClass('selected'))
				liContainer.removeClass('selected')
			classToAdd = 'close';
			classToRemove = 'open';
			html = '[-]';
		}else if (mode == 'close'){
			classToAdd = 'open';
			classToRemove = 'close';
			html = '[+]';
		}

		a.removeClass(classToRemove);
		a.addClass(classToAdd);
		a.set('html', html);
	},

	getFieldsMulti: function(){
		var tableName = this.selectQueryTable.get('value');
		var columns = Array();
		var inputs = this.selectedFields.getElements('li input.column.value');
		inputs.each(function(input){
			columns.push(input.get('value'));
		});

		if(tableName != ''){
			if(this.requestGetFieldsMulti != null)
				this.requestGetFieldsMulti.cancel();
			this.loadingSelectFields();

			this.requestGetFieldsMulti = new Request.JSON({
				url : _urls.reporting_getfieldsfrommutipletables,
				data: {
					'table_name' : tableName,
					'columns' : columns
				},
				method: 'post',
				onComplete: function(xhr){
					this.requestGetFieldsMulti = null;
					this.selectFieldsCurrent = xhr.fields;
					this.updateSelectFields();
					this.showSelectFields();
				}.bind(this)
			}).send();
		}
	},

    checkSelectedFields: function(){
        this.originalFields = this.content.getElement('ul.original_fields');
        this.selectedFields = this.content.getElement('ul.selected_fields');

        this.originalFields.getElements('input.column').each(function(oInput){
            var oInputValue = oInput.get('value');
            this.selectedFields.getElements('input.column').each(function(sInput){
                var sInputValue = sInput.get('value');
                if(oInputValue == sInputValue){
                    oInput.getParent('li').addClass('hidden');
                }
            });
        }.bind(this));
    },

	buildWhere: function(){
		this.queryWhereHtml = $('sls_graph_query_where_example').get('html');

		this.form.addEvent('click:relay(.and_group)', function(e){
			e.stop();
			this.whereAddGroup(e.target, 'and');
		}.bind(this));

		this.form.addEvent('click:relay(.or_group)', function(e){
			e.stop();
			this.whereAddGroup(e.target, 'or');
		}.bind(this));

		this.form.addEvent('click:relay(.and_clause)', function(e){
			e.stop();
			this.whereAddClause(e.target, 'and');
		}.bind(this));

		this.form.addEvent('click:relay(.or_clause)', function(e){
			e.stop();
			var whereGroup = this.whereAddGroup(e.target, 'or');
			this.whereAddClause(whereGroup.getElement('> .actions .or_clause'), 'or');
		}.bind(this));

		this.form.addEvent('change:relay(.operators)', function(e){
			e.stop();
			this.updateSelectOperator(e.target);
		}.bind(this));

		this.form.addEvent('click:relay(.delete)', function(e){
			e.stop();
			this.whereDeleteGroup(e.target);
		}.bind(this));


		this.form.addEvents({
			'mouseenter:relay(.delete, button)' : function(e){
				var button = e.target;
				var whereQuery = button.getParent('.sls_graph_query_where');
				whereQuery.addClass('hover');
			}.bind(this),
			'mouseleave:relay(.delete, button)' : function(e){
				var button = e.target;
				var whereQuery = button.getParent('.sls_graph_query_where');
				whereQuery.removeClass('hover');
			}.bind(this)
		});
	},

	updateSelectOperator: function(select){
		var inputValue = select.getParent('.field').getNext();
		if(select.get('value') == 'null' || select.get('value') == 'notnull')
			inputValue.addClass('hide');
		else
			inputValue.removeClass('hide');
	},

	whereDeleteGroup: function(button){
		var whereQuery = button.getParent('.sls_graph_query_where');
		if (confirm("Are you sure you want to delete this line ?")) {
			var whereQueryPrevious = whereQuery.getPrevious('.sls_graph_query_where');
			var whereQueryNext = whereQuery.getNext('.sls_graph_query_where');
			if(whereQueryPrevious == null && whereQueryNext != null)
				whereQueryNext.getElement('.condition').empty();
			whereQuery.destroy();
		}
	},

	whereAddGroup: function(button, type){
		var queryWhereParent = button.getParent('.sls_graph_query_where');
		if(queryWhereParent == null)
			return;
		var queryWhereParentChildren = queryWhereParent.getElement('> .sls_graph_query_where_children');
		var queryWhereParentNbChildren = queryWhereParentChildren.getElements('> .sls_graph_query_where').length;

		var queryWhere = new Element('div', {
			'class' : 'sls_graph_query_where',
			'html' : this.queryWhereHtml.substitute({
				'PATH' : queryWhereParent.getElement('> .sls_graph_query_where_tree').get('value')+'[sls_graph_query_where_children]',
				'NUM' : queryWhereParentNbChildren,
				'CONDITION' : queryWhereParentNbChildren == 0 ? '' : type,
				'TYPE' : 'group'
			})
		});

		if(queryWhereParentNbChildren == 0)
			queryWhere.getElement('> .condition').destroy();
		queryWhere.getElement('> .line').destroy();
		queryWhere.getElement('> .delete').destroy();

		queryWhere.inject(queryWhereParentChildren);
		return queryWhere;
	},

	whereAddClause: function(button, type){
		var queryWhereParent = button.getParent('.sls_graph_query_where');
		if(queryWhereParent == null)
			return;
		var queryWhereParentChildren = queryWhereParent.getElement('> .sls_graph_query_where_children');
		var queryWhereParentNbChildren = queryWhereParentChildren.getElements('> .sls_graph_query_where').length;

		var queryWhere = new Element('div', {
			'class' : 'sls_graph_query_where',
			'html' : this.queryWhereHtml.substitute({
				'PATH' : queryWhereParent.getElement('> .sls_graph_query_where_tree').get('value')+'[sls_graph_query_where_children]',
				'NUM' : queryWhereParentNbChildren,
				'CONDITION' : queryWhereParentNbChildren == 0 ? '' : type,
				'TYPE' : 'clause'
			})
		});

		if(queryWhereParentNbChildren == 0)
			queryWhere.getElement('> .condition').destroy();
		queryWhere.getElement('> .actions').destroy();

		queryWhere.inject(queryWhereParentChildren);

		queryWhere.getElements('select.columns').each(function(select){
			this.updateSelectField(select);
		}.bind(this));
	},

	updateAggregationFields: function(){
		this.selectsAggregation.each(function(selectAggregation){
			this.updateAggregationField(selectAggregation);
		}.bind(this));
	},

	updateAggregationField: function(selectAggregation){
		var selectAggregationValue = selectAggregation.get('value');
		var aggregationField = selectAggregation.getParent('.row').getElement('.field_aggregation');
		if(selectAggregationValue == 'count' || selectAggregationValue == '')
			aggregationField.addClass('hide');
		else
			aggregationField.removeClass('hide');
	},

	updateSelectFields: function(){
		this.form.getElements('select.columns').each(function(selectField){
			this.updateSelectField(selectField);
		}.bind(this));
	},

	updateSelectField: function(selectField){
		var container = selectField.getParent('.field');
		var selectOld = selectField;
		var selectOldValue = selectOld.get('value');

		var options = '<option value=""></option>';
		this.selectFieldsCurrent.each(function(field){
			if(field.field_name == selectOldValue)
				options += '<option selected="selected" value="'+field.field_name+'">'+field.field_label+'</option>';
			else
				options += '<option value="'+field.field_name+'">'+field.field_label+'</option>';

		});

		var selectOld = selectField;
		var selectNew = new Element('select', {
			'id' : selectOld.get('id'),
			'class' : selectOld.get('class'),
			'name' : selectOld.get('name'),
			'html' : options
		});

		selectOld.destroy();
		selectNew.inject(container);
	},

	hideSelectFields: function(){
		this.form.getElements('select.columns').each(function(selectField){
			selectField.getParent('.field').addClass('hide');
		});
	},

	loadingSelectFields: function(){
		this.form.getElements('.field.field_columns.hide').addClass('loading');
	},

	showSelectFields: function(){
		this.form.getElements('.field.field_columns.hide').removeClass('hide').removeClass('loading');
		this.updateAggregationFields();
	},

	showSection: function(section){
		this.hideSections();
		var section = this.sections.filter('#section_'+section);
		if(section)
			section.addClass('selected');
	},

	hideSections: function(){
		this.sections.removeClass('selected');
	}
});