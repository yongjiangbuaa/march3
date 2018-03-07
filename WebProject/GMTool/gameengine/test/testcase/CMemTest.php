<?php
require dirname(__FILE__).'/init.php';

class PersonP extends XActiveRecord {
	/** 
	 * @Id
	 */
	public $id;
	/**
	 */
	public $name;
	/**
	**
	*/
	public $address ;
	
	/**
	* @Index
	 */
	protected $age;
	
	function __construct($id, $name, $age)
	{
		parent::__construct();
		$this->id = $id;
		$this->name = $name;
		$this->age = $age ;
		$this->address = array(new Address($id, "new york", "US"), new Address($id."append", "new york", "US"));
	}
 	
	
}

Class Nation
{
	public $nation = "china";
	
	function __construct()
	{
	}
}
Class Address  extends XActiveRecord 
{
	/** 
	 * @Id
	 **/
	public $street;
	public $province;
	public $nation;
	
	function __construct($street, $province, $nation)
	{
		parent::__construct();
		$this->street = $street;
		$this->province = $province;
		$this->nation = new Nation();
	}
}

$pm = PersistenceSession::singleton();

$persons = array();
for($i = 0; $i < 100; $i++){
	$person = new PersonP($i, "jack@xingcloud.com",  $i);
	array_push($persons, $person);
}
$pm->put($persons);
$pm->flush();

//print_r($pm->get("PersonP","2"));


$query = new Query("PersonP");
$query->order("age")->filter("age<=",  3);
$queryResult = $pm->query($query);
print_r($queryResult);
?>
