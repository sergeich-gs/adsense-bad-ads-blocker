//—
function expand_close(obj_id, height=130) {
	obj_height=document.getElementById(obj_id).style.height;
	if(obj_height=='0px') document.getElementById(obj_id).style.height=height+'px';
	else document.getElementById(obj_id).style.height='0px';
}

function close_ad(obj) {
	style = window.getComputedStyle(obj.parentNode.parentNode, null);
	height =style.getPropertyValue("height");
	obj.parentNode.parentNode.style.height=height;

	setTimeout(function () {
		obj.parentNode.parentNode.style.height='0px';
		obj.parentNode.parentNode.style.paddingTop='0px';
		obj.parentNode.parentNode.style.paddingBottom='0px';
		obj.parentNode.parentNode.style.marginTop='0px';
		obj.parentNode.parentNode.style.marginBottom='0px';

	}, 10);
	obj.parentNode.parentNode.style.border='0px';

	setTimeout(function () {
		obj.parentNode.parentNode.outerHTML='';

	}, 550);

	return true;
}

function close_ad_column(id) {
   	document.getElementById('ads_list_'+id).innerHTML='';
	return true;
}

function insert_result_frame(obj) {

	if(oldframe=document.getElementById('result_frame')) {

		var instead_frame = document.createElement("iframe");
		instead_frame.id='instead_frame';
   	 	instead_frame.style.height='60px';
		instead_frame.style.margin='10px 0px 5px 0px';
		instead_frame.scrolling='no';

		oldframe.parentNode.replaceChild(instead_frame, oldframe);

		setTimeout(function () {
			instead_frame.style.height='0px';
			instead_frame.style.margin='0px';
		}, 1);

		setTimeout(function () {
			instead_frame.outerHTML='';
		}, 501);
	}

	var iframe = document.createElement("iframe");
	iframe.id='result_frame';
	iframe.name='result_frame';
	iframe.scrolling='no';
	iframe.style.height='0px';

	obj.parentNode.parentNode.parentNode.insertBefore(iframe, obj.parentNode.parentNode);

	setTimeout(function () {
		iframe.style.height='60px';
		iframe.style.margin='10px 0px 5px 0px';
	}, 1);

	//return true;
}

function time_convert(seconds) {
	sec_num = parseInt(seconds, 10);
	if(sec_num>=0) {
		hours   = Math.floor(sec_num / 3600);
		minutes = Math.floor((sec_num - (hours * 3600)) / 60);
		seconds = sec_num - (hours * 3600) - (minutes * 60);

		if (hours   < 10) {hours_out   = "0"+hours;} else {hours_out = hours;}
		if (minutes < 10) {minutes_out = "0"+minutes;} else {minutes_out = minutes;}
		if (seconds < 10) {seconds_out = "0"+seconds;} else {seconds_out  = seconds;}

		time=minutes_out+':'+seconds_out;
		if(hours>0) time=hours_out+':'+time;
		return time;
	} else
		return 0;
}


function start_searching(address, frame_id) {
	setTimeout(function() {	wait(frame_id); }, 10);
	document.getElementById(frame_id).src=address;
	//window.frames[0].src=address;
	//document.getElementById(frame_id).location.href=address;
	//document.getElementById(frame_id).location.assign(address);
	return true;
}

function wait(frame_id) {
	document.getElementById(frame_id).removeAttribute('src');
	document.getElementById(frame_id).classList.add('wait');
	return true;
}



function get_time (input_id) {
	var minutes=document.getElementById(input_id).value;
	return minutes*60;
}

function timer(time){
	var timer=document.getElementById('timer');
	timer.innerHTML=time_convert(time);
	time--;
	return time;
}


window.onload = function() {
	var seconds=get_time('run_interval');
	if(seconds) {
		var milliseconds=1000*seconds;

		setInterval( function() {

			time=timer(seconds);
			clearInterval(timerId);
			var timerId = setInterval(function() {
				time=timer(time);
				if(time<=0) clearInterval(timerId);
			}, 1000);

			start_searching('search_bad_ads.php', 'working_frame');


		}, milliseconds);

		time=timer(seconds);
		clearInterval(timerId_out);
		var timerId_out = setInterval(function() {
			time=timer(time);
			if(time<=0) clearInterval(timerId_out);
		}, 1000);

	}

}


function move(obj_id, position)	{
	var obj=document.getElementById(obj_id);

	switch (position) {
	case '1':
		obj.style.right='0%';
		obj.style.width='300%';
	    break
	case '2':
		obj.style.right='146%';
		obj.style.width='370%';
		break
	case '3':
		obj.style.right='193%';
		obj.style.width='300%';
		break
		
	case '1_bot':
		obj.style.right='0%';
		obj.style.width='300%';
	    break
	case '2_bot':
		obj.style.right='100%';
		obj.style.width='300%';
		break
	case '3_bot':
		obj.style.right='200%';
		obj.style.width='300%';
		break
		
 	case 'left':
		obj.style.right='0%';
		obj.style.width='157%';
		break
	case 'right':
		obj.style.right='78%';
		obj.style.width='186%';
		break
		
	case 'left_bot':
		obj.style.right='0%';
		obj.style.width='150%';
		break
	case 'right_bot':
		obj.style.right='50%';
		obj.style.width='150%';
		break
		
		
	default:
		obj.style.right='0%';
		obj.style.width='100%';
		break
	}
	return true;
}