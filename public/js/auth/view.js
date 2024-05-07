import { setFormErrors } from '../common/form.js';
import { showNotification } from '../common/notification.js';

/**
 * Обработка отправки формы авторизации
 */
$('#loginForm').on('submit', function (e) {
	e.preventDefault();

	// Собираем данные формы
	let formData = {
		username: $('#username').val(),
		password: $('#password').val(),
	};

	// Отправляем данные на сервер через AJAX
	$.ajax({
		type: 'POST',
		url: '/auth/login/make',
		data: formData,
		dataType: 'json',
		success: function (response) {
			location.reload();
		},
		error: function (xhr, status, error) {
			let responseJson = xhr.responseJSON;
			if (responseJson !== undefined && responseJson.status === 422) {
				if (responseJson.errors) {
					setFormErrors(responseJson.errors);
				}
			} else {
				showNotification('error', 'Ошибка авторизации', 'Произошла неизвестная ошибка');
			}
		}
	});
});