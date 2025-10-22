<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Karyawans extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'employee_code',
        'nik',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'marital_status',
        'department_id',
        'position_id',
        'join_date',
        'employment_status',
        'work_schedule_id',
        'supervisor_id',
        'salary_base',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'user_id',
        'status',
        'profile_photo'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'salary_base' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Karyawans::class, 'supervisor_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }
}
