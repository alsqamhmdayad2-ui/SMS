SMS.Core.Loader = (function() {
    const activeLoaders = new Set();
    
    return {
        show: function(id = 'global') {
            activeLoaders.add(id);
            // If global loader UI exists, show it here
            SMS.Events.emit('loader:show', { id: id });
        },
        hide: function(id = 'global') {
            activeLoaders.delete(id);
            SMS.Events.emit('loader:hide', { id: id });
            if (activeLoaders.size === 0) {
                // If global loader UI exists, hide it here
            }
        }
    };
})();
