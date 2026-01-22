<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
           {{ __("Benvingut/da, ") }} <strong>{{ Auth::user()->name }}</strong>!}
        </h3>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Estadísticas generales --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        {{ __("Aquí tens una visió general del teu compte.") }}
                    </div>
                </div>
                
                {{-- **AQUÍ INCLUIMOS LAS TARJETAS DE ADMINISTRACIÓN** --}}
                @include('components.admin-dashboard-cards')
                {{-- Fin de la sección de tarjetas de administración --}}
                    
            </div> 
    </div>
</div>
