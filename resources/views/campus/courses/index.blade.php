@extends('campus.shared.layout')

@section('title', __('campus.courses'))
@section('subtitle', __('campus.courses'))

@section('breadcrumbs')
    <li aria-current="page">
        <div class="flex items-center">
            <i class="bi bi-chevron-right text-gray-400 mx-1"></i>
            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{ __('campus.courses') }}</span>
        </div>
    </li>
@endsection

@section('actions')
    
    <x-campus-button href="{{ route('campus.courses.create') }}" variant="header">
        <i class="bi bi-plus-lg me-2"></i>
        {{ __('campus.new_course') }} 
    </x-campus-button>
    
   
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">
        {{ __('campus.courses') }}
    </h1>

    @can('campus.courses.create')
        <a href="{{ route('campus.courses.create') }}"
           class="campus-primary-button">
            {{ __('campus.new_course') }}
        </a>
    @endcan
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                {{ __('campus.title') }}
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                {{ __('campus.season') }}
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                {{ __('campus.category') }}
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                {{ __('campus.status') }}
            </th>
            <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">
                &nbsp;
            </th>
        </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($courses as $course)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('campus.courses.show', $course) }}"
                       class="font-medium text-blue-600 hover:underline">
                        {{ $course->title }}
                    </a>
                </td>
                <td class="px-4 py-3">
                    {{ $course->season?->name ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    {{ $course->category?->name ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    @if($course->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="bi bi-check-circle me-1"></i> {{ __('campus.active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="bi bi-x-circle me-1"></i> {{ __('campus.inactive') }}
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                    <div class="flex space-x-2">
                    @can('campus.courses.edit')
                            <a href="{{ route('campus.courses.edit', $course) }}" 
                                class="text-blue-600 hover:text-blue-900"
                                title="{{ __('campus.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                    @endcan

                    @can('campus.courses.view')
                            <a href="{{ route('campus.courses.show', $course) }}" 
                                class="text-blue-600 hover:text-blue-900"
                                title="{{ __('campus.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                    @endcan

                    @can('campus.courses.delete')
                        <form action="{{ route('campus.courses.destroy', $course) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('{{ __('campus.course_delete_confirmation') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="{{ __('campus.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                    @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                    {{ __('campus.no_courses') }}
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $courses->links() }}
</div>
@endsection
