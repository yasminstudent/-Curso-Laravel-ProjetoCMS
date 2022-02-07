@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <h1>
        Meus Usuários
        <a href="{{route('users.create')}}" class="btn btn-sm btn-success">Novo Usuário</a>
    </h1>
@endsection

@section('content')
    <div class="card">
        <h5 class="card-header text-white bg-dark">CMS</h5>
        <div class="card-body">
           <table class="table table-hover">
               <thead>
                   <tr>
                       <th>ID</th>
                       <th>Nome</th>
                       <th>Email</th>
                       <th>Ações</th>
                   </tr>
               </thead>
               <tbody>
                   @foreach($users as $user)
                       <tr>
                           <td>{{$user->id}}</td>
                           <td>{{$user->name}}</td>
                           <td>{{$user->email}}</td>
                           <td>
                               <a href="{{route('users.edit', ['user' => $user->id])}}" class="btn btn-sm btn-info">Editar</a>
                               @if(intval($loggedID) !== intval($user->id))
                                   <form class="d-inline" method="POST" action="{{route('users.destroy', ['user' => $user->id])}}" onsubmit="return confirm('Tem certeza de que deseja excluir?')">
                                       @method('DELETE')
                                       @csrf
                                       <button class="btn btn-sm btn-danger" type="submit">Excluir</button>
                                   </form>
                               @endif
                           </td>
                       </tr>
                   @endforeach
               </tbody>
           </table>
       </div>
    </div>

    {{ $users->links()  }}
@endsection

