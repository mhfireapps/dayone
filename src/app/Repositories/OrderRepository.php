<?php
namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
	protected $model;

	public function __construct(Order $order)
	{
		$this->model = $order;
	}
}
