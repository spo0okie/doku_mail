<?php

function wiki_rq($method,$params) {
	global $wiki_url;
	global $wiki_user;
	global $wiki_pass;

	$data=xmlrpc_encode_request($method,$params,['encoding'=>'utf-8','escaping'=>[]]);
	$curl=curl_init($wiki_url.'lib/exe/xmlrpc.php');
	$tmpfname = dirname(__FILE__).'/cookie.txt';
	curl_setopt($curl, CURLOPT_COOKIEJAR, $tmpfname);
	curl_setopt($curl, CURLOPT_COOKIEFILE, $tmpfname);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$response=curl_exec($curl);
	$response=xmlrpc_decode($response);
	return $response;
}

$cellStyle='style="border:1px solid #999; border-collapse:collapse; color:#333; padding:0.5em 1em"';

wiki_rq('dokuwiki.login',[$wiki_user,$wiki_pass]);
$changes=wiki_rq('wiki.getRecentChanges',$periodStart);
?>

<div style="color:#666">
<h1>Изменения в Wiki</h1>

<?php 
if (isset($changes['faultString'])) { 
	//если вылезла ошибка, то вместо отчета выводим ее
	echo '<p>'.$changes['faultString'].'</p>';

} elseif (!count($changes)) {
	//с последней версии отсутствие изменений - больше не ошибка. Симулируем ее
	echo '<p>There are no changes in the specified timeframe</p>';

} else { ?>
	<p>
	В таблице приведены статьи, которые были изменены за последние сутки. <br>
	Просьба всех ознакомиться с изменениями по статьям связанным с вашими обязанностями.
	</p>
	<table 
		<tr>
			<th <?= $cellStyle ?>>
				Статья
			</th>
			<th <?= $cellStyle ?>>
				Редакторы
			</th>
			<th  <?= $cellStyle ?>>
				Секции
			</th>
			<th  <?= $cellStyle ?>>
				Правки
			</th>
		</tr>


	<?php foreach ($changes as $change) {
		$versions=wiki_rq('wiki.getPageVersions',[$change['name'],0]);
		$authors=[];
		$sections=[];
		$rev=time();
		$count=0;
		//var_dump($versions);
		foreach ($versions as $version) {
			if ($rev>$periodStart) {
				$count++;
				if ($version['version']<$rev) $rev=$version['version'];
			} else {
				if ($version['version']>$rev && $version['version']<$periodStart) $rev=$version['version'];
			}
		
			if ($version['version']>=$periodStart) {
				$authors[$version['user']]=$version['version'];
				
			if (strlen(trim($version['sum'])))
				$sections[trim($version['sum'])]=$version['version'];
			}
		}
		$authors=array_flip($authors);
		$sections=array_flip($sections);
		ksort($authors);
		ksort($sections);
		?>
		<tr>
			<td <?= $cellStyle ?>><a href="<?= $wiki_url.$change['name'] ?>"><?= $change['name'] ?></td>
			<td <?= $cellStyle ?>><?= implode('<br>',$authors) ?></td>
			<td <?= $cellStyle ?>><?= implode('<br>',$sections); ?></td>
			<td <?= $cellStyle ?>><a href="<?= $wiki_url.$change['name'] ?>?do=diff&rev=<?= $rev ?>"><?= $count ?></a></td>
		</tr>
	<?php } ?>
	</table>

<?php } ?>

</div>

