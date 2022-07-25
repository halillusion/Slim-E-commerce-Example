<?php
namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\Helpers\Base;
use \App\Models\CategoryModel;
use \App\Models\OrderModel;
use \App\Models\ProductModel;

class OrderController {
		
	public function addToCart(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		extract(Base::input([
			'productId' => 'int',
			'quantity' => 'int',
		], $request->getParsedBody()));

		$userData = $request->getAttribute('userData');

		// parameter check
		if ($productId AND $quantity AND isset($userData->id) !== false) {

			
			$productModel = new ProductModel;
			$orderModel = new OrderModel;

			$getProduct = $productModel->select('id, name, price, stock, category_id')->where('id', $productId)->get();

			// product check
			if (isset($getProduct->id) !== false) {

				// stock check
				$getProduct->stock = (int) $getProduct->stock;
				if ($getProduct->stock AND $getProduct->stock >= $quantity) {

					$completed = false;
					$items = [];
					$getCart = $orderModel->where('user_id', $userData->id)->where('status', 'cart')->get();

					// we haven't cart
					if (isset($getCart->id) === false) {

						$total = $getProduct->price * $quantity;
						// create cart
						$items[$getProduct->id] = [
							'productId' => $getProduct->id,
							'quantity' => $quantity,
							'unitPrice' => $getProduct->price,
							'total' => $total
						];

						$insert = [
							'user_id' => $userData->id,
							'items' => json_encode($items),
							'total' => $total
						];

						if ($orderModel->insert($insert)) {
							$completed = true;
						}

					} else { // we have cart

						$total = 0;
						$items = json_decode($getCart->items, true);
						if (isset($items[$getProduct->id]) !== false) {

							$items[$getProduct->id]['quantity'] = $items[$getProduct->id]['quantity'] + $quantity;
							$items[$getProduct->id]['unitPrice'] = $getProduct->price;
							$items[$getProduct->id]['total'] = $items[$getProduct->id]['quantity'] * $getProduct->price;

						} else {
							$items[$getProduct->id] = [
								'productId' => $getProduct->id,
								'quantity' => $quantity,
								'unitPrice' => $getProduct->price,
								'total' => $getProduct->price * $quantity
							];
						}

						foreach ($items as $pId => $pDetails) {
							$total += $pDetails['total'];
						}

						$update = [
							'items' => json_encode($items),
							'total' => $total
						];

						if ($orderModel->where('id', $getCart->id)->update($update)) {
							$completed = true;
						}

					}


					if ($completed) {

						$productModel->where('id', $getProduct->id)
							->update([
								'stock' => $getProduct->stock - $quantity
							]);

						$return['status'] = true;
						$return['message'] = 'Ürün sepetinize başarıyla eklendi.';

					} else {
						$return['message'] = 'Ürün sepetinize eklenirken bir sorun oluştu!';
					}

				} else {
					$return['message'] = 'Ürünün stoğu yok ya da talebinizin altında!';
				}

			} else {
				$return['message'] = 'Böyle bir ürün yok!';
			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function removeFromCart(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		extract(Base::input([
			'productId' => 'int',
		], $request->getParsedBody()));

		$userData = $request->getAttribute('userData');

		// parameter check
		if ($productId AND isset($userData->id) !== false) {

			$productModel = new ProductModel;
			$orderModel = new OrderModel;

			$getProduct = $productModel->select('id, name, price, stock, category_id')->where('id', $productId)->get();

			// product check
			if (isset($getProduct->id) !== false) {

				$completed = false;
				$items = [];
				$getCart = $orderModel->where('user_id', $userData->id)->where('status', 'cart')->get();

				// cart check
				if (isset($getCart->id) !== false) {

					$items = json_decode($getCart->items, true);
					if (isset($items[$getProduct->id]) !== false) {

						$total = 0;
						$qty = $items[$getProduct->id]['quantity'];
						unset($items[$getProduct->id]);

						foreach ($items as $pId => $pDetails) {
							$total += $pDetails['total'];
						}

						$update = [
							'items' => json_encode($items),
							'total' => $total
						];

						if ($orderModel->where('id', $getCart->id)->update($update)) {

							$productModel->where('id', $getProduct->id)->update(['stock' => $getProduct->stock + $qty]);
							$return['status'] = true;
							$return['message'] = 'Ürün sepetinizden başarıyla çıkarıldı.';

						} else {
							$return['message'] = 'Ürün sepetinizden çıkarılırken bir sorun oluştu!';
						}

					} else {
						$return['message'] = 'Sepetinizde böyle bir ürün yok!';
					}

				} else {

					$return['message'] = 'Sepetiniz boş!';

				}

			} else {
				$return['message'] = 'Böyle bir ürün yok!';
			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function getCart(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		$userData = $request->getAttribute('userData');

		// parameter check
		if (isset($userData->id) !== false) {

			$orderModel = new OrderModel;
			$getCart = $orderModel->where('user_id', $userData->id)->where('status', 'cart')->get();

			if (isset($getCart->id) !== false AND count(json_decode($getCart->items, true))) {

				$return['status'] = true;
				$return['message'] = 'Sepet listelendi.';
				$return['items'] = json_decode($getCart->items);
				$return['total'] = $getCart->total;

			} else {

				$return['status'] = true;
				$return['message'] = 'Sepetiniz boş!';

			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function getDiscountedCart(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		$userData = $request->getAttribute('userData');

		// parameter check
		if (isset($userData->id) !== false) {

			$orderModel = new OrderModel;
			$getCart = $orderModel->where('user_id', $userData->id)->where('status', 'cart')->get();

			if (isset($getCart->id) !== false AND count(json_decode($getCart->items, true))) {

				$getCart->items = json_decode($getCart->items);
				$categoryModel = new CategoryModel;
				$getCategories = $categoryModel->select('id, discount_rules')->getAll();
				$campaigns = [];
				foreach ($getCategories as $category) {
					$campaigns[] = 
				}
				/*
				switch (variable) {
					case 'value':
						// code...
						break;
					
					default:
						// code...
						break;
				}
				*/

				$return['status'] = true;
				$return['message'] = 'İndirimli sepet listelendi.';
				$return['discounts'] = json_decode($discounts);
				$return['items'] = $getCart->items;
				$return['total'] = $getCart->total;
				$return['discounted_total'] = $discountedTotal;

			} else {

				$return['status'] = true;
				$return['message'] = 'Sepetiniz boş!';

			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function completeOrder(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		$userData = $request->getAttribute('userData');

		// parameter check
		if (isset($userData->id) !== false) {

			$orderModel = new OrderModel;
			$getCart = $orderModel->where('user_id', $userData->id)->where('status', 'cart')->get();

			if (isset($getCart->id) !== false) {

				if ($orderModel->where('id', $getCart->id)->update(['status' => 'completed'])) {
					$return['status'] = true;
					$return['message'] = 'Siparişiniz tamamlandı.';
				} else {
					$return['message'] = 'Siparişiniz tamamlanırken bir sorun oluştu!';
				}

			} else {

				$return['status'] = true;
				$return['message'] = 'Sepetiniz boş!';

			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function getOrders(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		$userData = $request->getAttribute('userData');

		// parameter check
		if (isset($userData->id) !== false) {

			$orderModel = new OrderModel;
			$getOrders = $orderModel->where('user_id', $userData->id)->notWhere('status', 'cart')->getAll();

			if (count($getOrders)) {

				$orders = [];
				$total = 0;
				foreach ($getOrders as $order) {
					$orders[] = [
						'id' => $order->id,
						'date' => date('d.m.Y H:i', $order->created_at),
						'items' => json_decode($order->items),
						'status' => $order->status,
						'total' => $order->total
					];
					$total += $order->total;
				}

				$return['status'] = true;
				$return['message'] = 'Siparişleriniz listelendi.';
				$return['orders'] = $orders;
				$return['total'] = $total;

			} else {

				$return['status'] = true;
				$return['message'] = 'Siparişiniz bulunmuyor!';

			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}

	public function cancelOrder(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Parametreler eksik!',
		];

		$userData = $request->getAttribute('userData');

		extract(Base::input([
			'orderId' => 'int',
		], $request->getParsedBody()));

		// parameter check
		if ($orderId AND isset($userData->id) !== false) {

			$orderModel = new OrderModel;
			$getOrder = $orderModel->where('id', $orderId)->where('user_id', $userData->id)->where('status', 'completed')->get();

			if (isset($getOrder->id) !== false) {

				if ($orderModel->where('id', $getOrder->id)->update(['status' => 'cancelled'])) {

					$productModel = new ProductModel;

					// update product stocks
					foreach (json_decode($getOrder->items) as $item) {
						$getProduct = $productModel->select('id, stock')->where('id', $item->productId)->get();
						if (isset($getProduct) !== false) {
							$productModel->where('id', $getProduct->id)->update(['stock' => $getProduct->stock + $item->quantity]);
						}
					}

					$return['status'] = true;
					$return['message'] = 'Siparişiniz başarıyla iptal edildi.';
				} else {
					$return['message'] = 'Siparişiniz iptal edilirken bir sorun oluştu!';
				}

			} else {

				$return['message'] = 'Böyle bir siparişiniz bulunmuyor.';

			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
		
	}
}