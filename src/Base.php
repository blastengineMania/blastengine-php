<?php

namespace Blastengine;

class Base
{
	protected ApiClient $_apiClient;

	function __construct()
	{
		$this->_apiClient = new ApiClient();
	}
}