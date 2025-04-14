@extends('layout.with-main')

@section('title')
    <title>Collectibles - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('collectibles.index') }}" class="breadcrumb__link">Collectibles</a>
    </li>
@endsection

@section('nav-tabs')
    <li class="nav-tabV2">
        <a class="nav-tab__link nav-tab--active__link" href="{{ route('collectibles.index') }}">
            {{ __('common.view-all') }} Collectibles
        </a>
    </li>
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('collectibles.my_collectibles') }}">
            {{ __('common.my') }} Collectibles
        </a>
    </li>
@endsection

@section('page', 'page__stats--overview')

@section('main')
    @foreach ($collectibleCategories as $collectibleCategory)
        <section class="panelV2">
            <h2 class="panel__heading">{{ $collectibleCategory->name }}</h2>
            <div class="panel__body">
                <ul class="mediahub-card__list">
                    @forelse ($collectibleCategory->collectibles as $collectible)
                        <li class="mediahub-card__list-item">
                            <a
                                href="{{ route('collectibles.show', ['collectible' => $collectible]) }}"
                                class="mediahub-card"
                            >
                                <h2 class="mediahub-card__heading">
                                    <i>
                                        <img
                                            src="{{ $collectible->icon === null ? '' : route('authenticated_images.collectible_icon', ['collectible' => $collectible]) }}"
                                        />
                                    </i>
                                </h2>
                                <h3 class="mediahub-card__subheading">
                                    {{ $collectible->name }}
                                </h3>
                                <h4 class="mediahub-card__subheading">
                                    {{ $collectible->description }}
                                </h4>
                                <h4 class="mediahub-card__subheading">
                                    Is in stock?
                                    @if ($collectible->inStock())
                                        True
                                    @else
                                            False
                                    @endif
                                </h4>
                            </a>
                        </li>
                    @empty
                        No items in this {{ __('common.category') }}.
                    @endforelse
                </ul>
            </div>
        </section>
    @endforeach
@endsection
