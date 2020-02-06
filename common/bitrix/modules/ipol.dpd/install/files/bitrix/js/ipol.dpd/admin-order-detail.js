BX.ready(function() {
	var formHelper = new DpdOrderDetailHelper();
});

function DpdOrderDetailHelper()
{
	if (this === window) {
		return new DpdOrderDetailHelper();
	}

	this.init();
}

DpdOrderDetailHelper.prototype.init = function()
{
	this.initButton();
	this.initDialog();
	this.initEvents();
}

DpdOrderDetailHelper.prototype.initButton = function()
{
	if (BX('IPOLH_DPD_ORDER_BUTTON')) {
		return ;
	}

	var button = BX.create('a', {
		props: {
			id: 'IPOLH_DPD_ORDER_BUTTON',
			className: 'adm-btn',
			style: 'color: '+ this.getButtonColor(),
		},

		text: BX.message('IPOLH_DPD_BUTTON_OPEN_DIALOG'),

		events: {
			click: BX.proxy(function(e) {
				this.open();

				return BX.PreventDefault(e);
			}, this),
		},
	});

	var parent = document.querySelector('.adm-detail-toolbar .adm-detail-toolbar-right');

	if (parent.firstChild) {
		parent.insertBefore(button, parent.firstChild);
	} else {
		parent.appendChild(button);
	}
}

DpdOrderDetailHelper.prototype.initDialog = function()
{
	this.DIV = BX('IPOLH_DPD_ORDER_FORM');

	this.dialog = new BX.CAdminDialog({
			title: BX.message('IPOLH_DPD_DIALOG_TITLE'),
			content: this.DIV,
			icon: 'head-block',
			width: 600,
			height: 500,
			resizable: true,
			draggable: true,
			buttons: [
				
			],
		});

	this.initDialogButtons();
}

DpdOrderDetailHelper.prototype.initDialogButtons = function()
{
	this.dialog.DIV.querySelector('#tabControl_buttons_div').style.display = 'none';

	var buttons = [];

	if (this.canCreateOrder()) {
		buttons.push({
			id: 'IPOLH_DPD_ORDER_BUTTON_CREATE_ORDER',
			className: 'adm-btn-save',
			title: BX.message('IPOLH_DPD_BUTTON_CREATE'),
			action: BX.proxy(this.process, this)
		});
	}

	if (this.canCancelOrder()) {
		buttons.push({
			id: 'IPOLH_DPD_ORDER_BUTTON_CANCEL_ORDER',
			className: '',
			title: BX.message('IPOLH_DPD_BUTTON_CANCEL'),
			action: BX.proxy(this.process, this)
		});
	}

	buttons.push(BX.CDialog.prototype.btnClose);

	this.dialog.ClearButtons();
	this.dialog.SetButtons(buttons);
}

DpdOrderDetailHelper.prototype.initEvents = function()
{
	BX.bind(BX('IPOLH_DPD_ORDER_SERVICE_CODE'),            'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_SERVICE_VARIANT'),         'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_CARGO_WEIGHT'),            'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_CARGO_VOLUME'),            'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_CARGO_NUM_PACK'),          'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_CARGO_VALUE'),             'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_NPP'),                     'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_DIMENSION_WIDTH'),         'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_DIMENSION_HEIGHT'),        'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_DIMENSION_LENGTH'),        'change', BX.proxy(this.recalculate, this));
	BX.bind(BX('IPOLH_DPD_ORDER_RECEIVER_TERMINAL_CODE'),  'change', BX.proxy(this.recalculate, this));

	BX.bind(BX('IPOLH_DPD_ORDER_BUTTON_INVOICE_FILE'), 'click',  BX.proxy(this.process, this));
	BX.bind(BX('IPOLH_DPD_ORDER_BUTTON_LABEL_FILE'),   'click',  BX.proxy(this.process, this));
}

DpdOrderDetailHelper.prototype.open = function()
{
	this.dialog.Show();
}

DpdOrderDetailHelper.prototype.close = function()
{
	this.dialog.Close();
}

DpdOrderDetailHelper.prototype.canCreateOrder = function()
{
	var status = this.DIV.dataset.orderStatus;

	return status == 'NEW' 
		|| status == 'Canceled';
}

DpdOrderDetailHelper.prototype.canCancelOrder = function()
{
	var status = this.DIV.dataset.orderStatus;

	return status != 'NEW'
		&& status != 'OrderError'
		&& status != 'Canceled';
}

DpdOrderDetailHelper.prototype.process = function(action)
{
	if (this._ajaxRequest) {
		return;
	}

	var btn = BX.type.isString(action) ? false : action.target;
	var form = this.dialog.GetForm();

	this.showLoading(btn);
	this._ajaxRequest = BX.ajax({
		method: 'POST',
		url: form.getAttribute('action') || document.location.href,
		data: this.dialog.GetParameters()
			+ '&IPOLH_DPD_ACTION=' + (
				BX.type.isString(action) 
					? action 
					: action.target.id.replace('IPOLH_DPD_ORDER_BUTTON_', '')
			)
		,
		dataType: 'html',
		onsuccess: BX.proxy(function(response) {
			try {
				this.dialog.SetContent(response);
				this.DIV = BX('IPOLH_DPD_ORDER_FORM');

				this.initEvents();
				this.initDialogButtons();
				this.hideLoading(btn);
			} catch (e) {
				console.log(e);
			}

			this._ajaxRequest = false;
		}, this)
	});
}

DpdOrderDetailHelper.prototype.recalculate = function()
{
	return this.process('RECALCULATE');
}

DpdOrderDetailHelper.prototype.showLoading = function(btn)
{
	this.hideLoading();
	
	this._loading = BX.showWait();
	this._loading_btn = btn ? this.dialog.showWait(btn) : false;
}

DpdOrderDetailHelper.prototype.hideLoading = function(btn)
{
	this._loading && BX.closeWait(this._loading);
	this._loading_btn && this.dialog.closeWait(btn);

	this._loading = false;
	this._loading_btn = false;
}

DpdOrderDetailHelper.prototype.getButtonColor = function()
{
	var status = BX('IPOLH_DPD_ORDER_FORM').dataset.orderStatus;

	if (status == 'OrderError'
		|| status == 'Canceled'
	) {
		return '#cb4143';
	}

	if (status == 'OrderPending') {
		return '#3d7fb5';
	}

	if (status != 'NEW') {
		return 'green';
	}

	return '';
}
