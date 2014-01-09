<?php echo $this->element('tabs'); ?>
<h1>System Monitor</h1>

<h2>Mysql</h2>
<?php echo $this->element('mysql_processes'); ?>

<h2>Apache</h2>
<?php echo $this->element('apache_status'); ?>

<?php $this->Asset->blockStart(); ?>
var refreshSeconds = 10;
var tid = setInterval(apacheStatusUpdate, refreshSeconds * 1000);
function apacheStatusUpdate() {
	$('.ajaxApacheContainer').ajaxLoad('/staff/system_monitors/apache', {loadTarget: "#apacheStatus"});
}
function apacheStatusAbort() {
	clearInterval(tid);
}

$('.ajaxApacheContainer a').live('click', function() {
	$('.ajaxApacheContainer').ajaxLoad($(this).attr('href'), {loadTarget: "#apacheStatus"});
	return false;
});
<?php $this->Asset->blockEnd(); ?>