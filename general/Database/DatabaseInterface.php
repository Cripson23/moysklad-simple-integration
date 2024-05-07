<?php

namespace general\Database;

use PDO;

interface DatabaseInterface
{
	public function getConnection(): ?PDO;
}