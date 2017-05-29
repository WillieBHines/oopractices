<?
namespace Classes\Models;
/*
* most classes get one instance per row
* this class gets the whole table since it's just a lookup table
* stores the data in a static property (essentially global)
*/
class Carriers extends Model {
	
	protected $table_name = 'carriers';
	public static $carriers;
	
	function __construct() {
		parent::__construct(); // sets DB object
		$this->get_carriers();
	}
	
	public function get_carriers() {
		if (!Carriers::$carriers) {
			Carriers::$carriers = $this->get_everything();
		}
		return Carriers::$carriers;
	}
	
	public function get_carriers_dropdown() {
		$c = $this->get_carriers();
		$drop = array();
		foreach ($c as $id => $row) {
			$drop[$id] = $row['network'];
		}
		return $drop;
	}

	public function shorten_link($link) {
	
		// bit.ly registered token is: 70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94
		// under whines@gmail.com / meet1962
	
		//tempoary while working locally
		$link = preg_replace('/localhost:8888/', 'www.willhines.net', $link);
		$link = urlencode($link);
		$response = file_get_contents("https://api-ssl.bitly.com/v3/shorten?access_token=70cc52665d5f7df5eaeb2dcee5f1cdba14f5ec94&longUrl={$link}&format=txt");
		return $response;
	}
					
}	
?>