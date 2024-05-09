<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffRoleRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $authRole = auth()->user()->roles->pluck('name')[0] ?? null;
        
        // Check if the user's role is 'Staff'
        return $authRole === 'Staff';
    }

    public function message()
    {
        return 'Unauthorized: Only Staff users are allowed.';
    }
}
