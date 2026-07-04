@extends('admin.layout')
@section('header_title', 'Formulários')
@section('header_subtitle', 'Editar: ' . $form->slug)
@section('content')
    @include('forms::admin.forms._form', ['action' => route('admin.forms.update', $form->id), 'form' => $form])
@endsection
