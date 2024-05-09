<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class HistoryScheduleDateRange implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value):bool
    {
        // If the value is null, it's considered valid
        if ($value === null) {
            return true;
        }

        $today = Carbon::today();
        $nextDay = $today->addDay();
        
        // Convert the input value to a Carbon instance for comparison
        $inputDate = Carbon::createFromFormat('Y-m-d', $value);

        // Check if the input date is smaller than or equal to today's date
        return $inputDate->lte($nextDay);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message():string
    {
        return 'The selected date must be smaller than or equal to yesterday.';
    }
}
