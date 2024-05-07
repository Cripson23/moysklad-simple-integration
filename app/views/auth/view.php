<link rel="stylesheet" href="/css/auth/view.css">
<script type="module" src="/js/auth/view.js"></script>

<div class="form-container">
	<form id="loginForm">
		<div class="form-item-container">
			<label for="username">Логин:</label>
			<input class="ui-input" type="text" id="username" name="ui-input" placeholder="">
			<div class="error" id="error-username"></div>
		</div>

		<div class="form-item-container">
			<label for="password">Пароль:</label>
			<input class="ui-input" type="password" id="password" name="ui-input" placeholder="">
			<div class="error" id="error-password"></div>
		</div>

		<div class="form-item-container">
			<button type="submit" class="button button--success" id="login-btn">Авторизоваться</button>
		</div>
	</form>
</div>