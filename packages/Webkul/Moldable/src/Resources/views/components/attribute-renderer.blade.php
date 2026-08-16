<div class="moldable-attribute-field space-y-1.5" data-code="{{ $attribute->code }}" data-type="{{ $attribute->type }}">
    <!-- Attribute Label -->
    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
        {{ $attribute->name }}
        @if($attribute->is_required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @php
        $val = is_array($value) ? $value : json_decode($value, true) ?? $value;
        $reqAttr = $attribute->is_required ? 'required' : '';
        $uniqAttr = $attribute->is_unique ? 'data-unique=1' : '';
        $valRule = $attribute->validation ? 'data-validation='.$attribute->validation : '';
    @endphp

    <!-- MOLD-029: Text & Textarea Renderer -->
    @if($attribute->type === 'text')
        <input
            type="text"
            name="{{ $attribute->code }}"
            value="{{ is_string($val) ? $val : '' }}"
            placeholder="{{ $attribute->name }}"
            {{ $reqAttr }} {{ $uniqAttr }} {{ $valRule }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        />
    @elseif($attribute->type === 'textarea')
        <textarea
            name="{{ $attribute->code }}"
            rows="3"
            placeholder="{{ $attribute->name }}"
            {{ $reqAttr }} {{ $valRule }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        >{{ is_string($val) ? $val : '' }}</textarea>

    <!-- MOLD-030: Numeric & Price Renderer -->
    @elseif(in_array($attribute->type, ['price', 'number', 'decimal']))
        <div class="relative">
            @if($attribute->type === 'price')
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">$</span>
            @endif
            <input
                type="number"
                step="0.01"
                name="{{ $attribute->code }}"
                value="{{ is_numeric($val) ? $val : '' }}"
                placeholder="0.00"
                {{ $reqAttr }} {{ $uniqAttr }} {{ $valRule }}
                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white {{ $attribute->type === 'price' ? 'pl-7' : '' }}"
            />
        </div>

    <!-- MOLD-031: Selection Renderer (Select, Multiselect, Checkbox, Boolean) -->
    @elseif($attribute->type === 'select')
        <select name="{{ $attribute->code }}" {{ $reqAttr }} class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            <option value="">-- Select {{ $attribute->name }} --</option>
            @foreach($attribute->options as $option)
                <option value="{{ $option->id }}" {{ $val == $option->id ? 'selected' : '' }}>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    @elseif(in_array($attribute->type, ['multiselect', 'checkbox']))
        <select name="{{ $attribute->code }}[]" multiple {{ $reqAttr }} class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            @foreach($attribute->options as $option)
                @php
                    $selectedArray = is_array($val) ? $val : explode(',', (string)$val);
                @endphp
                <option value="{{ $option->id }}" {{ in_array($option->id, $selectedArray) ? 'selected' : '' }}>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    @elseif($attribute->type === 'boolean')
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="{{ $attribute->code }}" value="1" {{ $val ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-xs text-gray-700 dark:text-gray-300">Enabled</span>
        </label>

    <!-- MOLD-032: Date & Datetime Renderer -->
    @elseif($attribute->type === 'date')
        <input
            type="date"
            name="{{ $attribute->code }}"
            value="{{ is_string($val) ? $val : '' }}"
            {{ $reqAttr }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        />
    @elseif($attribute->type === 'datetime')
        <input
            type="datetime-local"
            name="{{ $attribute->code }}"
            value="{{ is_string($val) ? $val : '' }}"
            {{ $reqAttr }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        />

    <!-- MOLD-033: Relationship / Lookup Renderer -->
    @elseif($attribute->type === 'lookup')
        <select name="{{ $attribute->code }}" {{ $reqAttr }} class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            <option value="">-- Select {{ ucfirst($attribute->lookup_type ?? 'Record') }} --</option>
            @if(isset($attribute->lookup_options))
                @foreach($attribute->lookup_options as $lookupItem)
                    <option value="{{ $lookupItem->id }}" {{ $val == $lookupItem->id ? 'selected' : '' }}>
                        {{ $lookupItem->name ?? $lookupItem->title ?? 'ID: '.$lookupItem->id }}
                    </option>
                @endforeach
            @endif
        </select>

    <!-- MOLD-034: Contact Renderer (Email, Phone, Address) -->
    @elseif($attribute->type === 'email')
        <input
            type="email"
            name="{{ $attribute->code }}"
            value="{{ is_array($val) ? ($val['value'] ?? '') : (is_string($val) ? $val : '') }}"
            placeholder="example@domain.com"
            {{ $reqAttr }} {{ $uniqAttr }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        />
    @elseif($attribute->type === 'phone')
        <input
            type="tel"
            name="{{ $attribute->code }}"
            value="{{ is_array($val) ? ($val['value'] ?? '') : (is_string($val) ? $val : '') }}"
            placeholder="+1 555-0199"
            {{ $reqAttr }} {{ $uniqAttr }}
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
        />
    @elseif($attribute->type === 'address')
        @php
            $address = is_array($val) ? $val : ['address' => (string)$val];
        @endphp
        <div class="space-y-2 rounded-lg border border-gray-200 dark:border-gray-800 p-3 bg-gray-50/50 dark:bg-gray-800/40">
            <input type="text" name="{{ $attribute->code }}[address]" value="{{ $address['address'] ?? '' }}" placeholder="Street Address" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="{{ $attribute->code }}[city]" value="{{ $address['city'] ?? '' }}" placeholder="City" class="rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                <input type="text" name="{{ $attribute->code }}[state]" value="{{ $address['state'] ?? '' }}" placeholder="State" class="rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <input type="text" name="{{ $attribute->code }}[postcode]" value="{{ $address['postcode'] ?? '' }}" placeholder="Postal Code" class="rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                <input type="text" name="{{ $attribute->code }}[country]" value="{{ $address['country'] ?? '' }}" placeholder="Country" class="rounded-lg border border-gray-300 dark:border-gray-700 p-2 text-xs bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
            </div>
        </div>

    <!-- MOLD-035: File & Image Renderer (Upload, Preview, Remove) -->
    @elseif(in_array($attribute->type, ['file', 'image']))
        <div class="space-y-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-3 text-center bg-gray-50/30 dark:bg-gray-800/20">
            @if(!empty($val))
                <div class="flex items-center justify-between p-2 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        @if($attribute->type === 'image')
                            <img src="{{ asset('storage/' . $val) }}" alt="Preview" class="h-8 w-8 object-cover rounded" />
                        @else
                            <span class="icon-paperclip text-sm text-gray-500"></span>
                        @endif
                        <span class="text-xs font-mono truncate max-w-[150px]">{{ basename((string)$val) }}</span>
                    </div>
                    <button type="button" onclick="this.closest('.moldable-attribute-field').querySelector('input[type=file]').value=''; this.parentElement.remove();" class="text-xs text-red-500 font-semibold hover:underline">
                        [X] Remove
                    </button>
                </div>
            @endif

            <input
                type="file"
                name="{{ $attribute->code }}"
                {{ $attribute->type === 'image' ? 'accept=image/*' : '' }}
                {{ $reqAttr }}
                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-300"
            />
        </div>
    @endif
</div>
