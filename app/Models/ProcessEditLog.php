<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessEditLog extends Model
{
    protected $fillable = [
        'production_process_id', 'edited_by',
        'hasil_jadi_before', 'jumlah_reject_before', 'tanggal_selesai_before', 'catatan_before', 'foto_before',
        'hasil_jadi_after',  'jumlah_reject_after',  'tanggal_selesai_after',  'catatan_after',  'foto_after',
        'alasan_edit',
    ];

    protected $casts = [
        'tanggal_selesai_before' => 'date',
        'tanggal_selesai_after'  => 'date',
        'hasil_jadi_before'      => 'decimal:2',
        'hasil_jadi_after'       => 'decimal:2',
        'jumlah_reject_before'   => 'decimal:2',
        'jumlah_reject_after'    => 'decimal:2',
    ];

    public function process()
    {
        return $this->belongsTo(ProductionProcess::class, 'production_process_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // Daftar field yang berubah dalam log ini
    public function getChangedFieldsAttribute(): array
    {
        $changes = [];

        if ($this->hasil_jadi_before != $this->hasil_jadi_after) {
            $changes[] = [
                'field'  => 'Hasil Jadi',
                'before' => number_format($this->hasil_jadi_before, 0, ',', '.'),
                'after'  => number_format($this->hasil_jadi_after, 0, ',', '.'),
            ];
        }
        if ($this->jumlah_reject_before != $this->jumlah_reject_after) {
            $changes[] = [
                'field'  => 'Jumlah Reject',
                'before' => number_format($this->jumlah_reject_before, 0, ',', '.'),
                'after'  => number_format($this->jumlah_reject_after, 0, ',', '.'),
            ];
        }
        if ($this->tanggal_selesai_before != $this->tanggal_selesai_after) {
            $changes[] = [
                'field'  => 'Tanggal Selesai',
                'before' => $this->tanggal_selesai_before?->format('d/m/Y') ?? '-',
                'after'  => $this->tanggal_selesai_after?->format('d/m/Y') ?? '-',
            ];
        }
        if ($this->catatan_before != $this->catatan_after) {
            $changes[] = [
                'field'  => 'Catatan',
                'before' => $this->catatan_before ?? '-',
                'after'  => $this->catatan_after ?? '-',
            ];
        }
        if ($this->foto_after && $this->foto_before != $this->foto_after) {
            $changes[] = [
                'field'  => 'Foto',
                'before' => 'foto lama',
                'after'  => 'foto baru',
            ];
        }

        return $changes;
    }
}
