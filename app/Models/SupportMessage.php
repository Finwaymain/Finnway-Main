<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $table = 'support_messages';

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'sender_type',
        'sender_name',
        'message',
        'attachment',
        'is_read',
    ];

    protected $casts = [
        'ticket_id' => 'integer',
        'sender_id' => 'integer',
        'is_read' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
