<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public const TYPES = ['bank_transfer' => 'تحويل بنكي'];   // the only active method in this release

    protected $fillable = ['type', 'name', 'bank_name', 'beneficiary_name', 'iban', 'account_number', 'instructions', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** What a student may see (after accepting the agreement). */
    public function studentDetails(): array
    {
        return [
            'type' => $this->type,
            'bank_name' => $this->bank_name,
            'beneficiary_name' => $this->beneficiary_name,
            'iban' => $this->iban,
            'account_number' => $this->account_number,
            'instructions' => $this->instructions,
        ];
    }
}
