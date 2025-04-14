@extends('layout.with-main-and-sidebar')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        Badges
    </li>
@endsection

@section('page', 'page__badge--index')

@section('main')
    @foreach ($collectibleCategories as $collectibleCategory)
        <section class="panelV2">
            <header class="panel__header">
                <h2 class="panel__heading">{{ $collectibleCategory->name }}</h2>
                <div class="panel__actions">
                    <div class="panel__action">
                        <a
                            class="form__button form__button--text"
                            href="{{ route('staff.collectibles.create', ['collectibleCategoryId' => $collectibleCategory->id]) }}"
                        >
                            {{ __('common.add') }} Item
                        </a>
                    </div>
                    <div class="panel__action">
                        <a
                            href="{{ route('staff.collectible_categories.edit', ['collectibleCategory' => $collectibleCategory]) }}"
                            class="form__button form__button--text"
                        >
                            {{ __('common.edit') }} {{ __('common.category') }}
                        </a>
                    </div>
                    <form
                        action="{{ route('staff.collectible_categories.destroy', ['collectibleCategory' => $collectibleCategory]) }}"
                        method="POST"
                        style="display: contents"
                        x-data="confirmation"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            class="form__button form__button--text"
                            x-on:click.prevent="confirmAction"
                            data-b64-deletion-message="{{ base64_encode('Are you sure you want to delete this category (' . $collectibleCategory->name . ') and all its items?') }}"
                        >
                            {{ __('common.delete') }}
                        </button>
                    </form>
                </div>
            </header>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('common.name') }}</th>
                            <th>Price</th>
                            <th>Allow resell?</th>
                            <th>{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($collectibleCategory->collectibles as $collectible)
                            <tr>
                                <td>
                                    <a href="">
                                        {{ $collectible->name }}
                                    </a>
                                </td>
                                <td>
                                    {{ $collectible->price }}
                                </td>
                                <td>
                                    @if ($collectible->resell)
                                        <i
                                            class="{{ config('other.font-awesome') }} fa-check text-green"
                                        ></i>
                                    @else
                                        <i
                                            class="{{ config('other.font-awesome') }} fa-times text-red"
                                        ></i>
                                    @endif
                                </td>
                                <td>
                                    <menu class="data-table__actions">
                                        <li class="data-table__action">
                                            <a
                                                class="form__button form__button--text"
                                                href="{{ route('staff.collectibles.edit', ['collectible' => $collectible]) }}"
                                            >
                                                {{ __('common.edit') }}
                                            </a>
                                        </li>
                                        <li class="data-table__action">
                                            <form
                                                method="POST"
                                                action=""
                                                x-data="confirmation"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    x-on:click.prevent="confirmAction"
                                                    data-b64-deletion-message="{{ base64_encode('Are you sure you want to delete this item?') }}"
                                                    class="form__button form__button--text"
                                                >
                                                    {{ __('common.delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </menu>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
@endsection

@section('sidebar')
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('common.actions') }}</h2>
        <div class="panel__body">
            <p class="form__group form__group--horizontal">
                <a
                    href="{{ route('staff.collectible_categories.create') }}"
                    class="form__button form__button--filled form__button--centered"
                >
                    Create new {{ __('common.category') }}
                </a>
            </p>
        </div>
    </section>
@endsection
