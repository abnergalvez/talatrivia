<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Datos básicos
            $table->string('name');
            $table->string('email')->unique();

            // Autenticación por contraseña
            $table->string('password');

            // Token de API tipo "personal token"
            $table->string('api_token', 80)->unique()->nullable();

            // Opcional: si quieres asignar un rol directo
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();

            // Para login tipo "remember me" (opcional pero recomendado)
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
