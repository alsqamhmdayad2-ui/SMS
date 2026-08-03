SMS.Events = {
    Types: {
        MARK_SAVED: 'MARK_SAVED',
        SESSION_LOCKED: 'SESSION_LOCKED',
        ATTENDANCE_UPDATED: 'ATTENDANCE_UPDATED',
        REPORT_GENERATED: 'REPORT_GENERATED',
        MODAL_OPENED: 'MODAL_OPENED',
        MODAL_CLOSED: 'MODAL_CLOSED'
    },
    events: {},
    on: function(eventName, fn) {
        this.events[eventName] = this.events[eventName] || [];
        this.events[eventName].push(fn);
    },
    off: function(eventName, fn) {
        if (this.events[eventName]) {
            for (let i = 0; i < this.events[eventName].length; i++) {
                if (this.events[eventName][i] === fn) {
                    this.events[eventName].splice(i, 1);
                    break;
                }
            }
        }
    },
    emit: function(eventName, data) {
        if (this.events[eventName]) {
            this.events[eventName].forEach(function(fn) {
                fn(data);
            });
        }
    }
};
