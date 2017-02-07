$('.tabsButtons').each(function() {
	var tabsButtonsWrapper = $(this);
	var tabWrapper = $(tabsButtonsWrapper.data('href'));

	tabsButtonsWrapper.find('a').click(function(event) {
		event.preventDefault();
		var aEl = $(this);
		tabsButtonsWrapper.find('.active').removeClass('active');
		tabWrapper.find('>:not(.hide)').addClass('hide');
		aEl.parent().addClass('active');
		$(aEl.attr('href')).removeClass('hide');
	});
});

$('body').on('click', '.switchFrame', function(event) {
	event.preventDefault();
	var href = $(this).data('href');
	$('.frame').addClass('hide');
	$(href).removeClass('hide');
});
var app = {
	rowDataTemplate: Handlebars.compile($("#rowDataTemplate").html())
};

var mainForm = {
	urlInputEl: $("#urlInput"),
	answerContent: $("#answerContent"),
	htmlContent: $("#htmlFrameWindow"),
	headersContent: $("#headersContent"),
	requestDataContent: $("#requestData"),
	sendTypeSelect: $("#sendTypeSelect"),
	autoPickupCheckbox: $("#autoPickupCookies"),
	pickupCookieButton: $("#pickupCookie"),
	autoRedirectTypeSelect: $("#autoRedirectType"),
	nextPageButton: $("#nextPage"),
	throwValuesCheckbox: $("#throwValueOnRedirect"),
	timerInput: $("#timer"),
	timerEnableCheckbox: $("#timerEnable"),
	timeoutTimer: 0,
	sendButton: $("#sendButton"),
	baseLoginInput: $("#baseLogin"),
	basePassInput: $("#basePass"),
	lastAnswer: {
		OK: 0,
		html: '',
		htmlEscaped: '',
		setCookies: null,
		headers: null
	},
	init: function() {
		var that = this;
		this.sendButton.click(function(event) {
			event.preventDefault();
			if (that.timeoutTimer) {
				clearInterval(that.timeoutTimer);
				that.sendButton.html('SEND');
				that.timeoutTimer = 0;
			} else {
				if (that.timerEnableCheckbox.prop('checked')) {
					that.sendButton.html('STOP');
					that.sendRequest();
					that.timeoutTimer = setInterval(function () {
						that.sendRequest();
					}, parseInt(that.timerInput.val()));
				} else {
					that.sendRequest();
				}
			}
		});

		this.pickupCookieButton.click(function(event) {
			event.preventDefault();
			for (var name in that.lastAnswer.setCookies) {
				if (that.lastAnswer.setCookies.hasOwnProperty(name)) {
					cookieDataFrame.cookieData[name] = that.lastAnswer.setCookies[name];
				}
			}
			cookieDataFrame.redraw();
		});

		this.nextPageButton.click(function(event) {
			event.preventDefault();
			if (that.lastAnswer.nextPage) {
				var forward = that.throwValuesCheckbox.prop('checked');
				that.sendRequest(that.lastAnswer.nextPage, forward);
			}
		})
	},
	isThrowState: function() {
		return +this.autoRedirectTypeSelect.val() === 2 && this.throwValuesCheckbox.prop('checked');
	},
	sendRequest: function(url, forward) {
		var that = this;
		var data = {};
		if (forward !== false) {
			data = postDataFrame.requestData;
		}
		var sendedData = {
			url: url ? url : $.trim(this.urlInputEl.val()),
			method: this.sendTypeSelect.val(),
			data: data,
			cookies: cookieDataFrame.cookieData,
			headers: headerDataFrame.headerData,
			redirectType: this.isThrowState() ? 1 : +this.autoRedirectTypeSelect.val(),
			baseLogin: that.baseLoginInput.val(),
			basePass: that.basePassInput.val()
		};
		$.post('/getAnswer', sendedData, function(answ) {
			that.lastAnswer = answ;
			if (that.isThrowState()) {
				if (answ.nextPage) {
					that.sendRequest(answ.nextPage);
					return 0;
				}
			}
			that.renderAnswer();
		}, 'json');
	},
	renderAnswer: function() {
		var that = this;
		var answ = this.lastAnswer;
		if (answ.OK) {
			that.answerContent.html(answ.htmlEscaped);
			var htmlString = answ.html;
			if (answ.isImage) {
				htmlString = "<img src='" + answ.requestUrl + "'/>";
			}
			try {
				that.htmlContent.contents().find('html').html(htmlString);
			} catch (e) {}
			var headersString = "";
            headersString = 'Status Code: ' + answ.statusCode + '<br />';
			for (var headName in answ.headers) {
				if (answ.headers.hasOwnProperty(headName)) {
					headersString += headName + " = " + answ.headers[headName] + "<br />";
				}
			}
			that.headersContent.html(headersString);
			if (that.autoPickupCheckbox.prop('checked')) {
				that.pickupCookieButton.click();
			}
			that.nextPageButton.removeAttr('title');
			that.nextPageButton.prop('disabled', true);

			if (answ.nextPage) {
				that.nextPageButton.attr('title', answ.nextPage);
				that.nextPageButton.prop('disabled', false);
			}

			var requestString = this.sendTypeSelect.val();
			requestString += " " + this.urlInputEl.val();
			// Headers
			requestString += "<br /><br />Headers: <br />";
			for (var head in this.lastAnswer.reqHeaders) {
				if (this.lastAnswer.reqHeaders.hasOwnProperty(head)) {
					requestString += "&nbsp;&nbsp;&nbsp;&nbsp;" + head + " = " + this.lastAnswer.reqHeaders[head] + "<br />";
				}
			}
			requestString += "<br />";
			// SendedData
			requestString += "Data: <br />";
			for (var dataName in postDataFrame.requestData) {
				if (postDataFrame.requestData.hasOwnProperty(dataName)) {
					requestString += "&nbsp;&nbsp;&nbsp;&nbsp;" + dataName + " = " + postDataFrame.requestData[dataName] + "<br />";
				}
			}
			this.requestDataContent.html(requestString);
		}
	}
};
mainForm.init();

