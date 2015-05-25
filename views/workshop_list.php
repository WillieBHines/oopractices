<?php
$rows = $data['upcoming'];
	
$rowclass = array(
	'soldout' => 'danger',
	'open' => 'success',
	'past' => ''
);	
	
echo "<table class='table table-striped'><thead><tr>
	<th width='500'>Title</th>
	<th>When</th>
	<th>Where</th>
	<th>Cost</th>
	<th>Spots</th>
	<th>Action</th>
	</tr></thead><tbody>\n";
	
	$i = 0;
	foreach ($rows as $row) {
		$public = '';
		if ($data['admin'] && $row['when_public']) {
			$public = "<br><small>Public: ".date('D M j - g:ia', strtotime($row['when_public']))."</small>\n";
		}	
		$i++;
		echo "<tr class='{$rowclass[$row['type']]}'>";
		$titlelink = ($data['admin'] ? "<a href='$sc?wid={$row['id']}&v=ed'>{$row['title']}</a>" : "<a href='$sc?wid={$row['id']}&v=view'>{$row['title']}</a>");
		echo "<td>{$titlelink}".($row['notes'] ? "<p class='small text-muted'>{$row['notes']}</p>" : '')."</td>
		<td>{$row['when']}{$public}</td>
		<td>{$row['place']}</td>
		<td>{$row['cost']}</td>
		<td>".number_format($row['open'], 0)." of ".number_format($row['capacity'], 0).",<br> ".number_format($row['waiting']+$row['invited'])." waiting</td>
";
		if ($data['admin']) {
			echo "<td><a href=\"$sc?wid={$row['id']}\">Clone</a></td></tr>\n";
		} else {
			$call = ($row['type'] == 'soldout' ? 'Join Wait List' : 'Enroll');
			echo "<td><a href=\"{$sc}?wid={$row['id']}&ac=enroll\">{$call}</a></td></tr>\n";
		}
	}
	if (!$i) {
		echo "<p>No upcoming workshops!</p>\n";
	}
	echo "</tbody></table>\n";
		
	
	
	
?>