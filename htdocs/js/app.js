

function createFieldForm(name, action,  formHTML, fields, record) {
	//var req;
	//req=(name!='rsc-string-del');
$().w2form({
		name: name,
		style: 'border: 0px; background-color: transparent;',
url: '../w2ui.php',
		postData: {
			"obj_id"		: '{$obj_id}', 
			"page_signature": page_signature,
			"jsframework" 	: "w2ui",
			"component" 	: "form",
			"save-record"	: action,
		},
		formHTML: 
			formHTML,
		fields: fields ,
		record: record,
		actions: {
			reset : function () { this.clear();$().w2popup('close'); },
			save  : function () {
					var obj = this;
					this.save({}, function (data) { 
						if (data.status == 'error') {
							console.log('ERROR: '+ data.message);
							return;
						} else if  (data.status == 'success') {
							console.log('SUCCESS: '+ data.message);
							$().w2popup('close');
							//window.location.href=data.record.url;
							return;
						}
					});
				},
			}
	});
//<bleetz:debug>
	w2ui[name].on('save', function(target, eventData) {
	console.log(eventData);
});
//</bleetz:debug>
}

function openFieldPopup (formname, fieldname) {
	
	$().w2popup('open', {
		title   :  fieldname,
		showClose: false,
		body    : '<div id="form" style="width: 100%; height: 100%;"></div>',
		style   : 'padding: 15px 0px 0px 0px',
		width   : 400,
		height  : 200, 
		onOpen  : function () {
			var f=$('#w2ui-popup #form');
			w2ui[formname].record["field_name"]=fieldname;
			f.w2render(formname);
		},
	});

}


