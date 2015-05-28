<?php
// must have u, wk and r set
// also faq and dropping_late_warning

$url = URL."index.php?key={$u->key->keycode}&wid={$wk->getCol('id')}";
$late = '';
		
switch ($r->getCol('status_id')) {
	case ENROLLED:
		$point = "You are ENROLLED in the below workshop.";
		$call = "To DROP, click here:\n{$url}&ac=drop";
		break;
	case WAITING:
		$point = "You are WAITING (spot {$r->getCol('rank')}) for the below workshop.";
		$call = "To DROP, click here:\n{$url}&ac=drop";
		break;
	case INVITED:
		$point = "You are INVITED for the below workshop.";
		$call = "To ACCEPT, click here:\n{$url}&ac=accept\n\nTo DECLINE, click here:\n{$url}&ac=decline";
		break;
	case DROPPED:
		$point = "You have DROPPED from the below workshop";
		$call = "If you change your mind, re-enroll here:\n{$url}&ac=enroll";
		break;
}

// late warning should be set before calling this page		
if ($r->getCol('while_soldout') != 1) {
	$late_warning = '';
}		
echo "You are: {$u->getCol('email')}

$point 

Title: {$wk->getCol('title')}
When: {$wk->getCol('when')}
Where: {$wk->getCol('place')} {$wk->getCol('lwhere')}
Cost: {$wk->getCol('cost')}
Description: {$wk->getCol('notes')}

$call

$late_warning

To just see a list of practices you've taken, click here:
{$url}

Thanks!
-Will

{$faq}";

?>