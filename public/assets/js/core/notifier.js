SMS.Core.Notifier = (function() {
    // Basic implementation that can be hooked into Toastr, SweetAlert, etc.
    function show(type, msg, title) {
        // Fallback to basic alerts for now
        // TODO: integrate with actual UI library
        if (typeof Swal !== 'undefined') {
            Swal.fire(title || type.toUpperCase(), msg, type);
        } else if (typeof toastr !== 'undefined') {
            toastr[type](msg, title);
        } else {
            console.log(`[${type.toUpperCase()}] ${title ? title + ': ' : ''}${msg}`);
            // alert(`[${type.toUpperCase()}] ${msg}`);
        }
    }

    function notify(options) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: options.title || '',
                text: options.text || options.message || '',
                icon: options.type || options.icon || 'info',
                ...options
            });
        } else {
            show(options.type || 'info', options.text || options.message || '', options.title || '');
        }
    }

    return {
        notify: notify,
        success: (msg, title = 'نجاح') => show('success', msg, title),
        error: (msg, title = 'خطأ') => show('error', msg, title),
        warning: (msg, title = 'تحذير') => show('warning', msg, title),
        info: (msg, title = 'معلومة') => show('info', msg, title),
        confirm: (msg, callback) => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'تأكيد',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) callback();
                });
            } else {
                if (window.confirm(msg)) {
                    callback();
                }
            }
        }
    };
})();
