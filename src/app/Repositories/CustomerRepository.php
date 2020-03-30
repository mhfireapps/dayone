<?php
namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository
{
	protected $model;

	public function __construct(Customer $customer)
	{
		$this->model = $customer;
	}
}
