<?php
namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\Helpers\Base;
use \App\Helpers\Token;
use \App\Models\UserModel;

class UserController {
		
	public function login(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Eksik parametre olmamalı!',
		];

		extract(Base::input([
			'email' => 'nulled_email',
			'password' => 'nulled_password'
		], $request->getParsedBody()));

		if ($email AND $password) {

			$model = new UserModel;
			$userCheck = $model->select('id, name, email')
				->where('email', $email)
				->get();

			if (isset($userCheck->id) !== false) {

				$return['user'] = $userCheck;
				$return['status'] = true;
				$return['message'] = 'Oturum başarıyla açıldı!';
				$return['token'] = Token::create($userCheck->id);

			} else {
				$return['message'] = 'Böyle bir kullanıcı yok!';
			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
	}

	public function register(Request $request, Response $response) {

		$return = [
			'status' => false,
			'message' => 'Eksik parametre olmamalı!',
		];

		extract(Base::input([
			'name' => 'nulled_text',
			'email' => 'nulled_email',
			'password' => 'nulled_password'
		], $request->getParsedBody()));

		if ($name AND $email AND $password) {

			$model = new UserModel;
			$userCheck = $model->where('email', $email)->getAll();
			if (! count($userCheck)) {

				if ($model->insert(['name' => $name, 'email' => $email, 'password' => $password])) {
					$return['status'] = true;
					$return['message'] = 'Hesap başarıyla oluşturuldu.';
				} else {
					$return['message'] = 'Hesap oluşturulurken bir sorun oluştu!';
				}

			} else {
				$return['message'] = 'Bu eposta adresi ile zaten bir kullanıcı kaydolmuş.';
			}

		}

		$response->getBody()
			->write(json_encode($return));

		return $response->withHeader('content-type', 'application/json');
	}
}