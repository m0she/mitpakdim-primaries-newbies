
function getXmlHttpObj() {
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else {// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xmlhttp;
}

function call_logic(method, params) {
	var s = [];
	s.push("method=" + method);
	for (var p in params) {
		s.push(p + "=" + params[p]);
	}
	var xml = getXmlHttpObj();
	xml.open("POST", "logic.php", false); 
	xml.setRequestHeader("Content-type","application/x-www-form-urlencoded; charset=UTF-8");
	//alert(s.join('&'));
	xml.send(s.join('&'));
	return get_result(xml);
}

function get_result(xml) {
	if (xml.responseText) {
		var res = eval("var _x=" + xml.responseText + ";_x");
		if (res.type && res.type == 'success') {
			return (res.value || true);
		} else if (res.type && res.type == 'error') {
			var msg = res.msg;
			if (res.details) {
				msg += " (" + res.details + ")";
			}
			throw new Error(msg);
		} else {
			throw new Error('Unknown error');
		}
	}
	throw new Error('No response');
}

function list_parties() {
	return call_logic('list_parties', {});
}

function list_regions(party) {
	return call_logic('list_regions', { party: party });
}

function new_candidate(fname, lname, zehut, party, region, email, phone, website, facebook) {
	return call_logic('new_candidate', { fname: fname, lname: lname, zehut: zehut, party: party, region: region, email: email, phone: phone, website: website, facebook: facebook });
}

function get_candidate_status() {
	return call_logic('get_candidate_status', {});
}

function resend_confirmation() {
	return call_logic('resend_confirmation', {});
}