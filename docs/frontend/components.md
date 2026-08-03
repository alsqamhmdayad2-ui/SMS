# Blade Components Library (UI Kit)

The SMS Frontend uses standard Laravel Blade Components for all UI elements to ensure consistency and eliminate repetitive HTML/CSS.

## Directory Structure
Components are located in `resources/views/components/` and are logically grouped:
- `layout/`: Navbar, Sidebar, Footer
- `shared/`: Cards, Alerts, Badges, Modals, Empty State, Loading
- `dashboard/`: Stat Cards, Progress Bars, Widgets
- `form/`: Inputs, Selects, Checkboxes, Switches
- `table/`: Data Tables, Headers, Actions

---

## 1. Shared Components

### `<x-shared.card>`
A standard container for grouped content.
```html
<x-shared.card title="Student Information" shadow="sm">
    <p>Content goes here</p>
    <x-slot:footer>
        <button class="btn btn-primary">Save</button>
    </x-slot>
</x-shared.card>
```
**Props:** `title`, `footer`, `shadow` (sm, md, lg), `class`

### `<x-shared.alert>`
Displays alert messages.
```html
<x-shared.alert type="danger" dismissible="true">
    Something went wrong!
</x-shared.alert>
```
**Props:** `type` (success, danger, warning, info), `dismissible` (bool), `icon` (string/bool), `class`

### `<x-shared.badge>`
```html
<x-shared.badge type="success" pill="true">Active</x-shared.badge>
```
**Props:** `type`, `pill` (bool), `class`

### `<x-shared.modal>`
A pure HTML wrapper for Bootstrap Modals.
```html
<x-shared.modal id="deleteModal" title="Confirm Delete" size="md">
    Are you sure?
</x-shared.modal>
```
**Props:** `id`, `title`, `size` (sm, md, lg, xl), `static` (bool), `scrollable` (bool), `centered` (bool)

---

## 2. Dashboard Components

### `<x-dashboard.stat-card>`
Used to display KPIs on the dashboard.
```html
<x-dashboard.stat-card 
    title="Total Students" 
    value="1,245" 
    icon="fas fa-users" 
    color="primary" 
    trend="up" 
    trendValue="+12%" 
/>
```
**Props:** `title`, `value`, `icon`, `color`, `trend` (up/down/null), `trendValue`, `trendText`

---

## 3. Form Components

### `<x-form.input>`
A standardized input field with labels and error handling.
```html
<x-form.input name="email" label="Email Address" type="email" required="true" :error="$errors->first('email')" />
```
**Props:** `name`, `label`, `type`, `value`, `error`, `required`, `placeholder`, `disabled`, `readonly`

### `<x-form.select>`
```html
<x-form.select name="role" label="User Role" :options="['admin' => 'Admin', 'teacher' => 'Teacher']" />
```

---

## 4. Table Components

### `<x-table.data-table>`
The main wrapper for all tabular data. Does NOT include query logic.
```html
<x-table.data-table striped="true" hover="true">
    <x-slot:header>
        <th>ID</th>
        <th>Name</th>
        <th>Actions</th>
    </x-slot>
    <x-slot:body>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>
                    <x-table.table-actions viewUrl="/users/{{ $user->id }}" />
                </td>
            </tr>
        @endforeach
    </x-slot>
</x-table.data-table>
```
**Props:** `hover`, `striped`, `bordered`, `class`

### `<x-table.table-actions>`
Standardized action buttons.
**Props:** `viewUrl`, `editUrl`, `deleteUrl`, `deleteId`
