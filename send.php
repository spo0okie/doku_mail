#!/usr/bin/php
<?php
	ob_start();
	include __DIR__.'/settings.php';
	include __DIR__.'/render.php';
	$changes=ob_get_clean();

	if (strpos($changes,'<p>There are no changes in the specified timeframe</p>')) {
		echo 'no changes';
	//	exit;
	}
	//var_dump($changes);
	system ("echo \"$changes\" | $mutt -F $muttrc -e \"set content_type=text/html\" -s \"Обновления в вики\" -- $sendTo");
