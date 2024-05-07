<?php
/**
 * @var int $errorCode
 * @var string $errorMessage
 */
?>

<link rel="stylesheet" href="/css/error.css">

<div class="container">
	<h1>Ошибка с кодом <?= $errorCode ?></h1>
	<p><?= $errorMessage ?></p>
	<a class="button" href="<?= $_SERVER['HTTP_REFERER'] ?? '/' ?>">Назад</a>
</div>