function DpdPickupMap(map, arObjects) {
	if (this === window) {
		return new DpdPickupMap(map, arObjects);
	}

	this.map = map;
	this.placemarks = arObjects.PLACEMARKS;

	this.containerNode = BX('DPD_pvz');
	this.terminalsNode = BX('DPD_wrapper');
	this.maskNode = BX('DPD_mask');

	this.init();

	// TODO: циклическая ссылка
	this.containerNode.DpdPickupMap = this;
}

DpdPickupMap.prototype.init = function () {
	this._bindDomEvents();
	this._bindMapEvents();

	this.setCenterMap();

	this.map.controls.get('zoomControl').options.set('position', { left: this.getZoomMargin(10).left, top: 110 });
}

DpdPickupMap.prototype.setCenterMap = function (x, y, callback) {
	this.map.container.fitToViewport();

	if (x != window.undef && y != window.undef) {
		return this.map.setCenter([x, y]);
	}

	if (this.placemarks.length < 1) {
		return false;
	}

	if (this.placemarks.length > 1) {
		var bounds = this.map.geoObjects.getBounds();
		return this.map.setBounds(bounds, {
			zoomMargin: [this.getZoomMargin().top, 0, 0, this.getZoomMargin().left]
		});
	}

	return this.map.setCenter(this.placemarks[0].geometry.getCoordinates());
}

DpdPickupMap.prototype.getZoomMargin = function (offset) {
	if (window.innerWidth > 919) {
		return { left: 260, top: 0 };
	}

	return { left: 0 + (offset || 0), top: 100 };
}

DpdPickupMap.prototype.selectCity = function (cityId) {
	this.sendRequest('set_city', { city_id: cityId }, this._bind('doReload'));
}

DpdPickupMap.prototype.selectTerminal = function (code) {
	BX.onCustomEvent(this.containerNode, 'dpdPickup:selectTerminal', [code]);

	// this.sendRequest('set_terminal', {terminal_code: code}, this._bind(function(arResult) {
	// }));
}

DpdPickupMap.prototype.highlightTerminal = function (parms) {
	var nodes = this.containerNode.querySelectorAll('.DPD_terminalSelect');
	var node = parms.code
		? this.containerNode.querySelector('.DPD_terminalSelect[data-terminal-code="' + parms.code + '"]')
		: nodes.item(parms.index);

	if (!node) {
		return false;
	}

	[].forEach.call(nodes, function (node) {
		BX.removeClass(node, 'DPD_chosen');
	});

	BX.addClass(node, 'DPD_chosen');

	index = parms.index || [].indexOf.call(nodes, node);

	if (!this.placemarks[index].balloon.isOpen()
		&& (!!parms.openBalloon) !== false
	) {
		this.placemarks[index].balloon.open();
	}

	if (!!parms.highlightIcon) {
		this.placemarks[index].options.set('iconImageHref', '/bitrix/images/ipol.dpd/pickup_locationmarker_highlighted.png');
	}
}

DpdPickupMap.prototype.resetTerminals = function (arPlacemarks) {
	this.showLoading();
	this.clearTerminals();

	var terminalTypes = ['all'];
	for (var i in arPlacemarks) {
		var arPlacemark = arPlacemarks[i];
		var obPlacemark = BX_YMapAddPlacemarkDPD(this.map, arPlacemark);
		var node = BX.create('p', {
			attrs: {
				'data-terminal-code': arPlacemark['CODE'],
				'data-terminal-type': arPlacemark['TYPE'],
				'data-terminal-addr': arPlacemark['ADDR']
			},

			props: {
				className: 'DPD_terminalSelect',
			},

			text: arPlacemark['TITLE']
		});

		this.terminalsNode.appendChild(node);
		this.placemarks.push(obPlacemark);

		if (arPlacemark.TYPE && terminalTypes.indexOf(arPlacemark.TYPE) == -1) {
			terminalTypes.push(arPlacemark.TYPE);
		}
	}

	this.containerNode.querySelector('#DPD_modController').className = terminalTypes.length > 2 ? '' : 'dpd-hidden';

	[].forEach.call(this.containerNode.querySelectorAll('.DPD_mC_block'), function (node) {
		if (terminalTypes.indexOf(node.dataset.type) != -1) {
			BX.removeClass(node, 'dpd-hidden');
		} else {
			BX.addClass(node, 'dpd-hidden');
		}
	});

	this._bindMapEvents();
	this.showByType('all');
	this.setCenterMap();
	this.hideLoading();
}

DpdPickupMap.prototype.clearTerminals = function () {
	this.map.geoObjects.removeAll();
	BX.cleanNode(this.terminalsNode);

	this.placemarks = [];
}

DpdPickupMap.prototype.reload = function (componentParms, callback) {
	this
		.setComponentParams(componentParms)
		.sendRequest('reload', {}, this._bind('doReload'));
}

DpdPickupMap.prototype.liveReload = function (componentParms, componentResult) {
	this
		.setComponentParams(componentParms)
		.doReload(componentResult)
}

DpdPickupMap.prototype.doReload = function (arResult) {
	var node;

	(node = BX('DPD_cityName')) && (node.innerHTML = arResult.CITY_NAME);

	this.resetTerminals(arResult.PLACEMARKS);
	this.setTariff(arResult.TARIFFS.COURIER, 'c');
	this.setTariff(arResult.TARIFFS.PICKUP, 'p');
}

DpdPickupMap.prototype.setTariff = function (tariff, prefix) {
	var node;

	(node = this.containerNode.querySelector('#DPD_' + prefix + 'Price'))
		&& (node.innerHTML = BX.message('IPOLH_DPD_PICKUP_COST_TEXT').replace('#PRICE#', tariff ? tariff.COST : '--'));

	(node = this.containerNode.querySelector('#DPD_' + prefix + 'Date'))
		&& (node.innerHTML = BX.message('IPOLH_DPD_PICKUP_PERIOD_TEXT').replace('#DAYS#', tariff ? tariff.DAYS : '--'));
}

