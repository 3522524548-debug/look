<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('care_logs'); // 强力重置，防止已存在报错
        Schema::create('care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete();
            $table->date('care_date')->index();
            $table->string('type', 30);
            $table->text('notes')->nullable();
            $table->decimal('weight', 6, 2)->nullable(); 
            $table->decimal('temperature', 4, 1)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('care_logs'); }
};