@php
    /** @var array<string, array{label: string, image: string}> $options */
    $options = $getViewData()['options'] ?? [];
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @if (blank($options))
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $getViewData()['empty'] ?? __('Choose a section type first.') }}</p>
    @else
        <div
            x-data="{ state: $wire.$entangle(@js($statePath)) }"
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3"
            role="radiogroup"
        >
            @php($lastGroup = null)
            @foreach ($options as $key => $option)
                @if (filled($option['group'] ?? null) && $option['group'] !== $lastGroup)
                    @php($lastGroup = $option['group'])
                    <p class="col-span-full mt-2 border-b border-gray-200 pb-1 text-sm font-bold text-gray-700 first:mt-0 dark:border-white/10 dark:text-gray-200">{{ $option['group'] }}</p>
                @endif
                <label
                    class="group relative cursor-pointer rounded-xl border bg-white p-2 shadow-sm transition hover:border-primary-400 dark:bg-white/5"
                    x-bind:class="state === @js($key)
                        ? 'border-primary-600 ring-2 ring-primary-500/40 dark:border-primary-500'
                        : 'border-gray-200 dark:border-white/10'"
                >
                    <input
                        type="radio"
                        class="sr-only"
                        value="{{ $key }}"
                        x-model="state"
                        name="{{ $statePath }}-design"
                    />

                    <img
                        src="{{ $option['image'] }}"
                        alt="{{ $option['label'] }}"
                        loading="lazy"
                        class="aspect-[16/10] w-full rounded-lg border border-gray-100 object-cover dark:border-white/10"
                    />

                    <span class="mt-2 flex items-center justify-between gap-2 px-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $option['label'] }}

                        <span
                            x-show="state === @js($key)"
                            x-cloak
                            class="grid size-5 place-items-center rounded-full bg-primary-600 text-white"
                        >
                            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.8 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</x-dynamic-component>
