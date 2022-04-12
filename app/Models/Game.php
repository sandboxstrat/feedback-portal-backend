<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Game extends Model
{
    use Uuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'name', 'url', 'website', 'publisher', 'publisher_website', 'developer','developer_website','description','confirmation','background_image','logo', 'options_background_image','game_page_active','active','created_by','last_edited_by','feedback_page'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}