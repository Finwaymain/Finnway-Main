<?php
namespace App\Exports;

use App\Models\UserApp;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class UserExport implements FromCollection,WithHeadings
{
    /**
     * @return array
     */
    public function headings():array{
        return[
            'Sr. No.',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'statut',
            'Address'
        ];
    }
    public function collection()
    {
        return UserApp::all();
    }
}