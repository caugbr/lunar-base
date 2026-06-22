<?php

namespace App\View\Components;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\View\Component;

class QrCode extends Component
{
    public string $data;
    public int $size;

    public function __construct(string $data, int $size = 200)
    {
        $this->data = $data;
        $this->size = $size;
    }

    public function render()
    {
        $renderer = new ImageRenderer(
            new RendererStyle($this->size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($this->data);

        return view('components.qr-code', [
            'svg' => $svg,
        ]);
    }
}
