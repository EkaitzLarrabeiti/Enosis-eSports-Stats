@extends('layouts.app')

@section('title', 'Registro | Enosis eSports')
@section('hideHeader', true)
@section('mainClass', 'w-full min-h-screen flex items-center justify-center p-4')

@section('content')
    <div class="bg-black/50 backdrop-blur-sm p-8 border border-gray-800/30 rounded-xl shadow-lg w-full max-w-md relative z-10">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Enosis eSports Stats</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block mb-1 font-medium text-white">Nombre</label>
                <input type="text" name="name" id="name" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="nickname" class="block mb-1 font-medium text-white">Nickname</label>
                <input type="text" name="nickname" id="nickname" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="email" class="block mb-1 font-medium text-white">Correo</label>
                <input type="email" name="email" id="email" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="password" class="block mb-1 font-medium text-white">Contrasena</label>
                <input type="password" name="password" id="password" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div>
                <label for="password_confirmation" class="block mb-1 font-medium text-white">Confirmar contrasena</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Crear</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Entrar</a>
        </p>
    </div>
@endsection
