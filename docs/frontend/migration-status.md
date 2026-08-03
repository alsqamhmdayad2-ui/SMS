# Frontend Migration Status (Phase 9)

## Group A - High Priority (Anomalies in Migrated Modules)
These modules were supposed to be fully migrated but still contain legacy UI Kit elements. Needs investigation:
- `panels/admin/exams/*` (e.g., `builder.blade.php`, `marks-entry/index.blade.php`, `gradebook/index.blade.php`, `grade-scales/index.blade.php`, `assessment-components/index.blade.php`)
- `panels/admin/attendance/show.blade.php`
- `panels/admin/widgets/attendance-chart.blade.php`
- `panels/teacher/attendance/take.blade.php`

## Group B - Pending UI Kit Migration
These modules have not been touched yet and are expected to have legacy UI. (Pending for v1.1):
- `panels/admin/timetables/*`
- `panels/admin/report-cards/*`

## Group C - Legacy Admin Views
These are outside `panels/admin` and are pending migration. (Pending for v1.1):
- `admin/subjects/*`
- `admin/sections/*`
