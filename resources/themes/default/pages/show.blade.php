@extends('theme::layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' - ' . config('app.name'))

{{-- SEO meta tags injected into <head> via @stack('head') --}}
@push('head')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endpush

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('shop.home') }}</a></li>
            <li class="breadcrumb-item active">{{ $page->title }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-4">{{ $page->title }}</h1>

            <div class="cms-page-content">
                {{-- Raw HTML content – entered exclusively by authenticated admins.
                     Never render user-submitted content here without prior sanitisation. --}}
                {!! $page->content !!}
            </div>
        </div>
    </div>
@endsection
