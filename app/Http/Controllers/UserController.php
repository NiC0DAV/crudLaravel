<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(){

    }

    public function register(Request $request){
        // $payload = $request->getContent();
        // $payloadObj = json_decode($payload);
        // $payloadArr = json_decode($payload, true);
        $name = $request->input('name', null);
        $email = $request->input('email', null);
        $password = $request->input('password', null);

        $payloadArr = ["name" => $name, "email" => $email, "password" => $password];

        $validateRequestData = Validator::make($payloadArr, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if(!$validateRequestData->fails()){
            $hashedPass = Hash::make($payloadArr['password']);

            $user = new User;
            $user->name = $payloadArr['name'];
            // $user->surname = $payloadObj->surname;
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
        }else{
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

    public function updateUser($id){

    }

    public function deleteUser($id){

    }

    public function listUser($id){

    }

    public function listUsers(){

    }
}
