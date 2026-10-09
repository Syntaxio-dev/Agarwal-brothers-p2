@extends('errors.layout')

@section('title', 'Too many requests')
@section('eyebrow', 'Error 429')
@section('code', '429')
@section('heading', 'Too many requests')
@section('message', 'You have sent a lot of requests in a short time. Please wait a minute and try again.')

@section('actions')
    <a href="javascript:history.back()" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">Go back</a>
    <a href="/" class="btn-ghost">Back to home</a>
@endsection
