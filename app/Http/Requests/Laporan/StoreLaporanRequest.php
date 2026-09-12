<?php

namespace App\Http\Requests\Laporan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Perangkat Desa yang punya desa_id terasosiasi yang boleh buat laporan
        $user = $this->user();
        if (!$user || empty($user->desa_id)) {
            return false;
        }

        $roleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
        return $roleVal === 'desa';
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('laporan');
        $hasExistingLampiran = false;

        if ($isUpdate) {
            $laporan = $this->route('laporan');
            if ($laporan instanceof \App\Models\Laporan) {
                $hasExistingLampiran = $laporan->lampiranList()->exists();
            }
        }

        $lampiranRules = ($isUpdate && $hasExistingLampiran)
            ? ['nullable', 'array', 'max:2']
            : ['required', 'array', 'min:1', 'max:2'];

        return [
            'kategori_id' => ['required', 'exists:kategori_laporans,id'],
            'judul' => ['required', 'string', 'max:150'],
            'tanggal_kejadian' => ['required', 'date', 'before_or_equal:tomorrow'],
            'lokasi_detail' => ['required', 'string', 'max:100'],
            'kronologi' => ['required', 'string'],
            'estimasi_jumlah_orang' => ['required', 'integer', 'min:1'],
            'lampiran' => $lampiranRules,
            'lampiran.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'], // Max 2MB (2048 KB)
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori laporan wajib dipilih.',
            'judul.required' => 'Judul laporan wajib diisi.',
            'tanggal_kejadian.required' => 'Tanggal kejadian wajib diisi.',
            'tanggal_kejadian.before_or_equal' => 'Tanggal kejadian tidak boleh di masa mendatang.',
            'lokasi_detail.required' => 'Lokasi detail (Dusun/RT/RW) wajib diisi.',
            'kronologi.required' => 'Kronologi kejadian wajib diisi.',
            'estimasi_jumlah_orang.required' => 'Estimasi jumlah orang yang terlibat wajib diisi.',
            'estimasi_jumlah_orang.min' => 'Estimasi jumlah orang minimal 1 orang.',
            'lampiran.required' => 'Berkas dokumen/lampiran bukti pendukung wajib diunggah.',
            'lampiran.min' => 'Wajib melampirkan minimal 1 berkas dokumen bukti pendukung.',
            'lampiran.max' => 'Maksimal 2 file lampiran dalam satu pengajuan.',
            'lampiran.*.max' => 'Ukuran setiap file lampiran maksimal 2MB.',
            'lampiran.*.mimes' => 'Format file lampiran harus berupa JPG, PNG, atau PDF.',
        ];
    }
}
