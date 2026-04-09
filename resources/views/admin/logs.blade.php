@extends('Layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card mb-4 shadow-sm border-0  rounded-4"> 
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h1>Aktivitas sistem</h1>
                <div class="card-body p-0">
                    <div class="table responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection