<?php
session_start();
if(!isset($_SESSION['ticketCount'])) {
	$_SESSION['ticketCount'] = 0;
}

$url = 'https://fashiondays.unfuddle.com/api/v1/ticket_reports/dynamic.json?conditions_string=status-neq-closed%2Cassignee-eq-current&title=4.+My+Active+Tickets&group_by=project&sort_by=last_comment_at&sort_direction=DESC&fields_string=project+%2Cnumber+%2Csummary+%2Cpriority+%2Cdue_on+%2Creporter+%2Cstatus+%2Ccreated_at+%2Cupdated_at+%2Clast_comment_at&pretty=true&exclude_description=true';

$creds = json_decode(file_get_contents('credentials'), true);
$config_headers[] = 'Accept: application/xml';

function getCommentsForTicket($projectNo, $ticketNo) {
	global $creds, $config_headers;
	$date = date('Y/n/j');
	$url = 'https://fashiondays.unfuddle.com/api/v1/projects/' . $projectNo . '/tickets/' . $ticketNo . '/comments.xml?formatted=true';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $config_headers);
	curl_setopt($ch, CURLOPT_USERPWD, $creds['login'] . ':' . $creds['pw']);
	$response = curl_exec($ch);
	echo $response;
	curl_close($ch);
}

$login_url = $url;
$loginpage = curl_init();

// curl_setopt($loginpage, CURLOPT_HEADER, 1);
curl_setopt($loginpage, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($loginpage, CURLOPT_URL, $login_url);
curl_setopt($loginpage, CURLOPT_HTTPHEADER, $config_headers);
curl_setopt($loginpage, CURLOPT_USERPWD, $creds['login'] . ':' . $creds['pw']);

$response = curl_exec($loginpage);
curl_close($loginpage);
// $response = file_get_contents('temp.txt');
// echo $response;
$decoded = json_decode($response, true);
// print_r($decoded);
$groups = $decoded['groups'];

$priorities = array(5 => 'Highest', 4 => 'High', 3 => 'Normal', 2 => 'Low');
$output = '';
$ticketCount = 0;

foreach($groups as $group) {
	$tickets = $group['tickets'];
	foreach($tickets as $ticket) {
		$ticketCount++;
		$priority = isset($priorities[$ticket['priority']]) ? $priorities[$ticket['priority']] : $ticket['priority'];
		$link = 'https://fashiondays.unfuddle.com/a#/projects/' . $ticket['project_id'] . '/tickets/by_number/' . $ticket['number'] . '?cycle=true';
		$output .= '<div class="ticket-holder ' . strtolower($priority) . '">';
		$output .= '<h3>#' . $ticket['number'] . ' -- ' . $ticket['summary'] . ' <span class="priority">' . $priority . '</span>'  . '</h3>';
// 		echo '<b>Ticket No</b>: ' . $ticket['number'] . '<br />';
		$output .= '<b>Created at</b>: ' . date('d-m-Y H:i:s', strtotime($ticket['created_at'])) . '<br />';
		$output .= '<b>Last comment at</b>: ' . date('d-m-Y H:i:s', strtotime($ticket['last_comment_at'])) . '<br />';
		$output .= '<b>Project</b>: ' . $ticket['project_pretty'] . '<br />';
// 		$output .= '<b>Link</b>: <a href="' . $link . '" target="_blank">' . $link . '</a><br />';
		$output .= '<b>Link</b>: ' . $link . '<br />';
		$output .= '<a class="biglink" href="' . $link . '" target="_blank"><img src="/unfuddle/images/goto.png" /></a>';

		$output .= '</div>';
	}
}

$h = fopen('newTickets', 'w');
fwrite($h, ($ticketCount - $_SESSION['ticketCount']) > 0 ? $ticketCount - $_SESSION['ticketCount'] : 0);
fclose($h);

$_SESSION['ticketCount'] = $ticketCount;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<link rel="shortcut icon" type="image/x-icon" href="/unfuddle/favicon.png" />
<meta http-equiv="refresh" content="300">
<title>Tichetele mele (<?php echo $ticketCount; ?>)</title>
</head>

<body>
<div id="container">
	<div id="header">
		<h1>Tichetele mele (<?php echo $ticketCount; ?>)</h1>
		<h2>Ultimul refresh: <?php echo date("H:i:s"); ?></h2>
	</div>
    <div id="content">
		<?php echo $output; ?>
          <p>&nbsp;</p>
          <p>&nbsp;</p>
          <p>&nbsp;</p>
    </div>
    <div id="footer">&nbsp;</div>
</div>
</body>
</html>
