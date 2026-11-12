<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;
use ZipArchive;

class ValidZipFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile
            || ! $value->isValid()
            || strtolower($value->getClientOriginalExtension()) !== 'zip'
        ) {
            $fail('The :attribute must be a valid ZIP file.');

            return;
        }

        if (in_array($value->getClientMimeType(), [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-compressed',
            'application/x-zip',
        ], true)) {
            return;
        }

        $path = $value->getRealPath();
        $archive = new ZipArchive;

        if ($path === false || $archive->open($path) !== true) {
            $fail('The :attribute must be a valid ZIP file.');

            return;
        }

        $archive->close();
    }
}
