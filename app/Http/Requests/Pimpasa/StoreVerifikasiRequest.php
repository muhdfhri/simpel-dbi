<?php

namespace App\Http\Requests\Pimpasa;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->value === 'pimpasa' || $this->user()?->role === 'pimpasa';
    }

    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'string', 'in:diverifikasi,minta_perbaikan,ditolak'],
            'checklist_validitas' => ['nullable', 'array'],
            'checklist_validitas.kelengkapan_identitas' => ['nullable', 'boolean'],
            'checklist_validitas.kesesuaian_lokasi' => ['nullable', 'boolean'],
            'checklist_validitas.indikasi_awal_valid' => ['nullable', 'boolean'],
            'catatan' => ['required_if:keputusan,minta_perbaikan,ditolak', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'keputusan.required' => 'Pilihlah salah satu keputusan verifikasi.',
            'keputusan.in' => 'Keputusan verifikasi tidak valid.',
            'catatan.required_if' => 'Catatan alasan wajib diisi jika keputusan adalah Minta Perbaikan atau Ditolak.',
        ];
    }
}
