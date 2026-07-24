<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProduksiBaruEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pesan;

    public function __construct(Pesan $pesan)
    {
        $this->pesan = $pesan;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('produksi-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'produksi.baru';
    }
}