var postDataFrame = {
	addDataButton: $("#addData"),
	postDataFrame: $("#postDataFrame"),
	saveDataButton: $("#saveData"),
	postDataContent: $("#postDataContent"),
	requestData: {},
	init: function() {
		var that = this;
		this.addDataButton.click(function(event) {
			event.preventDefault();
			that.addRow();
		});
		this.addDataButton.click();

		this.saveDataButton.click(function(event) {
			event.preventDefault();
			that.requestData = {};
			that.postDataContent.find('.row').each(function() {
				var el = $(this);
				var inputs = el.find('input');
				if (inputs.eq(0).size()) {
					var key = $.trim(inputs.eq(0).val());
					var val = inputs.eq(1).val();
					if (key) {
						that.requestData[key] = val;
					}
				}
			});
			that.clearData();
		});
	},
	clearData: function(full) {
		if (full) {
			that.requestData = {};
		}
		this.postDataContent.find('.row').each(function() {
			var el = $(this);
			if (full) {
				el.remove();
			} else {
				var inputs = el.find('input');
				var key = $.trim(inputs.eq(0).val());
				var val = $.trim(inputs.eq(1).val());
				if (!key && !val) {
					el.remove();
				}
			}
		});
		var rows = this.postDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	},
	addRow: function(key, value) {
		this.postDataContent.append(app.rowDataTemplate({key: key, value: value}));
	},
	redraw: function() {
		this.postDataContent.find('.row').remove();
		for (var varName in this.requestData) {
			if (this.requestData.hasOwnProperty(varName)) {
				this.addRow(varName, this.requestData[varName]);
			}
		}
		var rows = this.postDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	}
};
postDataFrame.init();

