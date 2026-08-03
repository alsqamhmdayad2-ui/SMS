/**
 * SMS Base Module
 * Core class for all frontend modules to extend.
 * Enforces a standardized lifecycle and structured implementation.
 */
class BaseModule {
    constructor(config = {}) {
        this.config = config;
        this.state = {};
        this.elements = {};
    }

    /**
     * Initializes the module.
     * Overriding methods should call super.init() to ensure lifecycle execution.
     */
    init() {
        this.beforeMount();
        this.cache();
        this.bind();
        this.afterMount();
        
        SMS.Events.emit(`${this.constructor.name}:initialized`, this.config);
    }

    /**
     * Hook to run logic before caching elements and binding events.
     */
    beforeMount() {}

    /**
     * Cache DOM elements into this.elements.
     */
    cache() {
        console.warn(`[SMS BaseModule] ${this.constructor.name} should implement cache()`);
    }

    /**
     * Bind Event Listeners.
     */
    bind() {
        console.warn(`[SMS BaseModule] ${this.constructor.name} should implement bind()`);
    }

    /**
     * Hook to run logic after elements are cached and events are bound.
     */
    afterMount() {}

    /**
     * Refresh UI based on updated state or data.
     */
    refresh() {}

    /**
     * Unbind events to prevent memory leaks when navigating or destroying.
     */
    unbind() {}

    /**
     * Destroy the module, cleaning up listeners and state.
     */
    destroy() {
        this.unbind();
        this.elements = {};
        this.state = {};
        SMS.Events.emit(`${this.constructor.name}:destroyed`, this.config);
    }
}

// Register globally
window.SMS = window.SMS || {};
SMS.Core = SMS.Core || {};
SMS.Core.BaseModule = BaseModule;
