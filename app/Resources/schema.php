<?php

/**
 * Database Structure
 *
 * The schema here is used to add new tables or to add new columns to existing tables.
 * Column parameters is as follows.
 *
 * > type:          Type parameters(required) -> (INT | VARCHAR | TEXT | DATE | ENUM | JSON)
 * > nullable:      True if it is an empty field.
 * > auto_inc:      True if it is an auto increment field.
 * > attr:          Attribute parameters -> (BINARY | UNSIGNED | UNSIGNED ZEROFILL | ON UPDATE CURRENT_TIMESTAMP)
 * > type_values:   ENUM -> ['on', 'off'] | INT, VARCHAR -> 255
 * > default:       Default value -> NULL, 'string' or CURRENT_TIMESTAMP
 * > index:         Index type -> (INDEX | PRIMARY | UNIQUE | FULLTEXT)
 */

return [
	'tables' => [

		/* Users Table */
		'users' => [
			'cols' => [
				'id' => [
					'type'          => 'int',
					'auto_inc'      => true,
					'attr'          => 'UNSIGNED',
					'type_values'   => 11,
					'index'         => 'PRIMARY'
				],
				'name' => [
					'type'          => 'varchar',
					'type_values'   => 255,
					'index'         => 'INDEX'
				],
				'email' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'UNIQUE'
				],
				'password' => [
					'type'          => 'varchar',
					'type_values'   => 120,
				],
				'created_at' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'INDEX'
				],
				'created_by' => [
					'type'          => 'int',
					'type_values'   => 10,
					'default'       => 0,
					'index'         => 'INDEX'
				],
			],
		],

		/* Products Table */
		'products' => [
			'cols' => [
				'id' => [
					'type'          => 'int',
					'auto_inc'      => true,
					'attr'          => 'unsigned',
					'type_values'   => 11,
					'index'         => 'PRIMARY'
				],
				'name' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'UNIQUE',
				],
				'category_id' => [
					'type'          => 'int',
					'type_values'   => 10,
				],
				'price' => [
					'type'          => 'float',
					'type_values'   => '10,2',
					'index'         => 'INDEX'
				],
				'stock' => [
					'type'          => 'int',
					'type_values'   => 10,
					'default'       => 0,
				],
				'created_at' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'INDEX'
				],
				'created_by' => [
					'type'          => 'int',
					'type_values'   => 10,
					'index'         => 'INDEX'
				],
			],
		],

		/* Categories Table */
		'categories' => [
			'cols' => [
				'id' => [
					'type'          => 'int',
					'auto_inc'      => true,
					'attr'          => 'unsigned',
					'type_values'   => 11,
					'index'         => 'PRIMARY'
				],
				'name' => [
					'type'          => 'varchar',
					'type_values'   => 140,
					'index'         => 'INDEX',
				],
				'discount_rules' => [
					'type'          => 'text',
				],
				'created_at' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'INDEX'
				],
				'created_by' => [
					'type'          => 'int',
					'type_values'   => 10,
					'index'         => 'INDEX'
				],
			]
		],

		/* Orders Table */
		'orders' => [
			'cols' => [
				'id' => [
					'type'          => 'int',
					'auto_inc'      => true,
					'attr'          => 'unsigned',
					'type_values'   => 11,
					'index'         => 'PRIMARY'
				],
				'user_id' => [
					'type'          => 'int',
					'index'         => 'INDEX'
				],
				'items' => [
					'type'          => 'json',
					'nullable'      => true,
				],
				'total' => [
					'type'          => 'float',
					'type_values'   => '10,2',
					'index'         => 'INDEX'
				],
				'created_at' => [
					'type'          => 'varchar',
					'type_values'   => 80,
					'index'         => 'INDEX'
				],
				'created_by' => [
					'type'          => 'int',
					'type_values'   => 10,
					'default'       => 0,
					'index'         => 'INDEX'
				],
				'status' => [
					'type'          => 'enum',
					'type_values'   => ['cart', 'completed', 'cancelled'],
					'default'       => 'cart',
					'index'         => 'INDEX'
				]
			]
		],

	],
	'table_values' => [
		'charset'   => 'utf8mb4', // You can use 'utf8' if the structure is causing problems.
		'collate'   => 'utf8mb4_unicode_520_ci', // You can use 'utf8_general_ci' if the structure is causing problems.
		'engine'    => 'InnoDB',
		'specific'  => [ // You can give specific value.
			'sessions' => [
				'engine'    => 'MEMORY'
			],
		]
	],
	'data'  => [
		'users' => [
			[
				'name'                	=> 'Halil İbrahim Erçelik',
				'email'                 => 'hiercelik@gmail.com',
				'password'              => '$2y$10$1i5w0tYbExemlpAAsospSOZ.n06NELYooYa5UJhdytvBEn85U8lly', // 1234
				'created_at'            => time(),
				'created_by'            => 0
			],
		],
		'products' => [
			[
				'name'                	=> 'Black&Decker A7062 40 Parça Cırcırlı Tornavida Seti',
				'category_id'           => '1',
				'price'              	=> 120.75,
				'stock'              	=> 10,
				'created_at'            => time(),
				'created_by'            => 0
			],
			[
				'name'                	=> 'Reko Mini Tamir Hassas Tornavida Seti 32\'li',
				'category_id'           => '1',
				'price'              	=> 49.50,
				'stock'              	=> 10,
				'created_at'            => time(),
				'created_by'            => 0
			],
			[
				'name'                	=> 'Viko Karre Anahtar - Beyaz',
				'category_id'           => '2',
				'price'              	=> 11.28,
				'stock'              	=> 10,
				'created_at'            => time(),
				'created_by'            => 0
			],
			[
				'name'                	=> 'Legrand Salbei Anahtar, Alüminyum',
				'category_id'           => '2',
				'price'              	=> 22.80,
				'stock'              	=> 10,
				'created_at'            => time(),
				'created_by'            => 0
			],
			[
				'name'                	=> 'Schneider Asfora Beyaz Komütatör',
				'category_id'           => '2',
				'price'              	=> 12.95,
				'stock'              	=> 10,
				'created_at'            => time(),
				'created_by'            => 0
			],
		],
		'categories' => [
			[
				'name'                	=> 'A Kategorisi',
				'discount_rules'        => 'BUY_2_PLUS_PERCENT_20_LOWEST',
				'created_at'            => time(),
				'created_by'            => 0
			],
			[
				'name'                	=> 'B Kategorisi',
				'discount_rules'        => 'BUY_5_GET_1',
				'created_at'            => time(),
				'created_by'            => 0
			],
		],
	],
];