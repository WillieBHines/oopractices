<?php
	
//make variables a bit easier
$sc = $data['sc'];
$wk = isset($data['workshop']) ? $data['workshop'] : null;
$u = isset($data['user']) ? $data['user'] : null;
$r = isset($data['registration']) ? $data['registration'] : null;
$key = isset($u->key->keycode) ? $u->key->keycode : null;
$uid = isset($u->cols['id']) ? $u->cols['id'] : null;
$wid = isset($wk->cols['id']) ? $wk->cols['id'] : null;
$admin = isset($data['admin']) ? $data['admin'] : null;
?>