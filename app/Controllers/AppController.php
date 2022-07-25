<?php
namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \App\Models\BaseModel;
use \App\Helpers\Base;

class AppController {
		
	public function index(Request $request, Response $response) {

		/**
		 * We used for testing.
		 */
		if (file_exists($path = Base::path('app/Resources/test.html'))) {
			$html = file_get_contents($path);
		} else {
			$html = '<pre>API TEST</pre>';
		}

		$response->getBody()->write($html);
		return $response;
	}

	public function prepareDb(Request $request, Response $response) {

		/**
		 * Fake ORM
		 */

		$html = '<pre>';
		if (file_exists($path = Base::path('app/Resources/schema.php'))) {

			$model = new BaseModel;
			$schema = require $path;

			if ($model->dbInit($schema) === 0) {

				$html .= 'Veri tabanı başarıyla hazırlandı.' . PHP_EOL;
				if ($model->dbSeed($schema) === 0) {
					$html .= 'Veri tabanı başarıyla içeri aktarıldı. Her şey hazır. <a href="/">Teste Dön</a>' . PHP_EOL;
				} else {
					$html .= 'Veri tabanı içeri aktarılamadı!' . PHP_EOL;
				}

			} else {
				$html .= 'Veri tabanı hazırlanamadı!' . PHP_EOL;
			}

		} else {
			$html .= 'Veri tabanı şema dosyası bulunamadı!' . PHP_EOL;
		}
		$html .= '</pre>';

		

		$response->getBody()->write($html);
		return $response;
	}
}