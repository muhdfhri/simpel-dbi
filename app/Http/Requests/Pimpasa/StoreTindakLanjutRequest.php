<?php

namespace App\Http\Requests\Pimpasa;

use Illuminate\Foundation\Http\FormRequest;

class StoreTindakLanjutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->value === 'pimpasa' || $this->user()?->role === 'pimpasa';
    }

    public function rules(): array
    {
        return [
            'nomor_registrasi' => ['nullable', 'string', 'max:100'],
            'seksi_penanggung_jawab' => ['required', 'string', 'in:inteldak,inteldakim'],
            'bentuk_intervensi' => ['required', 'string', 'in:sosialisasi,pemeriksaan_lapangan,operasi_gabungan,proses_hukum'],
            'ringkasan_hasil' => ['required', 'string', 'max:2000'],
            'is_selesai' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'seksi_penanggung_jawab.required' => 'Pilihlah seksi penanggung jawab UPT.',
            'bentuk_intervensi.required' => 'Pilihlah bentuk intervensi lapangan.',
            'ringkasan_hasil.required' => 'Isikan ringkasan hasil tindak lanjut UPT.',
        ];
    }
}
