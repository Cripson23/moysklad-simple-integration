<?php

namespace app\services\Auth\AuthorizationType;

interface AuthorizationTypeInterface
{
	public function getAuthorizationHeader(): string;
}