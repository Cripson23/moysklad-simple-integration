export { showNotification, setNotificationInLocalStorage };

$(document).ready(function() {
    // Получаем уведомление из локального хранилища (если есть)
    let notification = localStorage.getItem('notification');
    if (notification) {
        notification = JSON.parse(notification);
        showNotification(notification['name'], notification['title'], notification['message']);
        localStorage.removeItem('notification');
    }
});

/**
 * Задаёт уведомление в локальном хранилище
 * @param name
 * @param title
 * @param message
 */
function setNotificationInLocalStorage(name, title, message)
{
    let notification = {'name': name, 'title': title, 'message': message};
    localStorage.setItem('notification', JSON.stringify(notification));
    location.reload();
}

/**
 * Показывает уведомление в popup
 * @param name
 * @param title
 * @param message
 */
function showNotification(name, title, message)
{
    const popup = $('.js-popup-window');

    popup.data('name', name);
    popup.find('.popup__title').text(title);
    popup.find('.popup__content p').text(message);
    popup.removeClass('b-hide');
}