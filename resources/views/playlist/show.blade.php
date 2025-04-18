@extends('layout.with-main-and-sidebar')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('playlists.index') }}" class="breadcrumb__link">
            {{ __('playlist.playlists') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ $playlist->name }}
    </li>
@endsection

@section('sidebar')
    @if (auth()->id() === $playlist->user_id || auth()->user()->group->is_modo)
        <section class="panelV2">
            <h2 class="panel__heading">{{ __('common.actions') }}</h2>
            <div class="panel__body">
                <div class="form__group form__group--horizontal" x-data="dialog">
                    <button
                        class="form__button form__button--filled form__button--centered"
                        x-bind="showDialog"
                    >
                        <i class="{{ config('other.font-awesome') }} fa-search-plus"></i>
                        {{ __('playlist.add-torrent') }}
                    </button>
                    <dialog class="dialog" x-bind="dialogElement">
                        <h4 class="dialog__heading">
                            {{ __('playlist.add-to-playlist') }}
                        </h4>
                        <form
                            class="dialog__form"
                            method="POST"
                            action="{{ route('playlist_torrents.massUpsert') }}"
                            x-bind="dialogForm"
                        >
                            @csrf
                            @method('PUT')
                            <p class="form__group">
                                <input
                                    id="playlist_id"
                                    name="playlist_id"
                                    type="hidden"
                                    value="{{ $playlist->id }}"
                                />
                            </p>
                            <p class="form__group">
                                <textarea
                                    id="torrent_urls"
                                    class="form__textarea"
                                    name="torrent_urls"
                                    type="text"
                                    required
                                >
{{ old('torrent_urls') }}</textarea
                                >
                                <label class="form__label form__label--floating" for="torrent_urls">
                                    Torrent IDs/URLs (One per line)
                                </label>
                            </p>
                            <p class="form__group">
                                <button class="form__button form__button--filled">
                                    {{ __('common.add') }}
                                </button>
                                <button
                                    formmethod="dialog"
                                    formnovalidate
                                    class="form__button form__button--outlined"
                                >
                                    {{ __('common.cancel') }}
                                </button>
                            </p>
                        </form>
                    </dialog>
                </div>
                <p class="form__group form__group--horizontal">
                    <a
                        href="{{ route('playlists.edit', ['playlist' => $playlist]) }}"
                        class="form__button form__button--filled form__button--centered"
                    >
                        <i class="{{ config('other.font-awesome') }} fa-edit"></i>
                        {{ __('playlist.edit-playlist') }}
                    </a>
                </p>
                <form
                    action="{{ route('playlists.destroy', ['playlist' => $playlist]) }}"
                    method="POST"
                    x-data="confirmation"
                >
                    @csrf
                    @method('DELETE')
                    <p class="form__group form__group--horizontal">
                        <button
                            x-on:click.prevent="confirmAction"
                            data-b64-deletion-message="{{ base64_encode('Are you sure you want to delete this playlist: ' . $playlist->name . '?') }}"
                            class="form__button form__button--filled form__button--centered"
                        >
                            <i class="{{ config('other.font-awesome') }} fa-trash"></i>
                            {{ __('common.delete') }}
                        </button>
                    </p>
                </form>
            </div>
        </section>
    @endif

    <section class="panelV2">
        <h2 class="panel__heading">{{ __('common.download') }}</h2>
        <div class="panel__body">
            <p class="form__group form__group--horizontal">
                <a
                    href="{{ route('playlist_zips.show', ['playlist' => $playlist]) }}"
                    class="form__button form__button--filled form__button--centered"
                >
                    <i class="{{ config('other.font-awesome') }} fa-download"></i>
                    {{ __('playlist.download-all') }}
                </a>
            </p>
            <p class="form__group form__group--horizontal">
                <a
                    href="{{ route('torrents.index', ['playlistId' => $playlist->id]) }}"
                    class="form__button form__button--filled form__button--centered"
                >
                    <i class="{{ config('other.font-awesome') }} fa-eye"></i>
                    Playlist Torrents List
                </a>
            </p>
        </div>
    </section>
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('common.info') }}</h2>
        <dl class="key-value">
            <div class="key-value__group">
                <dt>{{ __('common.created_at') }}</dt>
                <dd>
                    <time
                        datetime="{{ $playlist->created_at }}"
                        title="{{ $playlist->created_at }}"
                    >
                        {{ $playlist->created_at->diffForHumans() }}
                    </time>
                </dd>
            </div>
            <div class="key-value__group">
                <dt>{{ __('torrent.updated_at') }}</dt>
                <dd>
                    <time
                        datetime="{{ $playlist->updated_at }}"
                        title="{{ $playlist->updated_at }}"
                    >
                        {{ $playlist->updated_at->diffForHumans() }}
                    </time>
                </dd>
            </div>
            @if ($latestPlaylistTorrent !== null)
                <div class="key-value__group">
                    <dt>{{ __('playlist.last-addition-at') }}</dt>
                    <dd>
                        <time
                            datetime="{{ $latestPlaylistTorrent->pivot->created_at }}"
                            title="{{ $latestPlaylistTorrent->pivot->created_at }}"
                        >
                            {{ $latestPlaylistTorrent->pivot->created_at->diffForHumans() }}
                        </time>
                    </dd>
                </div>
            @endif
        </dl>
    </section>
@endsection

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">{{ $playlist->name }}</h2>
        @php
            $tmdb_backdrop = isset($meta->backdrop)
                ? tmdb_image('back_big', $meta->backdrop)
                : 'https://via.placeholder.com/1280x350';
        @endphp

        <div class="playlist__backdrop" style="background-image: url('{{ $tmdb_backdrop }}')">
            <div class="playlist__backdrop-filter">
                <a
                    class="playlist__author-link"
                    href="{{ route('users.show', ['user' => $playlist->user]) }}"
                >
                    <img
                        class="playlist__author-avatar"
                        src="{{ $playlist->user->image ? route('authenticated_images.user_avatar', ['user' => $playlist->user]) : url('img/profile.png') }}"
                        alt="{{ $playlist->user->username }}"
                    />
                </a>
                <p class="playlist__author">
                    <x-user_tag :user="$playlist->user" :anon="false" />
                </p>
                <p class="playlist__description bbcode-rendered">
                    @bbcode($playlist->description)
                </p>
            </div>
        </div>
    </section>
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('torrent.torrents') }}</h2>
        <div class="panel__body playlist__torrents">
            @foreach ($torrents as $torrent)
                <div class="playlist__torrent-container">
                    <x-torrent.card :meta="$torrent->meta" :torrent="$torrent" />
                    @if (auth()->id() === $playlist->user_id || auth()->user()->group->is_modo)
                        <form
                            action="{{ route('playlist_torrents.destroy', ['playlistTorrent' => $torrent->pivot]) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="form__standard-icon-button">
                                <i class="{{ config('other.font-awesome') }} fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        {{ $torrents->links('partials.pagination') }}
    </section>
    <livewire:comments :model="$playlist" />
@endsection
