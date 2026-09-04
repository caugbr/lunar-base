@php
    $assetId = $attr['id'] ?? ($attr['href'] ?? md5(serialize($attr)));
@endphp

@onceAsset($assetId)
    <link @foreach(collect($attr)->except('id') as $k => $v) {{ $k }}="{{ $v }}" @endforeach />
@endonceAsset
