<?php

if(!class_exists('DB')){
	class DB{
		public function __construct($host,$user,$pass,$db_name){
			$dsn = "mysql:host=$host;dbname=$db_name";
			$options = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			);
			$this->db = new PDO($dsn, $user, $pass, $options);

		}

		public function generic_query($query){
			$stmt = $this->db->query($query);

			while($row = $stmt->fetch()){
				$results[] = $row;
			}
			return $results;
		}

		public function get_results($query, $params = array()){

			if(empty($params)){
				return $this->generic_query($query);
			}
			if(!$stmt = $this->db->prepare($query)){
				return false;
			}
			$stmt->execute($params);

			while($row = $stmt->fetch()){
				$results[] = $row;
			}
			if(empty($results)){
				return false;
			}
			return $results;

		}

		public function fetch_row($table, $id){
			$stmt = $this->db->prepare("SELECT * FROM $table WHERE ID=:id");
			$stmt->execute(array('id'=>$id));
			$result = $stmt->fetch();

			return $result;
		}

		public function insert($table, $data = array()){

			if(empty($table) || empty($data)){
				return false;
			}

			$columns = '';
			$placeholders = '';

			//Looping through $data for column and placeholder names
			foreach($data as $key=>$value){
				$columns .= sprintf('%s,', $key);
				$placeholders .= sprintf(":%s,", $key);
			}

			//rtrim column and placeholder strings
			$columns_t = rtrim($columns,',');
			$placeholders_t = rtrim($placeholders,',');

			//prepare query
			$stmt = $this->db->prepare("INSERT INTO {$table} ({$columns_t}) 
				VALUES ({$placeholders_t})");
			$stmt->execute($data);

			//Checking if action is successful
			if($stmt->rowCount()){
				return true;
			}
			return false;

		}

		public function update($table, $data, $where_id){

			if(empty($table) || empty($data) || empty($where_id)){
				return false;
			}

			//Looping thru $data for column and placeholder names
			foreach($data as $key=>$value){
				$placeholders .= sprintf('%s=:%s,',$key,$key);
			}

			//Trimming placeholders string on the right
			$placeholders = rtrim(',', $placeholders);

			//Append ID to $data array
			$data['where_id'] = $where_id;

			//Preparing query
			$stmt = $this->db->prepare("UPDATE $table SET $placeholders WHERE ID=:where_id");
			$stmt->execute($data);

			//Checking if action is successful
			if($stmt->rowCount()){
				return true;
			}
			return false;

		}

		public function delete($table, $where_field = 'ID', $where_value) {
			// Prepary our query for binding
			$stmt = $this->db->prepare("DELETE FROM {$table} WHERE {$where_field} = :where_value");
			
			// Execute the query
			$stmt->execute(array('where_value'=>$where_value));
			
			// Check for successful insertion
			if ( $stmt->rowCount() ) {
				return true;
			}
			
			return false;
		}
	}
}

$db = new DB(DB_HOST, DB_USER, DB_PASS, DB_NAME);

?>