import { showNotification } from "../common/notification.js";

/**
 * Выход из системы
 */
$('#logout-button').on('click', function () {
    $.ajax({
        type: 'DELETE',
        url: '/auth/logout',
        success: function (response) {
            window.location.assign('/auth/login');
        },
        error: function (xhr, status, error) {
            showNotification('logout-error', 'Выход из системы', 'Произошла неизвестная ошибка при выходе из системы')
        }
    });
});

/**
 * При клике на головной чекбокс задаём табличным то же самое значение
 */
$('.ui-table__head-checkbox').click(function() {
    $('.ui-table__row-checkbox').prop('checked', this.checked);
});

/**
 * Показ выпадающего списка для выбора статуса
 */
$('.ui-table__state-button').on('click', function(event) {
    let orderUuid = $(this).data('order-uuid');
    let stateUuid = $(this).data('state-uuid');

    // Позиционирование выпадающего списка
    let top = $(this).offset().top + $(this).outerHeight() + 3;
    let left = $(this).offset().left;

    $('#dropdown-content').css({top: top, left: left}).data('order-uuid', orderUuid).data('state-uuid', stateUuid).toggle();

    // Запрет "всплытия" клика, чтобы не сработало скрытие списка
    event.stopPropagation();
});

/**
 * Скрытие выпадающего списка при клике вне его
 */
$(document).on('click', function() {
    $('#dropdown-content').hide();
});

/**
 * Обработчик клика для элементов статуса
 */
$('#dropdown-content').on('click', '.dropdown-content__state-line', function() {
    let stateName = $(this).text().trim();
    let stateColor = $(this).find('.dropdown-content__state-line__color-marker').css('background-color');

    let orderUuid = $('#dropdown-content').data('order-uuid');
    let nowStateUuid = $('#dropdown-content').data('state-uuid');
    let stateUuid = $(this).data('state-uuid');

    if (nowStateUuid !== stateUuid) {
        changeOrderStatus(orderUuid, stateUuid, stateName, stateColor);
    }

    // Скрыть выпадающий список после выбора
    $(this).parent().hide();
});

/**
 * Изменение статуса заказа
 *
 * @param orderUuid
 * @param stateUuid
 * @param stateName
 * @param stateColor
 */
function changeOrderStatus(orderUuid, stateUuid, stateName, stateColor) {
    $.ajax({
        type: 'POST',
        url: '/order/state/update',
        data: {'order_uuid': orderUuid, 'state_uuid': stateUuid},
        dataType: 'json',
        success: function (response) {
            // update button
            $('.ui-table__state-button[data-order-uuid="' + orderUuid + '"]')
              .data('state-uuid', stateUuid).text(stateName)
              .css('background-color', stateColor);

            // update updated_at
            updateLastModifiedOrderTime(orderUuid);
        },
        error: function (xhr, status, error) {
            let responseJson = xhr.responseJSON;
            if (responseJson !== undefined && responseJson.status === 401) {
                showNotification('order-change-state', 'Смена статуса заказа', 'Авторизационная сессия не прошла проверку, пожалуйста, авторизуйтесь снова')
                window.location.assign('/auth/login');
            } else {
                showNotification('order-change-state', 'Смена статуса заказа', 'Произошла неизвестная ошибка при смене статуса заказа')
            }
        }
    });
}

/**
 * Обновление даты последнего изменения заказа
 *
 * @param orderUuid
 */
function updateLastModifiedOrderTime(orderUuid) {
    $.ajax({
        type: 'GET',
        url: '/order/get/last-modified-date',
        data: { 'order_uuid': orderUuid },
        success: function (response) {
            console.log(response);
            $('.ui-table__state-button[data-order-uuid="' + orderUuid + '"]')
                .closest('tr')
                .find('.ui-table__updated-at')
                .text(response.updated_at);
        },
        error: function () {
            showNotification('update-time-error', 'Обновление заказа', 'Не удалось обновить дату последнего изменения');
        }
    });
}