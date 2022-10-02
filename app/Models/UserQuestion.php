<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestion extends Model
{
    use HasFactory;
    // Status column values
    const STATUS_INCORRECT = 0;
    const STATUS_CORRECT = 1;
    const STATUS_TEXT_MAPPING = [
        0 => 'Incorrect',
        1 => 'Correct'
    ];
    protected $fillable = [
        'question_id',
        'status',
        'user_id'
    ];
    public static function getStatusText($status)
    {
        return self::STATUS_TEXT_MAPPING[$status] ?? "Not answered";
    }
}
