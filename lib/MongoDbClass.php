<?php
/*
*  @author Rajneesh Singh <rajneesh.hlm@gmail.com>
*/

class MongoDbClass{	
	protected $collection = null;

	/**
    * @param string $collection required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return collection or error
    */
	public function __construct($collection){
		// connect to a remote host at a given port
		$client = new MongoDB\Client('mongodb://'.MONGO_SERVER.':'.MONGO_PORT);
		
		if(!$client){
			die("Mongo Error");
		}else{
			$db = DATABASE;
			$this->collection = $client->$db->$collection;
			return $this->collection;
		}
	}

	/**
    * @param array $document required
    * @param boolean $insertOne optional
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return inserted Id or error
    */
	public function insert($document, $insertOne = false){
		if(empty($document) && !is_array($document))
			return 'Invalid document';

		if($insertOne == true){
			$result = $this->collection->insertOne($document);
			$id = $result->getInsertedId();
		}else{
			$result = $this->collection->insertMany($document);
			$id = $result->getInsertedIds();
		}
		return $id;
	}

	/**
    * @param array $document required
    * @param array $condition required
    * @param boolean $updateOne optional
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return inserted Id or error
    */
	public function update($condition, $document, $updateOne = false){
		if(empty($condition) && !is_array($condition)){
			return 'Invalid condition';
		}else if(empty($document) && !is_array($document)){
			return 'Invalid document';
		}

		$updateResult = '';
		$document = array('$set'=> $document);
		if($updateOne == true){
			$updateResult = $this->collection->updateOne($condition, $document);
		}else{
			$updateResult = $this->collection->updateMany($condition, $document);
		}
		$modifiedCount = $updateResult->getModifiedCount();
		return $modifiedCount;
	}

	/**
    * @param array $condition required
    * @param boolean $deleteOne optional
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return deleted Id or error
    */
	public function delete($condition, $deleteOne = false){
		if(empty($condition) && !is_array($condition))
			return 'Invalid condition';

		$deleteResult = '';
		if($deleteOne == true){
			$deleteResult = $this->collection->deleteOne($condition);
		}else{
			$deleteResult = $this->collection->deleteMany($condition);
		}

		return $deleteResult;
	}	

	/**
    * @param array $id required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return deleted Id or error
    */
	public function deleteById($id){
		if(empty($id))
			return 'Invalid id';

		$condition = [
			'_id' => new MongoDB\BSON\ObjectID($id)
		];
		$deleteResult = $this->collection->deleteOne($condition);

		return $deleteResult;
	}

	/**
    * @param array $condition required
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return array
    */
	public function getRow($condition){
		if(empty($condition) && !is_array($condition))
			return 'Invalid condition';

		$result = $this->collection->findOne($condition);
		return $result;
	}

	/**
    * @param array $condition optional
    * @author Rajneesh Singh <rajneesh.singh@girnarsoft.com>
    * @return array
    */
	function getAll($condition = []){
		if($condition && !is_array($condition))
			return 'Invalid condition';

		$cursor = $this->collection->find($condition);
		$results = iterator_to_array($cursor);
		return $results;
	}
}
?>