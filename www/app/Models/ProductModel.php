<?php

/**
 * @package KN
 * @subpackage Model
 */

declare(strict_types=1);

namespace App\Models;

use \App\Models\BaseModel;
use \App\Helpers\Base;

final class ProductModel extends BaseModel {

    function __construct () {

        $this->table = 'products';
        $this->created = true;

        parent::__construct();

    }

}