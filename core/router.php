<?php
// parse the URL, find controller, and go!



$params = parse_incoming_url();

// wrap this all up in Flow?
// was a controller named? does it exist?
// was a method named? does it exist in that controller?
// can we check a number of arguments in the method? eh, just call it. it has to be handle incorrect arguments

// default
$c = ($c ? : $c : 'Workshops');
$m = ($m ? : $m : 'index');

