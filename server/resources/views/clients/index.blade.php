@extends('template')

@section('title', 'Clients')

@section('content')
<div class="p-6 bg-white rounded-lg">
  <h1 class="mb-6 text-3xl font-bold text-black">Clients</h1>
  <table class="w-full">
    <thead>
      <tr>
        <th class="p-2 text-left">Nom</th>
        <th class="p-2 text-left">Email</th>
        <th class="p-2 text-left">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($clients as $client)
      <tr>
        <td class="p-2">{{ $client->name }}</td>
        <td class="p-2">{{ $client->email }}</td>
        <td class="p-2">
          <form action="{{ route('clients.destroy', $client->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-500">Supprimer</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection