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
	defaultTime:'12:00', 
    allowTimes: [ '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00',
                  '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
                  '18:00', '18:30', '19:00', '19:30', '20:00', '20:30'],
    format: 'H:i',
	step: 30
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
    allowTimes: [ '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00',
                  '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
                  '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'],
	format: 'H:i',
	step: 30
});

</script>
