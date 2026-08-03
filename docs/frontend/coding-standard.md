# Coding Standards (The Golden Rules)

To maintain a healthy, scalable codebase, all frontend code MUST adhere to the following rules. There are absolutely no exceptions.

## ❌ The Blacklist (DO NOT USE)
1. **❌ Inline JavaScript**: Never use `onclick=""`, `onchange=""` in Blade files. Always use `addEventListener` or Event Delegation in the Module's `bind()` method.
2. **❌ Inline CSS**: Never use `<style>` blocks or `style="..."` inside Blade templates unless dynamically calculated.
3. **❌ Direct `fetch()` or `$.ajax()`**: Always use `SMS.Core.Http.post()` or `SMS.Core.Http.get()`.
4. **❌ Direct `alert()`, `Swal.fire()`, `toastr`**: Always use `SMS.Core.Notifier.success()`, `.error()`, etc.
5. **❌ Direct `console.log()` in production**: Clean up your logs.
6. **❌ New jQuery Code**: Do not write new jQuery unless interacting with a legacy vendor plugin. Use `SMS.Core.DOM`.
7. **❌ Hardcoded URLs in JS**: Never write `const url = '/admin/marks'`. Use `data-url` attributes on the HTML elements or configure them in `SMS.Config.routes`.
8. **❌ Hardcoded Colors**: Use CSS variables.
9. **❌ Raw HTML component generation**: Do not build repetitive UI using raw HTML. Use `<x-card>`, `<x-data-table>`, etc.

## ✅ The Whitelist (BEST PRACTICES)
1. **✅ Event Delegation**: Attach listeners to parent containers for dynamic elements.
2. **✅ ES6 Classes**: All modules must extend `SMS.Core.BaseModule`.
3. **✅ ApiResponse Trait**: All controllers responding to AJAX must use the `ApiResponse` trait.
4. **✅ Blade Components**: Build UIs exclusively with the UI Kit (`resources/views/components/`).
