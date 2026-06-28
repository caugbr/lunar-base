@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ativar autenticação de dois fatores</h1>

    <p>Escaneie o QR code abaixo com seu aplicativo autenticador (Google Authenticator, Authy, etc.):</p>

    <div class="qr-code">
        {!! \BaconQrCode\Renderer\ImageRenderer::class
            ? (new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
              ))->render($qrCodeUrl)
            : 'QR Code não disponível' !!}
    </div>

    <p>Ou insira manualmente esta chave:</p>
    <code>{{ $secret }}</code>

    <form method="POST" action="{{ route('two-factor.setup') }}">
        @csrf

        <div>
            <label for="code">Código de verificação (6 dígitos)</label>
            <input type="text" name="code" id="code" maxlength="6" required autofocus>
            @error('code')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit">Confirmar e ativar</button>
    </form>

    <form method="POST" action="{{ route('two-factor.cancel') }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-cancel">Cancelar</button>
    </form>
</div>
@endsection
