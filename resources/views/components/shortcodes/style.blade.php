@php
    $assetId = $attr['id'] ?? md5(serialize($attr) . $content);
@endphp

@onceAsset($assetId)
    <style @foreach(collect($attr)->except('id') as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
        {!! $content !!}
    </style>
@endonceAsset
