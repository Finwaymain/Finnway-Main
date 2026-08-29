<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportQuickQuestion extends Model
{
    use HasFactory;

    protected $table = 'support_quick_questions';

    protected $fillable = [
        'user_type',
        'category',
        'question',
        'auto_reply',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUserType($query, $type)
    {
        if (empty($type) || $type === 'all') {
            return $query;
        }
        return $query->whereIn('user_type', [$type, 'all']);
    }
}
