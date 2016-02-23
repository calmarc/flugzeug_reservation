<script src="datetime/jquery.js"></script>
<script src="datetime/build/jquery.datetimepicker.full.js"></script>
<script>/*
window.onerror = function(errorMsg) {
	$('#console').html($('#console').html()+'<br>'+errorMsg)
}*/

$.datetimepicker.setLocale('de');

$('#vontag').datetimepicker({
	yearOffset: 0,
	lang:'de',
	timepicker:false,
	format:'d.m.Y',
	formatDate:'Y/m/d', // 1971..
	minDate:'0', // yesterday is minimum date
	maxDate:'+1971/01/01' // (diff to 1970.. -> 1 year)
});
$('#vonzeit').datetimepicker({
	datepicker:false,
	defaultTime: '12:00', 
    format:'H:i',
	step:5
});
$('#bistag').datetimepicker({
	yearOffset:0,
	lang:'de',
	timepicker:false,
	format:'d.m.Y',
	formatDate:'Y/m/d',
	minDate:'0', // yesterday is minimum date
	maxDate:'+1971/01/01' // (diff to 1970.. -> 1 year)
});
$('#biszeit').datetimepicker({
	datepicker:false,
	defaultTime: '12:00', 
	format:'H:i',
	step:5
});

</script>
