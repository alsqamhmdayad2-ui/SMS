# API Contract

All JSON responses from the backend to the frontend must follow this exact structure. This contract is enforced via the `App\Support\Http\ApiResponse` trait.

## Standard JSON Structure
```json
{
    "success": true,        // Boolean indicating success or failure
    "message": "Success",   // Human-readable message (e.g., for Toastrs)
    "code": "MARK_SAVED",   // Machine-readable enum code
    "data": {},             // The actual payload (Array, Object, or null)
    "errors": []            // Array of validation errors if applicable
}
```

## Standardized Response Codes
Frontend modules should rely on `code`, not `message`, to perform logic branching.

### Success Codes
- `SUCCESS`
- `MARK_SAVED`
- `MARK_DELETED`
- `ATTENDANCE_LOCKED`

### Error Codes
- `ERROR`: General server error (500).
- `VALIDATION_FAILED`: Form validation failed (422). The `errors` array will be populated.
- `UNAUTHORIZED`: User is not logged in or session expired (401). `SMS.Core.Http` will automatically redirect.
- `FORBIDDEN`: User lacks permissions (403).
- `NOT_FOUND`: Resource missing (404).

## Integration with `SMS.Core.Http`
The frontend `Http` module automatically intercepts `401`, `403`, and `422` status codes and fires the appropriate `SMS.Core.Notifier` alerts. You only need to handle the `.catch()` block if you want specific UI changes (like red borders on a row).
