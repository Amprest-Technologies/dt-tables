<div class="my-4 overflow-x-auto rounded-md border border-gray-200 bg-white">
    <table class="docs-table">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $text)
                        <td>{{ $cell($text) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
