<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromArray, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;
    protected $fields;
    public function __construct($data, $fields)
    {
        $this->data = $data;
        $this->fields = $fields;
    }

    public function array(): array
    {
        return $this->data->map(function ($item) {
            return collect($item)->only($this->fields)->toArray();
        })->toArray();
    }

    public function headings(): array
    {
        $customHeaders = [
            'id' => 'ID',
            'nom' => 'Last Name',
            'prenom' => 'First Name',
            'statut' => 'Status',
            'creer' => 'Created At',
            'depart_name' => 'Source',
            'destination_name' => 'Destination',
            'date_debut'=>'Start Date',
            'date_fin'=>'End Date',
            

        ];
        return array_map(function ($field) use ($customHeaders) {
            return $customHeaders[$field] ?? ucfirst(str_replace('_', ' ', $field));
        }, $this->fields);
    }
    public function collection()
    {
        //
    }
}
