/**
 * DOM Utility
 * Simplifies DOM operations across the application.
 */
SMS.Core.DOM = {
    get: (selector, context = document) => context.querySelector(selector),
    
    getAll: (selector, context = document) => Array.from(context.querySelectorAll(selector)),
    
    closest: (element, selector) => element.closest(selector),
    
    create: (tag, options = {}) => {
        const el = document.createElement(tag);
        if (options.className) el.className = options.className;
        if (options.id) el.id = options.id;
        if (options.html) el.innerHTML = options.html;
        if (options.text) el.textContent = options.text;
        if (options.dataset) {
            Object.keys(options.dataset).forEach(key => {
                el.dataset[key] = options.dataset[key];
            });
        }
        return el;
    },
    
    remove: (element) => {
        if (element && element.parentNode) {
            element.parentNode.removeChild(element);
        }
    },
    
    toggle: (element, className) => {
        if (element) {
            element.classList.toggle(className);
        }
    }
};
