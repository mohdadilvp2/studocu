<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserQuestion;

class Question extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'answer',
    ];

    public static function totalQuestions()
    {
        return self::count();
    }
    public function users()
    {
        return $this->hasMany(UserQuestion::class, 'question_id', 'id');
    }
}
