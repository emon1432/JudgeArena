@extends('web.layouts.app')
@section('content')
    <main class="landing-main">
        @include('web.pages.home.partials.hero')
        @include('web.pages.home.partials.platforms')
        @include('web.pages.home.partials.why-judgearena')
        @include('web.pages.home.partials.unified-profile')
        @include('web.pages.home.partials.analytics')
        @include('web.pages.home.partials.community')
        @include('web.pages.home.partials.statistics')
        @include('web.pages.home.partials.explore')
        @include('web.pages.home.partials.who-uses')
        @guest
            @include('web.pages.home.partials.cta')
        @endguest
    </main>
@endsection
