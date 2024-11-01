const { __, _x, _n, _nx } = wp.i18n;

function removeActivity(id){
	var r = confirm(__("are you sure you want to remove this activity?", 'wp-attend'));
	if(r == true){
		var adition = "";
		var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity/' + id;
		jQuery.ajax(urlRequest,{
			url : urlRequest,
			type : "DELETE",
			data : {},
			beforeSend: function (xhr) {
				xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
			}
		}).done((response) => {
			var red = '';
			if(window.location.href.includes('?'))
				red = window.location.href + '&result='+__('Activity deleted succesfully', 'wp-attend');
			else
				red = window.location.href + '?result='+__('Activity deleted succesfully', 'wp-attend');
			window.location.href = red;
		}).fail(function (data) {
			document.getElementById('error-result-msg').style.display = 'block';
			if(data.status == 400){
				var msg = data.responseJSON['message'];
				document.getElementById('error-result-msg').textContent = msg;
			}
			else{
				document.getElementById('error-result-msg').textContent = __('Something went wrong. Please contact site administrator.', 'wp-attend');
			}
		});

	}
}

function createActivity($event){
	if(!validateActivityForm('aForm'))
	{
		console.log("invalid");
		return false;
	}
	$event.preventDefault();
	var object = {};
	var formData = new FormData(document.getElementById("aForm"));
	formData.forEach(function(value, key){
		if(key == 'emailSubscribers' && value == 'on')
			object[key] = true;
		else
			object[key] = value;
	});
	var data = JSON.stringify(object);
	var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity';
	jQuery.ajax(urlRequest, {
		url: urlRequest,
		type: "POST",
		data: data,
		beforeSend: function (xhr) {
			xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
		}
	}).done((response) => {
		var red = '';
		if(window.location.href.includes('?'))
			red = window.location.href + '&result=' + __('Activity created succesfully', 'wp-attend');
		else
			red = window.location.href + '?result=' + __('Activity created succesfully', 'wp-attend');
		window.location.href = red;
	}).fail(function (data) {
		document.getElementById('error-result-msg').style.display = 'block';
		if(data.status == 400){
			var msg = data.responseJSON['message'];
			document.getElementById('error-result-msg').textContent = msg;
		}
		else{
			var msg = data.responseJSON['message'];
			if(msg == 'email failed'){
				document.getElementById('error-result-msg').textContent = __('Sending email failed. Please contact site administrator', 'wp-attend');
			}
			else{
				document.getElementById('error-result-msg').textContent = __('Something went wrong. Please contact site administrator.', 'wp-attend');
			}
		}
		closeForm();
	});
}

function editActivity($event){
	if(!validateActivityForm('editForm'))
	{
		console.log("invalid");
		return false;
	}
	$event.preventDefault();
	var object = {};
	var formData = new FormData(document.getElementById("editForm"));
	formData.forEach(function(value, key){
		if(key == 'emailSubscribers' && value == 'on')
			object[key] = true;
		else
			object[key] = value;
	});
	var data = JSON.stringify(object);
	var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity';
	jQuery.ajax(urlRequest, {
		url: urlRequest,
		type: "PUT",
		data: data,
		beforeSend: function (xhr) {
			xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
		}
	}).done((response) => {
		var red = '';
		if(window.location.href.includes('?'))
			red = window.location.href + '&result='+ __('Activity updated succesfully', 'wp-attend');
		else
			red = window.location.href + '?result='+ __('Activity updated succesfully', 'wp-attend');
		window.location.href = red;
	}).fail(function (data) {
		document.getElementById('error-result-msg').style.display = 'block';
		if(data.status == 400){
			var msg = data.responseJSON['message'];
			document.getElementById('error-result-msg').textContent = msg;
		}
		else{
			document.getElementById('error-result-msg').textContent = __('Something went wrong. Please contact site administrator.', 'wp-attend');
		}
		closeEditForm();
	});
}

function getActivity(activity_id){
	var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity/' + activity_id;
	return jQuery.ajax(urlRequest, {
		url: urlRequest,
		type: "GET",
		beforeSend: function (xhr) {
			xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
		}
	});
}

function validateActivityForm(formname){
	var description = document.forms[formname]["description"].value;
	var location = document.forms[formname]["location"].value;
	var time = document.forms[formname]["time"].value;
	if(description == null || description == "" || location == null || location == "" || time == null || time == ""){
		return false;
	}
	return  true;
}

function getAttendanceByActivity(activity_id){
	var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity/attendance/' + activity_id;
	return jQuery.ajax(urlRequest,{
		url:urlRequest,
		type: "GET",
		beforeSend: function (xhr) {
			xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
		}
	});
}

function setAttendance(verificationCode, willAttend){
	var attendstring = '';
	if(willAttend == true)
		attendstring = 'true';
	else if(willAttend == false)
		attendstring = 'false';
	else if(willAttend == null)
		attendstring = 'null';
	var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/activity/attendance?code=' +verificationCode + '&willattend=' + attendstring;
	return jQuery.ajax(urlRequest, {
		url:urlRequest,
		type:"GET"
	});
}
