<?php
/*
* view a workshop info
*/

$wid = $wk->getCol('id');
$uid = $u->getCol('id');
$key = $u->key->keycode;


// set the action ($point) for the user pending enrollment, if workshop is past, if user is logged in...
if ($wk->getCol('type') == 'past') {
	$point = "This workshop is IN THE PAST.";
} elseif ($r->getCol('status_id')) {
	switch ($r->getCol('status_id')) {
		case ENROLLED:
			$point = "You are ENROLLED in this practice. Would you like to <a class='btn btn-default' href='$sc?ac=drop&wid={$wid}&key={$key}'>drop</a> it?";
			break;
		case WAITING:
			$point = "You are spot number {$r->getCol('rank')} on the WAIT LIST for this practice. Would you like to <a class='btn btn-default' href='$sc?ac=drop&wid={$wid}&key={$key}'>drop</a> it?";
			break;
		case INVITED:
			$point = "A spot opened up in this practice. Would you like to <a class='btn btn-default' href='$sc?ac=accept&wid={$wid}&key={$key}'>accept</a> it, or <a class='btn btn-default' href='$sc?ac=decline&wid={$wid}&key={$key}'>decline</a> it?";
			break;
		case DROPPED:
			$point = "You have dropped out of this practice. Would you like to <a class='btn btn-default'  href='$sc?ac=enroll&wid={$wid}&key={$key}'>re-enroll</a>?";
			break;
		default:
			$point = "You are a status id of '{$r->cols['status_id']}' (I don't know what that is) for this practice:";
			break;
	}
} elseif ($u->logged_in()) {
	if ($wk->cols['type'] == 'soldout') {
		$point = "This workshop is sold out. Would you like to <a class='btn btn-default'  href='$sc?ac=enroll&wid={$wid}'>join the wait list</a>?";
	} else {
		$point = "This workshop has open spots. Would you like to <a class='btn btn-default'  href='$sc?ac=enroll&wid={$wid}'>enroll</a> in it?";
	}
} else {
	$point = "You are not logged in! Return to the <a href='$sc'>front page</a> and log in if you want to join this class.";
}


// the html
echo "
<table class=\"table table-striped\">
<tbody>
<tr><td>Title:</td><td>{$wk->cols['title']}</tr>
<tr><td>When:</td><td>{$wk->cols['when']}</tr>
<tr><td>Where:</td><td>{$wk->cols['place']} {$wk->cols['lwhere']}</tr>
<tr><td>Cost:</td><td>{$wk->cols['cost']}</td></tr>
<tr><td>Open Spots:</td><td>{$wk->cols['open']} (of {$wk->cols['capacity']})</td></tr>
<tr><td>Waiting:</td><td>".($wk->cols['waiting']+$wk->cols['invited'])."</td></tr>
</tbody>
</table>
<p class='alert alert-info'>$point</p>
<p>Return to <a href='$sc'>the main page</a>.</p>\n
";

