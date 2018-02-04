<?php

date_default_timezone_set('UTC');

require_once 'include/lib_facebook_ical.php';
require_once 'include/lib_output.php';

$esc_url = '';
$esc_response = '';

if (! empty($_REQUEST['url'])) {
	$url = trim($_REQUEST['url']);
	$esc_url = htmlentities($url);
	$rsp = facebook_ical_birthdays($url);

	if (! empty($rsp['ok'])) {
		$user_id = $rsp['user_id'];
		$contacts = $rsp['contacts'];
		$filename = "facebook_export_$user_id.csv";
		output_csv($contacts, $filename, 'with headers');
	} else {
		$esc_response = 'Oops, something unexpected went wrong!';
		if (! empty($rsp['error'])) {
			$esc_response = htmlentities($rsp['error']);
		}
	}
}

?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Facebook contacts export</title>
	<link rel="stylesheet" href="css/source-sans-pro/source-sans-pro.css">
	<link rel="stylesheet" href="css/facebook-export.css">
	<link rel="shortcut icon" href="favicon.png">
</head>
<body>
	<header>
		<h1>Facebook contacts export</h1>
	</header>
	<div id="page">
		<p><em>This page is not associated with or endorsed by Facebook.</em></p>
		<p>This form lets you download a CSV file of your contacts on Facebook. Yes, you can generate a similar export by <a href="https://www.facebook.com/help/131112897028467">requesting an official archive</a> of your account, but the contact list <em>only includes names</em>.</p>
		<p>Having your contacts’ names is good, but you can’t really do anything useful with the data. This form generates a CSV that includes names, birthdays, and Facebook ID numbers. Those numeric ID numbers can be used to rebuild your social graph network somewhere else.</p>
		<p>Nothing gets saved on the server, and the code is available on <a href="https://github.com/smoldata/smol-facebook-export">GitHub</a>.</p>
		<form action="./" method="post">
			<label for="id">
				Facebook Birthdays calendar URL
			</label>
			<input type="text" name="url" id="url" value="<?php echo $esc_url; ?>" placeholder="webcal://www.facebook.com/ical/b.php?uid=xxx&key=yyy">
			<input type="submit" class="btn" value="Download">
			<?php

			if (! empty($esc_response)) {
				echo "<div class=\"response\">$esc_response</div>";
			}

			?>
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
			<dt>I tried to navigate to the Birthday Events page and I don’t see the link?</dt>
			<dd>All I can say is <em>Facebook is designed like a shopping mall</em>. You are meant to get lost and distracted, and maybe buy some stuff you didn’t plan on buying. So you may want to just click on <a href="https://www.facebook.com/events/">this link</a>, and avoid the twisty navigation choices. Then scroll down to find the little calendar link in the sidebar.</dd>
			<dt>What data is included in the CSV?</dt>
			<dd>The export file includes 3 columns: <code>facebook_id</code>, <code>name</code>, <code>birthday</code>.</dd>
			<dt>Is the export filename meaningful?</dt>
			<dd>Yes, it contains <em>your</em> Facebook ID: <code>facebook_export_[your Facebook ID].csv</code>
			<dt>Do you keep a copy of my contacts?</dt>
			<dd>No.</dd>
			<dt>Why do the birthdays start with <code>uuuu</code>?</dt>
			<dd>It’s a convention from the <a href="https://www.loc.gov/standards/datetime/">EDTF date standard</a>. Basically it stands for an <em>unknown year</em>, since the birthday calendar only shows the <em>next</em> birthday for each of your friends.</dd>
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
