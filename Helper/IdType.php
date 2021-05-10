<?php

namespace Grability\Mobu\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class IdType extends AbstractHelper
{
	CONST ATTR_CODE = 'id_type';

	CONST DEFAULT_STORE_ID = 0;

	CONST ATTRIBUTES = [
		"id_type" => [
			"eav_attribute" => [
				"entity_type_id" => 1,
				"attribute_code" => "id_type",
				"backend_type" => "int",
				"frontend_input" => "select",
				"frontend_label" => "ID Type",
				"source_model" => "Magento\\\Eav\\\Model\\\Entity\\\Attribute\\\Source\\\Table",
				"is_required" => 0,
				"is_user_defined" => 1,
				"is_unique" => 0
			],
			"customer_eav_attribute" => [
				"attribute_id" => "",
				"is_visible" => 1,
				"multiline_count" => 1,
				"is_system" => 0,
				"sort_order" => 1,
				"is_used_in_grid" => 0,
				"is_visible_in_grid" => 0,
				"is_filterable_in_grid" => 0,
				"is_searchable_in_grid" => 0
			],
			"customer_eav_attribute_website" => [
				"attribute_id" => "",
				"website_id" => 1
			],
			"customer_form_attribute" => [
				[
					"form_code" => "adminhtml_customer",
					"attribute_id" => ""
				], 
				[
					"form_code" => "customer_account_create",
					"attribute_id" => ""
				], 
				[
					"form_code" => "customer_account_edit",
					"attribute_id" => ""
				]
			],
			"eav_entity_attribute" => [
				"entity_type_id" => 1,
				"attribute_set_id" => 1,
				"attribute_group_id" => 1,
				"attribute_id" => "",
				"sort_order" => 888
			]  
		],
		"id_number" => [
			"eav_attribute" => [
				"entity_type_id" => 1,
				"attribute_code" => "id_number",
				"backend_type" => "varchar",
				"frontend_input" => "text",
				"frontend_label" => "ID Number",
				"is_required" => 0,
				"is_user_defined" => 1,
				"is_unique" => 0
			],
			"customer_eav_attribute" => [
				"attribute_id" => "",
				"is_visible" => 1,
				"multiline_count" => 1,
				"is_system" => 0,
				"sort_order" => 2,
				"is_used_in_grid" => 0,
				"is_visible_in_grid" => 0,
				"is_filterable_in_grid" => 0,
				"is_searchable_in_grid" => 0
			],
			"customer_eav_attribute_website" => [
				"attribute_id" => "",
				"website_id" => 1
			],
			"customer_form_attribute" => [
				[
					"form_code" => "adminhtml_customer",
					"attribute_id" => ""
				], 
				[
					"form_code" => "customer_account_create",
					"attribute_id" => ""
				], 
				[
					"form_code" => "customer_account_edit",
					"attribute_id" => ""
				]
			],
			"eav_entity_attribute" => [
				"entity_type_id" => 1,
				"attribute_set_id" => 1,
				"attribute_group_id" => 1,
				"attribute_id" => "",
				"sort_order" => 999
			] 
		],
	];

	private $resourceConnection;

	private $connection;

	private $httpContext;

    public function __construct(
        Context $context,
        HttpContext $httpContext,
        ResourceConnection $resourceConnection
    ) 
    {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
    }

