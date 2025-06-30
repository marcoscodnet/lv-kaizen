<?php

namespace App\Traits;

trait SanitizesInput
{
    public function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeValue'], $input);
        }

        return $this->sanitizeValue($input);
    }

    protected function sanitizeValue($value)
    {
        if (is_string($value)) {
            return strip_tags(trim($value));
        }

        return $value;
    }
}
