<?php

echo "<div class='row'><div class='col-md-8'><div id='login_prompt' class='well'>\n";
if ($u->logged_in()) {
	echo  "<h2>Welcome</h2>\n";
	echo  "<p>You are logged in as {$u->cols['email']}! (You can <a href='$sc?v=email'>change your email</a> or <a href='$sc?ac=lo'>log out</a>)</p>";			
	echo  "<p>".($u->cols['send_text'] ? "You have signed up for text notfications. " : "Would you like text notifications?")." <a  class='btn btn-primary' href='$sc?v=text'>Set your text preferences</a>.</p>\n";

} else {
	echo  "<h2>Log In</h2>\n";
	echo  "<p>You are not logged in. To log in, you don't need a password or a Facebook account but you do need an email account.</p>";
	echo  $u->get_login_form()->get_form('inline');
}
	echo  "</div></div></div>\n"; // end of log in prompt div, and its column and row

	echo  "<div class='row'><div class='col-md-12'>\n";
	echo  "<h2>All Upcoming Workshops</h2>\n"; 
include "{$data['path']}workshop_list.php"; 	
	echo  "</div></div> <!-- end of col and row -->\n";
		
	echo  "<div class='row'><div class='col-md-12'>";
	echo  "<h2>Your Current/Past Workshops</h2>";
if ($u->logged_in()) {
include "{$data['path']}transcript.php"; 	
} else {
	echo  "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
}
	echo  "<h2>Questions</h2>\n";
	echo  "<p>Paying? Lateness? Levels? See <a href='$sc?v=faq'>questions</a>.</p>\n";		
	echo  "</div></div> <!-- end of col and row -->\n";	
		
include "{$data['path']}mailchimp.php";
			
echo "<br><br>\n";
