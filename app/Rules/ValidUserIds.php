<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class ValidUserIds implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $userId) {
            if (!User::find($userId)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'One or more selected user IDs are invalid.';
    }
}

