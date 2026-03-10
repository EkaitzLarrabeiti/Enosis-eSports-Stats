@extends('layouts.app')

@section('title', 'Iniciar sesion | Enosis eSports')
@section('mainClass', 'w-full min-h-screen flex items-center justify-center p-4')

@section('content')
    <div class="bg-black/50 backdrop-blur-sm p-8 border border-gray-800/30 rounded-xl shadow-lg w-full max-w-md relative z-10">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Login</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block mb-1 font-medium text-white">Correo</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
                @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block mb-1 font-medium text-white">Contrasena</label>
                <input type="password" name="password" id="password" required class="w-full px-3 bg-white/25 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
                @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Entrar</button>
        </form>

        <p class="mt-4 text-center text-gray-400">
            Primera vez? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Crea tu cuenta</a>
        </p>
    </div>
@endsection
