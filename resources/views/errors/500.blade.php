@extends('errors.layout')

@section('title', 'Something went wrong')
@section('eyebrow', 'Error 500')
@section('code', '500')
@section('heading', 'Something went wrong on our side')
@section('message', 'We hit an unexpected problem. It has been noted and we are on it. Please try again in a moment.')

@section('actions')
    <a href="javascript:location.reload()" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">Try again</a>
    <a href="/" class="btn-ghost">Back to home</a>
@endsection
