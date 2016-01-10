<?php
	
// expects $students, a three-deep array
// first level: status_id
// second level: user_id
// third level: array of values from db row for that user_id	
	
$wid = $wk->getCol('id');
$statuses = Statuses::$statuses;

echo "<h2>{$wk->getCol('showtitle')}</h2>
			<div class='row'>\n";

// enrollment and change log column

echo "<div class='col-md-7'><h2>Enrollment Info <small><br><a class='btn btn-default' href='$sc?v=em&wid={$wid}'>see emails</a> <a class='btn btn-default'  href='$sc?v=at&wid={$wid}'>attendance</a> <a class='btn btn-default'  href='$sc?ac=cw&wid={$wid}'>check waiting</a></small></h2>\n";
		
		//show enrollment totals at top
		$stats = array();
		foreach ($students as $stid => $students_by_status) {
			$stats[$stid] = count($students_by_status);
		}
		echo "<p>totals: (".implode(" / ", array_values($stats)).")<p>\n";
		
		// list students for each status
		foreach ($students as $stid => $students_by_status) {
			echo "<h4>{$statuses[$stid]} (".$stats[$stid].")</h4>\n";
			foreach ($students_by_status as $uid => $urow) { // 's' for student data
				echo "<div class='row'><div class='col-md-6'><a href='{$sc}?v=astd&uid={$uid}&wid={$wid}'>{$urow['email']}</a> <small>".date('M j g:ia', strtotime($urow['last_modified']))."</small></div>
				<div class='col-md-6'>
				<a class='btn btn-primary' href='$sc?v=cs&wid={$wid}&uid={$uid}'>change status</a> <a class='btn btn-danger' href='$sc?v=rem&uid={$uid}&wid={$wid}'>remove</a></div>".
				"</div>\n";				
			}
		}
		
		echo "<h2>Change Log</h2>\n";
		
		if (count($changes) > 0) {
			echo "<table class='table table-striped'><tbody>\n";
			foreach ($changes as $change) {
				echo "<tr><td>{$change['email']}</td><td>{$change['status_name']}</td><td><small>".date('j-M-y g:ia', strtotime($change['happened']))."</small></td></tr>\n";
			}
			echo "</tbody></table>\n";
		} else {
			echo "<p>No changes to report!</p>\n";
		}
		
		echo "</div>"; // end of first column
		
		// start of second column (workshop fields)
		echo "<div class='col-md-5'>
		<h2>Practice Info</h2>".
		$wk->get_workshop_form()->get_form().
		"<a href=\"{$sc}?wid={$wid}&ac=cdel\">Delete This Practice</a>\n\n";
		
		echo "<h2>Add Student</h2>\n";
		echo $wk->add_student_form()->get_form();
		echo "</div>\n"; // end of second column
				
		echo "</div>\n"; //end of row