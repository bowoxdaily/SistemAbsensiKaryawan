<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'notes',
        'photo_in',
        'photo_out',
        'location_in',
        'location_out',
        'late_minutes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s',
        'late_minutes' => 'integer',
    ];

    /**
     * Relasi ke Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
