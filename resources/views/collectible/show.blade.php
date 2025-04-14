@extends('layout.with-main')

@section('title')
    <title>Collectibles - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('collectibles.index') }}" class="breadcrumb__link">Collectibles</a>
    </li>
    <li class="breadcrumb--active">
        {{ $collectible->name }}
    </li>
@endsection

@section('page', 'page__stats--overview')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">{{ $collectible->name }}</h2>
        <i>
            <img
                src="{{ $collectible->icon === null ? '' : route('authenticated_images.collectible_icon', ['collectible' => $collectible]) }}"
            />
        </i>
        <dl class="key-value">
            <div class="key-value__group">
                <dt>Part of</dt>
                <dd>{{ $collectible->category->name }}</dd>
            </div>
            <div class="key-value__group">
                <dt>Name</dt>
                <dd>{{ $collectible->name }}</dd>
            </div>
            <div class="key-value__group">
                <dt>Description</dt>
                <dd>{{ $collectible->description }}</dd>
            </div>
            <div class="key-value__group">
                <dt>Initial Price</dt>
                <dd>{{ $collectible->price }} {{ __('bon.bon') }}</dd>
            </div>
        </dl>

        @if (! $userOwns && $collectible->inStock())
            <form
                role="form"
                method="POST"
                action="{{ route('collectibles.buy', ['collectible' => $collectible]) }}"
            >
                @csrf
                <p class="form__group form__group--horizontal">
                    <button class="form__button form__button--filled form__button--centered">
                        Buy from Store for
                        {{ \number_format($collectible->price, 0, null, "\u{202F}") }}
                        {{ __('bon.bon') }}
                    </button>
                </p>
            </form>
        @endif

        <p class="form__group form__group--horizontal">
            <a
                class="form__button form__button--filled form__button--centered"
                href="{{ route('collectibles.create_offer', ['collectible' => $collectible]) }}"
                role="button"
                @if (! $userOwns || $collectible->inStock() || $userIsSelling)
                    disabled
                @endif
            >
                Sell
            </a>
        </p>
    </section>

    <section class="panelV2" x-data="toggle">
        <h2 class="panel__heading" style="cursor: pointer" x-on:click="toggle">
            Price History
            <i
                class="{{ config('other.font-awesome') }} fa-plus-circle fa-pull-right"
                x-show="isToggledOff"
            ></i>
            <i
                class="{{ config('other.font-awesome') }} fa-minus-circle fa-pull-right"
                x-show="isToggledOn"
                x-cloak
            ></i>
        </h2>
        <div class="chart-wrapper" x-show="isToggledOn" x-cloak>
            <div style="padding: 25px">
                <canvas id="dailyPriceHistory"></canvas>
            </div>
        </div>
    </section>

    <section class="panelV2">
        <h2 class="panel__heading">Offers</h2>
        <div class="panel__body">
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>{{ __('user.user') }}</th>
                            <th>Price</th>
                            <th>{{ __('common.created_at') }}</th>
                            <th>{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td>{{ $offer->collectible->name }}</td>
                                <td>
                                    <x-user_tag
                                        :user="$offer->seller"
                                        :anon="$offer->seller->privacy?->private_profile"
                                    />
                                </td>
                                <td>{{ $offer->price }}</td>
                                <td>{{ $offer->created_at }}</td>
                                <td>
                                    <menu class="data-table__actions">
                                        <li class="data-table__action">
                                            <form
                                                action="{{ route('collectibles.accept_offer', ['collectibleOffer' => $offer]) }}"
                                                method="POST"
                                                x-data="confirmation"
                                            >
                                                @csrf
                                                <button
                                                    x-on:click.prevent="confirmAction"
                                                    data-b64-deletion-message="{{ base64_encode('Are you sure you want to accept this offer: ' . $offer->collectible->name . '?') }}"
                                                    class="form__button form__button--filled"
                                                    @if (auth()->id() === $offer->seller->id || $userOwns)
                                                        disabled
                                                    @endif
                                                >
                                                    Buy
                                                </button>
                                            </form>
                                        </li>
                                    </menu>
                                </td>
                            </tr>
                        @empty
                            No offers available.
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @vite('resources/js/vendor/chart.js')
    <script nonce="{{ HDVinnie\SecureHeaders\SecureHeaders::nonce('script') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const transactions = {!! Js::encode($transactions) !!};

            // Daily Donations Chart
            const dailyCtx = document.getElementById('dailyPriceHistory').getContext('2d');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: transactions.map((transaction) => transaction.created_at),
                    datasets: [
                        {
                            label: 'Price History last 20 Transactions',
                            data: transactions.map((transaction) => transaction.price),
                            borderWidth: 1,
                            fill: false,
                        },
                    ],
                },
                options: {
                    scales: {
                        x: { type: 'time', time: { unit: 'month' } },
                        y: { beginAtZero: true },
                    },
                },
            });
        });
    </script>
@endsection
