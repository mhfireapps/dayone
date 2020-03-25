<?php
namespace App\Respositories;

use App\Models\ShopifyAuth;
class AuthResponsitory
{
	protected $model;

	public function __construct(ShopifyAuth $auth)
	{
		$this->model = $auth;
	}

	
}
