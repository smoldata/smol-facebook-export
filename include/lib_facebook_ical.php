<?php

// facebook_ical_birthdays takes a Birthdays iCal URL and returns the user's ID
// and a list of contacts.
function facebook_ical_birthdays($url) {

	// Check that $url actually looks like a Facebook birthdays iCal URL
	$regex = '|^webcal://www\.facebook\.com/ical/b\.php\?uid=(\d+)&key=(\w+)$|';
	if (! preg_match($regex, $url, $matches)) {
		return array(
			'ok' => 0,
			'error' => 'That calendar URL doesn’t match what we expected.'
		);
	}

	// $uid is the Facebook ID of the owner of this iCal URL, $key is for
	// preventing strangers from requesting each other's contact data.
	$user_id = $matches[1];
	$key = $matches[2];

	// It's time to download the birthday calendar!
	$ch = curl_init();
	$url = "https://www.facebook.com/ical/b.php?uid=$user_id&key=$key";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mac+OS+X/10.11.6 (15G19009) Calendar/2092.3');
	$calendar = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($status != 200) {
		$status = intval($status);
		return array(
			'ok' => 0,
			'error' => "Loading the URL returns HTTP status $status."
		);
	}

	// The first line of $calendar should be BEGIN:VCALENDAR
	$begin_calendar = strpos($calendar, 'BEGIN:VCALENDAR');

	// Make sure we got some actual calendar data
	if ($begin_calendar !== 0) {
		return array(
			'ok' => 0,
			'error' => 'We loaded the calendar URL, but got back data that doesn’t look calendar-y.'
		);
	}

	// Split the file on each calendar event (one per contact)
	$events = explode('BEGIN:VEVENT', $calendar);

	// We should have _at least_ two $events (calendar header + own birthday)
	if (count($events) < 2) {
		return array(
			'ok' => 0,
			'error' => 'The birthday events calendar didn’t return any useful data.'
		);
	}

	// Put each Facebook contact into the $contacts array
	$contacts = array();

	foreach ($events as $event) {

		/*
		$event contains:
			BEGIN:VEVENT
			DTSTART:20180401
			SUMMARY:Micah Irons's birthday
			RRULE:FREQ=YEARLY
			DURATION:P1D
			UID:b100022573605717@facebook.com
			END:VEVENT
		*/

		$facebook_id = null;
		$name = null;
		$birthday = null;

		// Extract the contact's Facebook ID
		$regex = '|UID:b(\d+)@facebook\.com|';
		if (preg_match($regex, $event, $matches)) {
			$facebook_id = $matches[1];
		}

		// Extract the contact's name
		$regex = '|SUMMARY:(.+)\'s birthday|';
		if (preg_match($regex, $event, $matches)) {
			$name = $matches[1];
		}

		// Extract the contact's birthday
		$regex = '|DTSTART:\d\d\d\d(\d\d)(\d\d)|';
		if (preg_match($regex, $event, $matches)) {
			$birthday = "uuuu-{$matches[1]}-{$matches[2]}";
		}

		// Add the contact data as a CSV row
		if (! empty($facebook_id) && ! empty($name) && ! empty($birthday)) {
			$contacts[] = array(
				'facebook_id' => $facebook_id,
				'name' => $name,
				'borthday' => $birthday
			);
		}
	}

	return array(
		'ok' => 1,
		'user_id' => $user_id,
		'contacts' => $contacts
	);
}
