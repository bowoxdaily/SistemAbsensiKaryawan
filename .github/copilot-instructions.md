# AI Coding Agent Instructions - Sistem Absensi Karyawan

## Project Overview

Laravel 11 employee attendance management system with face detection, WhatsApp notifications (Fonnte API), Excel import/export, and automated absent marking. Built with Sneat admin template and Chart.js visualizations.

## Architecture & Key Patterns

### Data Models Structure

-   **Dual naming**: `Employee` model uses `employees` table, but `Karyawans` model also exists for the same table (legacy compatibility)
-   **Work Schedules**: Employees have `work_schedule_id` (NOT `shift_type` anymore - migrated in `2025_10_22_000001_change_shift_type_to_work_schedule_id`)
-   **User Authentication**: Each employee has `user_id` foreign key; deleting employee cascades to user account

### API & Routing Pattern

-   **Dual routes**: Web routes for blade views + API routes for AJAX operations
-   **API Convention**: `/api/{resource}` returns JSON, web routes return blade views
-   **AJAX Pattern**: Blade views use jQuery AJAX to API endpoints for all CRUD operations
-   Example: `admin/karyawan` (blade) → calls → `/api/karyawan` (JSON)

### Frontend Architecture (Blade + jQuery)

-   **No Vue/React**: Pure jQuery + AJAX pattern throughout
-   **Modal-based CRUD**: All forms in Bootstrap modals, submission via AJAX
-   **Dual View Rendering**: Desktop (tables) + Mobile (cards) - check `d-none d-md-block` classes
-   **SweetAlert2**: Used for all confirmations, NOT native confirm()
-   **Chart.js v4.4.0**: For dashboard visualizations (line + doughnut charts)

## Critical Developer Workflows

### Setup & Dependencies

```bash
# First time setup (automated)
composer run setup

# Development (concurrent: server + queue + vite)
composer run dev

# Manual alternative
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Scheduled Tasks (IMPORTANT)

-   **Auto-generate absent** runs hourly (08:00-23:59, weekdays)
-   Command: `php artisan attendance:generate-absent [date]`
-   Logic: Marks employees as "alpha" if no check-in 30min after their work schedule's end_time
-   **Testing cron**: Use `/admin/settings/cronjob` UI or `php artisan schedule:work`

### Excel Import/Export Pattern

-   **Template-based**: Download template via `admin.karyawan.template` route first
-   **Import validation**: Uses `Maatwebsite\Excel` with `WithHeadingRow`, `WithValidation`, `SkipsOnError`
-   **Caching pattern**: Import caches Department/Position lookups in constructor to avoid N+1
-   **Export filtering**: Applies same filters from UI (`department_id`, `position_id`, `status`)

### WhatsApp Integration (Fonnte)

-   **Service class**: `App\Services\WhatsAppService` handles all notifications
-   **5 notification types**: check-in, check-out, leave-request, leave-approved, leave-rejected
-   **Photo toggle**: `send_photo_on_checkin` / `send_photo_on_checkout` flags control image attachments
-   **Variables**: Templates support `{name}`, `{time}`, `{duration}`, `{status}`, `{location}`, `{reason}`
-   **Testing**: Use `/admin/settings/whatsapp/send-test` endpoint before production use

## Database Conventions

### Migration Naming Pattern

-   Format: `YYYY_MM_DD_HHMMSS_action_table_name.php`
-   Example: `2025_10_21_051210_create_karyawans_table.php`

### Key Tables & Relationships

```php
employees (karyawans)
  ├─ department_id → departments
  ├─ position_id → positions
  ├─ work_schedule_id → work_schedules
  ├─ user_id → users (cascade delete)
  └─ supervisor_id → employees (self-referencing)

attendances
  ├─ employee_id → employees
  └─ Unique constraint: (employee_id, date, check_in_time)

leaves
  ├─ employee_id → employees
  └─ status: pending|approved|rejected
```

### Critical Fields

-   `employees.status`: `active|inactive|resign` (NOT boolean)
-   `employees.employment_status`: `Tetap|Kontrak|Magang|Outsource`
-   `work_schedules.is_active`: Only active schedules shown in dropdowns
-   `attendances.status`: `present|late|absent|leave|sick|alpha` (alpha = auto-generated)

## Code Style & Patterns

### Controller Pattern

```php
// Admin controllers: Return blade views
public function index() {
    return view('admin.resource.index');
}

// API methods: Return JSON
public function list(Request $request) {
    return response()->json(['data' => $paginated]);
}
```

### Validation Pattern

-   Use `Request::validate()` directly in controllers (no FormRequest classes yet)
-   Return 422 with `errors` array for AJAX forms
-   Example: `$validated = $request->validate(['field' => 'required|max:100']);`

### Date Handling

-   Always use Carbon for date operations
-   Work schedules: Handle both `H:i:s` (time) and `Y-m-d H:i:s` (datetime) formats
-   Example: `Carbon::parse($date)->format('d-m-Y')` for consistency

### AJAX Response Pattern

```javascript
// Success
{ "success": true, "message": "...", "data": {...} }

// Validation error (422)
{ "message": "...", "errors": { "field": ["error message"] } }

// Frontend handling
$(`#${field}`).addClass('is-invalid');
$(`#${field}Error`).text(errors[field][0]);
```

## Testing & Debugging

### Manual Testing Endpoints

-   Cron job status: `GET /admin/settings/cronjob/status`
-   WhatsApp test: `POST /admin/settings/whatsapp/test-connection`
-   Generate absent: `php artisan attendance:generate-absent 2025-10-24`

### Common Gotchas

1. **Weekend logic**: Auto-absent skips Saturdays (6) and Sundays (0) - check `$date->dayOfWeek`
2. **Grace period**: 30min after `end_time` before marking alpha
3. **Photo paths**: Stored in `storage/app/public/attendance` - must run `php artisan storage:link`
4. **Duplicate prevention**: Attendance has unique constraint on (employee_id, date, check_in_time)
5. **Modal resets**: Always call `$('#form')[0].reset()` and remove `.is-invalid` classes when opening

## File Organization

-   Controllers: Namespaced by role (`Admin\`, `Employee\`)
-   Views: Mirror controller namespaces (`admin/`, `employee/`)
-   Models: Single models folder (no subfolders)
-   Services: Use for external integrations (WhatsApp, etc.)

## Security Considerations

-   Rate limiting: Login (5/min), profile updates (60/min)
-   Middleware: `auth` → `admin` for admin-only routes
-   CSRF: All POST/PUT/DELETE require `@csrf` token
-   SQL injection: Use Eloquent ORM exclusively, no raw queries without bindings

## Recent Changes (Reference)

-   Oct 22, 2025: Migrated `shift_type` → `work_schedule_id` relationship
-   Oct 23, 2025: Added photo toggles for WhatsApp notifications
-   Oct 23, 2025: Added leave notification templates
-   Filter export: Export respects active UI filters (department, position, status)

## When Adding Features

1. **Controller**: Add to appropriate namespace (Admin/Employee)
2. **Routes**: Register in both `web.php` (blade) and `api.php` (JSON)
3. **Migration**: Follow date-prefixed naming convention
4. **View**: Use Sneat components, maintain mobile responsive patterns
5. **JS**: Use jQuery AJAX, SweetAlert2 for confirms, Bootstrap modals for forms
6. **WhatsApp**: Extend templates in `whatsapp_settings` table if adding new notification types
