<x-business-layout title="Notificaciones">
    <div class="page-header"><div><h1 class="page-header-title">Notificaciones</h1><p class="page-header-sub">Información operativa del panel.</p></div></div>
    <div class="card">@foreach ($notifications as $notification)<div class="notif-item"><div class="notif-icon {{ $notification['class'] }}"><i class="bx {{ $notification['icon'] }}"></i></div><div><div class="notif-title">{{ $notification['title'] }}</div><div class="notif-body">{{ $notification['body'] }}</div></div></div>@endforeach</div>
</x-business-layout>
