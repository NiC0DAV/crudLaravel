<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth;
        $payload = $request->getContent();
        $payloadArr = json_decode($payload, true);

        //TODO Validar que tengamos los valores pertinentes en la peticion
        $validateRequestData = Validator::make($payloadArr, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //TODO Revisamos que la validacion de los datos de payload no falle
        if (!$validateRequestData->fails()) {

            //TODO Accedemos a nuestra clase JwtAuth para ingresar o buscar el metodo createToken y asi generar o no el JWT
            $jwtGen = $jwtAuth->createToken($payloadArr['email'], $payloadArr['password']);

            //TODO Validamos que la respuesta del metodo createToken sea exitosa.
            if (empty($jwtGen['code']) && !empty($jwtGen)) {
                $serviceResponse = array(
                    'code' => 200,
                    'accessToken' => $jwtGen,
                    'message' => 'Inicio de sesión exitoso.',
                    'date' => date('Y-m-d H:i:s')
                );
            } else {
                $serviceResponse = array(
                    'code' => 403,
                    'message' => 'Error al iniciar sesión',
                    'date' => date('Y-m-d H:i:s')
                );
            }
        } else {
            header("HTTP/1.1 404 NOT FOUND");
            $serviceResponse = array(
                'code' => 404,
                'status' => 'Error',
                'message' => 'Faltan parametros en la petición',
                'data' => $validateRequestData->errors(),
                'date' => date('Y-m-d H:i:s')
            );
        }

        return response()->json($serviceResponse, $serviceResponse['code']);
    }

    //CREATE
    public function register(Request $request)
    {
        $payload = $request->getContent();
        $payloadObj = json_decode($payload);
        $payloadArr = json_decode($payload, true);

        $validateRequestData = Validator::make($payloadArr, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if (!$validateRequestData->fails()) {
            $hashedPass = Hash::make($payloadArr['password']);
            // $surname = !$payloadArr['surname'] ? '' : $payloadArr['surname'];

            $user = new User;
            $user->name = $payloadArr['name'];
            // $user->surname = $surname;
            $user->email = $payloadArr['email'];
            $user->password = $hashedPass;
            $user->save();

            header("HTTP/1.1 200 USER CREATED");
            $serviceResponse = array(
                'code' => 200,
                'status' => 'Success',
                'message' => 'El usuario ha sido creado exitosamente.',
                'data' => $user
            );
        } else {
            header("HTTP/1.1 400 ERROR");
            $serviceResponse = array(
                'code' => 404,
                'status' => 'Error',
                'message' => 'El usuario no pudo ser registrado, por favor verifique la informacion ingresada.',
                'data' => $validateRequestData->errors(),
                'date' => date('Y-m-d H:i:s')
            );
        }

        return response()->json($serviceResponse, $serviceResponse['code']);
    }

    //READ
    public function listUsers()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json($users, 200);
    }

    //READ
    public function listUser($id = '')
    {
        if(!empty($id)){
            $user = User::find($id);
            if(is_object($user)){
                $serviceResponse = array(
                    'code' => 200,
                    'data' => $user,
                    'date' => date('Y-m-d H:i:s')
                );
            }else{
                $serviceResponse = array(
                    'code' => 404,
                    'message' => 'Usuario no encontrado.',
                    'date' => date('Y-m-d H:i:s')
                );
            }
        }else{
            $serviceResponse = array(
                'code' => 400,
                'message' => 'La peticion no pudo ser procesada.',
                'date' => date('Y-m-d H:i:s')
            );
        }

        return response()->json($serviceResponse, $serviceResponse['code']);
    }

    //UPDATE
    public function updateUser(Request $request, $id)
    {
        $payload = $request->getContent();
        $payload = json_decode($payload, true);

        unset($payload['id']);
        unset($payload['created_at']);

        User::where('id', $id)->update($payload);

        $serviceResponse = array(
            'code' => 200,
            'status' => 'Success',
            'message' => 'El usuario ha sido actualizado de manera exitosa.',
            'data' => $payload
        );

        return response()->json($serviceResponse, $serviceResponse['code']);
    }

    //DELETE
    public function deleteUser($id)
    {
        $user= User::find($id);
        $user->delete();

        $serviceResponse = array(
            'code' => 200,
            'status' => 'Success',
            'message' => 'El usuario ha sido eliminado de manera exitosa.'
        );

        return response()->json($serviceResponse, $serviceResponse['code']);

    }
}
