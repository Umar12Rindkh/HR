@extends('gm.templates.index')

@section('page-gm')
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-semibold mb-4">KPI List</h2>
        @if (session('success'))
            <div class="bg-green-500 text-white p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <a href="{{ url('general-manager/add-kpi') }}" class="bg-blue-500 text-white py-2 px-4 rounded mb-4 inline-block">Add
            New KPI</a>
        <table class="table-auto w-full border-collapse border border-gray-200">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Nama</th>
                    <th class="border border-gray-300 p-2">Jabatan</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kpis->groupBy('users.name') as $kpiGroup)
                    <tr>
                        <td class="border border-gray-300 p-2">{{ $kpiGroup->first()->users->name }}</td>
                        <td class="border border-gray-300 p-2">{{ $kpiGroup->first()->users->jabatan }}</td>
                        <td class="border border-gray-300 p-2">
                            <a href="{{ route('kpi.detailkpi', $kpiGroup->first()->users->name) }}"
                                class="text-blue-500">Detail</a>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
@endsection
