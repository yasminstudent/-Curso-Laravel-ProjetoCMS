<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
USE App\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:edit-users');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $users = User::paginate(10);
        $loggedID = Auth::id();
        return view('admin.users.index', ['users' => $users, 'loggedID' => $loggedID]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only([
           'name',
           'email',
           'password',
           'password_confirmation'
        ]);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        if($validator->fails()){
            return  redirect()->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return  redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return View | RedirectResponse
     */
    public function edit($id)
    {
        $user = User::find($id);

        if($user){
            return view('admin.users.edit', ['user' => $user]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if($user){
            $data = $request->only([
               'name',
               'email',
               'password',
               'password_confirmation'
            ]);

            //validação inicial (nome e email)
            $validator = Validator::make([
                'name' => $data['name'],
                'email' => $data['email']
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255']
            ]);

            //alteração do nome
            $user->name = $data['name'];

            //alteração do email
            if($user->email != $data['email']){ //verifica se o email foi alterado
                //verifica se o novo email não pertence a um outro usuário
                $hasEmail = User::where('email', $data['email'])->get();
                if(count($hasEmail) === 0){
                    $user->email = $data['email'];
                }
                else{
                    $validator->errors()->add('email', __('validation.unique', [
                        'attribute' => 'email',
                    ]));
                }
            }

            //alteração da senha
            if(!empty($data['password'])){
                if(strlen($data['password']) < 4){
                    $validator->errors()->add('password', __('validation.min.string', [
                        'attribute' => 'password',
                        'min' => 4
                    ]));
                }
                elseif ($data['password'] !== $data['password_confirmation']){
                    $validator->errors()->add('password', __('validation.confirmed', [
                        'attribute' => 'password',
                    ]));
                }
                else{
                    $user->password = Hash::make($data['password']);
                }
            }

            if(count($validator->errors()) > 0){
                return redirect()->route('users.edit', ['user' => $id])
                    ->withErrors($validator);
            }

            $user->save();
        }

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $loggedId = intval(Auth::id());

        if($loggedId !== intval($id)){
            $user = User::find($id);
            $user->delete();
        }

        return redirect()->route('users.index');
    }
}