var cookieDataFrame = {
	addDataButton: $("#addCookieData"),
	cookieDataFrame: $("#cookieDataFrame"),
	saveDataButton: $("#saveCookieData"),
	cookieDataContent: $("#cookieDataContent"),
	requestData: {},
	cookieData: {},
	init: function() {
		var that = this;
		this.addDataButton.click(function(event) {
			event.preventDefault();
			that.addRow();
		});
		this.addDataButton.click();

		this.saveDataButton.click(function(event) {
			event.preventDefault();
			that.cookieData = {};
			that.cookieDataContent.find('.row').each(function() {
				var el = $(this);
				var inputs = el.find('input');
				if (inputs.eq(0).size()) {
					var key = $.trim(inputs.eq(0).val());
					var val = inputs.eq(1).val();
					if (key) {
						that.cookieData[key] = val;
					}
				}
			});
			that.clearData();
		});
	},
	addRow: function(key, value) {
		this.cookieDataContent.append(app.rowDataTemplate({key: key, value: value}));
	},
	clearData: function(full) {
		if (full) {
			this.cookieData = {};
		}
		this.cookieDataContent.find('.row').each(function() {
			var el = $(this);
			if (full) {
				el.remove();
			} else {
				var inputs = el.find('input');
				var key = $.trim(inputs.eq(0).val());
				var val = $.trim(inputs.eq(1).val());
				if (!key && !val) {
					el.remove();
				}
			}
		});
		var rows = this.cookieDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	},
	redraw: function() {
		this.cookieDataContent.find('.row').remove();
		for (var cookieName in this.cookieData) {
			if (this.cookieData.hasOwnProperty(cookieName)) {
				this.addRow(cookieName, this.cookieData[cookieName]);
			}
		}
		var rows = this.cookieDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	}
};
cookieDataFrame.init();

var headerDataFrame = {
	addDataButton: $("#addheaderData"),
	headerDataFrame: $("#headerDataFrame"),
	saveDataButton: $("#saveheaderData"),
	headerDataContent: $("#headerDataContent"),
	requestData: {},
	headerData: {},
	autoCompleteSettings: {
		minChars: 0,
		lookup: [
			'Accept', 'Accept-Charset', 'Accept-Encoding', 'Accept-Language', 'Accept-Datetime',
			'Authorization', 'Cache-Control', 'Connection', 'Cookie', 'Content-Length', 'Content-MD5',
			'Content-Type', 'Date', 'Expect', 'From', 'Host', 'If-Match', 'If-Modified-Since', 'If-None-Match',
			'If-Range', 'If-Unmodified-Since', 'Max-Forwards', 'Origin', 'Pragma', 'Proxy-Authorization',
			'Range', 'Referer', 'TE', 'User-Agent', 'Upgrade', 'Via', 'Warning', 'X-Requested-With', 'DNT',
			'X-Forwarded-For', 'X-Forwarded-Host', 'X-Forwarded-Proto', 'Front-End-Https', 'X-Http-Method-Override',
			'X-ATT-DeviceId', 'X-Wap-Profile', 'Proxy-Connection'
		]
	},
	init: function() {
		var that = this;
		this.addDataButton.click(function(event) {
			event.preventDefault();
			that.addRow();
		});
		this.addDataButton.click();

		this.saveDataButton.click(function(event) {
			event.preventDefault();
			that.headerData = {};
			that.headerDataContent.find('.row').each(function() {
				var el = $(this);
				var inputs = el.find('input');
				if (inputs.eq(0).size()) {
					var key = $.trim(inputs.eq(0).val());
					var val = inputs.eq(1).val();
					if (key) {
						that.headerData[key] = val;
					}
				}
			});
			that.clearData();
		});
	},
	clearData: function(full) {
		if (full) {
			this.headerData = {};
		}
		this.headerDataContent.find('.row').each(function() {
			var el = $(this);
			if (full) {
				el.remove();
			} else {
				var inputs = el.find('input');
				var key = $.trim(inputs.eq(0).val());
				var val = $.trim(inputs.eq(1).val());
				if (!key && !val) {
					el.remove();
				}
			}
		});
		var rows = this.headerDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	},
	addRow: function(key, value) {
		this.headerDataContent.append(app.rowDataTemplate({key: key, value: value}));
		this.headerDataContent.find('.row:last-child input').eq(0).autocomplete(this.autoCompleteSettings);
	},
	redraw: function() {
		this.headerDataContent.find('.row').remove();
		for (var header in this.headerData) {
			if (this.headerData.hasOwnProperty(header)) {
				this.addRow(header, this.headerData[header]);
			}
		}
		var rows = this.headerDataContent.find('.row');
		if (!rows.size()) {
			this.addDataButton.click();
		}
	}
};
headerDataFrame.init();

