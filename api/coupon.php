<?php
	$table = "coupon";
		
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$id = $route->getParameter(2);
			$result = Select($id);
			http_response_code($result['code']);
			echo json_encode($result['value']);
			break;
		case 'POST':
			$data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
			
			$result = Insert($data);
			http_response_code($result['code']);
			echo json_encode($result['value']);
			break;
		case 'DELETE':
			$id = $route->getParameter(2);
			if($id != '') {
				$result = Delete($id);
				http_response_code($result['code']);
				echo json_encode($result['value']);
			}
			else {
				http_response_code(400);
				echo json_encode("Please Input an Id");
			}
			break;
		default:
			break;
	}

	function Select($id) {
		global $sql;
		global $table;
		
		$response['code'] = null;
		$response['value'] = '';
		$date = date("Y-m-d");
		$query_select = "select * from $table   ";
		
		$query_where = "where ".(($id=='')?" CURRENT_DATE() between startdate and enddate ":"hash='$id'");
		$query = $query_select.$query_where;

		$result = $sql->query($query);
		if(!$result) {
			$response['value'] = $sql->error;
			$response['code'] = 400;
			return $response;
		}
		$index = 0;
		$response['value']=[];
		while($row = $result->fetch_assoc()) {
			$response['value'][$index] = $row;
			$index++;
		}
		
		if($index == 0) {
			$response['code'] = 404;
			$response['value'] = "Review Not Found";
		}
		else
			$response['code'] = 200;
		return $response;
	}

	function Insert($data) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		
		$keys = array_keys($data);
		$query_insert = "insert into $table ";
		$query_keys = "(".implode(",",$keys).")\n";
		$query_values = "values('".implode("','",$data)."')";
		$query = $query_insert.$query_keys.$query_values;

		$result = $sql->query($query);
		if(!$result) {
			$response['value'] = $sql->error;
			if(strpos($response['value'],"Duplicate")===false)
				$response['code'] = 400;
			else
			$response['code'] = 401;
			return $response;
		}
		$response['code'] = 200;
		$response['value'] = $sql->insert_id;
		return $response;
	}

	function Delete($id) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		
		$query_delete = "delete from $table ";
		$query_where = "WHERE hash='$id'";
		$query = $query_delete.$query_where;

		$result = $sql->query($query);
		if(!$result) {
			$response['code'] = 400;
			$response['value'] = $sql->error;
			return $response;
		}
		$response['code'] = 200;
		if($sql->affected_rows == 0)
			$response['value'] = "Nothing Changes";
		return $response;
	}
?>