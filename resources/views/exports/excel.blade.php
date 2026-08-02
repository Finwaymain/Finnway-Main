
<table>
    <thead>
        <tr>
            @foreach($fields as $field)
            <th>{{ucfirst(str_replace('_', ' ', $field)) }}</th>
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