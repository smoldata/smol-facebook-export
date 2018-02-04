<?php

function output_csv($data, $filename = 'output.csv', $headers = true) {

	// Instruct the browser to download the output as a CSV file
	header('Content-Type: text/csv');
	header("Content-Disposition: attachment; filename=\"$filename\"");

	// If we don't have any data, then ... we are done?
	if (empty($data)) {
		exit;
	}

	// We need to use a file handle for fputcsv()
	$out = fopen('php://output', 'w');

	// Output the column names as headers
	if (! empty($headers)) {
		$columns = array_keys($data[0]);
		fputcsv($out, $columns);
	}

	// Output each item in $data as a CSV row
	foreach ($data as $row) {
		fputcsv($out, $row);
	}

	// All done!
	fclose($out);
	exit;
}
