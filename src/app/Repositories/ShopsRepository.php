<?php
namespace App\Repositories;

use App\Models\Shops;

class ShopsRepository
{
	protected $model;

	public function __construct(Shops $shops)
	{
		$this->model = $shops;
	}

	/**
	 * Save access token
	 * @param  array  $response [description]
	 * @return mixed|bool
	 */
	public function saveData($response = [])
	{
		// To do something
	}
}
