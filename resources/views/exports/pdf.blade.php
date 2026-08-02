<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PDF Export</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            vertical-align: top;
        }
    </style>
</head>
@php
$customHeaders = [
'id' => 'ID',
'nom' => 'Last Name',
'prenom' => 'First Name',
'statut'=>'Status',
'creer'=>'Created At',
'depart_name'=>'Source',
'destination_name'=>'Destination',
'date_debut'=>'Start Date',
'date_fin'=>'End Date',
];
@endphp

<body>
    <table>
        <thead>
            <tr>
                @foreach($fields as $field)
                <th style="width: {{ 100 / count($fields) }}%;">{{ $customHeaders[$field] ?? ucfirst(str_replace('_', ' ', $field))  }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                @foreach($fields as $field)
                <td>{{ $item->$field }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>