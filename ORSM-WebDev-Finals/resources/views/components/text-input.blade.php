@props(['disabled' => false])

<input
    @disabled($disabled)
    {{
        $attributes->merge([
            'class' => 'bg-white text-gray-800 placeholder:text-gray-400 border border-gray-200 focus:border-indigo-400 focus:ring-indigo-400 rounded-md shadow-sm'
        ])
    }}
>
