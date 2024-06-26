<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class RoleExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
	protected $query;

	public function __construct(\Illuminate\Database\Eloquent\Builder $query){
		$this->query = $query;
	}

	public function query(){
		return $this->query;
	}

	public function headings(): array
    {
        return [
            'Title',
        ];
    }

	public function map($row): array{
        return [
			$row->title,
        ];
    }
	
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
