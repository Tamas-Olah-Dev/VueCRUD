@extends('layouts.app')
@section('content')
    @if((isset($backlink)) && ($backlink != null))
        <a style="font-weight: bold; color: blue; text-decoration: underline" href="{{ $backlink['url'] }}">{{ $backlink['label'] }}</a>
    @endif

    @include('packages.vue-crud.model-manager-inner')
@endsection