<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use \App\Traits\BelongsToBranch;

    protected $fillable = [
        'company', 'contact_person', 'email', 'phone', 
        'source', 'status', 'notes', 'assigned_to', 'branch_id'
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
