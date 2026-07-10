<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxWords implements Rule
{
    protected int $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function passes($attribute, $value): bool
    {
        $wordCount = str_word_count(strip_tags($value));
        return $wordCount <= $this->max;
    }

    public function message(): string
    {
        return 'The :attribute may not be greater than ' . $this->max . ' words.';
    }
}