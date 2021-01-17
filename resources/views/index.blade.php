@extends('layouts.app')

@section('content')
    <app
        code="{{ $code }}"
        country="{{ $country }}"
        search-name="{{ $searchName }}"
    ></app>
@endsection