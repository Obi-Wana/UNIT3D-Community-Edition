@extends('layout.default')

@section('breadcrumb')
<li>
    <a href="{{ route('staff.dashboard.index') }}" itemprop="url" class="l-breadcrumb-item-link">
        <span itemprop="title" class="l-breadcrumb-item-link-title">{{ __('staff.staff-dashboard') }}</span>
    </a>
</li>
<li class="active">
    <a href="#" itemprop="url" class="l-breadcrumb-item-link">
        <span itemprop="title" class="l-breadcrumb-item-link-title">Blacklists</span>
    </a>
</li>
<li class="active">
    <a href="{{ route('staff.blacklists.releasegroups.index') }}" itemprop="url" class="l-breadcrumb-item-link">
        <span itemprop="title" class="l-breadcrumb-item-link-title">Clients</span>
    </a>
</li>
    <li class="active">
        <a href="{{ route('staff.blacklists.releasegroups.create') }}" itemprop="url" class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">{{ __('common.add') }} Blacklist</span>
        </a>
    </li>
@endsection

@section('content')
    <div class="container box">
        <h2>{{ __('common.add') }} Blacklisted Client</h2>
        <div class="table-responsive">
            <form role="form" method="POST" action="{{ route('staff.blacklists.clients.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>{{ __('common.name') }}</th>
                            <th>Reason</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>
                                <label>
                                    <input type="text" class="form-control" name="name" placeholder="Transmission/2.93" required>
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="text" class="form-control" name="reason" placeholder="Security Vulnerabilities">
                                </label>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-default">{{ __('common.submit') }}</button>
            </form>
        </div>
    </div>
@endsection