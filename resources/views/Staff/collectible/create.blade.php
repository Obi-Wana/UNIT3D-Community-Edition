@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        <a href="{{ route('staff.collectibles.index') }}" class="breadcrumb__link">
            Badges
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('common.new-adj') }}
    </li>
@endsection

@section('page', 'page__badge--index')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">Add a new Item</h2>
        <div class="panel__body">
            <form
                class="form"
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('staff.collectibles.store') }}"
            >
                @csrf
                <p class="form__group">
                    <label for="icon" class="form__label">{{ __('common.image') }}</label>
                    <input class="form__file" type="file" name="icon" id="icon" />
                </p>
                <p class="form__group">
                    <input id="name" class="form__text" type="text" name="name" required />
                    <label class="form__label form__label--floating" for="name">Name</label>
                </p>
                <p class="form__group">
                    <input id="description" class="form__text" type="text" name="description" required />
                    <label class="form__label form__label--floating" for="description">Description</label>
                </p>
                <p class="form__group">
                    <select
                        id="category_id"
                        name="category_id"
                        class="form__select"
                        x-data="{ selected: {{ $collectibleCategoryId }} || '' }"
                        x-model="selected"
                        x-bind:class="selected === '' ? 'form__selected--default' : ''"
                        required
                    >
                        <option disabled hidden></option>
                        @foreach ($collectibleCategories as $collectibleCategory)
                            <option
                                value="{{ $collectibleCategory->id }}"
                                @selected($collectibleCategory->id === $collectibleCategoryId)
                            >
                                {{ $collectibleCategory->name }}
                            </option>
                        @endforeach
                    </select>
                    <label class="form__label form__label--floating" for="forum_category_id">
                        {{ __('common.category') }}
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="price"
                        class="form__text"
                        inputmode="numeric"
                        name="price"
                        pattern="[0-9]*"
                        required
                        type="text"
                    />
                    <label class="form__label form__label--floating" for="price">
                        BON per item
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="max_amount"
                        class="form__text"
                        inputmode="numeric"
                        name="max_amount"
                        pattern="[0-9]*"
                        required
                        type="text"
                    />
                    <label class="form__label form__label--floating" for="max_amount">
                        Amount of pieces for this item
                    </label>
                </p>
                <p class="form__group">
                    <input type="hidden" name="resell" value="0" />
                    <input
                        type="checkbox"
                        class="form__checkbox"
                        id="resell"
                        name="resell"
                        value="1"
                    />
                    <label class="form__label" for="resell">Allow resell?</label>
                </p>
                <p class="form__group">
                    <button class="form__button form__button--filled">{{ __('common.add') }}</button>
                </p>
            </forum>
        </div>
    </section>
@endsection
