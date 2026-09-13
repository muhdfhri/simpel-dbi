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
        $kegiatan = $this->route('kegiatan');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch') || $this->header('X-HTTP-Method-Override') === 'PUT' || $kegiatan !== null;

        $hasExistingLampiran = false;
        if ($kegiatan instanceof \App\Models\KegiatanPembinaan) {
            $hasExistingLampiran = $kegiatan->lampiranList()->count() > 0;
        }

        $lampiranRequired = !$isUpdate && !$hasExistingLampiran;

        return [
            'judul' => ['required', 'string', 'max:255'],
            'desa_ids' => ['required', 'array', 'min:1'],
            'desa_ids.*' => ['exists:desa_binaan,id'],
            'jenis_pembinaan' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal'],
            'jumlah_peserta' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:terjadwal,selesai,dibatalkan'],
            'lokasi' => ['required', 'string', 'max:255'],
            'ringkasan_materi' => ['required', 'string'],
            'lampiran_files' => [$lampiranRequired ? 'required' : 'nullable', 'array'],
            'lampiran_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'desa_ids.required' => 'Desa sasaran wajib dipilih minimal 1 desa.',
            'desa_ids.min' => 'Desa sasaran wajib dipilih minimal 1 desa.',
            'desa_ids.*.exists' => 'Pilihan desa sasaran tidak valid.',
            'jenis_pembinaan.required' => 'Jenis pembinaan wajib diisi.',
            'tanggal.required' => 'Tanggal mulai pelaksanaan wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai pelaksanaan wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'jumlah_peserta.required' => 'Jumlah peserta wajib diisi.',
            'status.required' => 'Status kegiatan wajib dipilih.',
            'lokasi.required' => 'Lokasi pelaksanaan wajib diisi.',
            'ringkasan_materi.required' => 'Ringkasan materi / catatan kegiatan wajib diisi.',
            'lampiran_files.required' => 'Foto dokumentasi / lampiran berkas wajib diunggah.',
            'lampiran_files.min' => 'Upload minimal 1 foto dokumentasi / berkas lampiran.',
            'lampiran_files.*.max' => 'Ukuran file dokumen lampiran maksimal 2MB per file.',
            'lampiran_files.*.mimes' => 'Format file harus berupa JPG, PNG, atau PDF.',
        ];
    }
}
