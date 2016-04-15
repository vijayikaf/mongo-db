<?php
/*
*  @author Rajneesh Singh <rajneesh.hlm@gmail.com>
*/

class MongoDbClass{
	/*port on which MongoDB instance should be run. 27017 is the default port*/
	protected $port = MONGO_PORT;
	/*web server on which MongoDB run*/
	protected $server = MONGO_SERVER;
	protected $database = DATABASE;
	protected $connection = null;
	protected $collection = null;

	/**
    * @param string $collection required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return collection or error
    */
	public function __construct($collection){
		// connect to a remote host at a given port
		$this->connection = new MongoClient('mongodb://'.$this->server.':'.$this->port);
		if(!$this->connection){
			return "Mongo Error";

		}else{
			//select DB
			$db = $this->connection->selectDB($this->database);
			//select collection
			$this->collection = $db->selectCollection($collection);
			return $this->collection;
		}
	}

	/**
    * @param array $document required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return inserted Id or error
    */
	public function insert($document){
		if(empty($document))
			return 'Invalid document';

		$collection = $this->collection;
		$id = $collection->insert($document);		
		return $id;
	}

	/**
    * @param array $document required
    * @param array $condition required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return inserted Id or error
    */
	public function update($condition, $document){
		if(empty($document) || empty($condition))
			return 'Invalid document or condition';

		$collection = $this->collection;
		$document = array('$set'=> $document);
		$id = $collection->update($condition, $document);
		return $id;
	}

	/**
    * @param array $condition required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return deleted Id or error
    */
	public function delete($condition){
		if(empty($condition))
			return 'Invalid condition';

		$collection = $this->collection;
		$data = $this->getRow($condition);
		if($data){
			$collection->remove($condition);
			#$collection->remove(array("_id"=> "56e2b20cc407bfbc428b4567"));
		}
	}	

	/**
    * @param array $condition required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return array
    */
	public function getRow($condition = array()){
		$collection = $this->collection;		
		$result = $collection->findOne($condition);
		return $result;
	}

	/**
    * @param array $condition required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return array
    */
	function getAll($condition = array()){
		$collection = $this->collection;
		$cursor = $collection->find($condition);
		$results = iterator_to_array($cursor);
		return $results;
	}
}
?>