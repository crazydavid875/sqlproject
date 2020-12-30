<?php
	$table = "review";
		
	switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$result = Select($route->getParameter(2));
		http_response_code($result['code']);
		echo json_encode($result['value']);
		break;
	case 'POST':
		$data = (array)json_decode(trim(file_get_contents('php://input'),"[]"));
		$result = Insert($data);
		http_response_code($result['code']);
		echo json_encode($result['value']);
		break;
	case 'PATCH':
		$id = $route->getParameter(2);
		$data = (array)json_decode(trim(file_get_contents('php://input'),"[]"));
		$result = Update($id, $data);
		$error = $query->ErrorMsg();
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
			echo json_encode("Please Input a Id");
		}
		break;
	default:
		break;
	}

	function Select($id) {
		global $sql;
		global $table;
		$index = 0;
		$response['code'] = null;
		$response['value'] = [];

		$query_select = "select * from $table ";
		$query_where = "where ".(($id=='')?"1":"id=$id");
		$query = $query_select.$query_where;

		$result = $sql->query($query);
		if(!$result) {
			$response['value'] = $sql->error;
			$response['code'] = 400;
			return $response;
		}
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
		$query_values = "values(".sprintf("'%s'",implode("','",$data)).")";
		echo $query = $query_insert.$query_keys.$query_values;

		$result = $sql->query($query);
		if(!$result) {
			$response['value'] = $sql->error;
			$response['code'] = 400;
			return $response;
		}
		$response['code'] = 200;
		$response['value'] = $sql->insert_id;
		return $response;
	}

	function Update($id, $data) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		
		$keys = array_keys($data);
		$sets = [];
		for($i = 0; $i < count($keys); $i++)
			$sets[$i] = sprintf("%s = '%s'", $keys[$i], $data[$keys[$i]]);
		$query_update = "update $table ";
		$query_set = "set ".implode(",", $sets);
		$query_where = "where id=$id";
		$query = $query_update.$query_set.$query_where;

		$result = $sql->query($query);
		if(!$result) {
			$response['value'] = $sql->error;
			$response['code'] = 400;
			return $response;
		}
		$response['code'] = 200;
		if($sql->affected_rows == 0)
			$response['value'] = "Nothing Changes";
		return $response;
	}

	function Delete($id) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		
		$query_delete = "delete from $table ";
		$query_where = "WHERE id=$id";
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
