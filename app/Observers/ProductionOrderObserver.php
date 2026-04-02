<?php

namespace App\Observers;

use App\Models\ProductionOrder;
use App\Http\Controllers\NotificationController;

class ProductionOrderObserver
{
    /**
     * SPK baru dibuat — notif ke semua anggota tim.
     */
    public function created(ProductionOrder $order): void
    {
        if (!$order->team_id) return;

        $tipeLabel = $order->isCorporate() ? '[CORPORATE] ' : '';
        $url = url('/spk/' . $order->id);

        NotificationController::sendToTeam(
            $order->team_id,
            $tipeLabel . 'SPK Baru: ' . $order->nomor_spk,
            $order->nama_customer . ' — ' . $order->nama_barang,
            $url
        );
    }

    /**
     * SPK diupdate — notif jika status berubah.
     */
    public function updated(ProductionOrder $order): void
    {
        if (!$order->wasChanged('status') || !$order->team_id) return;

        $url = url('/spk/' . $order->id);

        NotificationController::sendToTeam(
            $order->team_id,
            'Status SPK Berubah: ' . $order->nomor_spk,
            'Status: ' . $order->status_label . ' — ' . $order->nama_barang,
            $url
        );
    }
}
