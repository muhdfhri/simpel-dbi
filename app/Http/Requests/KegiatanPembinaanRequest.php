<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KegiatanPembinaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'desa_id' => ['required', 'exists:desa_binaan,id'],
            'jenis_pembinaan' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jumlah_peserta' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:terjadwal,selesai,dibatalkan'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'ringkasan_materi' => ['required', 'string'],
            'lampiran_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'desa_id.required' => 'Desa sasaran wajib dipilih.',
            'desa_id.exists' => 'Desa sasaran tidak valid.',
            'jenis_pembinaan.required' => 'Jenis pembinaan wajib diisi.',
            'tanggal.required' => 'Tanggal pelaksanaan wajib diisi.',
            'jumlah_peserta.required' => 'Jumlah peserta wajib diisi.',
            'status.required' => 'Status kegiatan wajib dipilih.',
            'ringkasan_materi.required' => 'Ringkasan materi / catatan kegiatan wajib diisi.',
            'lampiran_files.*.max' => 'Ukuran file dokumen lampiran maksimal 2MB per file.',
            'lampiran_files.*.mimes' => 'Format file harus berupa JPG, PNG, atau PDF.',
        ];
    }
}
