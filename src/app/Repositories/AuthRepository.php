<?php
namespace App\Repositories;

use App\Models\ShopifyAuth;

class AuthRepository
{
	protected $model;

	public function __construct(ShopifyAuth $auth)
	{
		$this->model = $auth;
	}

	/**
	 * Save access token
	 * @param  array  $response [description]
	 * @return mixed|bool
	 */
	public function saveAuth($response = [])
	{
		if ($this->isValid($response) === false)
			return false;

		if ($this->getAuth($response['store_url']))
			return;

		$this->model->access_token = $response['access_token'];
		$this->model->store_url = $response['store_url'];

		$this->model->save();
	}

	private function isValid($response)
	{
		if (!isset($response['access_token']) || !isset($response['store_url']))
			return false;

		return true;
	}

	public function getAuth($store_url = '')
	{
		if (empty($store_url))
			return null;

		$results = $this->model
					  ->where('store_url', $store_url)
					  ->select('id', 'access_token')
					  ->get();

		return $results;
	}
}
