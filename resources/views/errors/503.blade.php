@extends('errors.layout')

@section('title', 'Back soon')
@section('eyebrow', 'Scheduled maintenance')
@section('code', '503')
@section('heading', 'We will be back shortly')
@section('message', 'The website is briefly down for maintenance. Please check again in a few minutes. Thank you for your patience.')

@section('actions')
    <a href="javascript:location.reload()" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">Check again</a>
@endsection
