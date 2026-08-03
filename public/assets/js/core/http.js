SMS.Core.Http = (function() {
    function request(method, url, data = null, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': SMS.Config.csrf || ''
        };

        const fetchOptions = {
            method: method,
            headers: Object.assign(headers, options.headers || {})
        };

        if (data && method !== 'GET' && method !== 'HEAD') {
            if (data instanceof FormData) {
                delete fetchOptions.headers['Content-Type']; // Let browser set boundary
                fetchOptions.body = data;
            } else {
                fetchOptions.body = JSON.stringify(data);
            }
        }

        return fetch(url, fetchOptions).then(async response => {
            if (!response.ok) {
                const errData = await response.json().catch(() => null);
                
                // Handle specific status codes globally
                if (response.status === 401) {
                    if(SMS.Core.Notifier) SMS.Core.Notifier.error('يرجى تسجيل الدخول مجدداً');
                    setTimeout(() => window.location.reload(), 1500);
                } else if (response.status === 403) {
                    if(SMS.Core.Notifier) SMS.Core.Notifier.error('ليس لديك صلاحية لهذا الإجراء');
                } else if (response.status === 422) {
                    if(SMS.Core.Notifier) SMS.Core.Notifier.warning(errData?.message || 'بيانات غير صالحة');
                } else {
                    if(SMS.Core.Notifier) SMS.Core.Notifier.error(errData?.message || 'حدث خطأ في الخادم');
                }
                
                throw { status: response.status, data: errData };
            }
            return response.json();
        });
    }

    return {
        get: (url, options) => request('GET', url, null, options),
        post: (url, data, options) => request('POST', url, data, options),
        put: (url, data, options) => request('PUT', url, data, options),
        patch: (url, data, options) => request('PATCH', url, data, options),
        delete: (url, data, options) => request('DELETE', url, data, options),
        upload: (url, formData, options) => request('POST', url, formData, options)
    };
})();
