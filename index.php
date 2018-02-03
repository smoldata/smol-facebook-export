<?php

if (! empty($_REQUEST['url'])) {
	$url = trim($_REQUEST['url']);
}

$regex = '|^webcal://www\.facebook\.com/ical/b\.php\?uid=(\d+)&key=(\w+)$|';
if (! empty($url) && preg_match($regex, $url, $matches)) {

	$uid = $matches[1];
	$key = $matches[2];

	header('Content-Type: text/csv');
	header("Content-Disposition: attachment; filename=\"facebook_export_$uid.csv\"");

	$out = fopen('php://output', 'w');

	$ch = curl_init();
	$url = "https://www.facebook.com/ical/b.php?uid=$uid&key=$key";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mac+OS+X/10.11.6 (15G19009) Calendar/2092.3');
	$cal = curl_exec($ch);
	curl_close($ch);

	$cal_contacts = explode('BEGIN:VEVENT', $cal);

	fputcsv($out, array(
		'facebook_id',
		'name',
		'birthday'
	));

	foreach ($cal_contacts as $index => $cc) {

		$regex = '|DTSTART:\d\d\d\d(\d\d)(\d\d)|';
		if (preg_match($regex, $cc, $matches)) {
			$birthday = "uuuu-{$matches[1]}-{$matches[2]}";
		}

		$regex = '|SUMMARY:(.+)\'s birthday|';
		if (preg_match($regex, $cc, $matches)) {
			$name = $matches[1];
		}

		$regex = '|UID:b(\d+)@facebook\.com|';
		if (preg_match($regex, $cc, $matches)) {
			$id = $matches[1];
		}

		if (! empty($id) && ! empty($name) && ! empty($birthday)) {
			fputcsv($out, array(
				$id,
				$name,
				$birthday
			));
		}
	}

	// All done!
	exit;
}

?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Facebook contacts export</title>
	<link rel="stylesheet" href="css/source-sans-pro/source-sans-pro.css">
	<link rel="stylesheet" href="css/facebook-export.css">
</head>
<body>
	<header>
		<h1>Facebook contacts export</h1>
	</header>
	<div id="page">
		<p><em>This page is not associated with, or endorsed by, Facebook.</em></p>
		<p>This form lets you download a CSV file of your contacts on Facebook. Yes, you can generate a similar export by <a href="https://www.facebook.com/help/131112897028467">requesting an official archive</a> of your account, but the contact list <em>only includes names</em>.</p>
		<p>Having your contacts’ names is good, but you can’t really do anything useful with the data. This form generates a CSV that includes names, birthdays, and Facebook ID numbers. Those numeric ID numbers can be used to rebuild your social graph network somewhere else.</p>
		<p>Nothing gets saved on the server, and the code is available on <a href="https://github.com/smoldata/smol-facebook-export">GitHub</a>.</p>
		<form action="./" method="post">
			<label for="id">
				Facebook Birthdays calendar URL
			</label>
			<input type="text" name="url" id="url" placeholder="webcal://www.facebook.com/ical/b.php?uid=xxx&key=yyy">
			<input type="submit" class="btn" value="Download">
		</form>
		<hr>
		<h2>Questions</h2>
		<dl>
			<dt>How do I find my Birthdays calendar URL?</dt>
			<dd>
				<ol>
					<li>Go to your <a href="https://www.facebook.com/events/">Facebook Events</a> page</li>
					<li>Copy the <strong>Birthdays</strong> URL from the sidebar</li>
				</ol>
				<img src="img/copy.gif" alt="Copy Birthdays URL">
			</dd>
			<dt>What data is included in the CSV?</dt>
			<dd>The export file includes 3 columns: <code>facebook_id</code>, <code>name</code>, <code>birthday</code>.</dd>
			<dt>Is the export filename meaningful?</dt>
			<dd>Yes, it contains <em>your</em> Facebook ID: <code>facebook_export_[your ID number].csv</code>
			<dt>Do you keep a copy of my contacts?</dt>
			<dd>No.</dd>
			<dt>Why do the birthdays start with <code>uuuu</code>?</dt>
			<dd>It’s a convention from the <a href="https://www.loc.gov/standards/datetime/">EDTF date standard</a>.</dd>
			<dt>Why would I want to export my contacts?</dt>
			<dd>They are possibly the most valuable part of your Facebook account, and you should have your own copy of the data.</dd>
			<dt>Why don’t the number of exported contacts match the number of friends I have on Facebook?</dt>
			<dd>This may be due to privacy settings. By default birthdays are visible to friends of friends. If any of your friends have changed that setting to “only me,” they won’t appear in your Birthdays calendar and will be missing from the contacts export.</dd>
			<dt>How do you adjust your birthday privacy settings on Facebook?</dt>
			<dd>
				<ol>
					<li>Go to your profile page on Facebook (click on your name on the sidebar).</li>
					<li>Click on the <strong>About</strong> tab.</li>
					<li>Scroll down and select <strong>Contact and Basic Info</strong> from the sidebar.</li>
					<li>Hover over the Birth Date and click the Edit button on the right.</li>
				</ol>
				<img src="img/birthday-settings.png" alt="Birthday privacy settings">
			</dd>
			<dt>Are you hacking Facebook?</dt>
			<dd>No, this form uses an officially supported API.</dd>
			<dt>Who made this?</dt>
			<dd><a href="https://phiffer.org/">Dan Phiffer</a> with the <a href="https://smoldata.org/">Smol Data collective</a>.</dd>
		</dl>
	</div>
</body>