DpdPickupMap.prototype.sendRequest = function (action, parms, callback) {
	this._ajaxRequest && this._ajaxRequest.abort();
	this.showLoading();

	return this._ajaxRequest = BX.ajax({
		timeout: 60,
		method: 'POST',
		url: '/bitrix/components/ipol/ipol.dpdPickup/ajax.php',
		data: Object.assign({}, parms, {
			AJAX_CALL: 'Y',
			ACTION: action,
			COMPONENT_PARAMS: this.getComponentParams(),
			bitrix_sessid: BX.bitrix_sessid()
		}),
		dataType: 'json',
		onsuccess: this._bind(function (response) {
			try {
				if (response.status != 'ok') {
					alert(response.data);
				} else {
					callback(response.data);
				}

				this.hideLoading();
			} catch (e) {
				console.log(e);
			}
		})
	});
}

DpdPickupMap.prototype.getComponentParams = function () {
	return eval('(' + this.containerNode.dataset.componentParams + ')');
}

DpdPickupMap.prototype.setComponentParams = function (parms) {
	this.containerNode.dataset.componentParams = parms;
	return this;
}

DpdPickupMap.prototype.showByType = function (showedType) {
	var items = this.containerNode.querySelectorAll('.DPD_terminalSelect');

	for (var i = 0; i < items.length; i++) {
		var isShow = showedType == 'all' || showedType == items[i].dataset.terminalType;

		items[i].style.display = isShow ? 'block' : 'none';
		this.placemarks[i].options.set('visible', isShow);
	}

	BX.removeClass(this.containerNode.querySelector('.DPD_mC_block.active'), 'active');
	BX.addClass(this.containerNode.querySelector('.DPD_mC_block[data-type="' + showedType + '"]'), 'active');
}

//////////////////////////////////////////////////////////////////////////////////////

DpdPickupMap.prototype.onSelectCity = function (e) {
	var cityId = e.target.dataset.cityId;
	this.selectCity(cityId);
	return false;
}

DpdPickupMap.prototype.onClickTerminal = function (e) {
	var parms = { openBalloon: !!e.target };

	if (e.target) {
		parms.code = e.target.dataset.terminalCode;
	} else {
		parms.index = this.placemarks.indexOf(e.get('target'));
	}

	this.highlightTerminal(parms);


	[].forEach.call(this.containerNode.querySelectorAll('.arrow'), function (node) {
		BX.removeClass(node, 'up');
	});

	[].forEach.call(this.containerNode.querySelectorAll('#DPD_wrapper'), function (node) {
		BX.removeClass(node, 'show');
	});

	return false;
}

DpdPickupMap.prototype.onSelectTerminal = function (e) {
	var code = e.target.dataset.terminalCode;
	this.selectTerminal(code);
}

DpdPickupMap.prototype.onSearchCity = function (e) {
	var text = BX.util.trim(e.target.value.toLowerCase());
	var items = this.containerNode.querySelectorAll('.DPD_citySelect');

	for (var i = 0; i < items.length; i++) {
		var item = items[i];

		if (item.textContent.toLowerCase().indexOf(text) != -1) {
			item.style.display = 'block';
		} else {
			item.style.display = 'none';
		}
	}
}

DpdPickupMap.prototype.onSwitchShowedType = function (e) {
	var showedType = e.target.dataset.type;

	this.showByType(showedType);

	return false;
}

DpdPickupMap.prototype.onToggleList = function (e) {
	var arrow = this.containerNode.querySelectorAll('.DPD_arrow');
	var list = this.containerNode.querySelectorAll('#DPD_wrapper');

	[].forEach.call(arrow, function (node) {
		BX.toggleClass(node, ['up', 'down']);
	});

	[].forEach.call(list, function (node) {
		BX.toggleClass(node, ['show', 'hide']);
	});

	return false;
}

////////////////////////////////////////////////////////////////////////////////////////

DpdPickupMap.prototype._bind = function (callback) {
	var self = this;

	callback = (typeof callback == 'function')
		? callback
		: this[callback]
		;

	return function () {
		return callback.apply(self, arguments);
	}
}

DpdPickupMap.prototype._bindDomEvents = function () {
	BX.bindDelegate(this.containerNode, 'click', { className: 'DPD_citySelect' }, this._bind('onSelectCity'));
	BX.bindDelegate(this.containerNode, 'click', { className: 'DPD_terminalSelect' }, this._bind('onClickTerminal'));
	BX.bindDelegate(this.containerNode, 'click', { className: 'DPD_button' }, this._bind('onSelectTerminal'));
	BX.bindDelegate(this.containerNode, 'click', { className: 'DPD_mC_block' }, this._bind('onSwitchShowedType'));
	BX.bindDelegate(this.containerNode, 'click', { className: 'DPD_arrow' }, this._bind('onToggleList'));
	BX.bindDelegate(this.containerNode, 'keyup', { id: 'DPD_citySearcher' }, this._bind('onSearchCity'));
}

DpdPickupMap.prototype._bindMapEvents = function () {
	for (var i in this.placemarks) {
		this.placemarks[i].events.add('click', this._bind('onClickTerminal'));
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////

DpdPickupMap.prototype.showLoading = function () {
	this._loading && this.hideLoading();
	this._loading = BX.showWait(this.containerNode);
	this.maskNode.style.display = 'block';
}

DpdPickupMap.prototype.hideLoading = function () {
	BX.closeWait(this._loading);

	this._loading = false;
	this.maskNode.style.display = 'none';
}

