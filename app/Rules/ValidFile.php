<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidFile implements Rule
{

    public function passes($attribute, $value)
    {
        $allowedTypes = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'pdf', 'txt', 'doc', 'xls', 'xlsx', 'csv', 'jfif','docx', 'docs','webp'];
        $fileExtension = pathinfo($value->getClientOriginalName(), PATHINFO_EXTENSION);

        // Check if the file is an image or one of the specified document types
        $isImage = in_array($fileExtension, ['jpeg', 'png', 'jpg', 'gif', 'svg']);

        // Apply max file size validation only for images
        if ($isImage) {
            if (!$this->validateImage($value)) {
                $this->message = 'The file must be a valid image.';
                return false;
            }

            // if (!$this->validateMaxSize($value)) {
            //     $this->message = 'The image size must not exceed 2048 kilobytes.';
            //     return false;
            // }
        }

        return in_array($fileExtension, $allowedTypes);
    }

    protected function validateImage($value)
    {
        return in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml']);
    }

    protected function validateMaxSize($value)
    {
        // Adjust the maximum size as needed for images
        return $value->getSize() <= 2048; // Maximum size for images (in kilobytes)
    }

    public function message()
    {
        return $this->message ?? 'The file must be an image or one of the specified document types.';
    }

}
