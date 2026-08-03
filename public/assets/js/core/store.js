/**
 * Simple State Management Store
 */
SMS.Store = {
    state: {
        user: {},
        filters: {},
        page: {}
    },

    get: function(key) {
        return this.state[key];
    },

    set: function(key, value) {
        this.state[key] = value;
        SMS.Events.emit(`store:changed:${key}`, value);
    },

    update: function(key, updates) {
        if (typeof this.state[key] === 'object' && this.state[key] !== null) {
            this.state[key] = Object.assign({}, this.state[key], updates);
        } else {
            this.state[key] = updates;
        }
        SMS.Events.emit(`store:changed:${key}`, this.state[key]);
    }
};
