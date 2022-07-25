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
				$productModel = new ProductModel;
				$categoryModel = new CategoryModel;
				$getCategories = $categoryModel->select('id, discount_rules')->getAll();

				// extract campaigns
				$campaigns = [];
				foreach ($getCategories as $category) {
					$category->discount_rules = strpos($category->discount_rules, ',') !== false ? explode(',', $category->discount_rules) : [$category->discount_rules];
					foreach ($category->discount_rules as $rule) {
						if ($rule !== '') {
							$campaigns[] = [$rule, $category->id];
						}
					}
				}

				// get product data
				$products = [];
				foreach ($getCart->items as $cartProductId => $cartProductDetail) {
					
					$cartProduct = $productModel->where('id', $cartProductId)->get();
					if (isset($cartProduct->id) !== false) {
						$products[$cartProduct->id] = $cartProduct;
					}
				}

				// system campaigns
				$campaigns[] = ['10_PERCENT_OVER_1000', 0];

				$discounts = [];
				$discountedTotal = $getCart->total;
				if (count($campaigns)) {

					foreach ($campaigns as $campaign) {
						
						// not programmatic because I don't have time for that yet.
						switch ($campaign[0]) {
							case 'BUY_2_PLUS_PERCENT_20_LOWEST':

								$categoryTrap = [];
								foreach ($getCart->items as $item) {
									if (
										isset($products[$item->productId]->category_id) !== false AND 
										$products[$item->productId]->category_id === $campaign[1] AND 
										$item->quantity >= 2
									) {
										$item->categoryId = $campaign[1];
										$categoryTrap[] = $item;
									}
								}

								if (count($categoryTrap)) {

									usort($categoryTrap, function($a, $b) {
										return (float)$a->unitPrice <=> (float)$b->unitPrice;
									});
										
									$calcDiscount = (($categoryTrap[0]->unitPrice / 100) * 20);
									$discountedTotal -= $calcDiscount;
									$discounts[] = [
										'discountReason' => $campaign[0],
										'discountAmount' => number_format($calcDiscount, 2),
										'subtotal' => number_format($discountedTotal, 2)
									];

								}
								break;

							case 'BUY_5_GET_1':
								foreach ($getCart->items as $item) {
									if (
										isset($products[$item->productId]->category_id) !== false AND 
										$products[$item->productId]->category_id === $campaign[1] AND
										$item->quantity > 5
									) {

										$freeProduct = floor($item->quantity / 5);
										
										$calcDiscount = $freeProduct * $item->unitPrice;
										$discountedTotal -= $calcDiscount;
										$discounts[] = [
											'discountReason' => $campaign[0],
											'discountAmount' => number_format($calcDiscount, 2),
											'subtotal' => number_format($discountedTotal, 2)
										];
									}
								}
								break;

							case '10_PERCENT_OVER_1000':
								if ($campaign[1] === 0 AND $discountedTotal > 1000) {

									$calcDiscount = (($discountedTotal / 100) * 10);
									$discountedTotal -= $calcDiscount;
									$discounts[] = [
										'discountReason' => $campaign[0],
										'discountAmount' => number_format($calcDiscount, 2),
										'subtotal' => number_format($discountedTotal, 2)
									];

								}
								break;
						}

					}

				}

				$return['status'] = true;
				$return['message'] = 'İndirimli sepet listelendi.';
				$return['discounts'] = $discounts;
				$return['items'] = $getCart->items;
				$return['total'] = $getCart->total;
				$return['discounted_total'] = $discountedTotal;

			} else {

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

			if (isset($getCart->id) !== false AND count(json_decode($getCart->items, true))) {

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