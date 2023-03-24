<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use Illuminate\Support\Facades\Hash;
use Throwable;

Class JwtAuth{
    private $secretKey = 'ABC123456';

    public function createToken($email, $password, $getToken = null){
        $login = false;

        $user = User::where([
            'email' => $email
        ])->first();

        try{
            $hashedPass = Hash::check($password, $user->password);
            if($hashedPass == true && is_object($user)){
                $login = true;
            }
        }catch(Throwable $th){
            $login = false;
        }

        if($login){
            $data = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'iat' => time(),
                'exp' => time() + (60 * 60)
            );

            $jwtToken = JWT::encode($data, $this->secretKey, 'HS256');

            $decodedToken = JWT::decode($jwtToken, new key($this->secretKey, 'HS256'));

            if($getToken == null){
                $data = $jwtToken;
            }else{
                $data = $decodedToken;
            }
        }else{
            $data = array(
                'code' => 403,
                'status' => 'Error',
                'message' => 'Intento de inicio de sesión incorrecto'
            );
        }

        return $data;
    }


    public function checkToken($jwt, $getIdentity = false)
    {
        $response = false;

        try {
            if (strpos($jwt, 'Bearer') !== false) {
                $jwt = str_replace(array('"'), '', $jwt);
                $jwt = ltrim($jwt, 'Bearer');
                $jwt = ltrim($jwt, ' ');
            } else {
                $jwt = str_replace(array('"'), '', $jwt);
            }

            $decoded = JWT::decode($jwt, new key($this->secretKey, 'HS256'));
        } catch (DomainException $e) {
            $response = 'Unsupported algorithm or bad key was specified';
        } catch (ExpiredException $e) {
            $response = 'Expired token';
        } catch (InvalidArgumentException $e) {
            $response = 'Key may not be empty';
        } catch (UnexpectedValueException $e) {
            $response = 'El JWT proporcionado no es valido.';
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->name) && isset($decoded->email)) {
            $response = true;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $response;
    }
}
