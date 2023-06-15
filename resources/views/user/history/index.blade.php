@extends('layout.default')

@section('title')
    <title>{{ $user->username }} {{ __('user.torrents') }} - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('users.show', ['username' => $user->username]) }}" class="breadcrumb__link">
            {{ $user->username }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('user.torrents-history') }}
    </li>
@endsection

@section('page', 'page__user-torrents--index')

@section('nav-tabs')
    @include('user.buttons.user')
@endsection

@section('main')
    @livewire('user-torrents', ['userId' => $user->id])
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('user.statistics') }}</h2>
        <dl class="key-value">
            <dt>{{ __('user.total-download') }}</dt>
            <dd>{{ App\Helpers\StringHelper::formatBytes($history->download, 2) }}</dd>
            <dt>{{ __('user.total-download') }} ({{ __('user.credited-download') }})</dt>
            <dd>{{ App\Helpers\StringHelper::formatBytes($history->credited_download, 2) }}</dd>
            <dt>{{ __('user.total-upload') }}</dt>
            <dd>{{ App\Helpers\StringHelper::formatBytes($history->upload, 2) }}</dd>
            <dt>{{ __('user.total-upload') }} ({{ __('user.credited-upload') }})</dt>
            <dd>{{ App\Helpers\StringHelper::formatBytes($history->credited_upload, 2) }}</dd>
        </dl>
    </section>
@endsection