@php
    $assetId = $attr['id'] ?? ($attr['src'] ?? md5(serialize($attr) . $content));
@endphp

@onceAsset($assetId)
    <script @foreach(collect($attr)->except('id') as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
        {!! $content !!}
    </script>
@endonceAsset
