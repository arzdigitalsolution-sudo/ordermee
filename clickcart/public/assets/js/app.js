$(function () {
    const toastContainer = $('<div class="toast-container"></div>').appendTo('body');

    function showToast(message, type = 'info') {
        const toast = $(`<div class="toast toast-${type}">${message}</div>`).appendTo(toastContainer);
        setTimeout(() => toast.addClass('visible'), 10);
        setTimeout(() => toast.removeClass('visible'), 4000);
        setTimeout(() => toast.remove(), 4500);
    }

    window.ClickCart = {
        toast: showToast,
    };
});
