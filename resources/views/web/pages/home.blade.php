@extends('web.layouts.app')

@section('title', 'JudgeArena - Track Your Competitive Programming Journey')
@section('description', 'Track coding progress across platforms, compare leaderboard rankings, and showcase your competitive programming profile with JudgeArena.')
@section('keywords', 'JudgeArena, competitive programming tracker, coding leaderboard, coding profile, programming analytics')

@section('content')
    @include('web.pages.sections.hero')
    @include('web.pages.sections.features')
    @include('web.pages.sections.cta')
@endsection
