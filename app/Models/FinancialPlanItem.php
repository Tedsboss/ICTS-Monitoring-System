<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialPlanItem extends Model
{
    use HasFactory;

    // FinancialPlanItem.php
    public function saebEntries()
    {
        return $this->hasMany(Saeb::class);
    }

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

}
