<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function show($id)
    {
        $user = $this->user->findOrFail($id);

        return view('users.profile.show')
                ->with('user', $user);
    }

    public function edit()
    {
        $user = $this->user->findOrFail(Auth::user()->id);

        return view('users.profile.edit')
                ->with('user', $user);
    }

    // Change user's Information
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . Auth::user()->id,
            'avatar' => 'mimes:jpeg,png,jpg,gif|max:1048',
            'country' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->country = $request->country;
        $user->phone = $request->phone;

        if($request->hasFile('avatar')){
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('profile.show', Auth::user()->id);
    }

    // Change password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentpassword' => 'required',
            'newpassword' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if(!Hash::check($request->currentpassword, $user->password)){
            return back()->withErrors(['currentpassword' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->newpassword);
        $user->save();

        return back()->with('status', 'Password updated successfully.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        //logout from the session 
        Auth::logout();

        // delete user
        $user->delete();

        return redirect('/home');

    }
}
