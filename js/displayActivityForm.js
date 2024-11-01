

function displayActivityForm(date)
{
	console.log(date);
	document.getElementById("addActivityForm").style.display = "block";
	document.getElementById("date").value = date;
}

function displayEditActivityForm(activity_id)
{
    document.getElementById("editActivityForm").style.display = "block";
    getActivity(activity_id).done(function(activity){
        var form =document.getElementById('editForm');
        form.childNodes.forEach(function (node) {
            if(node.name == 'description')
                node.value = activity['description'];
            else if(node.name == 'location')
                node.value = activity['location'];
            else if(node.name == 'time') {
                var a=activity['timestamp'].split(" ");
                var t=a[1].split(":");
                node.value = t[0] + ":" + t[1];
            }
            else if(node.name == 'date'){
                var a = activity['timestamp'].split(" ");
                node.value = a[0];
            }
            else if(node.name == 'id'){
                node.value = activity_id;
            }
        });
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
    document.getElementById("editActivityForm").style.display = "block";
}

function closeForm()
{
	document.getElementById("addActivityForm").style.display = "none";
}

function closeEditForm()
{
    document.getElementById("editActivityForm").style.display = "none";
}


function displayDayOverview(date){
	var dayelement = document.getElementById("li-".concat(date));
	if(dayelement){
		dayelement.style.position="absolute";
		dayelement.style.paddingRight="10px";
		dayelement.style.paddingBottom="20px";
		dayelement.style.marginTop="-35px";
		dayelement.style.zIndex="900";
		dayelement.style.height="100%";
		dayelement.style.width="602px";
	}
	for (var i = 0; i < dayelement.childNodes.length; i++){
		if(dayelement.childNodes[i].className == "btn-day"){
			dayelement.childNodes[i].classList.remove("btn-day");
			dayelement.childNodes[i].classList.add("btn-day-no-hover");
			dayelement.childNodes[i].onclick = "";
			dayelement.childNodes[i].blur();
		}
		var removes = dayelement.getElementsByClassName("remove");
		for (var j = 0; j < removes.length; j++){
			removes[j].style.display="block";
			removes[j].style.pointerEvents="auto";
		}
        var edits = dayelement.getElementsByClassName("edit");
        for (var j = 0; j < edits.length; j++){
            edits[j].style.display="block";
            edits[j].style.pointerEvents="auto";
        }
		var attendancebtns = dayelement.getElementsByClassName('attendance-button');
		for(var j = 0; j < attendancebtns.length; j++){
			attendancebtns[j].style.display="block";
			attendancebtns[j].style.pointerEvents="auto";
		}
	}
	document.getElementById("prev").style.display="none";
	document.getElementById("next").style.display="none";
	document.getElementById("return").style.display="block";
	document.getElementById("return").value=date;
	document.getElementById("return").onclick = function(){returnFromDayOverview(date)};
	document.getElementById("add").style.display="block";
	document.getElementById("add").value=date;
}

function returnFromDayOverview(date){
	console.log(date);
	var dayelement = document.getElementById("li-".concat(date));
	if(dayelement){
		dayelement.style.position="relative";
		dayelement.style.padding="0";
		dayelement.style.marginTop="5px";
		dayelement.style.zIndex="auto";
		dayelement.style.height="80px";
		dayelement.style.width="80px";
	}
	for (var i = 0; i < dayelement.childNodes.length; i++){
		if(dayelement.childNodes[i].className == "btn-day-no-hover"){
			dayelement.childNodes[i].classList.remove("btn-day-no-hover");
			dayelement.childNodes[i].classList.add("btn-day");
			dayelement.childNodes[i].onclick = function(){displayDayOverview(date)};
			dayelement.childNodes[i].blur();
		}
		var removes = dayelement.getElementsByClassName("remove");
		for (var j = 0; j < removes.length; j++){
			removes[j].style.display="none";
		}
        var edits = dayelement.getElementsByClassName("edit");
        for (var j = 0; j < edits.length; j++){
            edits[j].style.display="none";
        }
		var attendancebtns = dayelement.getElementsByClassName('attendance-button');
		for(var j = 0; j < attendancebtns.length; j++){
			attendancebtns[j].style.display="none";
		}
	}
	document.getElementById("prev").style.display="block";
	document.getElementById("next").style.display="block";
	document.getElementById("return").style.display="none";
	document.getElementById("add").style.display="none";
}

function returnFromDisplayAttendance(activity_id, date){
	var attendanceli = document.getElementById('attendance' + activity_id);
	attendanceli.removeAttribute("style");
	attendanceli.childNodes.forEach(function(cnode){
		if(cnode.className == "attendance-div"){
			cnode.style.display = "none";
		}
		else if(cnode.className == "attendance-button"){
			cnode.style.display = "block";
		}
		else if(cnode.className == "remove"){
			cnode.style.display = "block";
		}
		else if(cnode.className == "edit"){
		    cnode.style.display = "block";
        }
	});
	document.getElementById("return").onclick = function(){returnFromDayOverview(date)};
	document.getElementById("add").style.display="block";
}

function displayAttendance(activity_id, date) {
	var attendances;
	var yetRespondList = document.getElementById("yetToRespond" + activity_id);
	var attendList = document.getElementById("willAttend" + activity_id);
	var notAttendList = document.getElementById("willNotAttend" + activity_id);
	while(attendList.childNodes.length > 1){
		if(attendList.childNodes[1])
			attendList.removeChild(attendList.childNodes[1]);
	}
	while(notAttendList.childNodes.length > 1){
		if(notAttendList.childNodes[1])
			notAttendList.removeChild(notAttendList.childNodes[1]);
	}
	while(yetRespondList.childNodes.length > 1){
		if(yetRespondList.childNodes[1])
			yetRespondList.removeChild(yetRespondList.childNodes[1]);
	}
	getAttendanceByActivity(activity_id).done(function(data){
		attendances = data;
		attendances.forEach(function(attendance){
			var node = document.createElement("LI");
			var textnode = document.createTextNode(attendance['s.name']);
			node.appendChild(textnode);
			node.draggable = true;
			node.ondragstart = function(){handleDragStart(node, event, attendance['activity_id'], attendance['verificationCode'], date)};
			node.ondragend = function(){handleDragEnd(node)};
			node.Value = attendance['id'];
			if(!attendance['willAttend']) {
				yetRespondList.appendChild(node);
			}
			else if(attendance['willAttend'] == 1){
				attendList.appendChild(node);
			}
			else if(attendance['willAttend'] == 0){
				notAttendList.appendChild(node);
			}
		});
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
	var attendanceli = document.getElementById('attendance' + activity_id);
	attendanceli.style.position = "absolute";
	attendanceli.style.paddingRight="10px";
	attendanceli.style.paddingBottom="20px";
	attendanceli.style.zIndex="1001";
	attendanceli.style.height="90%";
	attendanceli.style.width="602px";
	attendanceli.style.background = "#fff";
	attendanceli.style.fontSize="0px";
	attendanceli.style.textAlign="center";
	attendanceli.childNodes.forEach(function(cnode){
		if(cnode.className == "attendance-div"){
			cnode.style.display = "block";
		}
		else if(cnode.className == "attendance-button"){
			cnode.style.display = "none";
		}
		else if(cnode.className == "remove"){
			cnode.style.display = "none";
		}
		else if(cnode.className == "edit"){
		    cnode.style.display = "none";
        }
	});
	document.getElementById("return").onclick= function (){returnFromDisplayAttendance(activity_id, date)};
	document.getElementById("add").style.display="none";
}

function handleDragStart(e, ev, activity_id, verification, date){
	e.parentNode.style.opacity = '0.4';
	ev.dataTransfer.effectAllowed = "move";
	ev.dataTransfer.setData("activity_id", activity_id);
	ev.dataTransfer.setData("verification", verification);
	ev.dataTransfer.setData("date", date);
	ev.dataTransfer.setData("source", e.parentNode.id);
}

function handleDragOver(e){
	e.preventDefault();
	if(globalObject.loggedin)
		e.dataTransfer.dropEffect = "move";
	else
		e.dataTransfer.dropEffect = "none"
}

function handleDrop(e, ev){
	ev.preventDefault();
	if(globalObject.loggedin) {
		var activity_id = ev.dataTransfer.getData('activity_id');
		var verification = ev.dataTransfer.getData('verification');
		var date = ev.dataTransfer.getData('date');
		if (e.id == ev.dataTransfer.getData("source"))
			return;
		var attendstring = '';
		var attend;
		if (e.className == 'attend-list') {
			attendstring = 'Will attend';
			attend = true;
		} else if (e.className == 'notattend-list') {
			attendstring = 'Will NOT attend';
			attend = false;
		} else if (e.className == 'yettorespond-list') {
			attendstring = 'Yet to respond';
			attend = null;
		}
		var r = confirm("Manually set attendance to '" + attendstring + "'?");
		if (r == true) {
			setAttendance(verification, attend)
				.done(function () {
					displayAttendance(activity_id, date);
				}).fail(function (data) {
				document.getElementById('error-result-msg').style.display = 'block';
				if (data.status == 400) {
					var msg = data.responseJSON['message'];
					document.getElementById('error-result-msg').textContent = msg;
				} else {
					document.getElementById('error-result-msg').textContent = __('Something went wrong. Please contact site administrator.', 'wp-attend');
				}
			});
		}
	}
	else{
		alert(__('You must be logged in to change attendance status', 'wp-attend'));
	}
}

function  handleDragEnd(e) {
	e.parentNode.removeAttribute('style');
}