var loadPresetFrame = {
	presetsContent: $("#presetsContent"),
	loadPresetButton: $("#loadPreset"),
	deletePresetButton: $("#deletePreset"),
	savePresetButton: $("#savePreset"),
	presetNameInput: $("#presetName"),
	quitFromPresetsButton: $("#quitFromPresets"),
	presets: {},
	init: function() {
		var that = this;
		this.load();
		this.savePresetButton.click(function(event) {
			event.preventDefault();
			that.savePreset();
		});

		this.presetsContent.on('click', 'li', function() {
			var el = $(this);
			that.presetsContent.find('li.active').removeClass('active');
			el.addClass('active');
		});

		this.loadPresetButton.click(function(event) {
			event.preventDefault();
			var activePresetId = that.presetsContent.find('.active').data('presetid');
			if (activePresetId) {
				var data = that.presets[activePresetId];
				mainForm.urlInputEl.val(data.url);
				mainForm.sendTypeSelect.val(data.reqType);

				postDataFrame.requestData = data.data;
				postDataFrame.redraw();

				cookieDataFrame.cookieData = data.cookies;
				cookieDataFrame.redraw();

				headerDataFrame.headerData = data.headers;
				headerDataFrame.redraw();

				mainForm.autoRedirectTypeSelect.val(data.redirectType);
				mainForm.baseLoginInput.val(data.baseLogin);
				mainForm.basePassInput.val(data.basePass);
				mainForm.autoPickupCheckbox.prop('checked', data.autoPickup);
				mainForm.throwValuesCheckbox.prop('checked', data.throwValues);
				mainForm.timerEnableCheckbox.prop('checkbox', data.timerEnable);
				mainForm.timerInput.val(data.timerVal);
				that.quitFromPresetsButton.click();
			}
		});

		this.deletePresetButton.click(function(event) {
			event.preventDefault();
			var activePreset = that.presetsContent.find('.active');
			var activePresetId = activePreset.data('presetid');
			if (activePresetId) {
				$.post('/deletePreset', {presetId: activePresetId}, function(answ) {
					if (answ.OK) {
						activePreset.remove();
						delete(that.presets[activePresetId]);
					}
				}, 'json');
			}
		});
	},
	load: function() {
		var that = this;
		$.get('/loadPresets', {}, function(answ) {
			if (answ.OK) {
				for (var i in answ.presets) {
					if (answ.presets.hasOwnProperty(i)) {
						that.presets[i] = answ.presets[i].presetJson;
						that.presets[i].presetName = answ.presets[i].presetName;

						var liEl = $("<li />", {
							id: 'preset' + i,
							'data-presetid': i,
							html: answ.presets[i].presetName
						});
						that.presetsContent.append(liEl);
					}
				}
			}
		}, 'json');
	},
	savePreset: function() {
		var that = this;
		var presetName = $.trim(this.presetNameInput.val());
		if (presetName) {
			var sendedData = {
				presetName: that.presetNameInput.val(),
				url: mainForm.urlInputEl.val(),
				reqType: mainForm.sendTypeSelect.val(),
				data: postDataFrame.requestData,
				cookies: cookieDataFrame.cookieData,
				headers: headerDataFrame.headerData,
				redirectType: mainForm.autoRedirectTypeSelect.val(),
				baseLogin: mainForm.baseLoginInput.val(),
				basePass: mainForm.basePassInput.val(),
				autoPickup: mainForm.autoPickupCheckbox.prop('checked'),
				throwValues: mainForm.throwValuesCheckbox.prop('checked'),
				timerEnable: mainForm.timerEnableCheckbox.prop('checkbox'),
				timerVal: mainForm.timerInput.val()
			};
			$.post('/savePreset', sendedData, function(answ) {
				if (answ.OK) {
					that.presets[answ.presetId] = sendedData;
					var liEl = $("<li />", {
						id: 'preset' + answ.presetId,
						'data-presetid': answ.presetId,
						html: sendedData.presetName
					});
					that.presetsContent.append(liEl);
				}
			}, 'json');
		}
	}
};
loadPresetFrame.init();
