<?php
namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
	protected $model;

	public function __construct(Product $product)
	{
		$this->model = $product;
	}
}
