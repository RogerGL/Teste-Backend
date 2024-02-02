<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectLog extends Model
{
    protected $fillable = ['redirect_id', 'accessed_at', 'ip_address', 'user_agent'];

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }
}