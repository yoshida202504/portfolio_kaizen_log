@extends('errors.layout')

@section('code', '403')
@section('title', 'アクセスできません')
@section('message')
    @php($reason = $exception->getMessage())
    {{ $reason && $reason !== 'This action is unauthorized.' ? $reason : 'このページを表示する権限がありません。非公開の日報は、投稿者本人か相互フォローしているユーザーだけが閲覧できます。' }}
@endsection
