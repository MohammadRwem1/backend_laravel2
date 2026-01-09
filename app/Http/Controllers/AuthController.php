<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\UserRegister;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    private function uploadImage($file, $folder){
    if (!$file) {
        return null;
    }

    $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

    $path = 'uploads/' . $folder . '/' . $filename;
    $file->move(base_path('uploads/' . $folder), $filename);

    return $path; 
    }

    public function register(AuthRequest $request){
    $idImagePath = $this->uploadImage($request->file('id_image'),'id_image');
    $profileImagePath = $this->uploadImage($request->file('profile_image'),'profile_image');

    $role = strtolower($request->role) === 'owner' ? 'owner' : 'renter';

    $data = $request->validated();
    $data['id_image'] = $idImagePath;
    $data['profile_image'] = $profileImagePath;
    $data['password'] = Hash::make($data['password']);
    $data['role'] = $role;
    $data['status'] = 'pending';

    $user = User::create($data);

    $user->id_image = $user->id_image ? url($user->id_image) : null;
    $user->profile_image = $user->profile_image ? url($user->profile_image) : null;

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user
    ], 200);
    }
    

    public function login(Request $request){

        $request->validate([
            'phone'=>'required',
            'password'=>'required'
        ]);
        
        $user = User::where('phone',$request->phone)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message'=>'Invalid phone or password'], 401);
        }

        if ($user->status !== 'approved') {
            return response()->json([
            'message' => 'Account not approved yet'
        ], 403);
    }


        $token=$user->createToken('api_token')->plainTextToken;
        return response()->json(['message'=>'Logged in successfully',
        'user'=>$user,
        'token'=>$token], 200);

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message'=>'logged out successfully'], 200);
    }
}
