export { setFormErrors }

/**
 * Задаёт ошибки для полей формы
 * @param errors Ошибки полей формы
 */
function setFormErrors(errors)
{
	clearFormErrors();
	for (let field in errors) {
		let errorField = $(`#error-${field}`);
		errorField.text(errors[field]);
		errorField.css({'margin-bottom': '8px', 'margin-top': '5px', 'height': '10px'});
	}
}

/**
 * Очищает ошибки полей формы
 */
function clearFormErrors()
{
	// Очищаем текст ошибок
	$('.error').each(function() {
		$(this).text('');
	});

	// Возвращаем стили к исходным значениям
	$('.error').css({'margin-bottom': '', 'height': ''});
}