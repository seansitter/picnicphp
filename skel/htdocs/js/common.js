function showSiteAlert(alert_text) {
	$('site-alert-zone').innerHTML = $('pfw-alert-zone').innerHTML+"<div class=\"pfw-alert\">"+alert_text+"</div>";
}

function clearSiteAlerts() {
	$('site-alert-zone').innerHTML = "";
}
