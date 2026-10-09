@extends('errors.layout')

@section('title', 'Page expired')
@section('eyebrow', 'Error 419')
@section('code', '419')
@section('heading', 'This page has expired')
@section('message', 'Your session timed out before the form was sent. Nothing was submitted. Please go back, refresh the page and try again.')

@section('actions')
    <a href="javascript:history.back()" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">Go back and retry</a>
    <a href="/" class="btn-ghost">Back to home</a>
@endsection
