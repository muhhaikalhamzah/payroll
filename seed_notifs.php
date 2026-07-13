<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Notifications\Notification;

class DummyNotification extends Notification
{
    public $title;
    public $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}

try {
    $user = User::first();
    if ($user) {
        $user->notify(new DummyNotification('Cuti Disetujui', 'Pengajuan cuti tahunan Anda telah disetujui oleh HR.'));
        $user->notify(new DummyNotification('Gaji Telah Ditransfer', 'Gaji bulan ini telah berhasil ditransfer ke rekening Anda.'));
        echo "Successfully created 2 dummy notifications for user " . $user->name . "!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
