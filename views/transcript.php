<?php
$rows = $data['transcript'];
if (count($rows) == 0) {
	echo "<p>You have not taken any practices! Which is fine, but that's why this list is empty.</p>\n";
} else {
	echo "<table class='table table-striped'><thead><tr><th>Title</th><th>When</th><th>Where</th><th>Status</th><th>Action</th></tr></thead>\n";
	echo "<tbody>";

	foreach ($rows as $t) {
		
		$wk = new Workshop();
		$wk->format_workshop_startend($t); // use transcript data so wk doesn't need a data call
		
		$r = new Registration();
		$t['id'] = $t['registration_id'];
		$r->set_rank($t); // use transcript data so r only needs one data call
		 
		echo "<tr class='".(strtotime($t['start']) < time() ? '' : 'success')."'><td>";
		if ($admin) {
			echo "<a href=\"admin.php?wid={$t['workshop_id']}&v=ed\">{$t['title']}</a>";
		} else {
			echo $t['title'];
		}
		echo "</td><td>{$wk->cols['when']}</td><td>{$t['place']}</td><td>";
		echo "{$t['status_name']}";
		if ($t['status_id'] == WAITING) {
			echo " (spot {$r->cols['rank']})";
		}
		echo "</td><td><a href='index.php?v=view&wid={$t['workshop_id']}'>More Info</a></td></tr>\n";
	}
	echo "</tbody></table>\n";
}



	
?>