<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'row_number' => 'integer',
        'order' => 'integer',
        'is_required' => 'boolean',
        'has_remarks' => 'boolean',
        'status' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function values()
    {
        return $this->hasMany(FormSubmissionValue::class);
    }
}
