<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstructorImport implements ToCollection, WithHeadingRow
{
    /**
     * @var Collection<int, array<string, mixed>>
     */
    public Collection $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
