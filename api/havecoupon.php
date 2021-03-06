<?php
	$table = "havecoupon";

	switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if($route->getParameter(2)=='')
			$result = Select();
		else {
			$shoppinglistId = $route->getParameter(2);
			$couponId = $route->getParameter(3);
			$result = GetTotalPrice($shoppinglistId, $couponId);
		}
		http_response_code($result['code']);
		echo json_encode($result['value']);
		break;
	case 'POST':
		$couponid = $route->getParameter(2);
		$memberid = $route->getParameter(3);
		$result = Insert($couponid, $memberid);
		http_response_code($result['code']);
		echo json_encode($result['value']);
		break;
	case 'DELETE':
		$id = $route->getParameter(2);
		$result = Delete($id);
		http_response_code($result['code']);
		echo json_encode($result['value']);
		break;
	default:
		break;
	}

	function Select() {
		global $sql;
		global $table;
		global $authmemberid;
		$response['code'] = null;
		$response['value'] = '';
		$index = 0;

		$query_select = "select * from $table  ";
		$query_where = "where memberid = $authmemberid";
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
		if($index == 0)
			$response['value'] = "You Have No Coupon";
		$response['code'] = 200;
		return $response;
	}

	function GetTotalPrice($shoppinglistId,$couponId) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		
		$query_select = "select sum(game.price) from havelist  ";
		$query_join_shoppinglist = "left join shoppinglist on shoppinglist.id = $shoppinglistId  ";
		$query_join_game = "left join game on gameId = game.id";
		$query_join_coupon = "left join coupon on coupon.id = $couponId";
		$query = $query_select.$query_join_shoppinglist.$query_join_game.$query_join_coupon;

		$result = $sql->query($query);
		$row = $result->fetch_assoc();
		$response['value'] = $row;
		$response['code'] = 200;
		return $response;
	}

	function Insert($couponid, $memberid) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		$now =  date("Y-m-d H:i:s");

		$query_insert = "insert into $table ";
		$query_keys = "(couponid, memberid, gettime) ";
		$query_values = "values($couponid, $memberid, NOW())";
		$query = $query_insert.$query_keys.$query_values;

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

	function Delete($id) {
		global $sql;
		global $table;
		$response['code'] = null;
		$response['value'] = '';
		$query_delete = "delete from $table  ";
		$query_where = "where id = $id";
		$query = $query_delete.$query_where;

		$result = $sql->query($query);
		if(!$result) {
			$response['code'] = 400;
			$response['value'] = $sql->error;
			return $response;
		}
		$response['code'] = 200;
		return $response;
	}
?>