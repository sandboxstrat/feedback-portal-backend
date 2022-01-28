<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Feedback extends Model
{
    use Uuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'user_id', 'user_name', 'ip_address', 'ip_country', 'ip_region', 'ip_city','link','upload_image','feedback', 'feedback_option', 'game_id', 'viewed'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}