<?php
/**
 * Autor: Paula Aguirre Copyright (c) 2018.
 */

namespace App\Http\Controllers;

use App\Http\Requests\GerenciaFormRequest;
use Illuminate\Http\Request;
use App\Area;
use DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use App\User;
use App\Funcionario;
class GerenciaController extends Controller
{
    /**
     * GerenciaController constructor.
     */
    public function __construct ()
    {
        return $this->middleware ('roles: 1,2, 9 ')->except ('index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


       if ($request)
        {
            $query = trim ( $request->get ( 'searchText' ) );
            $gerencias = Area::with('user')
                ->where('tipo', '=', 'G')
                ->Where ('nombre', 'like', '%'.$query.'%')
                ->orderBy ('id', 'ASC')->paginate (8);
            return view ('gerencias.index', ['gerencias' => $gerencias, 'searchText' => $query]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $gerentes = DB::table ('users')
            ->get ();

        return view ('gerencias.create', ['gerentes'=>$gerentes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|unique:areas',
            'user_id'=> 'unique:areas'
        ]);

        $gerencia = new Area($request->all ());
        $gerencia->tipo = 'G';

        //Funcionario::where('user_id', '=', $gerencia->user_id)->first ();

        $gerencia->save ();

        $user = User::findOrFail ($gerencia->user_id);
        $user->role_id = 3;
        $user->update ();

        if ($funcionario = Funcionario::where('user_id', '=', $gerencia->user_id)->first ())
        {
            $funcionario->delete ();
        }

        return \redirect ('gerencias');
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
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $gerencia = Area::findOrFail ($id);
        $jefes = DB::table ('areas')->select ('user_id')
            ->where ('tipo', '=', 'D');
        //$funcionarios = DB::table ('funcionarios')->select ('user_id');

        $gerentes = DB::table ('users')
            //->whereNotIn ('id', $jefes)
            //->whereNotIn ('id', $funcionarios)
            ->get ();

        return view ('gerencias.edit', ['gerencia' => $gerencia, 'gerentes' => $gerentes]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $gerencia = Area::findOrFail ($id);

            $this->validate ($request,
            [
                'user_id'=>Rule::unique ('areas', 'user_id')->ignore ($id),
                'nombre'=>Rule::unique ('areas', 'nombre')->ignore ($id)
            ]);


            $gerente_anterior = $gerencia->user;
            $id_nuevo_gerente = $request->get ('user_id');

            if ($gerente_anterior->id == !$id_nuevo_gerente)
            {
                /**
                 * buscamos al gerente anterior para pasarle el nuevo rol de 'user'
                 */
                $gerente_anterior->role_id = 8;
                $gerente_anterior->update();

                /**
                 * buscamos al gerente nuevo para pasarle el nuevo rol de 'approver'
                 */
                $gerente_nuevo = User::find($id_nuevo_gerente);

                if ($gerente_nuevo->funcionario){
                    $gerente_nuevo->funcionario->delete();
                }

                $gerente_nuevo->role_id = 3;
                $gerente_nuevo->update();
            }

            $gerencia->fill($request->all ());
            $gerencia->update();

        return redirect ()->to ('gerencias');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $gerencia = Area::findOrFail ($id);
        $gerencia->delete ();

        return \redirect ('gerencias');
    }
}
