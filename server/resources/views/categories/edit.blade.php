@extends('template')

@section('title', 'Modifier une catégorie')

@section('content')
<div class="max-w-4xl p-6 mx-auto bg-white border-2 border-orange-300 rounded-lg shadow-md">
  <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">Modifier une catégorie</h1>

  <form action="{{ route('categories.update', $category->id) }}" method="post">
    @csrf
    @method('PUT')
    <div class="mb-4">
      <label for="name" class="block mb-2 font-semibold text-black">Nom</label>
      <input type="text" name="name" id="name" class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300" value="{{ $category->name }}">
    </div>
    <button type="submit" class="px-4 py-2 font-semibold text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600">Modifier</button>
  </form>
</div>
@endsection