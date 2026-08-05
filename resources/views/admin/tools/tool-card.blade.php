        <div class="tools-card">
            <div class="tools-card-header">
                @if($icon)
                <div class="tools-card-icon">
                    <x-dynamic-component component="lucide-{{ $icon }}" class="lucid-icon" />
                </div>
                @endif
                <h4 class="tools-card-title">{{ $title }}</h4>
            </div>
            <div class="tools-card-body">
                <p class="tools-card-desc">
                    {{ $text }}
                </p>
            </div>
            <div class="tools-card-footer">
                <a href="{{ $buttonTarget }}" class="admin-btn admin-btn-secondary">
                    {{ $buttonLabel }}
                </a>
            </div>
        </div>
