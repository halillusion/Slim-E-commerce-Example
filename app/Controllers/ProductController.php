<?php
namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\Helpers\Base;
use \App\Models\ProductModel;

class ProductController {
		
	public function getProducts(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Ürün bulunamadı!',
		];

		$model = new ProductModel;
		$getProducts = $model->select('id, name, category_id, price, stock')->getAll();

		if (count($getProducts)) {


			$return['products'] = $getProducts;
			$return['total'] = count($getProducts);
			$return['status'] = true;
			$return['message'] = 'Ürünler listelendi.';

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
	}
}