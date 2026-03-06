@extends('layouts.app')

@section('title', 'Panel administrador | Enosis eSports')
@section('mainClass', 'w-full max-w-6xl mx-auto p-4 md:p-6 space-y-4')

@section('content')
    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Panel administrador</h1>
                <p class="text-gray-300">Gestion de usuarios</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 rounded hover:bg-red-700">Cerrar sesion</button>
            </form>
        </div>
    </section>

    @if(session('status'))
        <section class="bg-green-700/80 border border-green-500 rounded p-3">{{ session('status') }}</section>
    @endif

    @if($errors->any())
        <section class="bg-red-700/80 border border-red-500 rounded p-3">{{ $errors->first() }}</section>
    @endif

    <section class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-3">Crear manager</h2>
        <form action="{{ route('admin.managers.create') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Nombre" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
            <input type="email" name="email" placeholder="Correo" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
            <input type="password" name="password" placeholder="Contrasena" class="px-3 py-2 rounded bg-white/10 border border-gray-700">
            <button type="submit" class="md:col-span-3 px-4 py-2 bg-blue-700 rounded hover:bg-blue-800">Crear manager</button>
        </form>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold mb-3">Managers</h3>
            <div class="space-y-2">
                @forelse($managers as $manager)
                    <p class="bg-gray-900/80 rounded px-3 py-2">{{ $manager->name }} <span class="text-gray-400">({{ $manager->email }})</span></p>
                @empty
                    <p class="text-gray-300">Todavia no hay managers.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-black/50 backdrop-blur-sm p-4 md:p-6 border border-gray-800/30 rounded-xl shadow-lg">
            <h3 class="text-lg font-semibold mb-3">Pilotos</h3>
            <div class="space-y-2">
                @forelse($drivers as $driver)
                    <p class="bg-gray-900/80 rounded px-3 py-2">{{ $driver->name }} <span class="text-gray-400">({{ $driver->email }})</span></p>
                @empty
                    <p class="text-gray-300">Todavia no hay pilotos.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
