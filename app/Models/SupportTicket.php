<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'user_type',
        'user_name',
        'user_phone',
        'user_email',
        'user_photo',
        'topic',
        'last_message',
        'last_message_at',
        'last_sender',
        'unread_admin_count',
        'unread_user_count',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('id', 'asc');
    }

    public function customer()
    {
        return $this->belongsTo(UserApp::class, 'user_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'user_id', 'id');
    }
}
