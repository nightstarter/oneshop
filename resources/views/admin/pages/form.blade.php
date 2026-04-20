@extends('admin.layout')

@section('title', $isEdit ? __('shop.admin.edit_page') : __('shop.admin.new_page'))

@section('content')
<h1 class="h3 mb-3">{{ $isEdit ? __('shop.admin.edit_page') : __('shop.admin.new_page') }}</h1>

<form method="POST" action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="card">
    <div class="card-body row g-3">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Title --}}
        <div class="col-md-8">
            <label class="form-label" for="title">{{ __('forms.title') }}</label>
            <input
                id="title"
                name="title"
                class="form-control"
                value="{{ old('title', $page->title) }}"
                required
                maxlength="191"
            >
        </div>

        {{-- Slug --}}
        <div class="col-md-4">
            <label class="form-label" for="slug">{{ __('forms.slug') }}</label>
            <div class="input-group">
                <span class="input-group-text text-muted" style="font-size:.85rem">/info/</span>
                <input
                    id="slug"
                    name="slug"
                    class="form-control"
                    value="{{ old('slug', $page->slug) }}"
                    required
                    maxlength="191"
                    pattern="[a-z0-9\-]+"
                    title="{{ __('forms.slug_pattern_hint') }}"
                >
            </div>
            <div class="form-text">{{ __('forms.slug_hint') }}</div>
        </div>

        {{-- Content (TinyMCE) --}}
        <div class="col-12">
            <label class="form-label" for="content">{{ __('forms.content') }}</label>
            {{-- Content is entered by authenticated admins only. Rendered as raw HTML on frontend.
                 Do NOT expose this field to untrusted users. --}}
            <textarea
                id="content"
                name="content"
                class="form-control"
                rows="4"
            >{{ old('content', $page->content) }}</textarea>
        </div>

        {{-- SEO: meta title --}}
        <div class="col-md-6">
            <label class="form-label" for="meta_title">{{ __('forms.meta_title') }}</label>
            <input
                id="meta_title"
                name="meta_title"
                class="form-control"
                value="{{ old('meta_title', $page->meta_title) }}"
                maxlength="191"
                placeholder="{{ __('forms.meta_title_placeholder') }}"
            >
        </div>

        {{-- SEO: meta description --}}
        <div class="col-md-6">
            <label class="form-label" for="meta_description">{{ __('forms.meta_description') }}</label>
            <input
                id="meta_description"
                name="meta_description"
                class="form-control"
                value="{{ old('meta_description', $page->meta_description) }}"
                maxlength="320"
                placeholder="{{ __('forms.meta_description_placeholder') }}"
            >
        </div>

        {{-- Published toggle --}}
        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="is_published" value="0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="is_published"
                    value="1"
                    id="is_published"
                    @checked(old('is_published', $page->is_published ?? false))
                >
                <label class="form-check-label" for="is_published">
                    {{ __('forms.page_published') }}
                </label>
                @if($isEdit && $page->published_at)
                    <div class="form-text">{{ __('shop.admin.published_at') }}: {{ $page->published_at->format('d.m.Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary">{{ __('buttons.save') }}</button>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">{{ __('buttons.back') }}</a>
    </div>
</form>
@endsection

@push('scripts')
{{-- TinyMCE self-hosted (GPL). Assets served from /js/tinymce/ (public/js/tinymce/).
     No CDN, no API key required. Re-copy after npm install: node scripts/copy-tinymce.cjs --}}
<script src="/js/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    license_key: 'gpl',
    plugins: 'lists link code table image',
    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
    height: 450,
    menubar: false,
    branding: false,
    promotion: false,
    setup: function (editor) {
        editor.on('change input', function () {
            editor.save();
        });
    }
});

@if(! $isEdit)
{{-- Auto-generate slug from title only when creating a new page --}}
document.getElementById('title').addEventListener('input', function () {
    const slugField = document.getElementById('slug');
    if (slugField.dataset.touched) return;

    slugField.value = this.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')   // remove diacritics
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
});

document.getElementById('slug').addEventListener('input', function () {
    this.dataset.touched = '1';
});
@endif
</script>
@endpush
