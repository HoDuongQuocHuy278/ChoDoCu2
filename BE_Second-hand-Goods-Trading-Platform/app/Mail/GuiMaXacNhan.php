<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuiMaXacNhan extends Mailable
{
    use Queueable, SerializesModels;

    public $ma;

    public function __construct($ma)
    {
        $this->ma = $ma;
    }

    public function build()
    {
       return $this->subject('Mã xác minh tài khoản')
            ->view('view_1')  // đúng tên folder.file
            ->with(['ma' => $this->ma]);
    }
}
