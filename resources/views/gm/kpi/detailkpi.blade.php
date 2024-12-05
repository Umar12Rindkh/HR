@extends('gm.templates.index')

@section('page-gm')
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-semibold mb-4">KPI {{ $userName }} ({{ $jabatan }}) </h2>
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
                    <th class="border border-gray-300 p-2">Desc</th>
                    <th class="border border-gray-300 p-2">Bobot</th>
                    <th class="border border-gray-300 p-2">Target</th>
                    <th class="border border-gray-300 p-2">Realisasi</th>
                    <th class="border border-gray-300 p-2">Skor</th>
                    <th class="border border-gray-300 p-2">Final Skor</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kpis as $kpi)
                    <tr>
                        <td class="border border-gray-300 p-2">{{ \Illuminate\Support\Str::limit($kpi->desc, 30) }}</td>
                        <td class="border border-gray-300 p-2">{{ number_format($kpi->bobot, 0, '.', '') }}</td>
                        <td class="border border-gray-300 p-2">{{ number_format($kpi->target, 0, '.', '') }}</td>
                        <td class="border border-gray-300 p-2">{{ number_format($kpi->realisasi, 0, '.', '') }}</td>
                        <td class="border border-gray-300 p-2">{{ ($kpi->target * $kpi->realisasi) / 100 }}</td>
                        <td class="border border-gray-300 p-2">{{ $kpi->bobot * (($kpi->target * $kpi->realisasi) / 100 ) / 100 }}</td>
                        <td class="border border-gray-300 p-2">
                            <a href="{{ route('kpi.edit', $kpi->id) }}" class="text-blue-500">Edit</a>
                            <form action="{{ route('kpi.destroy', $kpi->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 ml-4">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
