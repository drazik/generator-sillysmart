window.addEvent('domready', function(){
	window._bo = new Bo();
});

var Bo = new Class ({
	initialize:function(){
		this.container = $('SLS_EditBo');
		this.columnsHead = this.container.getElement('.columns_head');
		this.columns = this.container.getElement('.columns_body');
		this.columnsLi = this.columns.getElements('li');
		this.addButtons = this.container.getElements('input.add');
		this.columnsLegends = this.container.getElements('.columns_head .col.available');
		this.build();
	},

	build: function(){
		this.container.getElements('select.operator').each(function(select){
			this.buildSelectOperator(select);
		}.bind(this));

		this.buildDragAndDrop();
		this.buildDelete();
		this.buildAdd();
		this.buildColumns();
	},

	buildColumns: function(){
		this.columnsLegends.each(function(legend){
			legend.addEvent('click', function(){
				this.toggleSelectLegends(legend);
			}.bind(this));
		}.bind(this));
	},

	toggleSelectLegends:function(legend){
		var index = this.columnsHead.getElements('li .col').indexOf(legend);

		if(this.allCheckboxesChecked(index))
			this.unselectAllCheckboxes(index);
		else
			this.selectAllCheckboxes(index);
	},

	allCheckboxesChecked: function(colIndex){
		var inputs = this.columns.getElements('li .col.available:nth-child('+(colIndex+1)+') input[type=checkbox]');
		return inputs.every(function(input){
			return input.checked;
		});
	},

	unselectAllCheckboxes: function(colIndex){
		var inputs = this.columns.getElements('li .col.available:nth-child('+(colIndex+1)+') input[type=checkbox]');
		inputs.each(function(input){
			input.checked = false;
		});
	},

	selectAllCheckboxes: function(colIndex){
		var inputs = this.columns.getElements('li .col.available:nth-child('+(colIndex+1)+') input[type=checkbox]');
		inputs.each(function(input){
			input.checked = true;
		});
	},

	buildAdd: function(){
		this.addButtons.each(function(button){
			button.store('position', button.getParent('table').getElements('tbody tr').length);
		});
		this.container.addEvent('click:relay(input.add)', function(e){
			this.addLine(e);
		}.bind(this));
	},

	buildDelete: function(){
		this.container.addEvent('click:relay(img.delete)', function(e){
			this.deleteLine(e);
		}.bind(this));
	},

	addLine: function(e){
		var button = e.target;
		var position = button.retrieve('position')+1;
		button.store('position', position);

		var tr = button.getParent('tr');
		var trExample = tr.getSiblings('tr.example')[0];
		var table = tr.getParent('table');

		var tr = new Element('tr', {
			'html' : trExample.get('html').substitute({
				'POSITION' : position,
				'NAME' : 'bo'
			})
		}).inject(table.getElement('tbody'));

		var selectOperator = tr.getElements('select.operator')[0];
		if(selectOperator != undefined ){
			this.buildSelectOperator(selectOperator);
		}
	},

	buildSelectOperator: function(operator){
		operator.addEvent('change', function(){
			this.updateLine(operator);
		}.bind(this));
	},

	updateLine: function(select){
		var option = select.getElements('option:selected')[0];
		var input = select.getSiblings('input')[0];
		if(option != undefined && option.hasClass('operator_need_value'))
			input.removeClass('hide');
		else
			input.addClass('hide');
	},

	deleteLine: function(e){
		e.target.getParent('tr').destroy();
	},


	buildDragAndDrop: function(){
		new Sortables(this.columns, {
			clone: true,
			revert: true,
			opacity: 0.7,
			onComplete: this.updatePosition.bind(this)
		});
	},

	updatePosition: function(){
		this.columnsLi.each(function(li, index){
			li.getElement('.position').set('value', index+1);
		});
	}
});
