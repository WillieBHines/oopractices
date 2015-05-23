<?php
/*
* these routines are sort of logically part of 
* registration management and user management
* but it felt neater to put them together since they
* deal with email and texting
*/
class Messages extends WBHObject {

	public $workshop;
	public $user;
	public $registration;
	public $carriers;
	public $statuses;


	function __construct() {
		
		// get global carriers array and put it locally for convenience
		$c = new Carriers;		
		$this->carriers = $c->get_carriers();
		
		$s = new Statuses;
		$this->statuses = $s->get_statuses();
	}
	
	function confirm_email($wk, $u, $status_id = ENROLLED) {
		if (!isset($u['key']) || !$u['key']) {
			$key = wbh_get_key($u['id']);
		} else {
			$key = $u['key'];
		}
		$e = wbh_get_an_enrollment($wk, $u); 
		$drop = URL."index.php?key=$key&ac=drop&wid={$wk['id']}";
		$trans = URL."index.php?key=$key&wid={$wk['id']}";
		$accept = URL."index.php?ac=accept&wid={$wk['id']}&key=$key";
		$decline = URL."index.php?ac=decline&wid={$wk['id']}&key=$key";
		$enroll = URL."index.php?key=$key&ac=enroll&wid={$wk['id']}";
		$textpref = URL."index.php?key=$key&v=text";
		$call = '';
		$late = '';
		
		if ($e['while_soldout']) { 
			$message .= '<br><br>'.wbh_get_dropping_late_warning();
		}
	
	
		$send_faq = false;
		switch ($status_id) {
			case 'already':
			case ENROLLED:
				$sub = "ENROLLED: {$wk['showtitle']}";
				$point = "You are ENROLLED in {$wk['showtitle']}.";
				$call = "To DROP, click here:\n{$drop}";
				$send_faq = true;
				break;
			case WAITING:
				$sub = "WAIT LIST: {$wk['showtitle']}";
				$point = "You are wait list spot {$e['rank']} for {$wk['showtitle']}:";
				$call = "To DROP, click here:\n{$drop}";
				break;
			case INVITED:
				$sub = "INVITED: {$wk['showtitle']}";
				$point = "A spot opened in {$wk['showtitle']}:";
				$call = "To ACCEPT, click here:\n{$accept}\n\nTo DECLINE, click here:\n{$decline}";
				break;
			case DROPPED:
				$sub = "DROPPED: {$wk['showtitle']}";
				$point = "You have dropped out of {$wk['showtitle']}";
				if ($e['while_soldout'] == 1) {
					$late .= "\n".wbh_get_dropping_late_warning();
				}
				$call = "If you change your mind, re-enroll here:\n{$enroll}";
				break;
			default:
				$sub = "{$statuses[$status_id]}: {$wk['showtitle']}";
				$point = "You are a status of '{$statuses[$status_id]}' for {$wk['showtitle']}";
				break;
		}

		$text = '';
		if ($u['send_text']) {
			$textmsg = $point.' for more info: '.wbh_shorten_link($trans);
			wbh_send_text($u, $textmsg);
		}
	
		$notifcations = '';
		if (!$u['send_text']) {
			$notifications = "\nWould you want to be notified via text? You can set text preferences:\n".$textpref;
		}

		$body = "You are: {$u['email']}

	$point $late
	$notifications

	Title: {$wk['title']}
	When: {$wk['when']}
	Where: {$wk['place']} {$wk['lwhere']}
	Cost: {$wk['cost']}

	$call

	To see all practices you've taken, click here:
	{$trans}

	".wbh_email_footer($send_faq);	
	
		return mail($u['email'], $sub, $body, "From: ".WEBMASTER);
	}


	function wbh_send_text($u, $msg) {
		if (!$u['send_text'] || !$u['carrier_id'] || !$u['phone'] || strlen($u['phone']) != 10) {
			return false;
		}
		$carriers = wbh_get_carriers();
		$to = $u['phone'].'@'.$carriers[$u['carrier_id']]['email'];	
		$mailed=  mail($to, '', $msg, "From: ".WEBMASTER);
		return $mailed;
	}


	function wbh_shorten_link($link) {
	
		// bit.ly registered token is: 70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94
		// under whines@gmail.com / meet1962
	
		//tempoary while working locally
		$link = preg_replace('/localhost:8888/', 'www.willhines.net', $link);
		$link = urlencode($link);
		$response = file_get_contents("https://api-ssl.bitly.com/v3/shorten?access_token=70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94&longUrl={$link}&format=txt");
		return $response;
	
	}

	function wbh_get_dropping_late_warning() {
		global $late_hours;
		return "NOTE: You are dropping within {$late_hours} hours of the start, and there was a waiting list. If it's too short notice for someone to take your spot, I might ask you to pay anyway.";
	
	}

	function wbh_email_footer($faq = false) {

		$faqadd = '';
		if ($faq) {
			$faqadd = strip_tags(wbh_get_faq());
		}
		return "
	$faqadd
		
	Thanks!
		
	-Will Hines
	HQ: 1948 Hillhurst Ave. Los Angeles, CA 90027
	";
	}

	function wbh_get_faq() {
	
		return "<h2>Questions</h2>
		<dl>
		<dt>Can I drop out?</dt>
		<dd>Yes, use the link in your confirmation email to go to the web site, where you can drop out.</dd>

		<dt>If there is a cost, how should I pay?</dt>
		<dd>In cash, at the practice. Or Venmo it to me - whines ATSIGN gmail.com</dd>

		<dt>What if I'm on a waiting list?</dt>
		<dd>You'll get an email if a spot opens up.</dd>

		<dt>What's the late policy? Or the policy on leaving early?</dt>
		<dd>Arriving late or leaving early is fine. If you're late I might ask you to wait to join in until I say so.</dd>

		<dt>What levels?</dt>
		<dd>Anyone can sign up. The description may recommend a level but I won't enforce it.</dd>
		</dl>";
	}
}	
?>