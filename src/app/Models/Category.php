<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // Knowledge との多対多関係
    public function knowledges()
    {
        return $this->belongsToMany(Knowledge::class, 'knowledge_category');
    }
}
