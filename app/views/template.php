<?php

/** @var $title */
/** @var $view */
/** @var $auth */

// Get notification from PHP
if (!empty($_SESSION['notification'])) {
	$notification = $_SESSION['notification'];
	unset($_SESSION['notification']);
} else {
	$notification = null;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<title><?= $title ?></title>

	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;700&display=swap" rel="stylesheet">

	<link rel="icon" href="/img/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">

	<link rel="stylesheet" href="/css/template.css">
	<link rel="stylesheet" href="/assets/html-marketplace-1.0-uikit/css/uikit.min.css">

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<script type="module" src="/assets/html-marketplace-1.0-uikit/js/uikit.min.js"></script>
	<script type="module" src="/js/common/notification.js"></script>

</head>

<body>
<div class="container">
	<header class="header">
	</header>

	<main>
	  <?php include $view; ?>
	</main>
</div>

<footer class="footer">
</footer>
</body>

<!-- Set notification from PHP -->
<?php if ($notification): ?>
	<script>
	  let notification = <?= $notification; ?>;
	  localStorage.setItem('notification', JSON.stringify(notification));
	</script>
<?php endif; ?>

<?php include ROOT_PATH . '/app/views/common/popup.php' ?>
</html>