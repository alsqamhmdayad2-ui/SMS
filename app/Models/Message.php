<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'subject', 'body', 'parent_id'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function parentMessage()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }
}
