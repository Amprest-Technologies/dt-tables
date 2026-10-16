<div>
    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
    @if($field['kind'] === 'select')
        <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="@error($field['error']) border-red-500! @enderror">
            @foreach($field['options'] as $value => $label)
                <option value="{{ $value }}" @selected((string) $field['value'] === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $field['name'] }}" type="text" name="{{ $field['name'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['placeholder'] }}" class="@error($field['error']) border-red-500! @enderror" autocomplete="off">
    @endif
    @error($field['error'])
        <small class="mt-1 block text-red-700">{{ $message }}</small>
    @enderror
</div>