	public function syncIdTypeMobu(array $data, $current_store)
	{
		try {
			if(!$this->validateInputs())
				$this->createInputs();

			$attribute_id = $this->getAttributeId();

			$data = $this->resolveData($attribute_id, $data);

			foreach ($data["data_to_delete"] as $key => $value) {
				$this->deleteOptionsValue($value);
				$this->deleteOption($value);
			}

			foreach ($data["data_to_update"] as $key => $value) {
				$this->updateOptionValue($value, $current_store);
			}

			foreach ($data["data_to_create"] as $key => $value) {
				$option_id = $this->addOption($attribute_id);
				$this->addOptionFromStore($option_id, $current_store, $value);
			}
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

		return true;
	}

	public function resolveData(int $attribute_id, array $data)
	{
    	$options = $this->getOptions($attribute_id);

    	$option_values = [];

    	foreach ($options as $key => $option) {
	    	$option_values[] = $this->getOptionValuesId($option["option_id"]);
    	}

		$data_resolved = [
			'data_to_delete' => $this->resolveDataToDelete($attribute_id, $data, $option_values),
			'data_to_update' => $this->resolveDataToUpdate($attribute_id, $data, $option_values),
			'data_to_create' => $this->resolveDataToCreate($attribute_id, $data, $option_values)
		];

		return $data_resolved;
	}


	public function resolveDataToDelete(int $attribute_id, array $data, array $option_values)
	{
    	$data_to_delete = [];

    	$count = 0;

    	foreach ($option_values as $key => $value) {
    		foreach ($data as $_key => $idType) {
    			if(isset($value[0])){
	    			if($value[0]["value"] == $idType->code){
	    				$count = $count + 1;
					}
    			}
    		}

    		if($count == 0){
    			if(isset($value[0])){
    				$data_to_delete[] = $value[0]["option_id"];
    			}
    		}

    		$count = 0;
    	}

    	return $data_to_delete;
	}

	public function resolveDataToUpdate(int $attribute_id, array $data, array $option_values)
	{
    	$data_to_update = [];

    	$i = 0;

    	foreach ($option_values as $key => $value) {
    		foreach ($data as $_key => $idType) {
    			if(isset($value[0])){
	    			if($value[0]["value"] == $idType->code){
						$data_to_update[$i]["option_id"] = $value[0]["option_id"];
						$data_to_update[$i]["label"] = $idType->description;
						$data_to_update[$i]["code"] = $idType->code;
						$i = $i + 1;
	    			}
    			}
    		}
    	}

    	return $data_to_update;
	}

	public function resolveDataToCreate(int $attribute_id, array $data, array $option_values)
	{
    	$count = 0;

    	$data_to_create = [];

    	foreach ($data as $key => $idType) {
    		foreach ($option_values as $_key => $value) {
    			if(isset($value[0])){
	    			if($value[0]["value"] == $idType->code)
						$count = $count + 1;
    			}
    		}

    		if($count == 0){
    			$data_to_create[] = $idType;
    		}

    		$count = 0;
    	}

    	return $data_to_create;
	}

	public function deleteOptionsValue(int $option_id)
	{
		try {
			/*$this->resourceConnection = \Magento\Framework\App\ObjectManager::getInstance()
												->get('Magento\Framework\App\ResourceConnection');

			$connection= $this->resourceConnection->getConnection();*/

			$table = $this->connection->getTableName('eav_attribute_option_value');

			$sql = "DELETE FROM " . $table . " WHERE option_id = " . $option_id;

			$this->connection->query($sql);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return false;
		}

		return true;
	}

	public function deleteOption(int $option_id)
	{
		try {
			
			$table = $this->connection->getTableName('eav_attribute_option');

			$sql = "DELETE FROM " . $table . " WHERE option_id = " . $option_id;

			$this->connection->query($sql);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return false;
		}

		return true;
	}	

	public function updateOptionValue(array $data, int $store)
	{
		try {

			//consulta de option value por tienda
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute_option_value');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE option_id = ". $data["option_id"] . " AND store_id = " . $store;

	        $result = $this->connection->fetchAll($query);


			if(!empty($result)){
				//actualiza
				$table = $this->connection->getTableName('eav_attribute_option_value');

				$sql = "UPDATE " . $table . " SET value = '". $data["label"] . "' WHERE option_id = " . $data["option_id"] . " AND store_id = " . $store;

				$this->connection->query($sql);				
			}
			else{
				//crea
				$value["description"] = $data["label"];
				$value["code"] = $data["code"];
				$value = (object)$value;
				
				$table = $this->connection->getTableName('eav_attribute_option_value');

				$sql = "INSERT INTO " . $table . "(option_id, store_id, value) VALUES ('" . $data["option_id"] . "', '" . $store . "', '" . $value->description . "')";
					$this->connection->query($sql);
			}


		}catch(\Exception $e){var_dump($e->getMessage());die();
			return false;
		}

		return true;
	}

	public function getAttributeId()
	{
		try{
			
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE attribute_code = '". self::ATTR_CODE . "'";

	        $result = $this->connection->fetchAll($query);			

		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

        return (int)$result[0]["attribute_id"];
	}



	public function getAttributeIdByAttributeCode($attribute_code)
	{
		try{
			
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE attribute_code = '". $attribute_code . "'";

	        $result = $this->connection->fetchAll($query);			

		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

        return (int)$result[0]["attribute_id"];
	}


	public function getOptions(int $attribute_id)
	{
		try{
			
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute_option');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE attribute_id = ". $attribute_id;

	        $result = $this->connection->fetchAll($query);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

        return $result;
	}

	public function getLastOptionId(int $attribute_id)
	{
		$options = $this->getOptions($attribute_id);

        return (int)end($options)["option_id"];
	}

	public function getOptionValues($option_id)
	{
		try{
			
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute_option_value');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE option_id = ". $option_id;

	        $result = $this->connection->fetchAll($query);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return false;
		}

        return $result;	
	}


	public function getOptionValuesId($option_id)
	{
		try{
			
	        // $table is table name
	        $table = $this->connection->getTableName('eav_attribute_option_value');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE option_id = ". $option_id . " AND store_id = ". self::DEFAULT_STORE_ID;

	        $result = $this->connection->fetchAll($query);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

        return $result;	
	}

	public function addOption(int $attribute_id)
	{
		try{

			$current_options = $this->getOptions($attribute_id);

			$sort_order = count($current_options) + 1;

			$table = $this->connection->getTableName('eav_attribute_option');

			$sql = "INSERT INTO " . $table . "(attribute_id, sort_order) VALUES ('" . $attribute_id . "', '" . $sort_order . "')";
			$this->connection->query($sql);
		}catch(\Exception $e){var_dump($e->getMessage());die();
			return $e->getMessage();
		}

		return $this->getLastOptionId($attribute_id);
	}

	public function addOptionFromStore(int $option_id, int $store_id, $value)
	{
		try {

			$table = $this->connection->getTableName('eav_attribute_option_value');

			$sql = "INSERT INTO " . $table . "(option_id, store_id, value) VALUES ('" . $option_id . "', '" . self::DEFAULT_STORE_ID . "', '" . $value->code . "')";
				$this->connection->query($sql);

			$sql = "INSERT INTO " . $table . "(option_id, store_id, value) VALUES ('" . $option_id . "', '" . $store_id . "', '" . $value->description . "')";
				$this->connection->query($sql);

		} catch (\Exception $e){var_dump($e->getMessage());die();
            return false;
        }

        return true;
	}

	public function validateInputs()
	{
		try{

			// $table is table name
	        $table = $this->connection->getTableName('eav_attribute');
	        //For Select query
	        $query = "Select * FROM " . $table. " WHERE attribute_code = '". self::ATTR_CODE . "'";

	        $result = $this->connection->fetchAll($query);

			if(empty($result))
				return false;

		}catch(\Exception $e){var_dump($e->getMessage());die();

		}

		return true;
	}

	public function createInputs()
	{
		try{

			$this->addCustomerAtribute("id_type");
			$this->addCustomerAtribute("id_number");

		}catch(\Exception $e){var_dump($e->getMessage());die();

		}

	}

	public function addCustomerAtribute(string $attribute_code)
	{
		try{

			//eav_attribute
			$columns = "";
			$values = "";
			$query_array = self::ATTRIBUTES[$attribute_code]["eav_attribute"];
			$table = $this->connection->getTableName('eav_attribute');
			$query_zise = count($query_array);
			$count = 0;

			foreach ($query_array as $key => $value) {
				$count = $count + 1; 
				if ($count === $query_zise){
					$columns = $columns . $key;
					$values = $values . "'" . $value . "'";
				}else{
					$columns = $columns . $key . ", ";
					$values = $values . "'" . $value . "', ";
				}
			} 															

			$sql = "INSERT INTO " . $table . "(".$columns.") VALUES (". $values .")";
			$this->connection->query($sql);

			$attribute_id = $this->getAttributeIdByAttributeCode($attribute_code);






			//customer_eav_attribute
			$columns = "";
			$values = "";
			$query_array = self::ATTRIBUTES[$attribute_code]["customer_eav_attribute"];
			$table = $this->connection->getTableName('customer_eav_attribute');
			$query_zise = count($query_array);
			$count = 0;

			foreach ($query_array as $key => $value) {
				$count = $count + 1; 
				if ($count === $query_zise){
					$columns = $columns . $key;
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "'";
				}else{
					$columns = $columns . $key . ", ";
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "', ";
				}
			} 
															

			$sql = "INSERT INTO " . $table . "(".$columns.") VALUES (". $values .")";
			#var_dump($sql);die();
			$this->connection->query($sql);






			//customer_eav_attribute_website
			$columns = "";
			$values = "";
			$query_array = self::ATTRIBUTES[$attribute_code]["customer_eav_attribute_website"];
			$table = $this->connection->getTableName('customer_eav_attribute_website');
			$query_zise = count($query_array);
			$count = 0;

			foreach ($query_array as $key => $value) {
				$count = $count + 1; 
				if ($count === $query_zise){
					$columns = $columns . $key;
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "'";
				}else{
					$columns = $columns . $key . ", ";
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "', ";
				}
			} 
															

			$sql = "INSERT INTO " . $table . "(".$columns.") VALUES (". $values .")";
			#var_dump($sql);die();
			$this->connection->query($sql);



			//customer_form_attribute
			$columns = "";
			$values = "";
			$queries = self::ATTRIBUTES[$attribute_code]["customer_form_attribute"];
			$table = $this->connection->getTableName('customer_form_attribute');

			foreach ($queries as $_key => $query_array) {

				$query_zise = count($query_array);
				$count = 0;

				foreach ($query_array as $key => $value) {
					$count = $count + 1; 
					if ($count === $query_zise){
						$columns = $columns . $key;
						if($key == "attribute_id")
							$value = $attribute_id;
						$values = $values . "'" . $value . "'";
					}else{
						$columns = $columns . $key . ", ";
						if($key == "attribute_id")
							$value = $attribute_id;
						$values = $values . "'" . $value . "', ";
					}
				}


				$sql = "INSERT INTO " . $table . "(".$columns.") VALUES (". $values .")";
				//var_dump($sql);die();
				$this->connection->query($sql);

				$columns = "";
				$values = "";
			 } 
															
			

			//eav_entity_attribute
			$columns = "";
			$values = "";
			$query_array = self::ATTRIBUTES[$attribute_code]["eav_entity_attribute"];
			$table = $this->connection->getTableName('eav_entity_attribute');
			$query_zise = count($query_array);
			$count = 0;

			foreach ($query_array as $key => $value) {
				$count = $count + 1; 
				if ($count === $query_zise){
					$columns = $columns . $key;
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "'";
				}else{
					$columns = $columns . $key . ", ";
					if($key == "attribute_id")
						$value = $attribute_id;
					$values = $values . "'" . $value . "', ";
				}
			} 
															

			$sql = "INSERT INTO " . $table . "(".$columns.") VALUES (". $values .")";
			#var_dump($sql);die();
			$this->connection->query($sql);





		}catch(\Exception $e){var_dump($e->getMessage());die();

		}

	}


    public function isLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

}
