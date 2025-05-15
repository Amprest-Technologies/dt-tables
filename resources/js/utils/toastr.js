import toastr from 'toastr';

/**
 * Define toastr as part of the window object
 */
window.toastr = toastr;

/**
 * Define toastr options
 */
toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: false,
    progressBar: true,
    positionClass: 'toast-top-left',
    preventDuplicates: true,
    showDuration: 300,
    hideDuration: 1000,
    timeOut: 10000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};