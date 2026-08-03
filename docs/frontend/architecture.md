# Frontend Architecture

This document describes the Enterprise Architecture of the Frontend for the School Management System (SMS).

## Core Principles
1. **No Technical Debt**: The architecture is designed to prevent "spaghetti code".
2. **Component-Based**: UI is built using standardized Blade Components.
3. **Module-Based**: JavaScript is organized into independent ES6 Classes extending a unified `BaseModule`.
4. **Decoupled Communication**: Modules communicate via an Event Bus (`SMS.Events`) rather than tight coupling.
5. **Standardized API Contract**: All backend endpoints must return a predictable JSON structure governed by the `ApiResponse` trait.

## The Global Namespace (`window.SMS`)
To avoid polluting the global window object, everything belongs to `window.SMS`:
- `SMS.Config`: Holds immutable configuration injected from Blade (CSRF, Locale, Roles).
- `SMS.Core`: Contains the foundation (Http, Loader, Notifier, DOM, BaseModule).
- `SMS.Events`: The application-wide pub/sub event bus.
- `SMS.Store`: Simple state management for cross-component data.
- `SMS.Modules`: Domain-specific business logic (e.g., `SMS.Modules.MarksEntry`).
- `SMS.Utils`: Helper functions (Form, Validation, Math).

## Lifecycle of a Module
Every module must extend `SMS.Core.BaseModule` and adhere to the strict lifecycle:
1. `constructor(config)`
2. `init()`: Starts the module.
3. `beforeMount()`: Logic before DOM caching.
4. `cache()`: Find all elements via `SMS.Core.DOM.get()`.
5. `bind()`: Attach event listeners.
6. `afterMount()`: Logic after DOM is bound.
7. `refresh()`: Update UI state.
8. `destroy()`: Unbind events and clean up.
