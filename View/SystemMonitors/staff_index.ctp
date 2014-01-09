<?php
echo $this->element('system_monitors/tabs');

echo $this->Html->tag('h1', 'System Monitor', array('class' => 'top'));

echo $this->Html->tag('h1', 'MySQL');
echo $this->element('system_monitors/mysql_processes');

echo $this->Html->tag('h1', 'Apache');
echo $this->Html->div('ajaxApacheContainer');
echo $this->element('system_monitors/apache_status');
echo "</div>\n";

$this->Asset->blockStart(); ?>
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