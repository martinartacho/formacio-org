<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Manager (resources\views\manager\dashboard.blade.php)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    <h4 class="text-lg font-medium text-gray-900 mb-2">Comprobar per que hem arribar aquí</h4>
    <pre class="text-xs bg-gray-100 p-2">{{ var_export($stats ?? 'NO STATS', true) }}</pre>
            
            <x-dashboard.manager
                :stats="$stats"
            />

        </div>
    </div>
</x-app-layout>
