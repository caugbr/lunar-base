@extends('admin.layout')
@section('header_title', 'Formulários')
@section('header_subtitle', 'Criar novo formulário')
@section('content')
    @include('admin.forms._form', ['action' => route('admin.forms.store')])
@endsection
